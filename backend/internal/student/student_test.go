package student

import (
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"
	"golang.org/x/crypto/bcrypt"
	gormmysql "gorm.io/driver/mysql"
	"gorm.io/driver/sqlite"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
	"image"
	"image/png"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"testing"
	"time"
)

const testSecret = "student-bridge-unit-test-secret-32-bytes"

func fixture(t *testing.T) *gorm.DB {
	t.Helper()
	db, err := gorm.Open(sqlite.Open(filepath.Join(t.TempDir(), "students.sqlite")+"?_foreign_keys=on"), &gorm.Config{Logger: logger.Default.LogMode(logger.Silent)})
	if err != nil {
		t.Fatal(err)
	}
	if os.Getenv("STUDENT_TEST_MYSQL") == "1" {
		sqliteConnection, _ := db.DB()
		sqliteConnection.Close()
		local, e := gorm.Open(gormmysql.Open("root@tcp(127.0.0.1:3307)/?parseTime=true"), &gorm.Config{Logger: logger.Default.LogMode(logger.Silent)})
		if e != nil {
			t.Fatal("isolated MySQL on port 3307 is required")
		}
		name := fmt.Sprintf("sabilillah_students_test_%d", time.Now().UnixNano())
		if e = local.Exec("CREATE DATABASE " + name + " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci").Error; e != nil {
			t.Fatal(e)
		}
		db, e = gorm.Open(gormmysql.Open("root@tcp(127.0.0.1:3307)/"+name+"?parseTime=true"), &gorm.Config{Logger: logger.Default.LogMode(logger.Silent)})
		if e != nil {
			t.Fatal(e)
		}
		t.Cleanup(func() { local.Exec("DROP DATABASE " + name); connection, _ := local.DB(); connection.Close() })
	}
	sqlDB, _ := db.DB()
	sqlDB.SetMaxOpenConns(1)
	t.Cleanup(func() { sqlDB.Close() })
	fields := []string{"id_siswa INTEGER PRIMARY KEY AUTOINCREMENT", "created_at DATETIME DEFAULT CURRENT_TIMESTAMP", "foto TEXT"}
	for _, f := range Fields() {
		typ := "TEXT"
		if f.Kind == "number" {
			typ = "INTEGER"
		}
		suffix := ""
		if f.Name == "nisn" {
			suffix = " UNIQUE"
		}
		if f.Name == "status_siswa" {
			suffix = " DEFAULT 'aktif'"
		}
		fields = append(fields, f.Name+" "+typ+suffix)
	}
	statements := []string{
		"CREATE TABLE siswa (" + strings.Join(fields, ",") + ")",
		`CREATE TABLE users (id_user INTEGER PRIMARY KEY AUTOINCREMENT,username TEXT UNIQUE,password TEXT,password_plain TEXT,nama_lengkap TEXT,role TEXT,id_ref INTEGER,status TEXT DEFAULT 'aktif')`,
		`CREATE TABLE tp (id_tp INTEGER PRIMARY KEY,nama_tp TEXT)`,
		`CREATE TABLE semester (id_semester INTEGER PRIMARY KEY,id_tp INTEGER,semester TEXT)`,
		`CREATE TABLE kelas (id_kelas INTEGER PRIMARY KEY,id_tp INTEGER,nama_kelas TEXT)`,
		`CREATE TABLE wali_kelas (id_walikelas INTEGER PRIMARY KEY,id_guru INTEGER,id_kelas INTEGER,id_tp INTEGER)`,
		`CREATE TABLE keanggotaan_kelas (id_keanggotaan INTEGER PRIMARY KEY,id_siswa INTEGER REFERENCES siswa(id_siswa) ON DELETE RESTRICT,id_kelas INTEGER,id_tp INTEGER)`,
		`CREATE TABLE pengaturan_aplikasi (id INTEGER PRIMARY KEY,nama_aplikasi TEXT,field_siswa_config TEXT,google_refresh_token TEXT,google_drive_folder_id TEXT)`,
		`CREATE TABLE pengaturan_sistem (key_name TEXT,value TEXT)`,
		`CREATE TABLE pengaturan_dokumen (kode TEXT,nama TEXT,wajib_siswa INTEGER,aktif INTEGER,urutan INTEGER)`,
		`CREATE TABLE siswa_dokumen (id_dokumen INTEGER PRIMARY KEY AUTOINCREMENT,id_siswa INTEGER REFERENCES siswa(id_siswa) ON DELETE CASCADE,jenis_dokumen TEXT,nama_file TEXT,path_file TEXT,ukuran INTEGER,status TEXT,catatan TEXT,uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,drive_file_id TEXT,drive_url TEXT)`,
		`CREATE TABLE absensi (id INTEGER PRIMARY KEY,id_siswa INTEGER REFERENCES siswa(id_siswa) ON DELETE RESTRICT)`,
		`CREATE TABLE pembayaran_transaksi (id INTEGER PRIMARY KEY,id_siswa INTEGER)`,
		`INSERT INTO tp VALUES (1,'Lama'),(2,'Aktif')`,
		`INSERT INTO semester VALUES (1,1,'Ganjil'),(2,2,'Ganjil')`,
		`INSERT INTO kelas VALUES (1,1,'Lama'),(2,2,'VII A'),(3,2,'VII B')`,
		`INSERT INTO siswa (id_siswa,nisn,nama_siswa,jenis_kelamin,ayah_no_hp,ibu_no_hp) VALUES (1,'0100000001','Siswa Satu','L','0811','0822'),(2,'0100000002','Siswa Dua','P','0833','0844'),(3,'legacy','Alumni','L',NULL,NULL)`,
		`UPDATE siswa SET status_siswa='lulus' WHERE id_siswa=3`,
		`INSERT INTO users (id_user,username,nama_lengkap,role,id_ref,status) VALUES (1,'admin','Admin','admin',0,'aktif'),(2,'walas','Walas','wali_kelas',10,'aktif'),(3,'0100000001','Siswa Satu','siswa',1,'aktif'),(4,'0100000002','Siswa Dua','siswa',2,'aktif')`,
		`INSERT INTO wali_kelas VALUES (1,10,2,2)`,
		`INSERT INTO keanggotaan_kelas VALUES (10,1,1,1),(20,1,3,2),(30,1,2,2),(40,2,3,2)`,
		`INSERT INTO pengaturan_aplikasi VALUES (1,'Madrasah','{}','refresh-test','folder-test')`,
		`INSERT INTO pengaturan_dokumen VALUES ('kartu_keluarga','Kartu Keluarga',1,1,1),('lama','Lama',0,0,2)`,
	}
	for _, sql := range statements {
		if db.Dialector.Name() == "mysql" {
			sql = strings.NewReplacer("AUTOINCREMENT", "AUTO_INCREMENT", "nisn TEXT UNIQUE", "nisn VARCHAR(20) UNIQUE", "username TEXT UNIQUE", "username VARCHAR(50) UNIQUE").Replace(sql)
		}
		if err := db.Exec(sql).Error; err != nil {
			t.Fatal(err)
		}
	}
	return db
}

var admin = Principal{1, "admin", 0, 2, 2}
var walas = Principal{2, "wali_kelas", 10, 2, 2}
var pupil = Principal{3, "siswa", 1, 2, 2}

type mockStorage struct {
	fail    bool
	deletes []string
	uploads int
}

func (m *mockStorage) UploadPhoto(ctx context.Context, id uint, data []byte, mime string) (string, error) {
	if m.fail {
		return "", remoteError("Storage unavailable")
	}
	m.uploads++
	return fmt.Sprintf("https://r2.example/%d.jpg", m.uploads), nil
}
func (m *mockStorage) DeletePhoto(ctx context.Context, url string) error {
	m.deletes = append(m.deletes, url)
	return nil
}
func (m *mockStorage) UploadDocument(ctx context.Context, row map[string]interface{}, kind, name string, data []byte, mime string) (RemoteFile, error) {
	if m.fail {
		return RemoteFile{}, remoteError("Drive unavailable")
	}
	m.uploads++
	id := fmt.Sprint(m.uploads)
	return RemoteFile{id, "https://lh3.googleusercontent.com/d/" + id}, nil
}
func (m *mockStorage) DeleteDocument(ctx context.Context, id string) error {
	m.deletes = append(m.deletes, id)
	return nil
}
func (m *mockStorage) ReadDocument(ctx context.Context, doc Document) ([]byte, string, error) {
	return []byte("%PDF-1.4\n%%EOF"), "application/pdf", nil
}
func statusOf(err error) int {
	var e *APIError
	if errors.As(err, &e) {
		return e.Status
	}
	return 500
}
func TestListAndPermissions(t *testing.T) {
	db := fixture(t)
	r := NewRepository(db)
	ctx := context.Background()
	o := ListOptions{Limit: 25, Status: "aktif", Sort: "nama_siswa"}
	rows, total, err := r.List(ctx, admin, o)
	if err != nil || total != 2 || len(rows) != 2 {
		t.Fatalf("list: %v %v", total, err)
	}
	for _, row := range rows {
		if row.ID == 1 && (row.ClassName == nil || *row.ClassName != "VII A") {
			t.Fatal("wrong current membership")
		}
	}
	for _, q := range []struct {
		text  string
		id    uint
		total int64
	}{{"0100000001", 1, 1}, {"Siswa Dua", 2, 1}, {"%", 0, 0}} {
		o.Query = q.text
		rows, total, err = r.List(ctx, admin, o)
		if err != nil || total != q.total || q.total > 0 && rows[0].ID != q.id {
			t.Fatal("search failed", q.text, err)
		}
	}
	o.Query = ""
	o.Class = 3
	_, total, err = r.List(ctx, admin, o)
	if err != nil || total != 1 {
		t.Fatal("class filter failed")
	}
	o.Class = 0
	rows, total, err = r.List(ctx, walas, o)
	if err != nil || total != 1 || rows[0].ID != 1 {
		t.Fatal("walas list leaked another class")
	}
	if statusOf(r.Authorize(ctx, walas, 2)) != 403 || statusOf(r.Authorize(ctx, pupil, 2)) != 403 {
		t.Fatal("row permissions failed")
	}
	data, err := r.Detail(ctx, pupil, 1)
	if err != nil {
		t.Fatal(err)
	}
	if _, ok := data["password_plain"]; ok {
		t.Fatal("detail contains password")
	}
	meta, err := r.Metadata(ctx, walas)
	if err != nil || meta["stats"].(Statistics).Total != 1 {
		t.Fatalf("metadata scope: %v", err)
	}
	o.Status = "lulus"
	rows, _, err = r.List(ctx, admin, o)
	if err != nil || len(rows) != 1 || rows[0].ClassName != nil {
		t.Fatal("no membership must remain unassigned")
	}
}
func TestUpdatesAndAccounts(t *testing.T) {
	db := fixture(t)
	s := NewService(NewRepository(db), &mockStorage{})
	ctx := context.Background()
	if err := s.Update(ctx, pupil, 1, map[string]interface{}{"alamat": "Baru"}); err != nil {
		t.Fatal(err)
	}
	row, _ := s.Repo.Detail(ctx, pupil, 1)
	if row["ayah_no_hp"] != "0811" || row["nama_siswa"] != "Siswa Satu" {
		t.Fatal("PATCH erased untouched fields")
	}
	for _, key := range []string{"nisn", "ayah_no_hp", "ibu_no_hp", "status_siswa", "foto", "password", "id_siswa"} {
		if statusOf(s.Update(ctx, pupil, 1, map[string]interface{}{key: "tamper"})) != 422 {
			t.Fatalf("protected field writable: %s", key)
		}
	}
	if statusOf(s.Update(ctx, walas, 2, map[string]interface{}{"nama_siswa": "Tidak boleh"})) != 403 {
		t.Fatal("walas can write other class")
	}
	if statusOf(s.Update(ctx, admin, 1, map[string]interface{}{"nisn": "0100000002"})) != 422 {
		t.Fatal("duplicate NISN allowed")
	}
	if err := s.Update(ctx, admin, 3, map[string]interface{}{"nisn": "legacy", "nama_siswa": "Alumni Baru"}); err != nil {
		t.Fatal("unchanged legacy NISN", err)
	}
	if err := s.Update(ctx, walas, 1, map[string]interface{}{"nama_siswa": "Nama Baru", "nisn": "0100000011", "password": "secret123"}); err != nil {
		t.Fatal(err)
	}
	var account struct{ Username, Password, PasswordPlain, NamaLengkap string }
	db.Table("users").Where("id_user=3").Take(&account)
	if account.Username != "0100000011" || account.NamaLengkap != "Nama Baru" || account.PasswordPlain != "secret123" || bcrypt.CompareHashAndPassword([]byte(account.Password), []byte("secret123")) != nil {
		t.Fatal("account sync failed")
	}
	cmd := exec.Command("php", "-n", "-r", `exit(password_verify('secret123',stream_get_contents(STDIN)) ? 0 : 1);`)
	cmd.Stdin = strings.NewReader(account.Password)
	if err := cmd.Run(); err != nil {
		t.Fatal("PHP cannot verify Go bcrypt", err)
	}
	if statusOf(s.Update(ctx, admin, 1, map[string]interface{}{"password": strings.Repeat("é", 37)})) != 422 {
		t.Fatal("bcrypt byte limit missing")
	}
}
func TestCreateAndRollback(t *testing.T) {
	db := fixture(t)
	s := NewService(NewRepository(db), &mockStorage{})
	ctx := context.Background()
	input := map[string]interface{}{"nisn": "0100000099", "nama_siswa": "Baru", "jenis_kelamin": "P", "password": "secret123"}
	id, err := s.Create(ctx, admin, input)
	if err != nil || id == 0 {
		t.Fatal(err)
	}
	var count int64
	db.Table("users").Where("role='siswa' AND id_ref=?", id).Count(&count)
	if count != 1 {
		t.Fatal("missing account")
	}
	if _, err = s.Create(ctx, walas, input); statusOf(err) != 403 {
		t.Fatal("nonadmin create allowed")
	}
	trigger := `CREATE TRIGGER block_account BEFORE INSERT ON users BEGIN SELECT RAISE(ABORT,'account unavailable'); END`
	if db.Dialector.Name() == "mysql" {
		trigger = `CREATE TRIGGER block_account BEFORE INSERT ON users FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='account unavailable'`
	}
	if err := db.Exec(trigger).Error; err != nil {
		t.Fatal(err)
	}
	input["nisn"] = "0100000098"
	if _, err = s.Create(ctx, admin, input); err == nil {
		t.Fatal("expected insertion failure")
	}
	db.Table("siswa").Where("nisn='0100000098'").Count(&count)
	if count != 0 {
		t.Fatal("student survived failed account transaction")
	}
}
func TestDeletePreservesHistory(t *testing.T) {
	db := fixture(t)
	s := NewService(NewRepository(db), &mockStorage{})
	ctx := context.Background()
	db.Exec("INSERT INTO absensi VALUES (1,1)")
	db.Exec("INSERT INTO pembayaran_transaksi VALUES (1,1)")
	if statusOf(s.Delete(ctx, admin, 1)) != 409 {
		t.Fatal("history should block deletion")
	}
	var count int64
	db.Table("absensi").Where("id_siswa=1").Count(&count)
	if count != 1 {
		t.Fatal("partial deletion occurred")
	}
	if statusOf(s.Delete(ctx, pupil, 1)) != 403 {
		t.Fatal("student delete allowed")
	}
	if err := s.Delete(ctx, admin, 2); err != nil {
		t.Fatal(err)
	}
	trigger := `CREATE TRIGGER block_delete BEFORE DELETE ON siswa WHEN OLD.id_siswa=1 BEGIN SELECT RAISE(ABORT,'blocked'); END`
	if db.Dialector.Name() == "mysql" {
		trigger = `CREATE TRIGGER block_delete BEFORE DELETE ON siswa FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='blocked'`
	}
	if err := db.Exec(trigger).Error; err != nil {
		t.Fatal(err)
	}
	db.Exec("DELETE FROM pembayaran_transaksi")
	if err := s.Delete(ctx, admin, 1); err == nil {
		t.Fatal("expected final failure")
	}
	db.Table("absensi").Count(&count)
	if count != 1 {
		t.Fatal("child rollback failed")
	}
}
func pngData(t *testing.T) []byte {
	t.Helper()
	var b bytes.Buffer
	if err := png.Encode(&b, image.NewRGBA(image.Rect(0, 0, 2, 2))); err != nil {
		t.Fatal(err)
	}
	return b.Bytes()
}
func TestDocumentsAndStorageFailures(t *testing.T) {
	db := fixture(t)
	storage := &mockStorage{}
	s := NewService(NewRepository(db), storage)
	ctx := context.Background()
	pdf := []byte("%PDF-1.4\n%%EOF")
	if err := s.UploadDocument(ctx, pupil, 1, "kartu_keluarga", "kk.pdf", pdf); err != nil {
		t.Fatal(err)
	}
	docs, err := s.Repo.Documents(ctx, 1)
	if err != nil || len(docs) != 1 || *docs[0].Status != "pending" {
		t.Fatal("document save", err)
	}
	storage.fail = true
	if err = s.UploadDocument(ctx, pupil, 1, "kartu_keluarga", "new.pdf", pdf); err == nil {
		t.Fatal("expected storage failure")
	}
	after, _ := s.Repo.Documents(ctx, 1)
	if len(after) != 1 || after[0].Name != "kk.pdf" || *after[0].DriveID != *docs[0].DriveID {
		t.Fatal("failed upload overwrote document")
	}
	storage.fail = false
	if err = s.UploadDocument(ctx, walas, 1, "kartu_keluarga", "new.pdf", pdf); err != nil {
		t.Fatal(err)
	}
	if len(storage.deletes) != 1 {
		t.Fatal("old object not removed")
	}
	if statusOf(s.UploadDocument(ctx, pupil, 2, "kartu_keluarga", "file.pdf", pdf)) != 403 {
		t.Fatal("other document writable")
	}
	if statusOf(s.UploadDocument(ctx, pupil, 1, "lama", "file.pdf", pdf)) != 422 {
		t.Fatal("inactive kind accepted")
	}
	if statusOf(s.DeleteDocument(ctx, pupil, 1, docs[0].ID)) != 403 {
		t.Fatal("student can delete document")
	}
	for _, data := range [][]byte{bytes.Repeat([]byte{'x'}, 2*1024*1024+1), []byte("not a pdf")} {
		if _, err := validateDocument(data); err == nil {
			t.Fatal("invalid file accepted")
		}
	}
	if _, err := s.UploadPhoto(ctx, walas, 1, pngData(t)); statusOf(err) != 403 {
		t.Fatal("walas photo allowed")
	}
	if _, err := s.UploadPhoto(ctx, admin, 1, pngData(t)); err != nil {
		t.Fatal(err)
	}
	if err := s.DeleteDocument(ctx, walas, 1, docs[0].ID); err != nil {
		t.Fatal(err)
	}
}
func token(t *testing.T, p Principal, mutate func(*Claims)) string {
	t.Helper()
	now := time.Now()
	c := Claims{UserID: p.UserID, Role: p.Role, Ref: p.Ref, TP: p.TP, Semester: p.Semester, RegisteredClaims: jwt.RegisteredClaims{Issuer: "sabilillah-php", Audience: jwt.ClaimStrings{"student-data"}, IssuedAt: jwt.NewNumericDate(now), ExpiresAt: jwt.NewNumericDate(now.Add(2 * time.Minute))}}
	if mutate != nil {
		mutate(&c)
	}
	v, err := jwt.NewWithClaims(jwt.SigningMethodHS256, c).SignedString([]byte(testSecret))
	if err != nil {
		t.Fatal(err)
	}
	return v
}
func TestTokenAndHTTP(t *testing.T) {
	gin.SetMode(gin.TestMode)
	db := fixture(t)
	router := gin.New()
	RegisterService(router.Group("/api/v1"), NewService(NewRepository(db), &mockStorage{}), testSecret)
	request := func(method, path, auth, body string) *httptest.ResponseRecorder {
		req := httptest.NewRequest(method, path, strings.NewReader(body))
		req.Header.Set("Authorization", "Bearer "+auth)
		req.Header.Set("Content-Type", "application/json")
		out := httptest.NewRecorder()
		router.ServeHTTP(out, req)
		return out
	}
	raw := token(t, admin, nil)
	out := request("GET", "/api/v1/students", raw, "")
	if out.Code != 200 || out.Header().Get("Cache-Control") != "no-store" {
		t.Fatal(out.Code, out.Body.String())
	}
	if strings.Contains(out.Body.String(), "password") || strings.Contains(out.Body.String(), "alamat") {
		t.Fatal("list leaks data")
	}
	for _, path := range []string{"?limit=101", "?sort=password", "?offset=-1"} {
		if request("GET", "/api/v1/students"+path, raw, "").Code != 400 {
			t.Fatal("invalid query accepted")
		}
	}
	if request("GET", "/api/v1/students/2", token(t, walas, nil), "").Code != 403 {
		t.Fatal("row permissions failed")
	}
	if request("GET", "/api/v1/students", token(t, pupil, nil), "").Code != 403 {
		t.Fatal("student can list others")
	}
	if out = request("GET", "/api/v1/students/me", token(t, pupil, nil), ""); out.Code != 200 {
		t.Fatal("self route", out.Code, out.Body.String())
	}
	for _, mutate := range []func(*Claims){func(c *Claims) { c.Audience = jwt.ClaimStrings{"ai"} }, func(c *Claims) { c.Issuer = "wrong" }, func(c *Claims) {
		c.IssuedAt = jwt.NewNumericDate(time.Now().Add(-time.Hour))
		c.ExpiresAt = jwt.NewNumericDate(time.Now().Add(-time.Minute))
	}, func(c *Claims) { c.ExpiresAt = jwt.NewNumericDate(time.Now().Add(time.Hour)) }} {
		if request("GET", "/api/v1/students", token(t, admin, mutate), "").Code != 401 {
			t.Fatal("invalid token accepted")
		}
	}
	db.Exec("UPDATE users SET status='nonaktif' WHERE id_user=3")
	if request("GET", "/api/v1/students/me", token(t, pupil, nil), "").Code != 401 {
		t.Fatal("disabled account accepted")
	}
	db.Exec("INSERT INTO pembayaran_transaksi VALUES (1,1)")
	out = request("POST", "/api/v1/students/bulk-delete", raw, `{"ids":[1,2]}`)
	var body struct{ Data []BulkResult }
	json.Unmarshal(out.Body.Bytes(), &body)
	if out.Code != 200 || len(body.Data) != 2 || body.Data[0].Success || !body.Data[1].Success {
		t.Fatal("bulk results", out.Body.String())
	}
}
func TestCloudStorageFailure(t *testing.T) {
	db := fixture(t)
	cloud := NewCloudStorage(db)
	if _, err := cloud.UploadPhoto(context.Background(), 1, pngData(t), "image/png"); statusOf(err) != 503 {
		t.Fatal("missing R2 config")
	}
	if _, err := cloud.UploadDocument(context.Background(), map[string]interface{}{}, "kartu_keluarga", "kk.pdf", nil, "application/pdf"); statusOf(err) != 503 {
		t.Fatal("missing Drive config")
	}
	db.Exec("INSERT INTO pengaturan_sistem VALUES ('google_client_id','test-id'),('google_client_secret','test-secret')")
	removed := false
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		switch {
		case r.URL.Path == "/token":
			io.WriteString(w, `{"access_token":"test-token"}`)
		case r.URL.Path == "/files" && r.Method == "GET":
			io.WriteString(w, `{"files":[{"id":"folder"}]}`)
		case r.URL.Path == "/upload":
			io.WriteString(w, `{"id":"uploaded"}`)
		case strings.HasSuffix(r.URL.Path, "/permissions"):
			w.WriteHeader(403)
		case r.Method == "DELETE":
			removed = true
			w.WriteHeader(204)
		default:
			w.WriteHeader(404)
		}
	}))
	defer server.Close()
	cloud.TokenURL = server.URL + "/token"
	cloud.DriveURL = server.URL + "/files"
	cloud.UploadURL = server.URL + "/upload"
	_, err := cloud.UploadDocument(context.Background(), map[string]interface{}{"nisn": "0100000001", "nama_siswa": "Siswa Satu", "id_siswa": 1}, "kartu_keluarga", "kk.pdf", []byte("%PDF-1.4\n%%EOF"), "application/pdf")
	if err == nil || !removed {
		t.Fatal("sharing failure did not clean object")
	}
}
func TestPHPTokenCompatibility(t *testing.T) {
	root, err := filepath.Abs("../../..")
	if err != nil {
		t.Fatal(err)
	}
	code := `define('APPROOT',getenv('STUDENT_APP_ROOT'));require APPROOT.'/app/core/StudentApp.php';echo StudentApp::token(['id_user'=>1,'role'=>'admin','id_ref'=>0],2,2);`
	cmd := exec.Command("php", "-n", "-r", code)
	cmd.Env = append(os.Environ(), "STUDENT_BRIDGE_SECRET="+testSecret, "STUDENT_APP_ROOT="+root)
	raw, err := cmd.Output()
	if err != nil {
		t.Fatal("PHP signing failed", err)
	}
	if _, err = ValidateToken(string(raw), testSecret); err != nil {
		t.Fatal("JWT mismatch", err)
	}
}
func TestLargeList(t *testing.T) {
	db := fixture(t)
	err := db.Transaction(func(tx *gorm.DB) error {
		for i := 10; i < 5010; i++ {
			if e := tx.Exec("INSERT INTO siswa (nisn,nama_siswa,jenis_kelamin) VALUES (?,?,?)", fmt.Sprintf("%010d", i), fmt.Sprintf("Test %05d", i), "L").Error; e != nil {
				return e
			}
		}
		return nil
	})
	if err != nil {
		t.Fatal(err)
	}
	start := time.Now()
	rows, total, err := NewRepository(db).List(context.Background(), admin, ListOptions{Status: "aktif", Limit: 25, Offset: 25, Sort: "nama_siswa"})
	if err != nil || total != 5002 || len(rows) != 25 {
		t.Fatal("large pagination", err, total)
	}
	t.Logf("isolated 5000-student fixture: 25 rows in %s", time.Since(start))
}

func TestUnmanagedForeignKeyRollback(t *testing.T) {
	db := fixture(t)
	if err := db.Exec("CREATE TABLE future_student_history (id INTEGER PRIMARY KEY,id_siswa INTEGER REFERENCES siswa(id_siswa) ON DELETE RESTRICT)").Error; err != nil {
		t.Fatal(err)
	}
	if err := db.Exec("INSERT INTO future_student_history VALUES (1,2)").Error; err != nil {
		t.Fatal(err)
	}
	service := NewService(NewRepository(db), &mockStorage{})
	if statusOf(service.Delete(context.Background(), admin, 2)) != 409 {
		t.Fatal("unmanaged reference must return 409")
	}
	var students, accounts, memberships int64
	db.Table("siswa").Where("id_siswa=2").Count(&students)
	db.Table("users").Where("id_ref=2 AND role='siswa'").Count(&accounts)
	db.Table("keanggotaan_kelas").Where("id_siswa=2").Count(&memberships)
	if students != 1 || accounts != 1 || memberships != 1 {
		t.Fatal("unmanaged FK did not roll back related deletions")
	}
}

func TestDocumentDatabaseFailureKeepsPrevious(t *testing.T) {
	db := fixture(t)
	storage := &mockStorage{}
	service := NewService(NewRepository(db), storage)
	ctx := context.Background()
	pdf := []byte("%PDF-1.4\n%%EOF")
	if err := service.UploadDocument(ctx, pupil, 1, "kartu_keluarga", "old.pdf", pdf); err != nil {
		t.Fatal(err)
	}
	trigger := `CREATE TRIGGER block_document BEFORE UPDATE ON siswa_dokumen BEGIN SELECT RAISE(ABORT,'database unavailable'); END`
	if db.Dialector.Name() == "mysql" {
		trigger = `CREATE TRIGGER block_document BEFORE UPDATE ON siswa_dokumen FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='database unavailable'`
	}
	if err := db.Exec(trigger).Error; err != nil {
		t.Fatal(err)
	}
	if err := service.UploadDocument(ctx, pupil, 1, "kartu_keluarga", "new.pdf", pdf); err == nil {
		t.Fatal("expected metadata failure")
	}
	docs, err := service.Repo.Documents(ctx, 1)
	if err != nil || len(docs) != 1 || docs[0].Name != "old.pdf" {
		t.Fatal("old document changed", err)
	}
	if len(storage.deletes) != 1 || storage.deletes[0] == *docs[0].DriveID {
		t.Fatal("must clean only the new object")
	}
}

type roundTripFunc func(*http.Request) (*http.Response, error)

func (f roundTripFunc) RoundTrip(r *http.Request) (*http.Response, error) { return f(r) }
func TestR2SigningAndOutage(t *testing.T) {
	db := fixture(t)
	for key, value := range map[string]string{"r2_account_id": "fixture", "r2_access_key_id": "fixture-access", "r2_secret_access_key": "fixture-secret", "r2_bucket": "test-bucket", "r2_public_url": "https://fixture.example"} {
		if err := db.Exec("INSERT INTO pengaturan_sistem (key_name,value) VALUES (?,?)", key, value).Error; err != nil {
			t.Fatal(err)
		}
	}
	cloud := NewCloudStorage(db)
	var paths []string
	cloud.Client = &http.Client{Transport: roundTripFunc(func(r *http.Request) (*http.Response, error) {
		paths = append(paths, r.URL.Path)
		if !strings.HasPrefix(r.Header.Get("Authorization"), "AWS4-HMAC-SHA256 Credential=fixture-access/") || r.Header.Get("X-Amz-Content-Sha256") == "" || r.Header.Get("Content-Type") != "image/png" {
			t.Fatal("R2 signing headers missing")
		}
		return &http.Response{StatusCode: 200, Body: io.NopCloser(strings.NewReader("")), Header: make(http.Header)}, nil
	})}
	first, err := cloud.UploadPhoto(context.Background(), 1, pngData(t), "image/png")
	if err != nil {
		t.Fatal(err)
	}
	second, err := cloud.UploadPhoto(context.Background(), 1, pngData(t), "image/png")
	if err != nil || first == second || paths[0] == paths[1] {
		t.Fatal("replacement reused a key")
	}
	cloud.Client.Transport = roundTripFunc(func(*http.Request) (*http.Response, error) { return nil, errors.New("storage unreachable") })
	service := NewService(NewRepository(db), cloud)
	db.Exec("UPDATE siswa SET foto='https://fixture.example/original.png' WHERE id_siswa=1")
	if _, err = service.UploadPhoto(context.Background(), admin, 1, pngData(t)); statusOf(err) != 502 {
		t.Fatal("R2 outage not actionable", err)
	}
	row, err := service.Repo.Detail(context.Background(), admin, 1)
	if err != nil || row["foto"] != "https://fixture.example/original.png" {
		t.Fatal("R2 outage replaced old photo", err)
	}
}
