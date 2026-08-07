<?php
// app/views/surat_tugas/surat/form.php
// UPDATED: Identity Type Selector, Removed Pangkat/Keterangan
$isEdit = isset($data['surat']);
$s = $data['surat'] ?? [];
$petugasList = $data['petugas_list'] ?? [];

// Jika create new, siapin 1 row kosong untuk petugas
if (!$isEdit) {
    $petugasList = [[]];
}
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
         <div class="flex items-center gap-4 mb-6">
            <a href="<?= BASEURL; ?>/suratTugas/surat" class="p-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5 text-gray-700"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= $data['judul']; ?></h1>
                <p class="text-sm text-gray-500">Buat atau edit data surat tugas</p>
            </div>
        </div>

        <form action="<?= BASEURL; ?>/suratTugas/simpanSurat" method="POST" class="space-y-6">
            <input type="hidden" name="id_surat" value="<?= $s['id_surat'] ?? ''; ?>">
            
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Info Surat -->
                <div class="xl:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                        <div class="flex items-center gap-2 mb-6 text-indigo-700 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            <h3 class="font-bold">Detail Surat</h3>
                        </div>
                        
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Lembaga <span class="text-red-500">*</span></label>
                            <select name="id_lembaga" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 font-medium" required>
                                <option value="">-- Pilih Lembaga --</option>
                                <?php foreach ($data['lembaga_list'] as $l): ?>
                                    <option value="<?= $l['id_lembaga']; ?>" <?= (isset($s['id_lembaga']) && $s['id_lembaga'] == $l['id_lembaga']) ? 'selected' : ''; ?>>
                                        <?= $l['nama_lembaga']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Surat <span class="text-red-500">*</span></label>
                            <input type="text" name="nomor_surat" 
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium" 
                                required value="<?= $s['nomor_surat'] ?? ''; ?>" placeholder="No. Surat...">
                        </div>

                         <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Surat <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_surat" 
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 font-medium" 
                                required value="<?= $s['tanggal_surat'] ?? date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Perihal Tugas <span class="text-red-500">*</span></label>
                            <textarea name="perihal" rows="3" 
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium" 
                                required placeholder="Contoh: Mengikuti kegiatan pelatihan..."><?= $s['perihal'] ?? ''; ?></textarea>
                        </div>

                         <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tempat Tugas <span class="text-red-500">*</span></label>
                             <input type="text" name="tempat_tugas" 
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 placeholder-gray-400 font-medium" 
                                required value="<?= $s['tempat_tugas'] ?? ''; ?>" placeholder="Lokasi tugas...">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Tgl Mulai</label>
                                <input type="date" name="tanggal_mulai" 
                                    class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-gray-50 text-sm font-medium focus:border-indigo-600 focus:ring-indigo-100" 
                                    value="<?= $s['tanggal_mulai'] ?? ''; ?>">
                            </div>
                             <div>
                                <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">Tgl Selesai</label>
                                <input type="date" name="tanggal_selesai" 
                                    class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-gray-50 text-sm font-medium focus:border-indigo-600 focus:ring-indigo-100" 
                                    value="<?= $s['tanggal_selesai'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Daftar Petugas -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 h-full flex flex-col">
                         <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <div class="flex items-center gap-2 text-indigo-700 bg-indigo-50 p-2 rounded-lg border border-indigo-100">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <h3 class="font-bold">Daftar Petugas</h3>
                            </div>
                            <button type="button" id="btn-add-petugas" class="flex items-center gap-2 text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-md shadow-indigo-200">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Petugas
                            </button>
                        </div>
                        
                        <div id="petugas-container" class="space-y-6 flex-1">
                            <!-- Rows will be injected here -->
                            <?php foreach ($petugasList as $i => $p): ?>
                            <div class="petugas-row relative bg-gray-50 p-5 rounded-xl border-2 border-dashed border-gray-300 hover:border-indigo-300 transition-colors group">
                                <div class="absolute -top-3 -right-3">
                                    <button type="button" class="btn-remove-petugas bg-white text-red-500 border border-red-200 hover:bg-red-50 rounded-full p-2 shadow-sm transition <?= count($petugasList) == 1 ? 'hidden' : ''; ?>" title="Hapus Petugas">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_petugas[]" 
                                            class="w-full px-4 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 font-bold text-gray-800 placeholder-gray-400" 
                                            placeholder="Nama Lengkap & Gelar" value="<?= $p['nama_petugas'] ?? ''; ?>" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Identitas</label>
                                        <select name="jenis_identitas[]" class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium">
                                            <option value="NIK" <?= (isset($p['jenis_identitas']) && $p['jenis_identitas'] == 'NIK') ? 'selected' : ''; ?>>NIK</option>
                                            <option value="NIP" <?= (isset($p['jenis_identitas']) && $p['jenis_identitas'] == 'NIP') ? 'selected' : ''; ?>>NIP</option>
                                            <option value="NISN" <?= (isset($p['jenis_identitas']) && $p['jenis_identitas'] == 'NISN') ? 'selected' : ''; ?>>NISN</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nomor Identitas</label>
                                         <input type="text" name="identitas_petugas[]" 
                                            class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium" 
                                            placeholder="Nomor Identitas" value="<?= $p['identitas_petugas'] ?? ''; ?>">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jabatan</label>
                                         <input type="text" name="jabatan_petugas[]" 
                                            class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium" 
                                            placeholder="Jabatan" value="<?= $p['jabatan_petugas'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                         <a href="<?= BASEURL; ?>/suratTugas/surat" class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-100 transition">Batal</a>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-200 hover:shadow-indigo-300 transform hover:-translate-y-0.5">
                            <?= $isEdit ? 'Update Surat Tugas' : 'Terbitkan Surat Tugas'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('petugas-container');
    const btnAdd = document.getElementById('btn-add-petugas');

    // Template row HTML string - Updated Structure (No Pangkat/Keterangan, Added Jenis Identitas)
    const templateRow = `
        <div class="petugas-row relative bg-gray-50 p-5 rounded-xl border-2 border-dashed border-gray-300 hover:border-indigo-300 transition-colors group animate-fade-in-down">
            <div class="absolute -top-3 -right-3">
                <button type="button" class="btn-remove-petugas bg-white text-red-500 border border-red-200 hover:bg-red-50 rounded-full p-2 shadow-sm transition" title="Hapus Petugas">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_petugas[]" 
                        class="w-full px-4 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 font-bold text-gray-800 placeholder-gray-400" 
                        placeholder="Nama Lengkap & Gelar" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Identitas</label>
                    <select name="jenis_identitas[]" class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium">
                        <option value="NIK">NIK</option>
                        <option value="NIP">NIP</option>
                        <option value="NISN">NISN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nomor Identitas</label>
                    <input type="text" name="identitas_petugas[]" 
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium" 
                        placeholder="Nomor Identitas">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jabatan</label>
                    <input type="text" name="jabatan_petugas[]" 
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 bg-white focus:border-indigo-600 focus:ring-indigo-100 text-sm font-medium" 
                        placeholder="Jabatan">
                </div>
            </div>
        </div>
    `;

    btnAdd.addEventListener('click', function() {
        // Append new row
        container.insertAdjacentHTML('beforeend', templateRow);
        // Refresh lucid icons for new elements
        lucide.createIcons();
        updateRemoveButtons();
        
        // Scroll to new element
        const newRow = container.lastElementChild;
        newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-petugas')) {
            const row = e.target.closest('.petugas-row');
            if (document.querySelectorAll('.petugas-row').length > 1) {
                row.remove();
                updateRemoveButtons();
            } else {
                alert('Minimal satu petugas tidak boleh dihapus.');
            }
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.petugas-row');
        const buttons = document.querySelectorAll('.btn-remove-petugas');
        if (rows.length === 1) {
            buttons.forEach(btn => btn.classList.add('hidden'));
        } else {
            buttons.forEach(btn => btn.classList.remove('hidden'));
        }
    }
});
</script>

<style>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>