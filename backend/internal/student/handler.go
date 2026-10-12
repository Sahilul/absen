package student

import (
	"context"
	"errors"
	"github.com/gin-gonic/gin"
	"github.com/rs/zerolog/log"
	"gorm.io/gorm"
	"io"
	"mime"
	"net/http"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

type Handler struct{ Service *Service }

func Register(group *gin.RouterGroup, db *gorm.DB, secret string) {
	RegisterService(group, NewService(NewRepository(db), NewCloudStorage(db)), secret)
}
func RegisterService(group *gin.RouterGroup, service *Service, secret string) {
	h := &Handler{service}
	g := group.Group("/students")
	g.Use(Auth(service.Repo.DB, secret))
	g.GET("", h.List)
	g.GET("/metadata", h.Metadata)
	g.GET("/me", h.Detail)
	g.PATCH("/me", h.Update)
	g.POST("", h.Create)
	g.POST("/bulk-delete", h.BulkDelete)
	g.GET("/:id", h.Detail)
	g.PATCH("/:id", h.Update)
	g.DELETE("/:id", h.Delete)
	g.POST("/:id/password/reveal", h.RevealPassword)
	g.POST("/:id/photo", h.UploadPhoto)
	g.DELETE("/:id/photo", h.DeletePhoto)
	g.GET("/:id/documents", h.Documents)
	g.POST("/:id/documents", h.UploadDocument)
	g.DELETE("/:id/documents/:docID", h.DeleteDocument)
	g.GET("/:id/documents/:docID/content", h.DocumentContent)
}
func respondError(c *gin.Context, err error) {
	var e *APIError
	if errors.As(err, &e) {
		c.JSON(e.Status, gin.H{"success": false, "error": e.Message, "field_errors": e.Fields})
		return
	}
	log.Error().Str("method", c.Request.Method).Str("path", c.Request.URL.Path).Msg("student API operation failed")
	c.JSON(500, gin.H{"success": false, "error": "Data siswa tidak dapat diproses. Coba kembali."})
}
func ok(c *gin.Context, data interface{}) { c.JSON(200, gin.H{"success": true, "data": data}) }
func studentID(c *gin.Context) (uint, error) {
	if c.Param("id") == "me" || strings.HasSuffix(c.Request.URL.Path, "/me") {
		if principal(c).Role != "siswa" {
			return 0, forbidden()
		}
		return principal(c).Ref, nil
	}
	n, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil || n == 0 {
		return 0, bad("ID siswa tidak valid.")
	}
	return uint(n), nil
}
func (h *Handler) List(c *gin.Context) {
	p := principal(c)
	if p.Role == "siswa" {
		respondError(c, forbidden())
		return
	}
	o := ListOptions{Query: strings.TrimSpace(c.Query("q")), Status: c.DefaultQuery("status", "aktif"), Sort: c.DefaultQuery("sort", "nama_siswa"), Desc: c.Query("direction") == "desc", Limit: 25}
	if len(o.Query) > 100 || o.Status != "aktif" && o.Status != "lulus" && o.Status != "pindah" && o.Status != "semua" || o.Sort != "nama_siswa" && o.Sort != "nisn" {
		respondError(c, bad("Filter tidak valid."))
		return
	}
	if raw := c.Query("limit"); raw != "" {
		n, e := strconv.Atoi(raw)
		if e != nil || (n != 25 && n != 50 && n != 100) {
			respondError(c, bad("Pilih batas 25, 50 atau 100 siswa."))
			return
		}
		o.Limit = n
	}
	if raw := c.Query("offset"); raw != "" {
		n, e := strconv.Atoi(raw)
		if e != nil || n < 0 || n > 1000000 {
			respondError(c, bad("Halaman tidak valid."))
			return
		}
		o.Offset = n
	}
	if raw := c.Query("class_id"); raw != "" {
		n, e := strconv.ParseUint(raw, 10, 32)
		if e != nil {
			respondError(c, bad("Kelas tidak valid."))
			return
		}
		o.Class = uint(n)
	}
	ctx, cancel := context.WithTimeout(c.Request.Context(), 5*time.Second)
	defer cancel()
	rows, total, err := h.Service.Repo.List(ctx, p, o)
	if err != nil {
		respondError(c, err)
		return
	}
	c.JSON(200, gin.H{"success": true, "data": rows, "meta": gin.H{"total": total, "limit": o.Limit, "offset": o.Offset}})
}
func (h *Handler) Metadata(c *gin.Context) {
	data, err := h.Service.Repo.Metadata(c.Request.Context(), principal(c))
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, data)
}
func (h *Handler) Detail(c *gin.Context) {
	id, err := studentID(c)
	if err != nil {
		respondError(c, err)
		return
	}
	data, err := h.Service.Repo.Detail(c.Request.Context(), principal(c), id)
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, data)
}
func input(c *gin.Context) (map[string]interface{}, error) {
	c.Request.Body = http.MaxBytesReader(c.Writer, c.Request.Body, 64*1024)
	var data map[string]interface{}
	if err := c.ShouldBindJSON(&data); err != nil || data == nil {
		return nil, bad("Data formulir tidak valid.")
	}
	return data, nil
}
func (h *Handler) Create(c *gin.Context) {
	data, err := input(c)
	if err != nil {
		respondError(c, err)
		return
	}
	id, err := h.Service.Create(c.Request.Context(), principal(c), data)
	if err != nil {
		respondError(c, err)
		return
	}
	c.JSON(201, gin.H{"success": true, "data": gin.H{"id_siswa": id}})
}
func (h *Handler) Update(c *gin.Context) {
	id, err := studentID(c)
	if err != nil {
		respondError(c, err)
		return
	}
	data, err := input(c)
	if err != nil {
		respondError(c, err)
		return
	}
	if err = h.Service.Update(c.Request.Context(), principal(c), id, data); err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"message": "Data siswa tersimpan."})
}
func (h *Handler) Delete(c *gin.Context) {
	id, err := studentID(c)
	if err == nil {
		err = h.Service.Delete(c.Request.Context(), principal(c), id)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"message": "Siswa berhasil dihapus."})
}
func (h *Handler) BulkDelete(c *gin.Context) {
	if principal(c).Role != "admin" {
		respondError(c, forbidden())
		return
	}
	c.Request.Body = http.MaxBytesReader(c.Writer, c.Request.Body, 8192)
	var req struct {
		IDs []uint `json:"ids"`
	}
	if c.ShouldBindJSON(&req) != nil || len(req.IDs) == 0 || len(req.IDs) > 100 {
		respondError(c, bad("Pilih 1 sampai 100 siswa."))
		return
	}
	results := []BulkResult{}
	seen := map[uint]bool{}
	for _, id := range req.IDs {
		if seen[id] {
			continue
		}
		seen[id] = true
		err := h.Service.Delete(c.Request.Context(), principal(c), id)
		result := BulkResult{ID: id, Success: err == nil}
		if err != nil {
			var e *APIError
			if errors.As(err, &e) {
				result.Error = e.Message
			} else {
				result.Error = "Penghapusan gagal. Data dipertahankan."
			}
		}
		results = append(results, result)
	}
	ok(c, results)
}
func (h *Handler) RevealPassword(c *gin.Context) {
	if principal(c).Role != "admin" {
		respondError(c, forbidden())
		return
	}
	id, err := studentID(c)
	if err != nil {
		respondError(c, err)
		return
	}
	var account struct {
		Password *string `gorm:"column:password_plain"`
	}
	err = h.Service.Repo.DB.WithContext(c.Request.Context()).Table("users").Select("password_plain").Where("id_ref=? AND role='siswa'", id).Order("id_user DESC").Take(&account).Error
	if errors.Is(err, gorm.ErrRecordNotFound) || account.Password == nil {
		respondError(c, notFound())
		return
	}
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"password": *account.Password})
}
func upload(c *gin.Context, key string, limit int64) ([]byte, string, error) {
	c.Request.Body = http.MaxBytesReader(c.Writer, c.Request.Body, limit+64*1024)
	if err := c.Request.ParseMultipartForm(limit + 64*1024); err != nil {
		return nil, "", bad("File terlalu besar atau upload tidak valid.")
	}
	if c.Request.MultipartForm != nil {
		defer c.Request.MultipartForm.RemoveAll()
	}
	file, header, err := c.Request.FormFile(key)
	if err != nil {
		return nil, "", bad("Pilih file untuk diunggah.")
	}
	defer file.Close()
	data, err := io.ReadAll(io.LimitReader(file, limit+1))
	if err != nil {
		return nil, "", bad("File tidak dapat dibaca.")
	}
	if len(data) == 0 || int64(len(data)) > limit {
		return nil, "", bad("Ukuran file melebihi batas yang diizinkan.")
	}
	return data, filepath.Base(header.Filename), nil
}
func (h *Handler) UploadPhoto(c *gin.Context) {
	id, err := studentID(c)
	if err == nil && principal(c).Role != "admin" {
		err = forbidden()
	}
	if err != nil {
		respondError(c, err)
		return
	}
	data, _, err := upload(c, "photo", 5*1024*1024)
	if err != nil {
		respondError(c, err)
		return
	}
	photo, err := h.Service.UploadPhoto(c.Request.Context(), principal(c), id, data)
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"foto": photo})
}
func (h *Handler) DeletePhoto(c *gin.Context) {
	id, err := studentID(c)
	if err == nil {
		err = h.Service.DeletePhoto(c.Request.Context(), principal(c), id)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"message": "Foto dihapus."})
}
func (h *Handler) Documents(c *gin.Context) {
	id, err := studentID(c)
	if err == nil {
		err = h.Service.Repo.Authorize(c.Request.Context(), principal(c), id)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	rows, err := h.Service.Repo.Documents(c.Request.Context(), id)
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, rows)
}
func (h *Handler) UploadDocument(c *gin.Context) {
	id, err := studentID(c)
	if err == nil {
		err = h.Service.Repo.Authorize(c.Request.Context(), principal(c), id)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	data, name, err := upload(c, "file", 2*1024*1024)
	if err == nil {
		err = h.Service.UploadDocument(c.Request.Context(), principal(c), id, c.Request.FormValue("jenis_dokumen"), name, data)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"message": "Dokumen diunggah dan menunggu verifikasi."})
}
func docID(c *gin.Context) (uint, error) {
	n, e := strconv.ParseUint(c.Param("docID"), 10, 32)
	if e != nil || n == 0 {
		return 0, bad("ID dokumen tidak valid.")
	}
	return uint(n), nil
}
func (h *Handler) DeleteDocument(c *gin.Context) {
	id, err := studentID(c)
	if err != nil {
		respondError(c, err)
		return
	}
	doc, err := docID(c)
	if err == nil {
		err = h.Service.DeleteDocument(c.Request.Context(), principal(c), id, doc)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	ok(c, gin.H{"message": "Dokumen dihapus."})
}
func (h *Handler) DocumentContent(c *gin.Context) {
	id, err := studentID(c)
	if err == nil {
		err = h.Service.Repo.Authorize(c.Request.Context(), principal(c), id)
	}
	if err != nil {
		respondError(c, err)
		return
	}
	doc, err := docID(c)
	if err != nil {
		respondError(c, err)
		return
	}
	row, err := h.Service.Repo.Document(c.Request.Context(), id, doc)
	if err != nil {
		respondError(c, err)
		return
	}
	data, typ, err := h.Service.Storage.ReadDocument(c.Request.Context(), row)
	if err != nil {
		respondError(c, err)
		return
	}
	c.Header("X-Content-Type-Options", "nosniff")
	c.Header("Content-Disposition", mime.FormatMediaType("attachment", map[string]string{"filename": row.Name}))
	c.Data(200, typ, data)
}
