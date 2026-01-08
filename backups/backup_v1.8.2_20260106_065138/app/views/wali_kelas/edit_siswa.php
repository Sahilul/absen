<?php $s = $data['siswa'] ?? []; ?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1"><?= $data['judul'] ?></h4>
                    <p class="text-slate-500 text-sm">
                        <span
                            class="font-semibold text-slate-700"><?= htmlspecialchars($data['wali_kelas_info']['nama_kelas'] ?? '-') ?></span>
                        <span class="mx-2">•</span>
                        <span
                            class="font-semibold text-slate-700"><?= htmlspecialchars($data['session_info']['nama_semester'] ?? '') ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form Edit Siswa -->
    <div class="bg-white shadow-sm rounded-xl p-4 md:p-6">
        <form action="<?= BASEURL ?>/waliKelas/updateSiswa" method="POST">
            <input type="hidden" name="id_siswa" value="<?= $s['id_siswa'] ?? '' ?>">

            <!-- Section: Data Identitas -->
            <div class="mb-6">
                <h3
                    class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b-2 border-indigo-200 flex items-center gap-2">
                    <i data-lucide="user-circle" class="w-5 h-5 text-indigo-600"></i>
                    Data Identitas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NISN <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nisn" required maxlength="20"
                            value="<?= htmlspecialchars($s['nisn'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                        <input type="text" name="nik" maxlength="16" value="<?= htmlspecialchars($s['nik'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                        <select name="agama"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $ag): ?>
                                <option value="<?= $ag ?>" <?= ($s['agama'] ?? '') == $ag ? 'selected' : '' ?>><?= $ag ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_siswa" required
                            value="<?= htmlspecialchars($s['nama_siswa'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span
                                class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <option value="L" <?= ($s['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki
                            </option>
                            <option value="P" <?= ($s['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan
                            </option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($s['tempat_lahir'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" value="<?= $s['tgl_lahir'] ?? '' ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anak Ke</label>
                        <input type="number" name="anak_ke" min="1" value="<?= $s['anak_ke'] ?? '1' ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Saudara</label>
                        <input type="number" name="jumlah_saudara" min="0" value="<?= $s['jumlah_saudara'] ?? '0' ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hobi</label>
                        <input type="text" name="hobi" value="<?= htmlspecialchars($s['hobi'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cita-cita</label>
                        <input type="text" name="cita_cita" value="<?= htmlspecialchars($s['cita_cita'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                        <input type="text" name="no_wa" value="<?= htmlspecialchars($s['no_wa'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($s['email'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Section: Alamat -->
            <div class="mb-6">
                <h3
                    class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b-2 border-green-200 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-green-600"></i>
                    Alamat Tempat Tinggal
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($s['alamat'] ?? '') ?></textarea>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RT</label>
                        <input type="text" name="rt" maxlength="3" value="<?= htmlspecialchars($s['rt'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RW</label>
                        <input type="text" name="rw" maxlength="3" value="<?= htmlspecialchars($s['rw'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dusun</label>
                        <input type="text" name="dusun" value="<?= htmlspecialchars($s['dusun'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                        <input type="text" name="kode_pos" maxlength="5"
                            value="<?= htmlspecialchars($s['kode_pos'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                        <select name="provinsi" id="provinsi"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                        <input type="hidden" name="id_provinsi" id="id_provinsi"
                            value="<?= htmlspecialchars($s['id_provinsi'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                        <select name="kabupaten" id="kabupaten"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih Kabupaten --</option>
                        </select>
                        <input type="hidden" name="id_kabupaten" id="id_kabupaten"
                            value="<?= htmlspecialchars($s['id_kabupaten'] ?? '') ?>">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                        <select name="kecamatan" id="kecamatan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih Kecamatan --</option>
                        </select>
                        <input type="hidden" name="id_kecamatan" id="id_kecamatan"
                            value="<?= htmlspecialchars($s['id_kecamatan'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan/Desa</label>
                        <select name="kelurahan" id="kelurahan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih Kelurahan --</option>
                        </select>
                        <input type="hidden" name="id_kelurahan" id="id_kelurahan"
                            value="<?= htmlspecialchars($s['id_kelurahan'] ?? '') ?>">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Tempat Tinggal</label>
                        <select name="status_tempat_tinggal"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['Milik Sendiri', 'Kontrak', 'Kos', 'Menumpang'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['status_tempat_tinggal'] ?? '') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jarak ke Sekolah</label>
                        <select name="jarak_ke_sekolah"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< 1 km', '1-5 km', '5-10 km', '> 10 km'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['jarak_ke_sekolah'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transportasi</label>
                        <select name="transportasi"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['Jalan Kaki', 'Sepeda', 'Motor', 'Mobil', 'Angkutan Umum'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['transportasi'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section: Data Ayah -->
            <div class="mb-6">
                <h3
                    class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b-2 border-blue-200 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                    Data Ayah
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                        <input type="text" name="ayah_kandung" value="<?= htmlspecialchars($s['ayah_kandung'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ayah</label>
                        <input type="text" name="ayah_nik" maxlength="16"
                            value="<?= htmlspecialchars($s['ayah_nik'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="ayah_tempat_lahir"
                            value="<?= htmlspecialchars($s['ayah_tempat_lahir'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="ayah_tanggal_lahir" value="<?= $s['ayah_tanggal_lahir'] ?? '' ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="ayah_status"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <?php foreach (['Masih Hidup', 'Meninggal', 'Tidak Diketahui'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ayah_status'] ?? 'Masih Hidup') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                        <select name="ayah_pendidikan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ayah_pendidikan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                        <input type="text" name="ayah_pekerjaan"
                            value="<?= htmlspecialchars($s['ayah_pekerjaan'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                        <select name="ayah_penghasilan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< 1 Juta', '1-3 Juta', '3-5 Juta', '5-10 Juta', '> 10 Juta'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ayah_penghasilan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ayah</label>
                    <input type="text" name="ayah_no_hp" value="<?= htmlspecialchars($s['ayah_no_hp'] ?? '') ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Section: Data Ibu -->
            <div class="mb-6">
                <h3
                    class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b-2 border-pink-200 flex items-center gap-2">
                    <i data-lucide="heart" class="w-5 h-5 text-pink-600"></i>
                    Data Ibu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                        <input type="text" name="ibu_kandung" value="<?= htmlspecialchars($s['ibu_kandung'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Ibu</label>
                        <input type="text" name="ibu_nik" maxlength="16"
                            value="<?= htmlspecialchars($s['ibu_nik'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="ibu_tempat_lahir"
                            value="<?= htmlspecialchars($s['ibu_tempat_lahir'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="ibu_tanggal_lahir" value="<?= $s['ibu_tanggal_lahir'] ?? '' ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="ibu_status"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <?php foreach (['Masih Hidup', 'Meninggal', 'Tidak Diketahui'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ibu_status'] ?? 'Masih Hidup') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                        <select name="ibu_pendidikan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ibu_pendidikan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                        <input type="text" name="ibu_pekerjaan"
                            value="<?= htmlspecialchars($s['ibu_pekerjaan'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                        <select name="ibu_penghasilan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< 1 Juta', '1-3 Juta', '3-5 Juta', '5-10 Juta', '> 10 Juta'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['ibu_penghasilan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Ibu</label>
                    <input type="text" name="ibu_no_hp" value="<?= htmlspecialchars($s['ibu_no_hp'] ?? '') ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Section: Data Wali -->
            <div class="mb-6">
                <h3
                    class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b-2 border-amber-200 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-amber-600"></i>
                    Data Wali (Opsional)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Wali</label>
                        <input type="text" name="wali_nama" value="<?= htmlspecialchars($s['wali_nama'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan dengan Siswa</label>
                        <select name="wali_hubungan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['Kakek', 'Nenek', 'Paman', 'Bibi', 'Kakak', 'Lainnya'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['wali_hubungan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK Wali</label>
                        <input type="text" name="wali_nik" maxlength="16"
                            value="<?= htmlspecialchars($s['wali_nik'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP Wali</label>
                        <input type="text" name="wali_no_hp" value="<?= htmlspecialchars($s['wali_no_hp'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                        <select name="wali_pendidikan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['wali_pendidikan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                        <input type="text" name="wali_pekerjaan"
                            value="<?= htmlspecialchars($s['wali_pekerjaan'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan</label>
                        <select name="wali_penghasilan"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< 1 Juta', '1-3 Juta', '3-5 Juta', '5-10 Juta', '> 10 Juta'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($s['wali_penghasilan'] ?? '') == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <a href="<?= BASEURL ?>/waliKelas/daftarSiswa"
                        class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-6 rounded-lg text-center flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Data Siswa
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    const API_WILAYAH = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    const savedProvinsi = '<?= htmlspecialchars($s['provinsi'] ?? '') ?>';
    const savedKabupaten = '<?= htmlspecialchars($s['kabupaten'] ?? '') ?>';
    const savedKecamatan = '<?= htmlspecialchars($s['kecamatan'] ?? '') ?>';
    const savedKelurahan = '<?= htmlspecialchars($s['kelurahan'] ?? '') ?>';

    function toTitleCase(str) {
        return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
    }

    async function loadProvinsi() {
        const select = document.getElementById('provinsi');
        try {
            const res = await fetch(`${API_WILAYAH}/provinces.json`);
            const data = await res.json();

            select.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
            data.forEach(p => {
                const name = toTitleCase(p.name);
                const selected = savedProvinsi.toLowerCase() === name.toLowerCase() ? 'selected' : '';
                select.innerHTML += `<option value="${name}" data-id="${p.id}" ${selected}>${name}</option>`;
            });

            const selOpt = select.options[select.selectedIndex];
            if (selOpt && selOpt.dataset.id) {
                document.getElementById('id_provinsi').value = selOpt.dataset.id;
                await loadKabupaten(selOpt.dataset.id);
            }
        } catch (e) {
            console.error('Error loading provinsi:', e);
        }
    }

    async function loadKabupaten(provinsiId) {
        const select = document.getElementById('kabupaten');
        const kecSelect = document.getElementById('kecamatan');
        const kelSelect = document.getElementById('kelurahan');

        select.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
        kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';

        const provSelect = document.getElementById('provinsi');
        const provOpt = provSelect.options[provSelect.selectedIndex];
        if (provOpt && provOpt.dataset.id) {
            document.getElementById('id_provinsi').value = provOpt.dataset.id;
        }

        if (!provinsiId) return;

        try {
            const res = await fetch(`${API_WILAYAH}/regencies/${provinsiId}.json`);
            const data = await res.json();

            data.forEach(k => {
                const name = toTitleCase(k.name);
                const selected = savedKabupaten.toLowerCase() === name.toLowerCase() ? 'selected' : '';
                select.innerHTML += `<option value="${name}" data-id="${k.id}" ${selected}>${name}</option>`;
            });

            const selOpt = select.options[select.selectedIndex];
            if (selOpt && selOpt.dataset.id) {
                document.getElementById('id_kabupaten').value = selOpt.dataset.id;
                await loadKecamatan(selOpt.dataset.id);
            }
        } catch (e) {
            console.error('Error loading kabupaten:', e);
        }
    }

    async function loadKecamatan(kabupatenId) {
        const select = document.getElementById('kecamatan');
        const kelSelect = document.getElementById('kelurahan');

        select.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';

        const kabSelect = document.getElementById('kabupaten');
        const kabOpt = kabSelect.options[kabSelect.selectedIndex];
        if (kabOpt && kabOpt.dataset.id) {
            document.getElementById('id_kabupaten').value = kabOpt.dataset.id;
        }

        if (!kabupatenId) return;

        try {
            const res = await fetch(`${API_WILAYAH}/districts/${kabupatenId}.json`);
            const data = await res.json();

            data.forEach(k => {
                const name = toTitleCase(k.name);
                const selected = savedKecamatan.toLowerCase() === name.toLowerCase() ? 'selected' : '';
                select.innerHTML += `<option value="${name}" data-id="${k.id}" ${selected}>${name}</option>`;
            });

            const selOpt = select.options[select.selectedIndex];
            if (selOpt && selOpt.dataset.id) {
                document.getElementById('id_kecamatan').value = selOpt.dataset.id;
                await loadKelurahan(selOpt.dataset.id);
            }
        } catch (e) {
            console.error('Error loading kecamatan:', e);
        }
    }

    async function loadKelurahan(kecamatanId) {
        const select = document.getElementById('kelurahan');

        select.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';

        const kecSelect = document.getElementById('kecamatan');
        const kecOpt = kecSelect.options[kecSelect.selectedIndex];
        if (kecOpt && kecOpt.dataset.id) {
            document.getElementById('id_kecamatan').value = kecOpt.dataset.id;
        }

        if (!kecamatanId) return;

        try {
            const res = await fetch(`${API_WILAYAH}/villages/${kecamatanId}.json`);
            const data = await res.json();

            data.forEach(k => {
                const name = toTitleCase(k.name);
                const selected = savedKelurahan.toLowerCase() === name.toLowerCase() ? 'selected' : '';
                select.innerHTML += `<option value="${name}" data-id="${k.id}" ${selected}>${name}</option>`;
            });

            const selOpt = select.options[select.selectedIndex];
            if (selOpt && selOpt.dataset.id) {
                document.getElementById('id_kelurahan').value = selOpt.dataset.id;
            }
        } catch (e) {
            console.error('Error loading kelurahan:', e);
        }
    }

    document.getElementById('provinsi')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('id_provinsi').value = opt?.dataset.id || '';
        loadKabupaten(opt?.dataset.id || '');
    });

    document.getElementById('kabupaten')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('id_kabupaten').value = opt?.dataset.id || '';
        loadKecamatan(opt?.dataset.id || '');
    });

    document.getElementById('kecamatan')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('id_kecamatan').value = opt?.dataset.id || '';
        loadKelurahan(opt?.dataset.id || '');
    });

    document.getElementById('kelurahan')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('id_kelurahan').value = opt?.dataset.id || '';
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        loadProvinsi();
    });
</script>