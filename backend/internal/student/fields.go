package student

import "strings"

// Config keys deliberately match PHP, including its legacy label aliases.
func Fields() []Field {
	var out []Field
	add := func(group, name, label, key, kind string, max int, options ...string) {
		out = append(out, Field{Name: name, Label: label, Group: group, Key: key, Kind: kind, Max: max, Enabled: true, Editable: true, Options: options})
	}
	add("Identitas", "nisn", "NISN", "nisn", "text", 20)
	add("Identitas", "nama_siswa", "Nama siswa", "nama", "text", 100)
	add("Identitas", "jenis_kelamin", "Jenis kelamin", "jenis_kelamin", "select", 1, "L", "P")
	add("Identitas", "tempat_lahir", "Tempat lahir", "tempat_lahir", "text", 100)
	add("Identitas", "tgl_lahir", "Tanggal lahir", "tanggal_lahir", "date", 0)
	add("Identitas", "nik", "NIK", "nik", "text", 20)
	add("Identitas", "agama", "Agama", "agama", "select", 20, "Islam", "Kristen", "Katolik", "Hindu", "Buddha", "Konghucu")
	for _, f := range []struct {
		n, l, k string
		m       int
	}{{"hobi", "Hobi", "hobi", 100}, {"cita_cita", "Cita-cita", "cita_cita", 100}, {"no_wa", "WhatsApp siswa", "no_wa", 20}, {"email", "Email siswa", "email", 100}, {"kip", "Nomor KIP", "no_kip", 50}, {"yang_membiayai", "Yang membiayai", "yang_membiayai", 50}, {"kebutuhan_khusus", "Kebutuhan khusus", "kebutuhan_khusus", 100}, {"kebutuhan_disabilitas", "Kebutuhan disabilitas", "kebutuhan_disabilitas", 100}} {
		add("Identitas", f.n, f.l, f.k, "text", f.m)
	}
	add("Identitas", "jumlah_saudara", "Jumlah saudara", "jumlah_saudara", "number", 0)
	add("Identitas", "anak_ke", "Anak ke", "anak_ke", "number", 0)
	add("Alamat", "alamat", "Alamat", "alamat", "textarea", 0)
	for _, f := range []struct {
		n, l, k string
		m       int
	}{{"rt", "RT", "rt", 5}, {"rw", "RW", "rw", 5}, {"dusun", "Dusun", "dusun", 100}, {"desa", "Desa", "desa", 100}, {"kelurahan", "Kelurahan", "kelurahan", 100}, {"kecamatan", "Kecamatan", "kecamatan", 100}, {"kabupaten", "Kabupaten", "kabupaten", 100}, {"kota", "Kota", "kota", 100}, {"provinsi", "Provinsi", "provinsi", 100}, {"kode_pos", "Kode pos", "kode_pos", 10}, {"status_tempat_tinggal", "Status tempat tinggal", "status_tempat_tinggal", 50}, {"jarak_ke_sekolah", "Jarak ke sekolah", "jarak_sekolah", 50}, {"waktu_tempuh", "Waktu tempuh", "waktu_tempuh", 50}, {"transportasi", "Transportasi", "transportasi", 50}} {
		add("Alamat", f.n, f.l, f.k, "text", f.m)
	}
	for _, who := range []string{"ayah", "ibu", "wali"} {
		group := "Data " + strings.ToUpper(who[:1]) + who[1:]
		name := who + "_kandung"
		key := who + "_nama"
		if who == "wali" {
			name = "wali_nama"
		}
		add(group, name, "Nama "+who, key, "text", 100)
		add(group, who+"_nik", "NIK "+who, who+"_nik", "text", 20)
		add(group, who+"_tempat_lahir", "Tempat lahir", who+"_tempat_lahir", "text", 100)
		add(group, who+"_tanggal_lahir", "Tanggal lahir", who+"_tanggal_lahir", "date", 0)
		if who != "wali" {
			add(group, who+"_status", "Status", who+"_status", "select", 20, "Masih Hidup", "Meninggal")
		}
		for _, f := range []struct {
			n, l string
			m    int
		}{{"pendidikan", "Pendidikan", 50}, {"pekerjaan", "Pekerjaan", 100}, {"penghasilan", "Penghasilan", 50}, {"no_hp", "Nomor telepon", 20}, {"alamat", "Alamat", 0}, {"status_rumah", "Status rumah", 50}} {
			add(group, who+"_"+f.n, f.l, who+"_"+f.n, "text", f.m)
		}
	}
	add("Data Wali", "wali_hubungan", "Hubungan dengan siswa", "wali_hubungan", "text", 50)
	add("Akademik", "status_siswa", "Status siswa", "status_siswa", "select", 6, "aktif", "lulus", "pindah")
	return out
}
func fieldEditable(role, name string) bool {
	if name == "status_siswa" {
		return role == "admin"
	}
	if role == "siswa" {
		switch name {
		case "nisn", "ayah_no_hp", "ibu_no_hp":
			return false
		}
	}
	return true
}
func ConfiguredFields(role string, config map[string]bool) []Field {
	fields := Fields()
	disabled := "rt rw dusun desa kota kode_pos provinsi kabupaten kecamatan kelurahan status_tempat_tinggal jarak_sekolah waktu_tempuh transportasi no_kip yang_membiayai kebutuhan_khusus kebutuhan_disabilitas ayah_nik ayah_tempat_lahir ayah_tanggal_lahir ayah_status ayah_pendidikan ayah_pekerjaan ayah_penghasilan ayah_alamat ayah_status_rumah ibu_nik ibu_tempat_lahir ibu_tanggal_lahir ibu_status ibu_pendidikan ibu_pekerjaan ibu_penghasilan ibu_alamat ibu_status_rumah wali_nama wali_hubungan wali_nik wali_no_hp wali_tempat_lahir wali_tanggal_lahir wali_pendidikan wali_pekerjaan wali_penghasilan wali_alamat wali_status_rumah"
	for i := range fields {
		f := &fields[i]
		f.Enabled = !strings.Contains(" "+disabled+" ", " "+f.Key+" ")
		if v, ok := config[f.Key]; ok {
			f.Enabled = v
		}
		switch f.Key {
		case "nisn", "nama", "jenis_kelamin", "tempat_lahir", "tanggal_lahir", "ayah_no_hp", "ibu_no_hp":
			f.Enabled = true
		}
		f.Editable = fieldEditable(role, f.Name)
	}
	return fields
}
