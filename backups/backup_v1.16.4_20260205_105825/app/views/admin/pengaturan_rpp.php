<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Pengaturan Template RPP</h2>
        <button onclick="openModalSection()" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Tambah Section
        </button>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Pengaturan Wajib RPP Disetujui -->
    <?php $wajibRPP = $data['pengaturan_wajib_rpp'] ?? []; ?>
    <div class="glass-effect rounded-lg p-4 mb-6 border-l-4 <?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'border-green-500 bg-green-50' : 'border-gray-300'; ?>">
        <form action="<?= BASEURL; ?>/admin/toggleWajibRPP" method="post" class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg <?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'bg-green-100' : 'bg-gray-100'; ?>">
                    <i data-lucide="<?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'shield-check' : 'shield-off'; ?>" class="w-5 h-5 <?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'text-green-600' : 'text-gray-400'; ?>"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-secondary-800">Wajib RPP Disetujui</h4>
                    <p class="text-sm text-secondary-600">
                        <?php if (!empty($wajibRPP['wajib_rpp_disetujui'])): ?>
                            <span class="text-green-600 font-medium">Aktif</span> - Guru harus membuat RPP dan disetujui Kepala Madrasah sebelum dapat membuat jurnal, absensi, dan input nilai.
                        <?php else: ?>
                            <span class="text-gray-500">Nonaktif</span> - Guru dapat langsung membuat jurnal, absensi, dan input nilai tanpa RPP.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg font-medium transition-all <?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200'; ?>">
                <?= !empty($wajibRPP['wajib_rpp_disetujui']) ? 'Nonaktifkan' : 'Aktifkan'; ?>
            </button>
        </form>
    </div>

    <div class="glass-effect rounded-lg p-4 mb-6">
        <p class="text-sm text-secondary-600 mb-2">
            <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
            Atur struktur template RPP sesuai kebutuhan. Tambah/edit/hapus section (poin utama) dan field (sub-poin) di dalamnya.
        </p>
    </div>

    <!-- Sections List -->
    <?php if (empty($data['sections'])): ?>
        <div class="glass-effect rounded-lg p-8 text-center">
            <p class="text-secondary-500">Belum ada section. Klik "Tambah Section" untuk memulai.</p>
        </div>
    <?php else: ?>
        <?php foreach ($data['sections'] as $section): ?>
            <div class="glass-effect rounded-lg mb-4 overflow-hidden <?= $section['is_active'] ? '' : 'opacity-50'; ?>">
                <!-- Section Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white p-4 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-lg"><?= htmlspecialchars($section['kode_section']); ?>.</span>
                        <span class="font-semibold"><?= htmlspecialchars($section['nama_section']); ?></span>
                        <?php if (!$section['is_active']): ?>
                            <span class="ml-2 text-xs bg-white/20 px-2 py-1 rounded">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openModalSection(<?= htmlspecialchars(json_encode($section)); ?>)" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-sm">
                            <i data-lucide="edit" class="w-4 h-4 inline"></i> Edit
                        </button>
                        <button onclick="openModalField(<?= $section['id_section']; ?>)" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-sm">
                            <i data-lucide="plus" class="w-4 h-4 inline"></i> Tambah Field
                        </button>
                        <form action="<?= BASEURL; ?>/admin/toggleSection/<?= $section['id_section']; ?>" method="post" class="inline">
                            <button type="submit" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-sm">
                                <?= $section['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                            </button>
                        </form>
                        <form action="<?= BASEURL; ?>/admin/hapusSection/<?= $section['id_section']; ?>" method="post" class="inline" onsubmit="return confirm('Hapus section ini beserta semua field-nya?');">
                            <button type="submit" class="bg-red-500/50 hover:bg-red-500/70 px-3 py-1 rounded text-sm">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Fields List -->
                <div class="p-4">
                    <?php 
                    $fields = $data['fields_by_section'][$section['id_section']] ?? [];
                    if (empty($fields)): 
                    ?>
                        <p class="text-sm text-secondary-400 italic">Belum ada field di section ini.</p>
                    <?php else: ?>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="p-2 w-12">#</th>
                                    <th class="p-2">Nama Field</th>
                                    <th class="p-2">Kode</th>
                                    <th class="p-2">Tipe</th>
                                    <th class="p-2">Wajib</th>
                                    <th class="p-2">Status</th>
                                    <th class="p-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $field): ?>
                                    <tr class="border-b <?= $field['is_active'] ? '' : 'opacity-50'; ?>">
                                        <td class="p-2"><?= $field['urutan']; ?></td>
                                        <td class="p-2 font-medium"><?= htmlspecialchars($field['nama_field']); ?></td>
                                        <td class="p-2 text-secondary-500"><?= htmlspecialchars($field['kode_field']); ?></td>
                                        <td class="p-2">
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs"><?= $field['tipe_input']; ?></span>
                                        </td>
                                        <td class="p-2">
                                            <?php if ($field['is_required']): ?>
                                                <span class="text-green-600">Ya</span>
                                            <?php else: ?>
                                                <span class="text-secondary-400">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-2">
                                            <?php if ($field['is_active']): ?>
                                                <span class="text-green-600">Aktif</span>
                                            <?php else: ?>
                                                <span class="text-red-500">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-2 text-right">
                                            <button onclick='openModalField(<?= $section["id_section"]; ?>, <?= json_encode($field); ?>)' class="text-blue-600 hover:underline text-xs mr-2">Edit</button>
                                            <form action="<?= BASEURL; ?>/admin/toggleField/<?= $field['id_field']; ?>" method="post" class="inline">
                                                <button type="submit" class="text-yellow-600 hover:underline text-xs mr-2"><?= $field['is_active'] ? 'Nonaktif' : 'Aktif'; ?></button>
                                            </form>
                                            <form action="<?= BASEURL; ?>/admin/hapusField/<?= $field['id_field']; ?>" method="post" class="inline" onsubmit="return confirm('Hapus field ini?');">
                                                <button type="submit" class="text-red-600 hover:underline text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Section -->
<div id="modalSection" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold mb-4" id="modalSectionTitle">Tambah Section</h3>
        <form action="<?= BASEURL; ?>/admin/simpanSection" method="post">
            <input type="hidden" name="id_section" id="sectionId">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Kode Section</label>
                <input type="text" name="kode_section" id="sectionKode" class="w-full p-2 border rounded" placeholder="Contoh: A, B, C..." required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Section</label>
                <input type="text" name="nama_section" id="sectionNama" class="w-full p-2 border rounded" placeholder="Contoh: Identifikasi" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Urutan</label>
                <input type="number" name="urutan" id="sectionUrutan" class="w-full p-2 border rounded" value="0">
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" id="sectionActive" value="1" checked class="mr-2">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModalSection()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Field -->
<div id="modalField" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
        <h3 class="text-lg font-bold mb-4" id="modalFieldTitle">Tambah Field</h3>
        <form action="<?= BASEURL; ?>/admin/simpanField" method="post">
            <input type="hidden" name="id_field" id="fieldId">
            <input type="hidden" name="id_section" id="fieldSection">
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Nama Field</label>
                    <input type="text" name="nama_field" id="fieldNama" class="w-full p-2 border rounded" placeholder="Contoh: Materi Pembelajaran" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Kode Field</label>
                    <input type="text" name="kode_field" id="fieldKode" class="w-full p-2 border rounded" placeholder="Contoh: materi_pelajaran" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Tipe Input</label>
                    <select name="tipe_input" id="fieldTipe" class="w-full p-2 border rounded">
                        <option value="textarea">Textarea</option>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="file">File</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Urutan</label>
                    <input type="number" name="urutan" id="fieldUrutan" class="w-full p-2 border rounded" value="0">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Placeholder</label>
                <input type="text" name="placeholder" id="fieldPlaceholder" class="w-full p-2 border rounded" placeholder="Petunjuk pengisian...">
            </div>
            <div class="flex gap-4 mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_required" id="fieldRequired" value="1" class="mr-2">
                    <span class="text-sm">Wajib Diisi</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" id="fieldActive" value="1" checked class="mr-2">
                    <span class="text-sm">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModalField()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalSection(data = null) {
    document.getElementById('modalSection').classList.remove('hidden');
    if (data) {
        document.getElementById('modalSectionTitle').textContent = 'Edit Section';
        document.getElementById('sectionId').value = data.id_section;
        document.getElementById('sectionKode').value = data.kode_section;
        document.getElementById('sectionNama').value = data.nama_section;
        document.getElementById('sectionUrutan').value = data.urutan;
        document.getElementById('sectionActive').checked = data.is_active == 1;
    } else {
        document.getElementById('modalSectionTitle').textContent = 'Tambah Section';
        document.getElementById('sectionId').value = '';
        document.getElementById('sectionKode').value = '';
        document.getElementById('sectionNama').value = '';
        document.getElementById('sectionUrutan').value = 0;
        document.getElementById('sectionActive').checked = true;
    }
}

function closeModalSection() {
    document.getElementById('modalSection').classList.add('hidden');
}

function openModalField(sectionId, data = null) {
    document.getElementById('modalField').classList.remove('hidden');
    document.getElementById('fieldSection').value = sectionId;
    if (data) {
        document.getElementById('modalFieldTitle').textContent = 'Edit Field';
        document.getElementById('fieldId').value = data.id_field;
        document.getElementById('fieldNama').value = data.nama_field;
        document.getElementById('fieldKode').value = data.kode_field;
        document.getElementById('fieldTipe').value = data.tipe_input;
        document.getElementById('fieldUrutan').value = data.urutan;
        document.getElementById('fieldPlaceholder').value = data.placeholder || '';
        document.getElementById('fieldRequired').checked = data.is_required == 1;
        document.getElementById('fieldActive').checked = data.is_active == 1;
    } else {
        document.getElementById('modalFieldTitle').textContent = 'Tambah Field';
        document.getElementById('fieldId').value = '';
        document.getElementById('fieldNama').value = '';
        document.getElementById('fieldKode').value = '';
        document.getElementById('fieldTipe').value = 'textarea';
        document.getElementById('fieldUrutan').value = 0;
        document.getElementById('fieldPlaceholder').value = '';
        document.getElementById('fieldRequired').checked = false;
        document.getElementById('fieldActive').checked = true;
    }
}

function closeModalField() {
    document.getElementById('modalField').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('modalSection').addEventListener('click', function(e) {
    if (e.target === this) closeModalSection();
});
document.getElementById('modalField').addEventListener('click', function(e) {
    if (e.target === this) closeModalField();
});
</script>
