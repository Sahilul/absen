package student

import (
	"bytes"
	"context"
	"errors"
	"github.com/go-sql-driver/mysql"
	"github.com/rs/zerolog/log"
	"golang.org/x/crypto/bcrypt"
	"gorm.io/gorm"
	"image"
	_ "image/jpeg"
	_ "image/png"
	"net/mail"
	"regexp"
	"strconv"
	"strings"
	"time"
	"unicode/utf8"
)

type Storage interface {
	UploadPhoto(context.Context, uint, []byte, string) (string, error)
	DeletePhoto(context.Context, string) error
	UploadDocument(context.Context, map[string]interface{}, string, string, []byte, string) (RemoteFile, error)
	DeleteDocument(context.Context, string) error
	ReadDocument(context.Context, Document) ([]byte, string, error)
}
type Service struct {
	Repo    *Repository
	Storage Storage
}

func NewService(r *Repository, s Storage) *Service { return &Service{r, s} }
func translate(err error) error {
	var duplicate *mysql.MySQLError
	if errors.As(err, &duplicate) && duplicate.Number == 1062 {
		return conflict("NISN atau username sudah digunakan.")
	}
	if errors.As(err, &duplicate) && duplicate.Number == 1451 || err != nil && strings.Contains(err.Error(), "FOREIGN KEY constraint failed") {
		return conflict("Siswa masih memiliki referensi data terkait. Penghapusan dibatalkan dan data dipertahankan.")
	}
	if err != nil && strings.Contains(err.Error(), "UNIQUE constraint failed") {
		return conflict("NISN atau username sudah digunakan.")
	}
	return err
}

var nisnPattern = regexp.MustCompile(`^\d{10}$`)

func (s *Service) validate(ctx context.Context, p Principal, id uint, input map[string]interface{}, create bool) (map[string]interface{}, string, string, error) {
	fields := fieldMap()
	errs := map[string]string{}
	out := map[string]interface{}{}
	password := ""
	hashed := ""
	old := map[string]interface{}{}
	if !create {
		var err error
		old, err = s.Repo.Detail(ctx, p, id)
		if err != nil {
			return nil, "", "", err
		}
	}
	for key, value := range input {
		if key == "password" {
			if p.Role == "siswa" {
				errs[key] = "Siswa tidak dapat mengubah akun melalui formulir ini."
				continue
			}
			v, ok := value.(string)
			if !ok {
				errs[key] = "Password harus berupa teks."
				continue
			}
			password = v
			continue
		}
		f, exists := fields[key]
		if !exists || !fieldEditable(p.Role, key) {
			errs[key] = "Field ini tidak dapat diubah."
			continue
		}
		if value != nil {
			if f.Kind == "number" {
				n, err := strconv.ParseFloat(textValue(value), 64)
				if err != nil || n < 0 || n > 100 || n != float64(int(n)) {
					errs[key] = "Isi angka bulat antara 0 dan 100."
					continue
				}
				value = int(n)
			} else {
				v, ok := value.(string)
				if !ok {
					errs[key] = "Nilai harus berupa teks."
					continue
				}
				value = strings.TrimSpace(v)
			}
		}
		// Old values are accepted unchanged, even if legacy data predates validation.
		if !create && textValue(value) == textValue(old[key]) {
			continue
		}
		v := textValue(value)
		if key == "nama_siswa" && v == "" {
			errs[key] = "Nama siswa wajib diisi."
		}
		if key == "nisn" && !nisnPattern.MatchString(v) {
			errs[key] = "NISN harus terdiri dari 10 digit."
		}
		if key == "jenis_kelamin" && v != "L" && v != "P" {
			errs[key] = "Pilih L atau P."
		}
		if key == "status_siswa" && v != "aktif" && v != "lulus" && v != "pindah" {
			errs[key] = "Status siswa tidak valid."
		}
		if f.Max > 0 && utf8.RuneCountInString(v) > f.Max {
			errs[key] = "Data melebihi panjang yang diizinkan."
		}
		if f.Kind == "date" && v != "" {
			if _, err := time.Parse("2006-01-02", v); err != nil {
				errs[key] = "Gunakan tanggal yang valid (YYYY-MM-DD)."
			}
		}
		if strings.HasSuffix(key, "nik") && v != "" {
			if ok, _ := regexp.MatchString(`^\d{16}$`, v); !ok {
				errs[key] = "NIK harus terdiri dari 16 digit."
			}
		}
		if key == "email" && v != "" {
			if addr, err := mail.ParseAddress(v); err != nil || addr.Address != v {
				errs[key] = "Alamat email tidak valid."
			}
		}
		if v == "" && key != "nama_siswa" && key != "jenis_kelamin" && key != "nisn" && key != "status_siswa" {
			value = nil
		}
		out[key] = value
	}
	if create {
		for _, key := range []string{"nisn", "nama_siswa", "jenis_kelamin"} {
			if textValue(out[key]) == "" {
				errs[key] = "Field ini wajib diisi."
			}
		}
		if password == "" {
			errs["password"] = "Password wajib diisi."
		}
		if _, ok := out["status_siswa"]; !ok {
			out["status_siswa"] = "aktif"
		}
	}
	if password != "" && (utf8.RuneCountInString(password) < 6 || len(password) > 72) {
		errs["password"] = "Password minimal 6 karakter dan maksimal 72 byte."
	}
	if nisn, ok := out["nisn"]; ok && nisnPattern.MatchString(textValue(nisn)) {
		duplicate, err := s.Repo.Duplicate(ctx, id, textValue(nisn))
		if err != nil {
			return nil, "", "", err
		}
		if duplicate {
			errs["nisn"] = "NISN atau username sudah digunakan."
		}
	}
	if len(errs) > 0 {
		return nil, "", "", invalid(errs)
	}
	if password != "" {
		hash, err := bcrypt.GenerateFromPassword([]byte(password), bcrypt.DefaultCost)
		if err != nil {
			return nil, "", "", err
		}
		hashed = string(hash)
	}
	return out, password, hashed, nil
}
func (s *Service) Create(ctx context.Context, p Principal, input map[string]interface{}) (uint, error) {
	if p.Role != "admin" {
		return 0, forbidden()
	}
	data, pwd, hash, err := s.validate(ctx, p, 0, input, true)
	if err != nil {
		return 0, err
	}
	id, err := s.Repo.Create(ctx, data, pwd, hash)
	return id, translate(err)
}
func (s *Service) Update(ctx context.Context, p Principal, id uint, input map[string]interface{}) error {
	data, pwd, hash, err := s.validate(ctx, p, id, input, false)
	if err != nil {
		return err
	}
	return translate(s.Repo.Update(ctx, p, id, data, pwd, hash))
}
func (s *Service) Delete(ctx context.Context, p Principal, id uint) error {
	if p.Role != "admin" {
		return forbidden()
	}
	return translate(s.Repo.Delete(ctx, id))
}
func validateImage(data []byte, max int) (string, error) {
	if len(data) == 0 || len(data) > max {
		return "", bad("Ukuran file tidak sesuai batas yang diizinkan.")
	}
	mime := fileMIME(data)
	if mime != "image/jpeg" && mime != "image/png" {
		return "", bad("Gunakan gambar JPG atau PNG yang valid.")
	}
	cfg, _, err := image.DecodeConfig(bytes.NewReader(data))
	if err != nil || cfg.Width < 1 || cfg.Height < 1 || int64(cfg.Width)*int64(cfg.Height) > 16_000_000 {
		return "", bad("Gambar tidak valid atau dimensinya terlalu besar.")
	}
	if _, _, err = image.Decode(bytes.NewReader(data)); err != nil {
		return "", bad("Isi gambar rusak atau tidak valid.")
	}
	return mime, nil
}
func validateDocument(data []byte) (string, error) {
	if len(data) == 0 || len(data) > 2*1024*1024 {
		return "", bad("Ukuran dokumen maksimal 2 MB.")
	}
	mime := fileMIME(data)
	if mime == "application/pdf" {
		if !bytes.HasPrefix(data, []byte("%PDF-")) || !bytes.Contains(data, []byte("%%EOF")) {
			return "", bad("Dokumen PDF tidak valid.")
		}
		return mime, nil
	}
	return validateImage(data, 2*1024*1024)
}
func (s *Service) UploadPhoto(ctx context.Context, p Principal, id uint, data []byte) (string, error) {
	if p.Role != "admin" {
		return "", forbidden()
	}
	_, err := s.Repo.Detail(ctx, p, id)
	if err != nil {
		return "", err
	}
	mime, err := validateImage(data, 5*1024*1024)
	if err != nil {
		return "", invalid(map[string]string{"photo": err.Error()})
	}
	url, err := s.Storage.UploadPhoto(ctx, id, data, mime)
	if err != nil {
		return "", err
	}
	var previous string
	err = s.Repo.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		row, e := lockStudent(tx, id)
		if e != nil {
			return e
		}
		previous = textValue(row["foto"])
		return tx.Table("siswa").Where("id_siswa=?", id).Update("foto", url).Error
	})
	if err != nil {
		s.cleanupPhoto(url)
		return "", err
	}
	s.cleanupPhoto(previous)
	return url, nil
}
func (s *Service) DeletePhoto(ctx context.Context, p Principal, id uint) error {
	if p.Role != "admin" {
		return forbidden()
	}
	var old string
	err := s.Repo.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		row, e := lockStudent(tx, id)
		if e != nil {
			return e
		}
		old = textValue(row["foto"])
		return tx.Table("siswa").Where("id_siswa=?", id).Update("foto", nil).Error
	})
	if err == nil {
		s.cleanupPhoto(old)
	}
	return err
}
func (s *Service) cleanupPhoto(url string) {
	if url == "" {
		return
	}
	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()
	if err := s.Storage.DeletePhoto(ctx, url); err != nil {
		log.Warn().Msg("student photo cleanup failed")
	}
}
func (s *Service) cleanupDocument(id string) {
	if id == "" {
		return
	}
	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()
	if err := s.Storage.DeleteDocument(ctx, id); err != nil {
		log.Warn().Msg("student document cleanup failed")
	}
}
func (s *Service) UploadDocument(ctx context.Context, p Principal, id uint, kind, name string, data []byte) error {
	row, err := s.Repo.Detail(ctx, p, id)
	if err != nil {
		return err
	}
	mime, err := validateDocument(data)
	if err != nil {
		return invalid(map[string]string{"file": err.Error()})
	}
	var count int64
	if err := s.Repo.DB.WithContext(ctx).Table("pengaturan_dokumen").Where("kode=? AND aktif=1", kind).Count(&count).Error; err != nil {
		return err
	}
	if count != 1 {
		return invalid(map[string]string{"jenis_dokumen": "Jenis dokumen tidak aktif atau tidak dikenal."})
	}
	if utf8.RuneCountInString(name) > 255 || name == "" {
		return bad("Nama file tidak valid.")
	}
	remote, err := s.Storage.UploadDocument(ctx, row, kind, name, data, mime)
	if err != nil {
		return err
	}
	var old *Document
	// Serialize replacements per student. A failed database write removes only the new object.
	err = s.Repo.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		if e := NewRepository(tx).Authorize(ctx, p, id); e != nil {
			return e
		}
		var e error
		old, e = NewRepository(tx).SaveDocument(ctx, id, kind, name, len(data), remote)
		return e
	})
	if err != nil {
		s.cleanupDocument(remote.ID)
		return err
	}
	if old != nil && old.DriveID != nil {
		s.cleanupDocument(*old.DriveID)
	}
	return nil
}
func (s *Service) DeleteDocument(ctx context.Context, p Principal, id, docID uint) error {
	if p.Role == "siswa" {
		return forbidden()
	}
	var doc Document
	err := s.Repo.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		if _, e := lockStudent(tx, id); e != nil {
			return e
		}
		r := NewRepository(tx)
		if e := r.Authorize(ctx, p, id); e != nil {
			return e
		}
		var e error
		doc, e = r.Document(ctx, id, docID)
		if e != nil {
			return e
		}
		return tx.Where("id_siswa=? AND id_dokumen=?", id, docID).Delete(&Document{}).Error
	})
	if err == nil && doc.DriveID != nil {
		s.cleanupDocument(*doc.DriveID)
	}
	return err
}
