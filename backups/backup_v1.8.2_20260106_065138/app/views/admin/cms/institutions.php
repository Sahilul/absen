<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6" x-data="{ 
          showAddModal: false, 
          showEditModal: false,
          editInst: {
              id: null,
              name: '',
              short_name: '',
              icon: 'school',
              color: 'blue',
              count_mode: 'manual',
              manual_count: 0,
              selected_classes: [],
              target_tp_id: '',
              description: '',
              order_index: 1
          }
      }">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Kelola Lembaga</h1>
                <p class="text-slate-500 text-sm mt-1">Atur lembaga-lembaga yayasan yang ditampilkan di landing page.
                </p>
            </div>
            <button @click="showAddModal = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>Tambah Lembaga</span>
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Info & Shortcut -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-medium mb-1">Mode Perhitungan Siswa:</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-600">
                        <li><strong>Manual</strong> - Input jumlah siswa secara langsung</li>
                        <li><strong>Auto</strong> - Dihitung otomatis dari data siswa berdasarkan kelas yang dipilih
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex flex-col justify-center">
            <div class="text-sm text-amber-800 font-medium mb-2">Data Akademik (Sumber Data Auto)</div>
            <div class="flex gap-2">
                <a href="<?= BASEURL ?>/admin/tahunPelajaran" target="_blank"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-white border border-amber-200 rounded-lg text-amber-700 text-sm hover:bg-amber-50 font-medium transition-colors">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    Atur Tahun Pelajaran
                </a>
                <a href="<?= BASEURL ?>/admin/siswa" target="_blank"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-white border border-amber-200 rounded-lg text-amber-700 text-sm hover:bg-amber-50 font-medium transition-colors">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Kelola Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- List Institutions -->
    <?php if (empty($data['institutions'])): ?>
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
            <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="building-2" class="w-8 h-8 text-blue-600"></i>
            </div>
            <h5 class="text-lg font-bold text-slate-800 mb-2">Belum ada lembaga</h5>
            <p class="text-slate-500 mb-6 max-w-sm mx-auto">Tambahkan lembaga yayasan seperti MA, MTs, Pondok Pesantren,
                dll.</p>
            <button @click="showAddModal = true" class="text-blue-600 font-semibold hover:underline">
                + Tambah Lembaga Sekarang
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($data['institutions'] as $inst):
                $isActive = $inst['is_active'] == 1;
                $colors = [
                    'blue' => 'bg-blue-500',
                    'green' => 'bg-green-500',
                    'orange' => 'bg-orange-500',
                    'purple' => 'bg-purple-500',
                    'pink' => 'bg-pink-500',
                    'red' => 'bg-red-500',
                    'teal' => 'bg-teal-500',
                    'indigo' => 'bg-indigo-500'
                ];
                $bgColor = $colors[$inst['color']] ?? 'bg-blue-500';
                $selectedClasses = !empty($inst['selected_classes']) ? json_decode($inst['selected_classes'], true) : [];
                ?>
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow <?= $isActive ? '' : 'opacity-60' ?>">
                    <!-- Header with Color -->
                    <div class="<?= $bgColor ?> p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <i data-lucide="<?= htmlspecialchars($inst['icon']) ?>" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-lg"><?= htmlspecialchars($inst['name']) ?></h5>
                                    <?php if (!empty($inst['short_name'])): ?>
                                        <span class="text-white/80 text-sm"><?= htmlspecialchars($inst['short_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-2xl font-bold">#<?= $inst['order_index'] ?></span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Mode Badge -->
                        <div class="flex items-center gap-2 mb-3">
                            <?php if ($inst['count_mode'] == 'manual'): ?>
                                <span
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                    Mode: Manual
                                </span>
                                <span class="text-sm text-slate-500">Jumlah:
                                    <strong><?= number_format($inst['manual_count']) ?></strong></span>
                            <?php else: ?>
                                <span
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-green-50 text-green-600 border border-green-200">
                                    Mode: Auto
                                </span>
                                <span class="text-sm text-slate-500"><?= count($selectedClasses) ?> kelas dipilih</span>
                            <?php endif; ?>
                        </div>

                        <!-- Class Breakdown Details -->
                        <?php if ($inst['count_mode'] == 'auto' && isset($inst['detail_counts'])): ?>
                            <div class="mb-4 bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-bold text-slate-600 uppercase">Rincian Per Kelas</span>
                                    <span class="text-xs bg-white px-2 py-0.5 rounded border text-slate-500">
                                        Total: <strong><?= $inst['student_count'] ?></strong>
                                    </span>
                                </div>
                                <?php if (empty($inst['detail_counts'])): ?>
                                    <div class="text-xs text-amber-600 flex items-center gap-1.5">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span>Tidak ada data siswa ditemukan!</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1 pl-4">Cek pilihan Tahun Pelajaran & data siswa di kelas
                                        terpilih.</p>
                                <?php else: ?>
                                    <div class="space-y-1 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                                        <?php foreach ($inst['detail_counts'] as $detail): ?>
                                            <div class="flex justify-between text-xs border-b border-slate-100 last:border-0 py-1">
                                                <span class="text-slate-600"><?= htmlspecialchars($detail['nama_kelas']) ?></span>
                                                <span
                                                    class="font-mono font-bold <?= $detail['count'] > 0 ? 'text-blue-600' : 'text-slate-300' ?>">
                                                    <?= $detail['count'] ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($inst['description'])): ?>
                            <p class="text-slate-500 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($inst['description']) ?></p>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <a href="<?= BASEURL ?>/cms/toggleLembaga/<?= $inst['id'] ?>/<?= $isActive ? 0 : 1 ?>"
                                class="relative inline-flex h-6 w-11 cursor-pointer rounded-full border-2 border-transparent transition-colors <?= $isActive ? 'bg-green-500' : 'bg-slate-300' ?>">
                                <span
                                    class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition <?= $isActive ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                            </a>

                            <div class="flex items-center gap-2">
                                <?php
                                $instData = json_encode([
                                    'id' => (int) $inst['id'],
                                    'name' => $inst['name'] ?? '',
                                    'short_name' => $inst['short_name'] ?? '',
                                    'icon' => $inst['icon'] ?? 'school',
                                    'color' => $inst['color'] ?? 'blue',
                                    'count_mode' => $inst['count_mode'] ?? 'manual',
                                    'manual_count' => (int) ($inst['manual_count'] ?? 0),
                                    'selected_classes' => $selectedClasses,
                                    'target_tp_id' => !empty($inst['target_tp_id']) ? (int) $inst['target_tp_id'] : '',
                                    'description' => $inst['description'] ?? '',
                                    'order_index' => (int) ($inst['order_index'] ?? 1)
                                ]);
                                ?>
                                <button @click='editInst = <?= htmlspecialchars($instData, ENT_QUOTES) ?>; showEditModal = true'
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 border border-blue-200 transition-colors"
                                    title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <a href="<?= BASEURL ?>/cms/hapusLembaga/<?= $inst['id'] ?>"
                                    onclick="return confirm('Hapus lembaga ini?')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200 transition-colors"
                                    title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Modal Tambah -->
    <div x-show="showAddModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;" x-cloak>
        <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Tambah Lembaga</h3>
                    </div>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL ?>/cms/tambahLembaga" method="POST" x-data="{ mode: 'manual' }">
                    <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lembaga *</label>
                                <input type="text" name="name" required placeholder="Contoh: MA Sabilillah"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Singkat</label>
                                <input type="text" name="short_name" placeholder="MA"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                                <input type="number" name="order_index" value="1" min="1"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Icon</label>
                                <select name="icon"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="school">🏫 School</option>
                                    <option value="book-open">📖 Book</option>
                                    <option value="users">👥 Users</option>
                                    <option value="graduation-cap">🎓 Graduation</option>
                                    <option value="building-2">🏢 Building</option>
                                    <option value="home">🏠 Home</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Warna</label>
                                <select name="color"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="blue">🔵 Biru</option>
                                    <option value="green">🟢 Hijau</option>
                                    <option value="orange">🟠 Oranye</option>
                                    <option value="purple">🟣 Ungu</option>
                                    <option value="pink">🩷 Pink</option>
                                    <option value="teal">🩵 Teal</option>
                                    <option value="indigo">💜 Indigo</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mode Perhitungan</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="count_mode" value="manual" x-model="mode"
                                        class="text-blue-600">
                                    <span class="text-sm">Manual</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="count_mode" value="auto" x-model="mode"
                                        class="text-blue-600">
                                    <span class="text-sm">Auto dari Kelas</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="mode === 'manual'">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Siswa</label>
                            <input type="number" name="manual_count" value="0" min="0"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div x-show="mode === 'auto'">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Pelajaran (Wajib
                                    Dipilih)</label>
                                <select name="target_tp_id"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Pilih Tahun Pelajaran --</option>
                                    <?php foreach ($data['tahun_pelajaran'] as $tp): ?>
                                        <option value="<?= $tp['id_tp'] ?>"><?= $tp['nama_tp'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-[10px] text-amber-600 mt-1">* Karena sistem tidak memiliki TP aktif
                                    global, pilih tahun pelajaran untuk perhitungan data siswa.</p>
                            </div>

                            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                            <div
                                class="grid grid-cols-3 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-50 rounded-lg border border-slate-200">
                                <?php foreach ($data['classes'] as $kelas): ?>
                                    <label
                                        class="flex items-center gap-2 cursor-pointer p-2 bg-white rounded-lg border border-slate-200 hover:bg-blue-50">
                                        <input type="checkbox" name="selected_classes[]" value="<?= $kelas['id_kelas'] ?>"
                                            class="text-blue-600 rounded">
                                        <span class="text-sm"><?= htmlspecialchars($kelas['nama_kelas']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Jumlah siswa akan dihitung dari kelas yang dipilih.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                            <textarea name="description" rows="2" placeholder="Deskripsi singkat lembaga..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showAddModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEditModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;" x-cloak>
        <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <i data-lucide="pencil" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Lembaga</h3>
                    </div>
                    <button @click="showEditModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL ?>/cms/updateLembaga" method="POST">
                    <input type="hidden" name="id" x-bind:value="editInst.id">
                    <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lembaga *</label>
                                <input type="text" name="name" required x-model="editInst.name"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Singkat</label>
                                <input type="text" name="short_name" x-model="editInst.short_name"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                                <input type="number" name="order_index" x-model="editInst.order_index" min="1"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Icon</label>
                                <select name="icon" x-model="editInst.icon"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="school">🏫 School</option>
                                    <option value="book-open">📖 Book</option>
                                    <option value="users">👥 Users</option>
                                    <option value="graduation-cap">🎓 Graduation</option>
                                    <option value="building-2">🏢 Building</option>
                                    <option value="home">🏠 Home</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Warna</label>
                                <select name="color" x-model="editInst.color"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="blue">🔵 Biru</option>
                                    <option value="green">🟢 Hijau</option>
                                    <option value="orange">🟠 Oranye</option>
                                    <option value="purple">🟣 Ungu</option>
                                    <option value="pink">🩷 Pink</option>
                                    <option value="teal">🩵 Teal</option>
                                    <option value="indigo">💜 Indigo</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mode Perhitungan</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="count_mode" value="manual" x-model="editInst.count_mode"
                                        class="text-blue-600">
                                    <span class="text-sm">Manual</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="count_mode" value="auto" x-model="editInst.count_mode"
                                        class="text-blue-600">
                                    <span class="text-sm">Auto dari Kelas</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="editInst.count_mode === 'manual'">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Siswa</label>
                            <input type="number" name="manual_count" x-model="editInst.manual_count" min="0"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div x-show="editInst.count_mode === 'auto'">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Pelajaran (Wajib
                                    Dipilih)</label>
                                <select name="target_tp_id" x-model="editInst.target_tp_id"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Pilih Tahun Pelajaran --</option>
                                    <?php foreach ($data['tahun_pelajaran'] as $tp): ?>
                                        <option value="<?= $tp['id_tp'] ?>"><?= $tp['nama_tp'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                            <div
                                class="grid grid-cols-3 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-50 rounded-lg border border-slate-200">
                                <?php foreach ($data['classes'] as $kelas): ?>
                                    <label
                                        class="flex items-center gap-2 cursor-pointer p-2 bg-white rounded-lg border border-slate-200 hover:bg-blue-50">
                                        <input type="checkbox" name="selected_classes[]" value="<?= $kelas['id_kelas'] ?>"
                                            :checked="editInst.selected_classes && editInst.selected_classes.includes(<?= $kelas['id_kelas'] ?>)"
                                            class="text-blue-600 rounded">
                                        <span class="text-sm"><?= htmlspecialchars($kelas['nama_kelas']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="2" x-model="editInst.description"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 shadow-lg shadow-amber-600/20 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
</script>