<?php
/**
 * View: Admin - Pengaturan Field Data Siswa
 * File: app/views/admin/pengaturan_field_siswa.php
 */
?>

<div class="content-wrapper bg-gray-50 min-h-screen">
    <div class="content-header px-6 py-4 border-b bg-white">
        <div class="container-fluid">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Pengaturan Field Data Siswa</h1>
                    <p class="text-gray-500 text-sm mt-1">Aktifkan atau nonaktifkan field yang ditampilkan pada form
                        data siswa</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content px-6 py-6">
        <form action="<?= BASEURL; ?>/admin/simpanPengaturanFieldSiswa" method="POST" id="formFieldSiswa">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Data Identitas -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="user" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Data Identitas
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $identitasFields = [
                            'nisn' => 'NISN',
                            'nama' => 'Nama Lengkap',
                            'jenis_kelamin' => 'Jenis Kelamin',
                            'tempat_lahir' => 'Tempat Lahir',
                            'tanggal_lahir' => 'Tanggal Lahir',
                            'nik' => 'NIK',
                            'password' => 'Password Baru',
                            'agama' => 'Agama',
                            'anak_ke' => 'Anak Ke',
                            'jumlah_saudara' => 'Jumlah Saudara',
                            'hobi' => 'Hobi',
                            'cita_cita' => 'Cita-cita',
                            'no_wa' => 'No. WhatsApp',
                            'email' => 'Email',
                            'no_kip' => 'No. KIP',
                            'yang_membiayai' => 'Yang Membiayai',
                            'kebutuhan_khusus' => 'Kebutuhan Khusus'
                        ];
                        foreach ($identitasFields as $key => $label):
                            $isMandatory = in_array($key, $data['mandatoryFields']);
                            $isChecked = $data['fieldConfig'][$key] ?? true;
                            ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="text-gray-700 text-sm">
                                        <?= $label; ?>
                                    </span>
                                    <?php if ($isMandatory): ?>
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full flex items-center">
                                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Wajib
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="fields[<?= $key; ?>]" value="1" class="sr-only peer"
                                        <?= $isChecked ? 'checked' : ''; ?>     <?= $isMandatory ? 'disabled checked' : ''; ?>>
                                    <?php if ($isMandatory): ?>
                                        <input type="hidden" name="fields[<?= $key; ?>]" value="1">
                                    <?php endif; ?>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 <?= $isMandatory ? 'opacity-60' : ''; ?>">
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-green-500"></i>
                        Alamat
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $alamatFields = [
                            'alamat' => 'Alamat Lengkap',
                            'rt' => 'RT',
                            'rw' => 'RW',
                            'dusun' => 'Dusun',
                            'kode_pos' => 'Kode Pos',
                            'provinsi' => 'Provinsi',
                            'kabupaten' => 'Kabupaten/Kota',
                            'kecamatan' => 'Kecamatan',
                            'kelurahan' => 'Kelurahan/Desa',
                            'status_tempat_tinggal' => 'Status Tempat Tinggal',
                            'jarak_sekolah' => 'Jarak ke Sekolah',
                            'transportasi' => 'Transportasi'
                        ];
                        foreach ($alamatFields as $key => $label):
                            $isChecked = $data['fieldConfig'][$key] ?? true;
                            ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <span class="text-gray-700 text-sm">
                                    <?= $label; ?>
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="fields[<?= $key; ?>]" value="1" class="sr-only peer"
                                        <?= $isChecked ? 'checked' : ''; ?>>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Data Ayah -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-orange-500 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="user-circle" class="w-5 h-5 mr-2 text-orange-500"></i>
                        Data Ayah
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $ayahFields = [
                            'ayah_nama' => 'Nama Ayah',
                            'ayah_no_hp' => 'No. HP Ayah',
                            'ayah_nik' => 'NIK Ayah',
                            'ayah_tempat_lahir' => 'Tempat Lahir',
                            'ayah_tanggal_lahir' => 'Tanggal Lahir',
                            'ayah_status' => 'Status (Hidup/Meninggal)',
                            'ayah_pendidikan' => 'Pendidikan',
                            'ayah_pekerjaan' => 'Pekerjaan',
                            'ayah_penghasilan' => 'Penghasilan'
                        ];
                        foreach ($ayahFields as $key => $label):
                            $isMandatory = in_array($key, $data['mandatoryFields']);
                            $isChecked = $data['fieldConfig'][$key] ?? true;
                            ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="text-gray-700 text-sm">
                                        <?= $label; ?>
                                    </span>
                                    <?php if ($isMandatory): ?>
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded-full flex items-center">
                                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Wajib
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="fields[<?= $key; ?>]" value="1" class="sr-only peer"
                                        <?= $isChecked ? 'checked' : ''; ?>     <?= $isMandatory ? 'disabled checked' : ''; ?>>
                                    <?php if ($isMandatory): ?>
                                        <input type="hidden" name="fields[<?= $key; ?>]" value="1">
                                    <?php endif; ?>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 <?= $isMandatory ? 'opacity-60' : ''; ?>">
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Data Ibu -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-pink-500 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="user-circle-2" class="w-5 h-5 mr-2 text-pink-500"></i>
                        Data Ibu
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $ibuFields = [
                            'ibu_nama' => 'Nama Ibu',
                            'ibu_no_hp' => 'No. HP Ibu',
                            'ibu_nik' => 'NIK Ibu',
                            'ibu_tempat_lahir' => 'Tempat Lahir',
                            'ibu_tanggal_lahir' => 'Tanggal Lahir',
                            'ibu_status' => 'Status (Hidup/Meninggal)',
                            'ibu_pendidikan' => 'Pendidikan',
                            'ibu_pekerjaan' => 'Pekerjaan',
                            'ibu_penghasilan' => 'Penghasilan'
                        ];
                        foreach ($ibuFields as $key => $label):
                            $isMandatory = in_array($key, $data['mandatoryFields']);
                            $isChecked = $data['fieldConfig'][$key] ?? true;
                            ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div class="flex items-center">
                                    <span class="text-gray-700 text-sm">
                                        <?= $label; ?>
                                    </span>
                                    <?php if ($isMandatory): ?>
                                        <span
                                            class="ml-2 px-2 py-0.5 text-xs bg-pink-100 text-pink-700 rounded-full flex items-center">
                                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Wajib
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="fields[<?= $key; ?>]" value="1" class="sr-only peer"
                                        <?= $isChecked ? 'checked' : ''; ?>     <?= $isMandatory ? 'disabled checked' : ''; ?>>
                                    <?php if ($isMandatory): ?>
                                        <input type="hidden" name="fields[<?= $key; ?>]" value="1">
                                    <?php endif; ?>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 <?= $isMandatory ? 'opacity-60' : ''; ?>">
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Data Wali -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="users" class="w-5 h-5 mr-2 text-purple-500"></i>
                        Data Wali (Opsional)
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $waliFields = [
                            'wali_nama' => 'Nama Wali',
                            'wali_hubungan' => 'Hubungan dengan Siswa',
                            'wali_nik' => 'NIK Wali',
                            'wali_no_hp' => 'No. HP Wali',
                            'wali_pendidikan' => 'Pendidikan',
                            'wali_pekerjaan' => 'Pekerjaan',
                            'wali_penghasilan' => 'Penghasilan'
                        ];
                        foreach ($waliFields as $key => $label):
                            $isChecked = $data['fieldConfig'][$key] ?? false;
                            ?>
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <span class="text-gray-700 text-sm">
                                    <?= $label; ?>
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="fields[<?= $key; ?>]" value="1" class="sr-only peer"
                                        <?= $isChecked ? 'checked' : ''; ?>>
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Save Button -->
            <div class="mt-8 flex justify-center">
                <button type="submit"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all flex items-center">
                    <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>