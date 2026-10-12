package student

import (
	"fmt"
	"net/http"
	"strconv"
	"strings"
	"time"
)

type Principal struct {
	UserID   uint
	Role     string
	Ref      uint
	TP       uint
	Semester uint
}
type APIError struct {
	Status  int
	Message string
	Fields  map[string]string
}

func (e *APIError) Error() string { return e.Message }
func bad(message string) error    { return &APIError{Status: 400, Message: message} }
func forbidden() error {
	return &APIError{Status: 403, Message: "Anda tidak memiliki akses ke data siswa ini."}
}
func notFound() error               { return &APIError{Status: 404, Message: "Data siswa tidak ditemukan."} }
func conflict(message string) error { return &APIError{Status: 409, Message: message} }
func invalid(fields map[string]string) error {
	return &APIError{Status: 422, Message: "Periksa kembali data yang diisi.", Fields: fields}
}

type ListOptions struct {
	Query  string
	Status string
	Class  uint
	Limit  int
	Offset int
	Sort   string
	Desc   bool
}
type Summary struct {
	ID            uint    `gorm:"column:id_siswa" json:"id_siswa"`
	NISN          *string `json:"nisn"`
	Name          string  `gorm:"column:nama_siswa" json:"nama_siswa"`
	Gender        string  `gorm:"column:jenis_kelamin" json:"jenis_kelamin"`
	Status        *string `gorm:"column:status_siswa" json:"status_siswa"`
	ClassID       *uint   `gorm:"column:id_kelas" json:"id_kelas"`
	ClassName     *string `gorm:"column:nama_kelas" json:"nama_kelas"`
	Photo         *string `gorm:"column:foto" json:"foto"`
	FatherPhone   *string `gorm:"column:ayah_no_hp" json:"ayah_no_hp"`
	MotherPhone   *string `gorm:"column:ibu_no_hp" json:"ibu_no_hp"`
	AccountID     *uint   `gorm:"column:id_user" json:"-"`
	AccountStatus *string `gorm:"column:account_status" json:"account_status"`
}
type Class struct {
	ID   uint   `gorm:"column:id_kelas" json:"id_kelas"`
	Name string `gorm:"column:nama_kelas" json:"nama_kelas"`
}
type Statistics struct {
	Total     int64 `json:"total"`
	Active    int64 `json:"aktif"`
	Graduated int64 `json:"lulus"`
	Moved     int64 `json:"pindah"`
	Male      int64 `json:"laki_laki"`
	Female    int64 `json:"perempuan"`
	Accounts  int64 `json:"akun_aktif"`
}
type Field struct {
	Name     string   `json:"name"`
	Label    string   `json:"label"`
	Group    string   `json:"group"`
	Kind     string   `json:"kind"`
	Key      string   `json:"-"`
	Max      int      `json:"max,omitempty"`
	Enabled  bool     `json:"enabled"`
	Editable bool     `json:"editable"`
	Options  []string `json:"options,omitempty"`
}
type Document struct {
	ID         uint      `gorm:"column:id_dokumen" json:"id_dokumen"`
	StudentID  uint      `gorm:"column:id_siswa" json:"id_siswa"`
	Kind       string    `gorm:"column:jenis_dokumen" json:"jenis_dokumen"`
	Name       string    `gorm:"column:nama_file" json:"nama_file"`
	Path       string    `gorm:"column:path_file" json:"-"`
	Size       int64     `gorm:"column:ukuran" json:"ukuran"`
	Status     *string   `json:"status"`
	Note       *string   `gorm:"column:catatan" json:"catatan"`
	UploadedAt time.Time `gorm:"column:uploaded_at" json:"uploaded_at"`
	DriveID    *string   `gorm:"column:drive_file_id" json:"-"`
	DriveURL   *string   `gorm:"column:drive_url" json:"-"`
}

func (Document) TableName() string { return "siswa_dokumen" }

type DocumentKind struct {
	Code     string `gorm:"column:kode" json:"kode"`
	Name     string `gorm:"column:nama" json:"nama"`
	Required bool   `gorm:"column:wajib_siswa" json:"wajib_siswa"`
}
type RemoteFile struct {
	ID  string
	URL string
}
type BulkResult struct {
	ID      uint   `json:"id_siswa"`
	Success bool   `json:"success"`
	Error   string `json:"error,omitempty"`
}

func uintValue(v interface{}) uint { n, _ := strconv.ParseUint(fmt.Sprint(v), 10, 64); return uint(n) }
func textValue(v interface{}) string {
	if v == nil {
		return ""
	}
	if b, ok := v.([]byte); ok {
		return string(b)
	}
	return fmt.Sprint(v)
}
func normalize(row map[string]interface{}) map[string]interface{} {
	for k, v := range row {
		switch t := v.(type) {
		case []byte:
			row[k] = string(t)
		case time.Time:
			if strings.Contains(k, "lahir") {
				row[k] = t.Format("2006-01-02")
			} else {
				row[k] = t.Format(time.RFC3339)
			}
		}
	}
	return row
}
func fileMIME(data []byte) string { return strings.Split(http.DetectContentType(data), ";")[0] }
