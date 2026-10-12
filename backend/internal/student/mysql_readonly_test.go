package student

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http/httptest"
	"os"
	"sort"
	"testing"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"
	"github.com/sabilillah/backend/internal/config"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

// Explicit opt-in. The driver opens an enforced READ ONLY transaction; no production writes.
func TestMySQLReadOnly(t *testing.T) {
	if os.Getenv("STUDENT_MYSQL_READONLY") != "1" {
		t.Skip("set STUDENT_MYSQL_READONLY=1 for the configured database")
	}
	cfg, err := config.Load("../../config.yaml")
	if err != nil {
		t.Fatal("cannot load database configuration")
	}
	d := cfg.Database
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?charset=utf8mb4&parseTime=true&loc=Asia%%2FJakarta", d.User, d.Password, d.Host, d.Port, d.Name)
	db, err := gorm.Open(mysql.Open(dsn), &gorm.Config{Logger: logger.Default.LogMode(logger.Silent)})
	if err != nil {
		t.Fatal("database connection failed")
	}
	raw, _ := db.DB()
	defer raw.Close()
	tx, err := raw.BeginTx(context.Background(), &sql.TxOptions{ReadOnly: true})
	if err != nil {
		t.Fatal(err)
	}
	defer tx.Rollback()
	db = db.Session(&gorm.Session{NewDB: true})
	db.Statement.ConnPool = tx
	var p Principal
	if err = db.Raw("SELECT id_user AS user_id,id_ref AS ref,role FROM users WHERE role='admin' AND status='aktif' ORDER BY id_user LIMIT 1").Scan(&p).Error; err != nil || p.UserID == 0 {
		t.Fatal("no active admin fixture", err)
	}
	var academic struct {
		TP       uint
		Semester uint
	}
	if err = db.Raw("SELECT id_tp AS tp,id_semester AS semester FROM semester ORDER BY id_semester DESC LIMIT 1").Scan(&academic).Error; err != nil {
		t.Fatal(err)
	}
	p.TP, p.Semester = academic.TP, academic.Semester
	r := NewRepository(db)
	meta, err := r.Metadata(context.Background(), p)
	if err != nil {
		t.Fatal("metadata schema mismatch", err)
	}
	rows, _, err := r.List(context.Background(), p, ListOptions{Limit: 25, Status: "semua", Sort: "nama_siswa"})
	if err != nil || len(rows) == 0 {
		t.Fatal(err)
	}
	if _, err = r.Detail(context.Background(), p, rows[0].ID); err != nil {
		t.Fatal("detail schema mismatch", err)
	}
	if _, err = r.Documents(context.Background(), rows[0].ID); err != nil {
		t.Fatal("documents schema mismatch", err)
	}
	gin.SetMode(gin.TestMode)
	router := gin.New()
	Register(router.Group("/api/v1"), db, testSecret)
	claims := Claims{UserID: p.UserID, Role: p.Role, Ref: p.Ref, TP: p.TP, Semester: p.Semester, RegisteredClaims: jwt.RegisteredClaims{Issuer: "sabilillah-php", Audience: jwt.ClaimStrings{"student-data"}, IssuedAt: jwt.NewNumericDate(time.Now()), ExpiresAt: jwt.NewNumericDate(time.Now().Add(2 * time.Minute))}}
	token, _ := jwt.NewWithClaims(jwt.SigningMethodHS256, claims).SignedString([]byte(testSecret))
	times := make([]float64, 0, 100)
	size := 0
	for i := 0; i < 100; i++ {
		request := httptest.NewRequest("GET", "/api/v1/students", nil)
		request.Header.Set("Authorization", "Bearer "+token)
		w := httptest.NewRecorder()
		start := time.Now()
		router.ServeHTTP(w, request)
		times = append(times, float64(time.Since(start).Microseconds())/1000)
		if w.Code != 200 {
			t.Fatalf("list HTTP status %d", w.Code)
		}
		var body struct{ Data []map[string]interface{} }
		if err = json.Unmarshal(w.Body.Bytes(), &body); err != nil {
			t.Fatal(err)
		}
		if len(body.Data) > 25 {
			t.Fatal("pagination limit broken")
		}
		for _, row := range body.Data {
			for _, key := range []string{"alamat", "password", "password_plain", "ayah_nik", "ibu_nik"} {
				if _, ok := row[key]; ok {
					t.Fatal("list includes full biodata", key)
				}
			}
		}
		size = w.Body.Len()
	}
	sort.Float64s(times)
	p95 := times[94]
	if p95 >= 200 {
		t.Fatalf("list p95 %.2f ms exceeds 200 ms", p95)
	}
	var old []map[string]interface{}
	start := time.Now()
	if err = db.Table("siswa").Find(&old).Error; err != nil {
		t.Fatal(err)
	}
	full, _ := json.Marshal(old)
	t.Logf("READ ONLY MySQL: %d existing students; full biodata %d bytes in %s; paginated list %d bytes; HTTP API p50 %.2f ms, p95 %.2f ms (100 requests); metadata fields %d", len(old), len(full), time.Since(start), size, times[49], p95, len(meta["fields"].([]Field)))
}
