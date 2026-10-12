// Administrative rollout helper. Never rewrites student records.
package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"

	"github.com/golang-jwt/jwt/v5"
	"github.com/sabilillah/backend/internal/config"
	"github.com/sabilillah/backend/internal/student"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

func fail(message string) { fmt.Fprintln(os.Stderr, message); os.Exit(1) }
func main() {
	cfgPath := flag.String("config", "config.yaml", "backend config path")
	flag.Parse()
	args := flag.Args()
	action := "status"
	if len(args) > 0 {
		action = args[0]
	}
	if action != "status" && action != "prepare" && action != "enable" && action != "disable" {
		fail("Usage: student-module [status|prepare|enable ROLE|disable ROLE]; ROLE admin, wali_kelas, siswa, all")
	}
	roles := []string{"admin", "wali_kelas", "siswa"}
	if action == "enable" || action == "disable" {
		if len(args) != 2 {
			fail("Specify a role or all")
		}
		if args[1] != "all" {
			found := false
			for _, role := range roles {
				if args[1] == role {
					found = true
				}
			}
			if !found {
				fail("Invalid role")
			}
			roles = []string{args[1]}
		}
	}
	cfg, err := config.Load(*cfgPath)
	if err != nil {
		fail("Cannot load backend configuration")
	}
	d := cfg.Database
	db, err := gorm.Open(mysql.Open(fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?charset=utf8mb4&parseTime=true", d.User, d.Password, d.Host, d.Port, d.Name)), &gorm.Config{Logger: logger.Default.LogMode(logger.Silent)})
	if err != nil {
		fail("Cannot connect to configured database")
	}
	sqlDB, _ := db.DB()
	defer sqlDB.Close()
	// config.yaml normally lives in backend/; resolve from its absolute path.
	absolute, _ := filepath.Abs(*cfgPath)
	root := filepath.Dir(filepath.Dir(absolute))
	if action == "prepare" {
		for _, index := range []struct{ name, columns string }{{"idx_kk_tp_siswa_latest", "id_tp,id_siswa,id_keanggotaan"}, {"idx_kk_tp_kelas_siswa", "id_tp,id_kelas,id_siswa,id_keanggotaan"}} {
			var count int64
			if err = db.Raw("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='keanggotaan_kelas' AND index_name=?", index.name).Scan(&count).Error; err != nil {
				fail("Cannot inspect membership indexes")
			}
			if count == 0 {
				if err = db.Exec("CREATE INDEX " + index.name + " ON keanggotaan_kelas (" + index.columns + ")").Error; err != nil {
					fail("Cannot add membership index: " + index.name)
				}
				fmt.Println("Added index", index.name)
			}
		}
		for _, role := range roles {
			if err = db.Exec("INSERT IGNORE INTO pengaturan_sistem (key_name,value) VALUES (?, '0')", "student_vue_"+role+"_enabled").Error; err != nil {
				fail("Cannot initialize feature flag")
			}
		}
	}
	if action == "enable" {
		secret := cfg.Auth.StudentBridgeSecret
		if secret == "" {
			raw, _ := os.ReadFile(filepath.Join(root, "config/student-bridge.env"))
			for _, line := range strings.Split(string(raw), "\n") {
				if strings.HasPrefix(line, "STUDENT_BRIDGE_SECRET=") {
					secret = strings.Trim(strings.TrimPrefix(line, "STUDENT_BRIDGE_SECRET="), "\"' ")
				}
			}
		}
		if len(secret) < 32 {
			fail("Bridge secret is missing")
		}
		raw, err := os.ReadFile(filepath.Join(root, "public/student-app/.vite/manifest.json"))
		if err != nil {
			fail("Build frontend before activation")
		}
		var manifest map[string]struct{ File string }
		if json.Unmarshal(raw, &manifest) != nil || manifest["src/main.ts"].File == "" {
			fail("Invalid frontend manifest")
		}
		if _, err = os.Stat(filepath.Join(root, "public/student-app", manifest["src/main.ts"].File)); err != nil {
			fail("Frontend assets are missing")
		}
		var p student.Principal
		if err = db.Raw("SELECT id_user AS user_id,role,id_ref AS ref FROM users WHERE role='admin' AND status='aktif' ORDER BY id_user LIMIT 1").Scan(&p).Error; err != nil || p.UserID == 0 {
			fail("Cannot check active admin")
		}
		var academic struct{ TP, Semester uint }
		db.Raw("SELECT id_tp AS tp,id_semester AS semester FROM semester ORDER BY id_semester DESC LIMIT 1").Scan(&academic)
		p.TP, p.Semester = academic.TP, academic.Semester
		claims := student.Claims{UserID: p.UserID, Role: p.Role, Ref: p.Ref, TP: p.TP, Semester: p.Semester, RegisteredClaims: jwt.RegisteredClaims{Issuer: "sabilillah-php", Audience: jwt.ClaimStrings{"student-data"}, IssuedAt: jwt.NewNumericDate(time.Now()), ExpiresAt: jwt.NewNumericDate(time.Now().Add(2 * time.Minute))}}
		token, _ := jwt.NewWithClaims(jwt.SigningMethodHS256, claims).SignedString([]byte(secret))
		req, _ := http.NewRequest("GET", fmt.Sprintf("http://127.0.0.1:%d/api/v1/students/metadata", cfg.Server.Port), nil)
		req.Header.Set("Authorization", "Bearer "+token)
		client := &http.Client{Timeout: 10 * time.Second}
		resp, err := client.Do(req)
		if err != nil {
			fail("Go student API is not reachable")
		}
		resp.Body.Close()
		if resp.StatusCode != 200 {
			fail(fmt.Sprintf("Go student API readiness failed (HTTP %d)", resp.StatusCode))
		}
	}
	if action == "enable" || action == "disable" {
		value := "0"
		if action == "enable" {
			value = "1"
		}
		err = db.Transaction(func(tx *gorm.DB) error {
			for _, role := range roles {
				if e := tx.Exec("INSERT INTO pengaturan_sistem (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)", "student_vue_"+role+"_enabled", value).Error; e != nil {
					return e
				}
			}
			return nil
		})
		if err != nil {
			fail("Cannot update feature flags")
		}
	}
	for _, role := range roles {
		var value string
		db.Table("pengaturan_sistem").Select("value").Where("key_name=?", "student_vue_"+role+"_enabled").Scan(&value)
		if value == "" {
			value = "0 (default)"
		}
		fmt.Printf("%s: %s\n", role, value)
	}
}
