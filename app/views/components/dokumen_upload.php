<?php
/**
 * Shared Document Upload Component
 * 
 * Variables yang harus disediakan:
 * - $dokumenConfig: array dari DokumenConfig_model (getAllDokumen atau getDokumenPSB/Siswa)
 * - $uploadedDocs: array dokumen yang sudah diupload, indexed by jenis_dokumen
 * - $nisn: NISN siswa untuk folder path
 * - $namaSiswa: Nama lengkap siswa untuk folder path
 * - $uploadUrl: URL endpoint untuk upload
 * - $idRef: ID referensi (id_pendaftar atau id_siswa)
 * - $context: 'psb', 'siswa', 'admin', atau 'wali_kelas'
 * - $readOnly: boolean, jika true maka tidak bisa upload (hanya view)
 */

$dokumenConfig = $dokumenConfig ?? [];
$uploadedDocs = $uploadedDocs ?? [];
$readOnly = $readOnly ?? false;
$context = $context ?? 'siswa';
?>

<div class="dokumen-upload-container">
    <!-- Info Banner -->
    <?php if (!$readOnly): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-blue-800 font-medium">Petunjuk Upload</p>
                    <p class="text-xs text-blue-700 mt-1">
                        Dokumen akan otomatis tersimpan setelah dipilih. Format: JPG, PNG, PDF. Maksimal 2MB per file.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Document Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($dokumenConfig as $doc):
            $kode = $doc['kode'];
            $uploaded = $uploadedDocs[$kode] ?? null;
            $isUploaded = !empty($uploaded);

            // Determine preview URL
            $previewUrl = '';
            $downloadUrl = '';
            $isDrive = false;

            if ($isUploaded) {
                $isDrive = !empty($uploaded['drive_file_id']);
                if ($isDrive) {
                    $previewUrl = 'https://drive.google.com/file/d/' . $uploaded['drive_file_id'] . '/preview';
                    $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . $uploaded['drive_file_id'];
                } else {
                    $previewUrl = BASEURL . '/uploads/dokumen_siswa/' . $uploaded['path_file'];
                    $downloadUrl = $previewUrl;
                }
            }

            $ext = $isUploaded ? strtolower(pathinfo($uploaded['nama_file'] ?? '', PATHINFO_EXTENSION)) : '';
            $isPdf = $ext === 'pdf';
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
            ?>
            <div class="dokumen-card border rounded-xl p-4 transition-all duration-200 
            <?= $isUploaded ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-white hover:border-primary-300' ?>"
                id="doc_card_<?= $kode ?>">

                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0
                    <?= $isUploaded ? 'bg-green-100' : 'bg-gray-100' ?>">
                        <?php if ($isUploaded): ?>
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                        <?php else: ?>
                            <i data-lucide="<?= htmlspecialchars($doc['icon'] ?? 'file-text') ?>"
                                class="w-6 h-6 text-gray-400"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">
                            <?= htmlspecialchars($doc['nama']) ?>
                        </h4>

                        <?php if ($isUploaded): ?>
                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                Sudah diupload
                                <?php if ($isDrive): ?>
                                    <span class="text-blue-500 ml-1">☁️</span>
                                <?php endif; ?>
                            </p>

                            <!-- Action Buttons -->
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button type="button"
                                    onclick="previewDokumen('<?= htmlspecialchars($previewUrl) ?>', '<?= htmlspecialchars($doc['nama']) ?>', <?= ($isPdf || $isDrive) ? 'true' : 'false' ?>)"
                                    class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-blue-50">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Lihat
                                </button>
                                <a href="<?= htmlspecialchars($downloadUrl) ?>" target="_blank"
                                    class="text-xs text-gray-600 hover:text-gray-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100">
                                    <i data-lucide="download" class="w-3 h-3"></i> Download
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-gray-500 mt-1">Belum diupload</p>
                        <?php endif; ?>

                        <!-- Upload Input -->
                        <?php if (!$readOnly): ?>
                            <div class="mt-3">
                                <input type="file" name="dokumen[<?= $kode ?>]" id="doc_input_<?= $kode ?>"
                                    accept=".jpg,.jpeg,.png,.pdf" class="hidden"
                                    onchange="uploadDokumenFile(this, '<?= $kode ?>', '<?= $idRef ?>', '<?= $context ?>')">
                                <label for="doc_input_<?= $kode ?>" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 
                                <?= $isUploaded ? 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' : 'bg-primary-50 border-primary-200 text-primary-700 hover:bg-primary-100' ?>
                                border rounded-lg text-xs font-medium transition-colors">
                                    <i data-lucide="upload" class="w-3 h-3"></i>
                                    <?= $isUploaded ? 'Ganti File' : 'Upload File' ?>
                                </label>
                                <div class="mt-2">
                                    <span class="text-xs text-gray-400 upload-status" id="status_<?= $kode ?>"></span>
                                    <div class="hidden mt-1" id="progress_container_<?= $kode ?>">
                                        <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-500 to-primary-500 transition-all duration-200"
                                                id="progress_bar_<?= $kode ?>" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Preview Modal -->
<div id="dokumenPreviewModal"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[10000]">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div
            class="px-6 py-4 border-b flex items-center justify-between bg-gradient-to-r from-primary-50 to-blue-50 rounded-t-2xl">
            <h4 id="dokumenPreviewTitle" class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="file" class="w-5 h-5 text-primary-600"></i>
                <span>Preview Dokumen</span>
            </h4>
            <button onclick="closeDokumenPreview()"
                class="p-2 hover:bg-white/70 rounded-lg text-gray-500 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div id="dokumenPreviewContent"
            class="flex-1 overflow-auto p-4 bg-gray-100 flex items-center justify-center min-h-[400px]">
            <!-- Content will be inserted here -->
        </div>
        <div class="px-6 py-3 border-t bg-gray-50 flex justify-end rounded-b-2xl">
            <button onclick="closeDokumenPreview()"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // Upload document file
    async function uploadDokumenFile(input, jenis, idRef, context) {
        const file = input.files[0];
        const statusEl = document.getElementById('status_' + jenis);
        const card = document.getElementById('doc_card_' + jenis);

        if (!file) return;

        // Validate file size (2MB max)
        /* REMOVED LIMIT AS REQUESTED
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            statusEl.textContent = '❌ File terlalu besar (max 2MB)';
            statusEl.className = 'text-xs text-red-500 ml-2 upload-status';
            return;
        }
        */

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            statusEl.textContent = '❌ Format tidak didukung';
            statusEl.className = 'text-xs text-red-500 ml-2 upload-status';
            return;
        }

        const progressContainer = document.getElementById('progress_container_' + jenis);
        const progressBar = document.getElementById('progress_bar_' + jenis);

        statusEl.textContent = '⏳ 0%';
        statusEl.className = 'text-xs text-blue-500 upload-status';
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';

        // Determine upload URL based on context
        let uploadUrl = '';
        switch (context) {
            case 'psb':
                uploadUrl = '<?= BASEURL ?>/psb/uploadDokumen/' + idRef;
                break;
            case 'siswa':
                uploadUrl = '<?= BASEURL ?>/siswa/uploadDokumen';
                break;
            case 'admin':
                uploadUrl = '<?= BASEURL ?>/admin/uploadDokumenSiswa/' + idRef;
                break;
            case 'wali_kelas':
                uploadUrl = '<?= BASEURL ?>/waliKelas/uploadDokumenWaliKelas';
                break;
        }

        const formData = new FormData();

        // PSB uses different field names
        if (context === 'psb') {
            formData.append('dokumen[' + jenis + ']', file);
            formData.append('jenis', jenis);
        } else {
            formData.append('file_dokumen', file);
            formData.append('jenis_dokumen', jenis);
            formData.append('id_siswa', idRef);
        }

        // Use XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                statusEl.textContent = '⏳ ' + percent + '%';
                progressBar.style.width = percent + '%';
            }
        });

        xhr.addEventListener('load', function () {
            progressContainer.classList.add('hidden');

            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        statusEl.textContent = '✓ Tersimpan!';
                        statusEl.className = 'text-xs text-green-500 upload-status';
                        if (typeof window.handleDocumentUploadSuccess === 'function') {
                            setTimeout(() => window.handleDocumentUploadSuccess(), 800);
                        } else {
                            setTimeout(() => location.reload(), 800);
                        }
                    } else {
                        statusEl.textContent = '❌ ' + (result.message || 'Gagal upload');
                        statusEl.className = 'text-xs text-red-500 upload-status';
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', xhr.responseText.substring(0, 200));
                    statusEl.textContent = '❌ Response tidak valid';
                    statusEl.className = 'text-xs text-red-500 upload-status';
                }
            } else {
                statusEl.textContent = '❌ Server error: ' + xhr.status;
                statusEl.className = 'text-xs text-red-500 upload-status';
            }
        });

        xhr.addEventListener('error', function () {
            progressContainer.classList.add('hidden');
            statusEl.textContent = '❌ Gagal menghubungi server';
            statusEl.className = 'text-xs text-red-500 upload-status';
        });

        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    }

    // Preview document
    function previewDokumen(url, title, isPdfOrDrive = false) {
        const modal = document.getElementById('dokumenPreviewModal');
        const content = document.getElementById('dokumenPreviewContent');
        const titleEl = document.getElementById('dokumenPreviewTitle').querySelector('span');

        titleEl.textContent = title;

        if (isPdfOrDrive) {
            content.innerHTML = `<iframe src="${url}" class="w-full h-[70vh] rounded-lg border bg-white" frameborder="0" allowfullscreen></iframe>`;
        } else {
            content.innerHTML = `<img src="${url}" alt="${title}" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">`;
        }

        modal.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Close preview modal
    function closeDokumenPreview() {
        document.getElementById('dokumenPreviewModal').classList.add('hidden');
        document.getElementById('dokumenPreviewContent').innerHTML = '';
    }

    // Close on escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDokumenPreview();
        }
    });

    // Initialize icons
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>