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
            <a href="<?= BASEURL; ?>/admin/tambahPenugasan"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                Tambah Penugasan
            </a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>