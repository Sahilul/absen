<?php
// File: app/views/admin/pengaturan_lembaga.php
// v1.32.0 - Pengaturan Lembaga (modal popup)
$lembagaList = $data['lembaga_list'] ?? [];
$jenjangOptions = ['VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-4">
        <div class="bg-white shadow-sm rounded-xl p-4 md:p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h4 class="text-lg md:text-xl font-bold text-slate-800 mb-1">Pengaturan Lembaga</h4>
                    <p class="text-slate-500 text-sm">
                        Kelola data lembaga (kop, nama lembaga, nama kepala) yang dipakai untuk rapor &amp; dokumen
                    </p>
                </div>
                <button type="button" onclick="openLembagaModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Lembaga
                </button>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Daftar Lembaga -->
    <div class="bg-white shadow-sm rounded-xl overflow-hidden mb-5">
        <div class="p-4 border-b border-gray-100">
            <h5 class="font-semibold text-gray-800">
                <i data-lucide="building-2" class="w-5 h-5 inline-block mr-2 text-primary-500"></i>
                Daftar Lembaga
            </h5>
        </div>

        <?php if (!empty($lembagaList)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lembaga</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kepala</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kop</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($lembagaList as $l): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-100 text-primary-800">
                                <?= htmlspecialchars($l['kode'] ?? '-'); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($l['nama_lembaga'] ?? '-'); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?php
                            $jenjangs = array_filter(array_map('trim', explode(',', $l['jenjang'] ?? '')));
                            foreach ($jenjangs as $j): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 mr-1"><?= htmlspecialchars($j); ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($l['nama_kepala'] ?? '-'); ?></td>
                        <td class="px-4 py-3">
                            <?php if (!empty($l['kop_rapor'])): ?>
                                <img src="<?= BASEURL ?>/public/img/lembaga/<?= htmlspecialchars($l['kop_rapor']); ?>"
                                     alt="Kop" class="h-8 w-auto max-w-[120px] object-contain border border-gray-200 rounded">
                            <?php else: ?>
                                <span class="text-xs text-gray-400 italic">Belum ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!empty($l['aktif'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button type="button"
                                onclick='openEditModal(<?= htmlspecialchars(json_encode($l), ENT_QUOTES, "UTF-8"); ?>)'
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5 mr-1"></i> Edit
                            </button>
                            <a href="<?= BASEURL ?>/admin/hapusLembaga/<?= $l['id_lembaga']; ?>"
                               onclick="return confirm('Hapus lembaga ini?');"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-danger-600 hover:bg-danger-700 transition-colors ml-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i data-lucide="building-2" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h6 class="text-gray-500 font-medium mb-2">Belum Ada Lembaga</h6>
            <p class="text-sm text-gray-400">Klik "Tambah Lembaga" untuk menambahkan data lembaga</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Box -->
    <div class="bg-sky-50 border border-sky-200 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-sky-600"></i>
            </div>
            <div class="flex-1">
                <h6 class="font-semibold text-sky-900 text-sm mb-1">Informasi</h6>
                <ul class="text-xs text-sky-700 space-y-1 list-disc list-inside">
                    <li>Data lembaga (kop, nama, kepala) dipakai otomatis saat mencetak rapor &amp; soal STS.</li>
                    <li>Setiap jenjang dipetakan ke satu lembaga (mis. VII–IX → MTs, X–XII → MA).</li>
                    <li>Pengaturan per wali kelas (mata pelajaran, persentase nilai) tetap diatur di menu Pengaturan Rapor.</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- ============================================== -->
<!-- MODAL: Tambah / Edit Lembaga -->
<!-- ============================================== -->
<div id="lembagaModal" class="fixed inset-0 bg-black/50 z-[70] hidden items-center justify-center p-4" onclick="if(event.target===this)closeLembagaModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h5 id="lembagaModalTitle" class="text-lg font-bold text-slate-800">
                <i data-lucide="building-2" class="w-5 h-5 inline-block mr-2 text-primary-500"></i>
                Tambah Lembaga
            </h5>
            <button type="button" onclick="closeLembagaModal()" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <form id="lembagaForm" action="<?= BASEURL ?>/admin/simpanPengaturanLembaga" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_lembaga" id="f_id_lembaga" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kode -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode <span class="text-danger-500">*</span></label>
                        <input type="text" name="kode" id="f_kode" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm"
                            placeholder="Contoh: MTS">
                    </div>

                    <!-- Nama Lembaga -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lembaga <span class="text-danger-500">*</span></label>
                        <input type="text" name="nama_lembaga" id="f_nama_lembaga" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm"
                            placeholder="Contoh: MTs Sabilillah">
                    </div>

                    <!-- Jenjang -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Jenjang (tingkat yang dicakup)</label>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($jenjangOptions as $j): ?>
                            <label class="inline-flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="checkbox" name="jenjang[]" value="<?= $j; ?>" id="f_jenjang_<?= $j; ?>"
                                    class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-200">
                                <span class="text-sm text-slate-700"><?= $j; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Pilih tingkat yang termasuk lembaga ini (mis. MTs = VII, VIII, IX)</p>
                    </div>

                    <!-- Nama Kepala -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala</label>
                        <input type="text" name="nama_kepala" id="f_nama_kepala"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm"
                            placeholder="Contoh: Drs. Ahmad Fauzi, M.Pd">
                    </div>

                    <!-- NIP Kepala -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NIP Kepala</label>
                        <input type="text" name="nip_kepala" id="f_nip_kepala"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm">
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                        <input type="text" name="alamat" id="f_alamat"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm">
                    </div>

                    <!-- Telp -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Telepon</label>
                        <input type="text" name="telp" id="f_telp"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="f_email"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm">
                    </div>

                    <!-- Website -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                        <input type="text" name="website" id="f_website"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm">
                    </div>

                    <!-- Kota -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kota</label>
                        <input type="text" name="kota" id="f_kota"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-400 text-sm"
                            placeholder="Contoh: Mojokerto">
                    </div>

                    <!-- Kop Rapor -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i data-lucide="image" class="w-4 h-4 inline-block mr-1"></i> Kop Rapor (Gambar)
                        </label>
                        <div id="preview_kop" class="mb-2 hidden">
                            <img id="img_kop" src="" alt="Kop" class="w-full h-auto max-h-28 object-contain border border-gray-200 rounded-lg bg-slate-50 p-2">
                        </div>
                        <input type="file" name="kop_rapor" accept="image/*"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        <p class="text-xs text-slate-500 mt-1">JPG/PNG, max 2MB</p>
                    </div>

                    <!-- Logo & TTD side by side -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i data-lucide="image" class="w-4 h-4 inline-block mr-1"></i> Logo
                        </label>
                        <div id="preview_logo" class="mb-2 hidden">
                            <img id="img_logo" src="" alt="Logo" class="h-14 w-auto object-contain border border-gray-200 rounded-lg bg-slate-50 p-1">
                        </div>
                        <input type="file" name="logo" accept="image/*"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        <p class="text-xs text-slate-500 mt-1">JPG/PNG, max 1MB</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            <i data-lucide="pen-tool" class="w-4 h-4 inline-block mr-1"></i> TTD Kepala
                        </label>
                        <div id="preview_ttd" class="mb-2 hidden">
                            <img id="img_ttd" src="" alt="TTD" class="h-14 w-auto object-contain border border-gray-200 rounded-lg bg-slate-50 p-1">
                        </div>
                        <input type="file" name="ttd_kepala" accept="image/*"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        <p class="text-xs text-slate-500 mt-1">JPG/PNG, max 1MB</p>
                    </div>

                    <!-- Aktif -->
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="aktif" value="1" id="f_aktif" checked
                                class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-200">
                            <span class="text-sm font-medium text-slate-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex flex-col sm:flex-row gap-3 justify-end">
                    <button type="button" onclick="closeLembagaModal()"
                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const jenjangAll = <?= json_encode($jenjangOptions); ?>;

function openLembagaModal() {
    document.getElementById('lembagaForm').reset();
    document.getElementById('f_id_lembaga').value = '';
    document.getElementById('f_aktif').checked = true;
    document.getElementById('lembagaModalTitle').innerHTML = '<i data-lucide="building-2" class="w-5 h-5 inline-block mr-2 text-primary-500"></i> Tambah Lembaga';
    jenjangAll.forEach(j => { const cb = document.getElementById('f_jenjang_' + j); if (cb) cb.checked = false; });
    hideAllPreviews();
    showModal();
}

function openEditModal(row) {
    document.getElementById('lembagaForm').reset();
    document.getElementById('f_id_lembaga').value = row.id_lembaga || '';
    document.getElementById('f_kode').value = row.kode || '';
    document.getElementById('f_nama_lembaga').value = row.nama_lembaga || '';
    document.getElementById('f_nama_kepala').value = row.nama_kepala || '';
    document.getElementById('f_nip_kepala').value = row.nip_kepala || '';
    document.getElementById('f_alamat').value = row.alamat || '';
    document.getElementById('f_telp').value = row.telp || '';
    document.getElementById('f_email').value = row.email || '';
    document.getElementById('f_website').value = row.website || '';
    document.getElementById('f_kota').value = row.kota || '';
    document.getElementById('f_aktif').checked = String(row.aktif) === '1';
    document.getElementById('lembagaModalTitle').innerHTML = '<i data-lucide="building-2" class="w-5 h-5 inline-block mr-2 text-primary-500"></i> Edit Lembaga';

    const jenjangs = (row.jenjang || '').split(',').map(s => s.trim()).filter(Boolean);
    jenjangAll.forEach(j => { const cb = document.getElementById('f_jenjang_' + j); if (cb) cb.checked = jenjangs.includes(j); });

    showPreview('kop', row.kop_rapor);
    showPreview('logo', row.logo);
    showPreview('ttd', row.ttd_kepala);
    showModal();
}

function showModal() {
    const m = document.getElementById('lembagaModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeLembagaModal() {
    const m = document.getElementById('lembagaModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
    document.body.style.overflow = '';
}

function showPreview(type, fileName) {
    const wrap = document.getElementById('preview_' + type);
    const img = document.getElementById('img_' + type);
    if (fileName && fileName.trim() !== '') {
        img.src = '<?= BASEURL ?>/public/img/lembaga/' + fileName;
        wrap.classList.remove('hidden');
    } else {
        wrap.classList.add('hidden');
        img.src = '';
    }
}

function hideAllPreviews() {
    ['kop', 'logo', 'ttd'].forEach(t => {
        document.getElementById('preview_' + t).classList.add('hidden');
        document.getElementById('img_' + t).src = '';
    });
}

document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLembagaModal(); });
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
