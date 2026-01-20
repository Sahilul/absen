<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .collapse-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .collapse-content.open {
            max-height: 2000px;
        }

        /* Responsive form fields - auto-arrange when siblings hidden */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row>div {
            flex: 1 1 200px;
            min-width: 200px;
            max-width: 100%;
        }

        @media (min-width: 768px) {
            .form-row>div {
                flex: 1 1 250px;
                max-width: 50%;
            }
        }

        @media (min-width: 1024px) {
            .form-row>div {
                max-width: 33.333%;
            }
        }

        /* For 4-column rows */
        .form-row-4>div {
            flex: 1 1 150px;
            min-width: 150px;
        }

        @media (min-width: 768px) {
            .form-row-4>div {
                max-width: 25%;
            }
        }

        /* For 2-column rows */
        .form-row-2>div {
            flex: 1 1 250px;
        }

        @media (min-width: 768px) {
            .form-row-2>div {
                max-width: 50%;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    $fc = $data['fieldConfig'] ?? [];
    ?>
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div class="flex items-center">
                <a href="<?= BASEURL; ?>/admin/siswa"
                    class="text-gray-500 hover:text-indigo-600 mr-4 p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Tambah Data Siswa Baru</h2>
                    <p class="text-gray-600 mt-1">Lengkapi informasi siswa untuk membuat akun baru</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden max-w-5xl mx-auto">
            <div class="p-6">
                <form action="<?= BASEURL; ?>/admin/prosesTambahSiswa" method="POST" enctype="multipart/form-data">

                    <!-- Section 1: Data Identitas -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between cursor-pointer p-3 bg-indigo-50 rounded-lg mb-4"
                            onclick="toggleSection('identitas')">
                            <h3 class="text-lg font-bold text-indigo-800 flex items-center gap-2">
                                <i data-lucide="user-circle" class="w-5 h-5"></i> Data Identitas
                            </h3>
                            <i data-lucide="chevron-down" id="icon-identitas"
                                class="w-5 h-5 text-indigo-600 transition-transform"></i>
                        </div>
                        <div id="section-identitas" class="collapse-content open space-y-4 px-2">
                            <div class="form-row">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">NISN <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nisn" required maxlength="20"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="0123456789">
                                </div>
                                <?php if ($fc['nik'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                                        <input type="text" name="nik" maxlength="16"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="16 digit NIK">
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span
                                            class="text-red-500">*</span></label>
                                    <input type="password" name="password" required minlength="6"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Min 6 karakter">
                                </div>
                            </div>
                            <div class="form-row form-row-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="nama_siswa" required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Nama lengkap sesuai akta">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span
                                            class="text-red-500">*</span></label>
                                    <select name="jenis_kelamin" required
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                        <option value="">-- Pilih --</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Kota kelahiran">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <?php if ($fc['agama'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                                        <select name="agama"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Kristen">Kristen</option>
                                            <option value="Katolik">Katolik</option>
                                            <option value="Hindu">Hindu</option>
                                            <option value="Buddha">Buddha</option>
                                            <option value="Konghucu">Konghucu</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row form-row-4">
                                <?php if ($fc['anak_ke'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Anak Ke</label>
                                        <input type="number" name="anak_ke" min="1"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="1">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['jumlah_saudara'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Saudara</label>
                                        <input type="number" name="jumlah_saudara" min="0"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="0">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['hobi'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hobi</label>
                                        <input type="text" name="hobi"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Membaca, Olahraga">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['cita_cita'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cita-cita</label>
                                        <input type="text" name="cita_cita"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Dokter, Guru">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row form-row-2">
                                <?php if ($fc['no_wa'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                                        <input type="text" name="no_wa"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="081234567890">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['email'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="siswa@email.com">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['no_kip'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No. KIP</label>
                                        <input type="text" name="kip"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Nomor KIP jika ada">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['yang_membiayai'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Yang Membiayai</label>
                                        <select name="yang_membiayai"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="Orang Tua">Orang Tua</option>
                                            <option value="Wali">Wali</option>
                                            <option value="Beasiswa">Beasiswa</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['kebutuhan_khusus'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan Khusus</label>
                                        <select name="kebutuhan_khusus"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="Tidak Ada">Tidak Ada</option>
                                            <option value="Tuna Rungu">Tuna Rungu</option>
                                            <option value="Tuna Netra">Tuna Netra</option>
                                            <option value="Tuna Daksa">Tuna Daksa</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Alamat -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between cursor-pointer p-3 bg-green-50 rounded-lg mb-4"
                            onclick="toggleSection('alamat')">
                            <h3 class="text-lg font-bold text-green-800 flex items-center gap-2">
                                <i data-lucide="map-pin" class="w-5 h-5"></i> Alamat Tempat Tinggal
                            </h3>
                            <i data-lucide="chevron-down" id="icon-alamat"
                                class="w-5 h-5 text-green-600 transition-transform"></i>
                        </div>
                        <div id="section-alamat" class="collapse-content open space-y-4 px-2">
                            <?php if ($fc['alamat'] ?? true): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="2"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Jalan, Gang, Nomor Rumah"></textarea>
                                </div>
                            <?php endif; ?>
                            <div class="form-row form-row-4">
                                <?php if ($fc['rt'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">RT</label>
                                        <input type="text" name="rt" maxlength="3"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="001">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['rw'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">RW</label>
                                        <input type="text" name="rw" maxlength="3"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="001">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['dusun'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dusun</label>
                                        <input type="text" name="dusun"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Nama dusun">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['kode_pos'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                                        <input type="text" name="kode_pos" maxlength="5"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="12345">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row form-row-2">
                                <?php if ($fc['provinsi'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                                        <select name="provinsi" id="provinsi"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih Provinsi --</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['kabupaten'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                                        <select name="kabupaten" id="kabupaten"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white"
                                            disabled>
                                            <option value="">-- Pilih Kabupaten --</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row form-row-2">
                                <?php if ($fc['kecamatan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                                        <select name="kecamatan" id="kecamatan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white"
                                            disabled>
                                            <option value="">-- Pilih Kecamatan --</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['kelurahan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                                        <select name="kelurahan" id="kelurahan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white"
                                            disabled>
                                            <option value="">-- Pilih Kelurahan --</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['status_tempat_tinggal'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Tempat
                                            Tinggal</label>
                                        <select name="status_tempat_tinggal"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="Milik Sendiri">Milik Sendiri</option>
                                            <option value="Kontrak">Kontrak</option>
                                            <option value="Kos">Kos</option>
                                            <option value="Menumpang">Menumpang</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['jarak_ke_sekolah'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jarak ke Sekolah</label>
                                        <select name="jarak_ke_sekolah"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="< 1 km">
                                                < 1 km</option>
                                            <option value="1-5 km">1-5 km</option>
                                            <option value="5-10 km">5-10 km</option>
                                            <option value="> 10 km">> 10 km</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['transportasi'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Transportasi</label>
                                        <select name="transportasi"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="Jalan Kaki">Jalan Kaki</option>
                                            <option value="Sepeda">Sepeda</option>
                                            <option value="Motor">Motor</option>
                                            <option value="Mobil">Mobil</option>
                                            <option value="Angkutan Umum">Angkutan Umum</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Data Ayah -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between cursor-pointer p-3 bg-blue-50 rounded-lg mb-4"
                            onclick="toggleSection('ayah')">
                            <h3 class="text-lg font-bold text-blue-800 flex items-center gap-2">
                                <i data-lucide="user" class="w-5 h-5"></i> Data Ayah
                            </h3>
                            <i data-lucide="chevron-down" id="icon-ayah"
                                class="w-5 h-5 text-blue-600 transition-transform"></i>
                        </div>
                        <div id="section-ayah" class="collapse-content space-y-4 px-2">
                            <div class="form-row form-row-2">
                                <?php if ($fc['ayah_nama'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                                        <input type="text" name="ayah_kandung"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Nama lengkap ayah">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ayah_nik'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ayah</label>
                                        <input type="text" name="ayah_nik" maxlength="16"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="16 digit NIK">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['ayah_tempat_lahir'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                                        <input type="text" name="ayah_tempat_lahir"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ayah_tanggal_lahir'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                        <input type="date" name="ayah_tanggal_lahir"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ayah_status'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="ayah_status"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="Masih Hidup">Masih Hidup</option>
                                            <option value="Meninggal">Meninggal</option>
                                            <option value="Tidak Diketahui">Tidak Diketahui</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['ayah_pendidikan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                                        <select name="ayah_pendidikan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="SD">SD</option>
                                            <option value="SMP">SMP</option>
                                            <option value="SMA">SMA</option>
                                            <option value="D3">D3</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ayah_pekerjaan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                                        <input type="text" name="ayah_pekerjaan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Wiraswasta, PNS, dll">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ayah_penghasilan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                                        <select name="ayah_penghasilan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="< 1 Juta">
                                                < 1 Juta</option>
                                            <option value="1-3 Juta">1-3 Juta</option>
                                            <option value="3-5 Juta">3-5 Juta</option>
                                            <option value="5-10 Juta">5-10 Juta</option>
                                            <option value="> 10 Juta">> 10 Juta</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($fc['ayah_no_hp'] ?? true): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ayah</label>
                                    <input type="text" name="ayah_no_hp"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Section 4: Data Ibu -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between cursor-pointer p-3 bg-pink-50 rounded-lg mb-4"
                            onclick="toggleSection('ibu')">
                            <h3 class="text-lg font-bold text-pink-800 flex items-center gap-2">
                                <i data-lucide="heart" class="w-5 h-5"></i> Data Ibu
                            </h3>
                            <i data-lucide="chevron-down" id="icon-ibu"
                                class="w-5 h-5 text-pink-600 transition-transform"></i>
                        </div>
                        <div id="section-ibu" class="collapse-content space-y-4 px-2">
                            <div class="form-row form-row-2">
                                <?php if ($fc['ibu_nama'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                                        <input type="text" name="ibu_kandung"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Nama lengkap ibu">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ibu_nik'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ibu</label>
                                        <input type="text" name="ibu_nik" maxlength="16"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="16 digit NIK">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['ibu_tempat_lahir'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                                        <input type="text" name="ibu_tempat_lahir"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ibu_tanggal_lahir'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                        <input type="date" name="ibu_tanggal_lahir"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ibu_status'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="ibu_status"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="Masih Hidup">Masih Hidup</option>
                                            <option value="Meninggal">Meninggal</option>
                                            <option value="Tidak Diketahui">Tidak Diketahui</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['ibu_pendidikan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                                        <select name="ibu_pendidikan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="SD">SD</option>
                                            <option value="SMP">SMP</option>
                                            <option value="SMA">SMA</option>
                                            <option value="D3">D3</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ibu_pekerjaan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                                        <input type="text" name="ibu_pekerjaan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="IRT, PNS, dll">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['ibu_penghasilan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                                        <select name="ibu_penghasilan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="< 1 Juta">
                                                < 1 Juta</option>
                                            <option value="1-3 Juta">1-3 Juta</option>
                                            <option value="3-5 Juta">3-5 Juta</option>
                                            <option value="5-10 Juta">5-10 Juta</option>
                                            <option value="> 10 Juta">> 10 Juta</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($fc['ibu_no_hp'] ?? true): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ibu</label>
                                    <input type="text" name="ibu_no_hp"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Section 5: Data Wali -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between cursor-pointer p-3 bg-amber-50 rounded-lg mb-4"
                            onclick="toggleSection('wali')">
                            <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                                <i data-lucide="users" class="w-5 h-5"></i> Data Wali (Opsional)
                            </h3>
                            <i data-lucide="chevron-down" id="icon-wali"
                                class="w-5 h-5 text-amber-600 transition-transform"></i>
                        </div>
                        <div id="section-wali" class="collapse-content space-y-4 px-2">
                            <div class="form-row form-row-2">
                                <?php if ($fc['wali_nama'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Wali</label>
                                        <input type="text" name="wali_nama"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Nama lengkap wali">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['wali_hubungan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan dengan
                                            Siswa</label>
                                        <select name="wali_hubungan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="Kakek">Kakek</option>
                                            <option value="Nenek">Nenek</option>
                                            <option value="Paman">Paman</option>
                                            <option value="Bibi">Bibi</option>
                                            <option value="Kakak">Kakak</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row form-row-2">
                                <?php if ($fc['wali_nik'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Wali</label>
                                        <input type="text" name="wali_nik" maxlength="16"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['wali_no_hp'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Wali</label>
                                        <input type="text" name="wali_no_hp"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                            placeholder="08xxxxxxxxxx">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <?php if ($fc['wali_pendidikan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                                        <select name="wali_pendidikan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="SD">SD</option>
                                            <option value="SMP">SMP</option>
                                            <option value="SMA">SMA</option>
                                            <option value="D3">D3</option>
                                            <option value="S1">S1</option>
                                            <option value="S2">S2</option>
                                            <option value="S3">S3</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['wali_pekerjaan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                                        <input type="text" name="wali_pekerjaan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                <?php endif; ?>
                                <?php if ($fc['wali_penghasilan'] ?? true): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                                        <select name="wali_penghasilan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="< 1 Juta">
                                                < 1 Juta</option>
                                            <option value="1-3 Juta">1-3 Juta</option>
                                            <option value="3-5 Juta">3-5 Juta</option>
                                            <option value="5-10 Juta">5-10 Juta</option>
                                            <option value="> 10 Juta">> 10 Juta</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-end gap-3">
                            <a href="<?= BASEURL; ?>/admin/siswa"
                                class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-6 rounded-lg text-center flex items-center justify-center">
                                <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
                            </a>
                            <button type="submit"
                                class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Data Siswa
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Toggle section collapse
        function toggleSection(sectionId) {
            const section = document.getElementById('section-' + sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            section.classList.toggle('open');
            icon.style.transform = section.classList.contains('open') ? 'rotate(0deg)' : 'rotate(-90deg)';
        }

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
            loadProvinsi();
        });

        // API Alamat Indonesia - using emsifa API
        const API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

        async function loadProvinsi() {
            try {
                const res = await fetch(API_BASE + '/provinces.json');
                const data = await res.json();
                const select = document.getElementById('provinsi');
                data.forEach(p => {
                    select.innerHTML += `<option value="${p.name}" data-id="${p.id}">${p.name}</option>`;
                });
            } catch (e) { console.error('Gagal load provinsi:', e); }
        }

        document.getElementById('provinsi').addEventListener('change', async function () {
            const provId = this.options[this.selectedIndex].dataset.id;
            const kabSelect = document.getElementById('kabupaten');
            kabSelect.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
            kabSelect.disabled = true;
            document.getElementById('kecamatan').innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            document.getElementById('kecamatan').disabled = true;
            document.getElementById('kelurahan').innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
            document.getElementById('kelurahan').disabled = true;

            if (!provId) return;

            try {
                const res = await fetch(API_BASE + '/regencies/' + provId + '.json');
                const data = await res.json();
                data.forEach(k => {
                    kabSelect.innerHTML += `<option value="${k.name}" data-id="${k.id}">${k.name}</option>`;
                });
                kabSelect.disabled = false;
            } catch (e) { console.error('Gagal load kabupaten:', e); }
        });

        document.getElementById('kabupaten').addEventListener('change', async function () {
            const kabId = this.options[this.selectedIndex].dataset.id;
            const kecSelect = document.getElementById('kecamatan');
            kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            kecSelect.disabled = true;
            document.getElementById('kelurahan').innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
            document.getElementById('kelurahan').disabled = true;

            if (!kabId) return;

            try {
                const res = await fetch(API_BASE + '/districts/' + kabId + '.json');
                const data = await res.json();
                data.forEach(k => {
                    kecSelect.innerHTML += `<option value="${k.name}" data-id="${k.id}">${k.name}</option>`;
                });
                kecSelect.disabled = false;
            } catch (e) { console.error('Gagal load kecamatan:', e); }
        });

        document.getElementById('kecamatan').addEventListener('change', async function () {
            const kecId = this.options[this.selectedIndex].dataset.id;
            const kelSelect = document.getElementById('kelurahan');
            kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
            kelSelect.disabled = true;

            if (!kecId) return;

            try {
                const res = await fetch(API_BASE + '/villages/' + kecId + '.json');
                const data = await res.json();
                data.forEach(k => {
                    kelSelect.innerHTML += `<option value="${k.name}">${k.name}</option>`;
                });
                kelSelect.disabled = false;
            } catch (e) { console.error('Gagal load kelurahan:', e); }
        });
    </script>
</body>

</html>