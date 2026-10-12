package student

import (
	"context"
	"encoding/json"
	"errors"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
	"gorm.io/gorm/logger"
	"strings"
)

type Repository struct{ DB *gorm.DB }

func NewRepository(db *gorm.DB) *Repository {
	// Never log biodata/password SQL parameters, including in a debug server.
	return &Repository{db.Session(&gorm.Session{Logger: logger.Default.LogMode(logger.Silent)})}
}

const joins = ` FROM siswa s
 LEFT JOIN keanggotaan_kelas kk ON kk.id_keanggotaan = (SELECT MAX(kx.id_keanggotaan) FROM keanggotaan_kelas kx WHERE kx.id_siswa=s.id_siswa AND kx.id_tp=?)
 LEFT JOIN kelas k ON k.id_kelas=kk.id_kelas
 LEFT JOIN users u ON u.id_user = (SELECT MAX(ux.id_user) FROM users ux WHERE ux.id_ref=s.id_siswa AND ux.role='siswa') `

func scope(p Principal) (string, []interface{}) {
	if p.Role == "admin" {
		return "1=1", nil
	}
	if p.Role == "siswa" {
		return "s.id_siswa=?", []interface{}{p.Ref}
	}
	return "kk.id_kelas IN (SELECT wk.id_kelas FROM wali_kelas wk WHERE wk.id_guru=? AND wk.id_tp=?)", []interface{}{p.Ref, p.TP}
}
func (r *Repository) List(ctx context.Context, p Principal, o ListOptions) ([]Summary, int64, error) {
	where, args := scope(p)
	params := append([]interface{}{p.TP}, args...)
	if o.Status != "semua" {
		where += " AND s.status_siswa=?"
		params = append(params, o.Status)
	}
	if o.Class > 0 {
		where += " AND kk.id_kelas=?"
		params = append(params, o.Class)
	}
	if o.Query != "" {
		q := strings.NewReplacer("!", "!!", "%", "!%", "_", "!_").Replace(o.Query)
		where += " AND (s.nama_siswa LIKE ? ESCAPE '!' OR s.nisn LIKE ? ESCAPE '!')"
		params = append(params, "%"+q+"%", q+"%")
	}
	var total int64
	db := r.DB.WithContext(ctx)
	if err := db.Raw("SELECT COUNT(*)"+joins+" WHERE "+where, params...).Scan(&total).Error; err != nil {
		return nil, 0, err
	}
	sortBy := "s.nama_siswa"
	if o.Sort == "nisn" {
		sortBy = "s.nisn"
	}
	dir := "ASC"
	if o.Desc {
		dir = "DESC"
	}
	var rows []Summary
	query := `SELECT s.id_siswa,s.nisn,s.nama_siswa,s.jenis_kelamin,s.status_siswa,s.foto,s.ayah_no_hp,s.ibu_no_hp,k.id_kelas,k.nama_kelas,u.id_user,u.status AS account_status` + joins + " WHERE " + where + " ORDER BY " + sortBy + " " + dir + ",s.id_siswa " + dir + " LIMIT ? OFFSET ?"
	err := db.Raw(query, append(params, o.Limit, o.Offset)...).Scan(&rows).Error
	if rows == nil {
		rows = []Summary{}
	}
	return rows, total, err
}
func (r *Repository) Authorize(ctx context.Context, p Principal, id uint) error {
	if id == 0 {
		return notFound()
	}
	if p.Role == "siswa" && id != p.Ref {
		return forbidden()
	}
	if p.Role == "wali_kelas" {
		var count int64
		err := r.DB.WithContext(ctx).Raw(`SELECT COUNT(*) FROM keanggotaan_kelas kk JOIN wali_kelas wk ON wk.id_kelas=kk.id_kelas AND wk.id_tp=kk.id_tp WHERE kk.id_keanggotaan=(SELECT MAX(id_keanggotaan) FROM keanggotaan_kelas WHERE id_siswa=? AND id_tp=?) AND wk.id_guru=? AND wk.id_tp=?`, id, p.TP, p.Ref, p.TP).Scan(&count).Error
		if err != nil {
			return err
		}
		if count == 0 {
			return forbidden()
		}
	}
	return nil
}
func (r *Repository) Detail(ctx context.Context, p Principal, id uint) (map[string]interface{}, error) {
	if err := r.Authorize(ctx, p, id); err != nil {
		return nil, err
	}
	rows := []map[string]interface{}{}
	cols := []string{"s.id_siswa", "s.created_at", "s.foto", "k.nama_kelas", "k.id_kelas", "u.username", "u.status AS account_status"}
	for _, f := range Fields() {
		cols = append(cols, "s."+f.Name)
	}
	err := r.DB.WithContext(ctx).Raw("SELECT "+strings.Join(cols, ",")+joins+" WHERE s.id_siswa=?", p.TP, id).Scan(&rows).Error
	if err != nil {
		return nil, err
	}
	if len(rows) == 0 {
		return nil, notFound()
	}
	return normalize(rows[0]), nil
}
func (r *Repository) Metadata(ctx context.Context, p Principal) (map[string]interface{}, error) {
	db := r.DB.WithContext(ctx)
	where, args := scope(p)
	var stats Statistics
	err := db.Raw(`SELECT COUNT(*) AS total,COALESCE(SUM(s.status_siswa='aktif'),0) AS active,COALESCE(SUM(s.status_siswa='lulus'),0) AS graduated,COALESCE(SUM(s.status_siswa='pindah'),0) AS moved,COALESCE(SUM(s.jenis_kelamin='L'),0) AS male,COALESCE(SUM(s.jenis_kelamin='P'),0) AS female,COALESCE(SUM(u.status='aktif'),0) AS accounts`+joins+" WHERE "+where, append([]interface{}{p.TP}, args...)...).Scan(&stats).Error
	if err != nil {
		return nil, err
	}
	classes := []Class{}
	q := db.Table("kelas").Select("id_kelas,nama_kelas").Where("id_tp=?", p.TP)
	if p.Role == "wali_kelas" {
		q = q.Where("id_kelas IN (SELECT id_kelas FROM wali_kelas WHERE id_guru=? AND id_tp=?)", p.Ref, p.TP)
	} else if p.Role == "siswa" {
		q = q.Where("id_kelas IN (SELECT id_kelas FROM keanggotaan_kelas WHERE id_siswa=? AND id_tp=?)", p.Ref, p.TP)
	}
	if err = q.Order("nama_kelas").Scan(&classes).Error; err != nil {
		return nil, err
	}
	var raw struct {
		Config *string `gorm:"column:field_siswa_config"`
		Name   string  `gorm:"column:nama_aplikasi"`
	}
	if err = db.Table("pengaturan_aplikasi").Select("field_siswa_config,nama_aplikasi").Where("id=1").Take(&raw).Error; err != nil {
		return nil, err
	}
	cfg := map[string]bool{}
	if raw.Config != nil {
		json.Unmarshal([]byte(*raw.Config), &cfg)
	}
	kinds := []DocumentKind{}
	if err = db.Table("pengaturan_dokumen").Select("kode,nama,wajib_siswa").Where("aktif=1").Order("urutan,nama").Scan(&kinds).Error; err != nil {
		return nil, err
	}
	var semester struct {
		Name string `gorm:"column:nama_tp"`
		Term string `gorm:"column:semester"`
	}
	if err = db.Table("semester sm").Select("tp.nama_tp,sm.semester").Joins("JOIN tp ON tp.id_tp=sm.id_tp").Where("sm.id_semester=? AND sm.id_tp=?", p.Semester, p.TP).Take(&semester).Error; err != nil {
		return nil, err
	}
	return map[string]interface{}{"stats": stats, "classes": classes, "fields": ConfiguredFields(p.Role, cfg), "document_kinds": kinds, "app_name": raw.Name, "semester_name": semester.Name + " - " + semester.Term, "id_tp": p.TP, "id_semester": p.Semester}, nil
}
func lockStudent(tx *gorm.DB, id uint) (map[string]interface{}, error) {
	var row map[string]interface{}
	err := tx.Table("siswa").Clauses(clause.Locking{Strength: "UPDATE"}).Where("id_siswa=?", id).Take(&row).Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		return nil, notFound()
	}
	return normalize(row), err
}
func (r *Repository) Create(ctx context.Context, data map[string]interface{}, password, hashed string) (uint, error) {
	var id uint
	err := r.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		if err := tx.Table("siswa").Create(&data).Error; err != nil {
			return err
		}
		if err := tx.Table("siswa").Select("id_siswa").Where("nisn=?", data["nisn"]).Scan(&id).Error; err != nil {
			return err
		}
		return tx.Table("users").Create(map[string]interface{}{"username": data["nisn"], "password": hashed, "password_plain": password, "nama_lengkap": data["nama_siswa"], "role": "siswa", "id_ref": id, "status": "aktif"}).Error
	})
	return id, err
}
func (r *Repository) Update(ctx context.Context, p Principal, id uint, data map[string]interface{}, password, hashed string) error {
	return r.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		old, err := lockStudent(tx, id)
		if err != nil {
			return err
		}
		// Recheck class membership inside the write transaction as well.
		if err := NewRepository(tx).Authorize(ctx, p, id); err != nil {
			return err
		}
		if len(data) > 0 {
			if err := tx.Table("siswa").Where("id_siswa=?", id).Updates(data).Error; err != nil {
				return err
			}
		}
		accounts := tx.Table("users").Where("id_ref=? AND role='siswa'", id)
		if name, ok := data["nama_siswa"]; ok {
			if err := accounts.Session(&gorm.Session{}).Update("nama_lengkap", name).Error; err != nil {
				return err
			}
		}
		if nisn, ok := data["nisn"]; ok {
			if err := tx.Table("users").Where("id_ref=? AND role='siswa' AND username=?", id, old["nisn"]).Update("username", nisn).Error; err != nil {
				return err
			}
		}
		if hashed != "" {
			var count int64
			if err := tx.Table("users").Where("id_ref=? AND role='siswa'", id).Count(&count).Error; err != nil {
				return err
			}
			if count == 0 {
				return conflict("Siswa belum memiliki akun login.")
			}
			return tx.Table("users").Where("id_ref=? AND role='siswa'", id).Updates(map[string]interface{}{"password": hashed, "password_plain": password}).Error
		}
		return nil
	})
}
func (r *Repository) Delete(ctx context.Context, id uint) error {
	return r.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		if _, err := lockStudent(tx, id); err != nil {
			return err
		}
		managed := []string{"absensi", "nilai_siswa", "jurnal", "performa_siswa", "keanggotaan_kelas", "pembayaran_tagihan_siswa", "rapor_sts", "siswa_dokumen"}
		allowed := map[string]bool{"siswa": true}
		for _, table := range managed {
			allowed[table] = true
		}
		// Discover all other id_siswa references, including tables without a foreign key.
		tables, err := tx.Migrator().GetTables()
		if err != nil {
			return err
		}
		for _, t := range tables {
			if allowed[t] || !tx.Migrator().HasColumn(t, "id_siswa") {
				continue
			}
			var count int64
			if err := tx.Table(t).Where("id_siswa=?", id).Count(&count).Error; err != nil {
				return err
			}
			if count > 0 {
				return conflict("Siswa memiliki riwayat terkait. Selesaikan data terkait sebelum menghapus.")
			}
		}
		for _, t := range managed {
			if tx.Migrator().HasTable(t) && tx.Migrator().HasColumn(t, "id_siswa") {
				if err := tx.Exec("DELETE FROM "+t+" WHERE id_siswa=?", id).Error; err != nil {
					return err
				}
			}
		}
		if err := tx.Exec("DELETE FROM users WHERE id_ref=? AND role='siswa'", id).Error; err != nil {
			return err
		}
		return tx.Exec("DELETE FROM siswa WHERE id_siswa=?", id).Error
	})
}
func (r *Repository) Documents(ctx context.Context, id uint) ([]Document, error) {
	rows := []Document{}
	err := r.DB.WithContext(ctx).Where("id_siswa=?", id).Order("jenis_dokumen,id_dokumen DESC").Find(&rows).Error
	return rows, err
}
func (r *Repository) Document(ctx context.Context, id, docID uint) (Document, error) {
	var doc Document
	err := r.DB.WithContext(ctx).Where("id_siswa=? AND id_dokumen=?", id, docID).Take(&doc).Error
	if errors.Is(err, gorm.ErrRecordNotFound) {
		return doc, notFound()
	}
	return doc, err
}
func (r *Repository) SaveDocument(ctx context.Context, id uint, kind, name string, size int, remote RemoteFile) (*Document, error) {
	var old *Document
	err := r.DB.WithContext(ctx).Transaction(func(tx *gorm.DB) error {
		if _, err := lockStudent(tx, id); err != nil {
			return err
		}
		var prev Document
		err := tx.Where("id_siswa=? AND jenis_dokumen=?", id, kind).Order("id_dokumen DESC").Take(&prev).Error
		if err != nil && !errors.Is(err, gorm.ErrRecordNotFound) {
			return err
		}
		values := map[string]interface{}{"nama_file": name, "path_file": remote.URL, "ukuran": size, "drive_file_id": remote.ID, "drive_url": remote.URL, "status": "pending", "catatan": nil, "uploaded_at": gorm.Expr("CURRENT_TIMESTAMP")}
		if err == nil {
			old = &prev
			return tx.Table("siswa_dokumen").Where("id_dokumen=?", prev.ID).Updates(values).Error
		}
		values["id_siswa"] = id
		values["jenis_dokumen"] = kind
		return tx.Table("siswa_dokumen").Create(values).Error
	})
	return old, err
}
func (r *Repository) Duplicate(ctx context.Context, id uint, nisn string) (bool, error) {
	var count int64
	err := r.DB.WithContext(ctx).Table("siswa").Where("nisn=? AND id_siswa<>?", nisn, id).Count(&count).Error
	if err != nil || count > 0 {
		return count > 0, err
	}
	err = r.DB.WithContext(ctx).Table("users").Where("username=? AND NOT (role='siswa' AND id_ref=?)", nisn, id).Count(&count).Error
	return count > 0, err
}
func fieldMap() map[string]Field {
	out := map[string]Field{}
	for _, f := range Fields() {
		out[f.Name] = f
	}
	return out
}
