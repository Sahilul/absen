package student

import (
	"bytes"
	"context"
	"crypto/hmac"
	"crypto/rand"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"gorm.io/gorm"
	"io"
	"mime/multipart"
	"net/http"
	"net/textproto"
	"net/url"
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"time"
)

type CloudStorage struct {
	DB        *gorm.DB
	Client    *http.Client
	Root      string
	TokenURL  string
	DriveURL  string
	UploadURL string
}

func NewCloudStorage(db *gorm.DB) *CloudStorage {
	return &CloudStorage{DB: db, Client: &http.Client{Timeout: 75 * time.Second}, Root: "/www/wwwroot/sabilillah.id", TokenURL: "https://oauth2.googleapis.com/token", DriveURL: "https://www.googleapis.com/drive/v3/files", UploadURL: "https://www.googleapis.com/upload/drive/v3/files"}
}
func remoteError(message string) error { return &APIError{Status: 502, Message: message} }
func (s *CloudStorage) settings(ctx context.Context) (map[string]string, error) {
	var rows []struct {
		Key   string `gorm:"column:key_name"`
		Value string
	}
	err := s.DB.WithContext(ctx).Table("pengaturan_sistem").Select("key_name,value").Where("key_name LIKE 'r2_%' OR key_name IN ('google_client_id','google_client_secret')").Find(&rows).Error
	out := map[string]string{}
	for _, r := range rows {
		out[r.Key] = r.Value
	}
	return out, err
}
func sha(data []byte) string { v := sha256.Sum256(data); return hex.EncodeToString(v[:]) }
func hmacBytes(key []byte, value string) []byte {
	h := hmac.New(sha256.New, key)
	h.Write([]byte(value))
	return h.Sum(nil)
}
func randomKey() string {
	var v [16]byte
	if _, err := rand.Read(v[:]); err != nil {
		panic("secure random unavailable")
	}
	return hex.EncodeToString(v[:])
}
func (s *CloudStorage) r2Request(ctx context.Context, method, key, mime string, data []byte, settings map[string]string) error {
	for _, field := range []string{"r2_account_id", "r2_access_key_id", "r2_secret_access_key", "r2_bucket", "r2_public_url"} {
		if settings[field] == "" {
			return &APIError{Status: 503, Message: "Penyimpanan foto belum dikonfigurasi. Hubungi admin."}
		}
	}
	endpoint := "https://" + settings["r2_account_id"] + ".r2.cloudflarestorage.com"
	uri := "/" + url.PathEscape(settings["r2_bucket"]) + "/" + strings.Join(strings.Split(key, "/"), "/")
	req, err := http.NewRequestWithContext(ctx, method, endpoint+uri, bytes.NewReader(data))
	if err != nil {
		return err
	}
	now := time.Now().UTC()
	stamp := now.Format("20060102T150405Z")
	day := now.Format("20060102")
	payload := sha(data)
	headers := "host:" + req.URL.Host + "\nx-amz-content-sha256:" + payload + "\nx-amz-date:" + stamp + "\n"
	signed := "host;x-amz-content-sha256;x-amz-date"
	if mime != "" {
		headers = "content-type:" + mime + "\n" + headers
		signed = "content-type;" + signed
		req.Header.Set("Content-Type", mime)
	}
	canonical := method + "\n" + req.URL.EscapedPath() + "\n\n" + headers + "\n" + signed + "\n" + payload
	scope := day + "/auto/s3/aws4_request"
	str := "AWS4-HMAC-SHA256\n" + stamp + "\n" + scope + "\n" + sha([]byte(canonical))
	k := hmacBytes([]byte("AWS4"+settings["r2_secret_access_key"]), day)
	k = hmacBytes(k, "auto")
	k = hmacBytes(k, "s3")
	k = hmacBytes(k, "aws4_request")
	req.Header.Set("X-Amz-Date", stamp)
	req.Header.Set("X-Amz-Content-Sha256", payload)
	req.Header.Set("Authorization", "AWS4-HMAC-SHA256 Credential="+settings["r2_access_key_id"]+"/"+scope+", SignedHeaders="+signed+", Signature="+hex.EncodeToString(hmacBytes(k, str)))
	resp, err := s.Client.Do(req)
	if err != nil {
		return remoteError("Tidak dapat menghubungi penyimpanan foto. Coba kembali.")
	}
	defer resp.Body.Close()
	io.Copy(io.Discard, io.LimitReader(resp.Body, 4096))
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return remoteError("Penyimpanan foto menolak permintaan. Hubungi admin.")
	}
	return nil
}
func (s *CloudStorage) UploadPhoto(ctx context.Context, id uint, data []byte, mime string) (string, error) {
	settings, err := s.settings(ctx)
	if err != nil {
		return "", err
	}
	ext := "jpg"
	if mime == "image/png" {
		ext = "png"
	}
	key := fmt.Sprintf("foto_siswa/%d_%s.%s", id, randomKey(), ext)
	if err = s.r2Request(ctx, "PUT", key, mime, data, settings); err != nil {
		return "", err
	}
	return strings.TrimRight(settings["r2_public_url"], "/") + "/" + key, nil
}
func (s *CloudStorage) DeletePhoto(ctx context.Context, photoURL string) error {
	settings, err := s.settings(ctx)
	if err != nil {
		return err
	}
	prefix := strings.TrimRight(settings["r2_public_url"], "/") + "/"
	if settings["r2_public_url"] == "" || !strings.HasPrefix(photoURL, prefix) {
		return nil
	}
	key := strings.TrimPrefix(photoURL, prefix)
	if strings.Contains(key, "..") || strings.ContainsAny(key, "?#") {
		return bad("Referensi foto tidak valid.")
	}
	return s.r2Request(ctx, "DELETE", key, "", nil, settings)
}
func (s *CloudStorage) driveToken(ctx context.Context) (string, string, error) {
	settings, err := s.settings(ctx)
	if err != nil {
		return "", "", err
	}
	var cfg struct {
		Refresh *string `gorm:"column:google_refresh_token"`
		Folder  *string `gorm:"column:google_drive_folder_id"`
	}
	if err = s.DB.WithContext(ctx).Table("pengaturan_aplikasi").Select("google_refresh_token,google_drive_folder_id").Where("id=1").Take(&cfg).Error; err != nil {
		return "", "", err
	}
	if cfg.Refresh == nil || *cfg.Refresh == "" || settings["google_client_id"] == "" || settings["google_client_secret"] == "" {
		return "", "", &APIError{Status: 503, Message: "Google Drive belum terhubung. Hubungi admin."}
	}
	body := url.Values{"client_id": {settings["google_client_id"]}, "client_secret": {settings["google_client_secret"]}, "refresh_token": {*cfg.Refresh}, "grant_type": {"refresh_token"}}.Encode()
	req, err := http.NewRequestWithContext(ctx, "POST", s.TokenURL, strings.NewReader(body))
	if err != nil {
		return "", "", err
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	resp, err := s.Client.Do(req)
	if err != nil {
		return "", "", remoteError("Tidak dapat menghubungi Google Drive.")
	}
	defer resp.Body.Close()
	var token struct {
		Access string `json:"access_token"`
	}
	err = json.NewDecoder(io.LimitReader(resp.Body, 1<<20)).Decode(&token)
	if resp.StatusCode != 200 || err != nil || token.Access == "" {
		return "", "", remoteError("Koneksi Google Drive perlu diperbarui oleh admin.")
	}
	folder := ""
	if cfg.Folder != nil {
		folder = *cfg.Folder
	}
	return token.Access, folder, nil
}
func (s *CloudStorage) driveRequest(ctx context.Context, token, method, target, contentType string, body []byte) ([]byte, error) {
	req, err := http.NewRequestWithContext(ctx, method, target, bytes.NewReader(body))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Authorization", "Bearer "+token)
	if contentType != "" {
		req.Header.Set("Content-Type", contentType)
	}
	resp, err := s.Client.Do(req)
	if err != nil {
		return nil, remoteError("Tidak dapat menghubungi Google Drive. Coba kembali.")
	}
	defer resp.Body.Close()
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return nil, remoteError("Google Drive menolak permintaan. Hubungi admin atau coba kembali.")
	}
	data, err := io.ReadAll(io.LimitReader(resp.Body, 8*1024*1024+1))
	if len(data) > 8*1024*1024 {
		return nil, bad("File terlalu besar untuk ditampilkan.")
	}
	return data, err
}
func escapeDrive(v string) string { return strings.NewReplacer("\\", "\\\\", "'", "\\'").Replace(v) }
func (s *CloudStorage) folder(ctx context.Context, token, parent, name string) (string, error) {
	query := "mimeType='application/vnd.google-apps.folder' and trashed=false and name='" + escapeDrive(name) + "'"
	if parent != "" {
		query += " and '" + escapeDrive(parent) + "' in parents"
	}
	data, err := s.driveRequest(ctx, token, "GET", s.DriveURL+"?"+url.Values{"q": {query}, "fields": {"files(id)"}, "pageSize": {"1"}}.Encode(), "", nil)
	if err != nil {
		return "", err
	}
	var found struct {
		Files []struct {
			ID string `json:"id"`
		} `json:"files"`
	}
	if err = json.Unmarshal(data, &found); err != nil {
		return "", remoteError("Respons Google Drive tidak valid.")
	}
	if len(found.Files) > 0 {
		return found.Files[0].ID, nil
	}
	obj := map[string]interface{}{"name": name, "mimeType": "application/vnd.google-apps.folder"}
	if parent != "" {
		obj["parents"] = []string{parent}
	}
	encoded, _ := json.Marshal(obj)
	data, err = s.driveRequest(ctx, token, "POST", s.DriveURL+"?fields=id", "application/json", encoded)
	if err != nil {
		return "", err
	}
	var result struct {
		ID string `json:"id"`
	}
	if json.Unmarshal(data, &result) != nil || result.ID == "" {
		return "", remoteError("Folder Google Drive tidak dapat dibuat.")
	}
	return result.ID, nil
}
func (s *CloudStorage) UploadDocument(ctx context.Context, student map[string]interface{}, kind, name string, data []byte, mime string) (RemoteFile, error) {
	token, parent, err := s.driveToken(ctx)
	if err != nil {
		return RemoteFile{}, err
	}
	nisn := textValue(student["nisn"])
	if nisn == "" {
		nisn = textValue(student["id_siswa"])
	}
	slug := strings.Trim(regexp.MustCompile(`[^a-z0-9]+`).ReplaceAllString(strings.ToLower(textValue(student["nama_siswa"])), "_"), "_")
	if len(slug) > 50 {
		slug = slug[:50]
	}
	// Match PHP's dokumen_siswa/{NISN}_{lowercase_slug} hierarchy exactly.
	parent, err = s.folder(ctx, token, parent, "dokumen_siswa")
	if err != nil {
		return RemoteFile{}, err
	}
	folderName := nisn + "_" + slug
	folder, err := s.folder(ctx, token, parent, folderName)
	if err != nil {
		return RemoteFile{}, err
	}
	obj := map[string]interface{}{"name": textValue(student["id_siswa"]) + "_" + kind + "_" + randomKey() + filepath.Ext(name), "parents": []string{folder}}
	metadata, _ := json.Marshal(obj)
	var buf bytes.Buffer
	writer := multipart.NewWriter(&buf)
	part, _ := writer.CreatePart(textproto.MIMEHeader{"Content-Type": {"application/json; charset=UTF-8"}})
	part.Write(metadata)
	part, _ = writer.CreatePart(textproto.MIMEHeader{"Content-Type": {mime}})
	part.Write(data)
	writer.Close()
	uploaded, err := s.driveRequest(ctx, token, "POST", s.UploadURL+"?uploadType=multipart&fields=id", "multipart/related; boundary="+writer.Boundary(), buf.Bytes())
	if err != nil {
		return RemoteFile{}, err
	}
	var result struct {
		ID string `json:"id"`
	}
	if json.Unmarshal(uploaded, &result) != nil || result.ID == "" {
		return RemoteFile{}, remoteError("Upload Google Drive tidak menghasilkan file.")
	}
	_, err = s.driveRequest(ctx, token, "POST", s.DriveURL+"/"+url.PathEscape(result.ID)+"/permissions", "application/json", []byte(`{"role":"reader","type":"anyone"}`))
	if err != nil {
		s.driveRequest(ctx, token, "DELETE", s.DriveURL+"/"+url.PathEscape(result.ID), "", nil)
		return RemoteFile{}, err
	}
	return RemoteFile{result.ID, "https://lh3.googleusercontent.com/d/" + result.ID}, nil
}
func (s *CloudStorage) DeleteDocument(ctx context.Context, id string) error {
	if !regexp.MustCompile(`^[A-Za-z0-9_-]+$`).MatchString(id) {
		return bad("Referensi dokumen tidak valid.")
	}
	token, _, err := s.driveToken(ctx)
	if err != nil {
		return err
	}
	_, err = s.driveRequest(ctx, token, "DELETE", s.DriveURL+"/"+id, "", nil)
	return err
}
func (s *CloudStorage) ReadDocument(ctx context.Context, doc Document) ([]byte, string, error) {
	id := ""
	if doc.DriveID != nil {
		id = *doc.DriveID
	}
	if id == "" && strings.HasPrefix(doc.Path, "https://lh3.googleusercontent.com/d/") {
		id = strings.TrimPrefix(doc.Path, "https://lh3.googleusercontent.com/d/")
	}
	if id != "" {
		if !regexp.MustCompile(`^[A-Za-z0-9_-]+$`).MatchString(id) {
			return nil, "", bad("Referensi dokumen tidak valid.")
		}
		token, _, err := s.driveToken(ctx)
		if err != nil {
			return nil, "", err
		}
		data, err := s.driveRequest(ctx, token, "GET", s.DriveURL+"/"+id+"?alt=media", "", nil)
		if err != nil {
			return nil, "", err
		}
		mime := fileMIME(data)
		if mime != "application/pdf" && mime != "image/jpeg" && mime != "image/png" {
			return nil, "", bad("Format dokumen tidak dapat ditampilkan.")
		}
		return data, mime, nil
	}
	if strings.Contains(doc.Path, "://") || filepath.Base(doc.Path) != doc.Path || strings.Contains(doc.Path, "..") {
		return nil, "", bad("Referensi dokumen lama tidak dapat dibuka.")
	}
	for _, dir := range []string{"uploads/siswa_dokumen", "public/uploads/siswa_dokumen"} {
		f, err := os.Open(filepath.Join(s.Root, dir, doc.Path))
		if err != nil {
			continue
		}
		data, err := io.ReadAll(io.LimitReader(f, 8*1024*1024+1))
		f.Close()
		if err != nil {
			return nil, "", err
		}
		if len(data) > 8*1024*1024 {
			return nil, "", bad("Dokumen lama terlalu besar.")
		}
		mime := fileMIME(data)
		if mime != "application/pdf" && mime != "image/jpeg" && mime != "image/png" {
			return nil, "", bad("Format dokumen tidak dapat ditampilkan.")
		}
		return data, mime, nil
	}
	return nil, "", notFound()
}
