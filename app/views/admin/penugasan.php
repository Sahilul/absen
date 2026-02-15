<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penugasan Guru</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Penugasan Guru</h2>
                <p class="text-gray-600 mt-1">Kelola tugas mengajar di semester ini</p>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                    <?= $_SESSION['nama_semester_aktif'] ?? 'Semester tidak terdeteksi'; ?>
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" onclick="openCopyModal()"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                    <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                    Copy dari Semester Lain
                </button>
                <a href="<?= BASEURL; ?>/admin/tambahPenugasan"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                    Tambah Penugasan
                </a>
            </div>
        </div>

        <!-- Tempat untuk menampilkan pesan flash -->
        <?php Flasher::flash(); ?>

        <!-- Stats Card -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                        <i data-lucide="briefcase" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Penugasan</p>
                        <p class="text-xl font-semibold text-gray-900"><?= count($data['penugasan']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Penugasan</h3>
                    <div class="flex items-center space-x-3">
                        <!-- Search Box can be added here -->
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <?php if (!empty($data['penugasan'])): ?>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Guru
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            // Grouping Logic
                            $groupedPenugasan = [];
                            foreach ($data['penugasan'] as $tugas) {
                                $groupedPenugasan[$tugas['nama_guru']][] = $tugas;
                            }
                            $no = 1;

                            foreach ($groupedPenugasan as $nama_guru => $listTugas):
                                $rowspan = count($listTugas);
                                $first = true;

                                foreach ($listTugas as $index => $tugas):
                                    ?>
                                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                    <?php if ($first): ?>
                                                            <td class="px-6 py-4 whitespace-nowrap align-top" rowspan="<?= $rowspan; ?>">
                                                                <span class="text-sm text-gray-500"><?= $no++; ?></span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap align-top" rowspan="<?= $rowspan; ?>">
                                                                <div class="flex items-center">
                                                                    <div
                                                                        class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                                        <span
                                                                            class="text-xs font-bold text-indigo-600"><?= substr($nama_guru, 0, 1); ?></span>
                                                                    </div>
                                                                    <div class="text-sm font-medium text-gray-900">
                                                                        <?= htmlspecialchars($nama_guru); ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                    <?php endif; ?>

                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <?= htmlspecialchars($tugas['nama_mapel']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="text-sm font-medium text-gray-700 bg-gray-100 px-2 py-1 rounded">
                                                            <?= htmlspecialchars($tugas['nama_kelas']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-3">
                                                            <a href="<?= BASEURL; ?>/admin/editPenugasan/<?= $tugas['id_penugasan']; ?>"
                                                                class="text-indigo-600 hover:text-indigo-800 transition-colors duration-150"
                                                                title="Edit Penugasan">
                                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                            </a>
                                                            <a href="<?= BASEURL; ?>/admin/hapusPenugasan/<?= $tugas['id_penugasan']; ?>"
                                                                class="text-red-600 hover:text-red-800 transition-colors duration-150"
                                                                title="Hapus Penugasan"
                                                                onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                $first = false;
                                endforeach;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
            <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="max-w-sm mx-auto">
                            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="server-off" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Penugasan</h3>
                            <p class="text-gray-500 mb-6">Pilih "Tambah Penugasan" untuk mulai membagikan tugas mengajar.</p>
                        </div>
                    </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Copy Penugasan -->
    <div id="copyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Copy Penugasan dari Semester Lain</h3>
                <button type="button" onclick="closeCopyModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <form id="copyPenugasanForm" action="<?= BASEURL; ?>/admin/prosesCopyPenugasan" method="POST">
                    <!-- Pilih Semester Sumber -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Semester Sumber</label>
                        <select name="id_semester_sumber" id="semesterSumber" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="previewPenugasan(this.value)">
                            <option value="">-- Pilih Semester --</option>
                            <?php foreach ($data['daftar_semester'] as $semester): ?>
                                    <?php if ($semester['id_semester'] != ($_SESSION['id_semester_aktif'] ?? 0)): ?>
                                        <option value="<?= $semester['id_semester']; ?>">
                                            <?= htmlspecialchars($semester['nama_tp']); ?> - Semester <?= $semester['semester']; ?>
                                        </option>
                                    <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Info Semester Tujuan -->
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-700">
                            <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                            Penugasan akan di-copy ke: <strong><?= $_SESSION['nama_semester_aktif'] ?? 'Semester Aktif'; ?></strong>
                        </p>
                    </div>

                    <!-- Preview Area -->
                    <div id="previewArea" class="hidden">
                        <div class="mb-3 flex items-center justify-between">
                            <h4 class="font-medium text-gray-700">Preview Penugasan</h4>
                            <span id="previewCount" class="text-sm text-gray-500"></span>
                        </div>
                        <div id="previewContent" class="border rounded-lg max-h-64 overflow-y-auto">
                            <!-- Data penugasan akan ditampilkan di sini -->
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i data-lucide="alert-circle" class="w-3 h-3 inline mr-1"></i>
                            Penugasan yang sudah ada di semester aktif akan otomatis di-skip.
                        </p>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingPreview" class="hidden text-center py-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                        <p class="text-sm text-gray-500 mt-2">Memuat data...</p>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeCopyModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="submitCopyForm()" id="btnCopy"
                    class="px-4 py-2 text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i data-lucide="copy" class="w-4 h-4 inline mr-1"></i>
                    Copy Penugasan
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        function openCopyModal() {
            document.getElementById('copyModal').classList.remove('hidden');
            document.getElementById('copyModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
            // Re-init icons
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 100);
            }
        }

        function closeCopyModal() {
            document.getElementById('copyModal').classList.add('hidden');
            document.getElementById('copyModal').classList.remove('flex');
            document.body.style.overflow = '';
            // Reset form
            document.getElementById('semesterSumber').value = '';
            document.getElementById('previewArea').classList.add('hidden');
            document.getElementById('btnCopy').disabled = true;
        }

        function previewPenugasan(idSemester) {
            if (!idSemester) {
                document.getElementById('previewArea').classList.add('hidden');
                document.getElementById('btnCopy').disabled = true;
                return;
            }

            // Show loading
            document.getElementById('loadingPreview').classList.remove('hidden');
            document.getElementById('previewArea').classList.add('hidden');
            document.getElementById('btnCopy').disabled = true;

            // Fetch data via AJAX
            fetch(`<?= BASEURL; ?>/admin/getPenugasanBySemesterApi/${idSemester}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingPreview').classList.add('hidden');
                    
                    if (data.success && data.count > 0) {
                        // Show preview
                        document.getElementById('previewArea').classList.remove('hidden');
                        document.getElementById('previewCount').textContent = `${data.count} penugasan ditemukan`;
                        
                        // Build preview table
                        let html = `<table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Guru</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Mata Pelajaran</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Kelas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">`;
                        
                        data.data.forEach(item => {
                            html += `<tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">${item.nama_guru}</td>
                                <td class="px-3 py-2">${item.nama_mapel}</td>
                                <td class="px-3 py-2">${item.nama_kelas}</td>
                            </tr>`;
                        });
                        
                        html += `</tbody></table>`;
                        document.getElementById('previewContent').innerHTML = html;
                        document.getElementById('btnCopy').disabled = false;
                    } else {
                        document.getElementById('previewArea').classList.remove('hidden');
                        document.getElementById('previewCount').textContent = '';
                        document.getElementById('previewContent').innerHTML = `
                            <div class="text-center py-6 text-gray-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                                <p>Tidak ada penugasan di semester ini</p>
                            </div>`;
                        document.getElementById('btnCopy').disabled = true;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingPreview').classList.add('hidden');
                    document.getElementById('previewArea').classList.remove('hidden');
                    document.getElementById('previewContent').innerHTML = `
                        <div class="text-center py-6 text-red-500">
                            <p>Gagal memuat data. Silakan coba lagi.</p>
                        </div>`;
                });
        }

        function submitCopyForm() {
            if (confirm('Apakah Anda yakin ingin meng-copy penugasan dari semester yang dipilih?')) {
                document.getElementById('copyPenugasanForm').submit();
            }
        }
    </script>
</body>

</html>