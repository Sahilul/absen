<?php
// File: app/views/admin/dokumen_siswa_partial.php
// Partial view for document modal - loaded via AJAX

$dokumenConfig = $data['dokumenConfig'] ?? [];
$uploadedDocs = $data['uploadedDocs'] ?? [];
$nisn = $data['nisn'] ?? '';
$namaSiswa = $data['namaSiswa'] ?? '';
$idRef = $data['idRef'] ?? 0;
$context = $data['context'] ?? 'admin';
$readOnly = $data['readOnly'] ?? false;
$siswa = $data['siswa'] ?? [];

// Create uploaded document map
$uploadedMap = [];
foreach ($uploadedDocs as $doc) {
    $uploadedMap[$doc['jenis_dokumen']] = $doc;
}

// Helper functions
function isDriveUrlPartial($path)
{
    return strpos($path, 'drive.google.com') !== false || strpos($path, 'http') === 0;
}

function getPreviewUrlPartial($doc)
{
    if (isDriveUrlPartial($doc['path_file'])) {
        return $doc['path_file'];
    }
    return BASEURL . '/uploads/siswa_dokumen/' . $doc['path_file'];
}
?>

<div class="p-4 md:p-6">
    <!-- Student Info -->
    <div class="flex items-center gap-4 mb-6 pb-4 border-b">
        <div
            class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl">
            <?= strtoupper(substr($namaSiswa, 0, 1)); ?>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($namaSiswa); ?></h3>
            <p class="text-sm text-gray-500">NISN: <?= htmlspecialchars($nisn); ?></p>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="dokumenGrid_<?= $idRef; ?>">
        <?php foreach ($dokumenConfig as $doc):
            $kode = $doc['kode'];
            $uploaded = $uploadedMap[$kode] ?? null;
            $isUploaded = !empty($uploaded);
            $isDrive = $isUploaded && isDriveUrlPartial($uploaded['path_file']);
            $previewUrl = $isUploaded ? getPreviewUrlPartial($uploaded) : '';
            $ext = $isUploaded ? strtolower(pathinfo($uploaded['nama_file'] ?? '', PATHINFO_EXTENSION)) : '';
            $isPdf = $ext === 'pdf';
            ?>
            <div class="border rounded-xl p-4 <?= $isUploaded ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-white'; ?>"
                id="docCard_<?= $kode; ?>_<?= $idRef; ?>">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 <?= $isUploaded ? 'bg-green-100' : 'bg-gray-100'; ?>">
                        <?php if ($isUploaded): ?>
                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                        <?php else: ?>
                            <i data-lucide="<?= htmlspecialchars($doc['icon'] ?? 'file-text'); ?>"
                                class="w-5 h-5 text-gray-400"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-800 text-sm leading-tight">
                            <?= htmlspecialchars($doc['nama']); ?>
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
                                    onclick="previewDoc('<?= htmlspecialchars($previewUrl); ?>', '<?= htmlspecialchars($doc['nama']); ?>', <?= ($isPdf || $isDrive) ? 'true' : 'false'; ?>)"
                                    class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 px-2 py-1 rounded hover:bg-blue-50">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Lihat
                                </button>
                                <a href="<?= htmlspecialchars($previewUrl); ?>" target="_blank"
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
                                <input type="file" id="docInput_<?= $kode; ?>_<?= $idRef; ?>" accept=".jpg,.jpeg,.png,.pdf"
                                    class="hidden" onchange="uploadDocModal(this, '<?= $kode; ?>', '<?= $idRef; ?>')">
                                <label for="docInput_<?= $kode; ?>_<?= $idRef; ?>" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 
                                    <?= $isUploaded ? 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100' : 'bg-indigo-50 border-indigo-200 text-indigo-700 hover:bg-indigo-100'; ?>
                                    border rounded-lg text-xs font-medium transition-colors">
                                    <i data-lucide="upload" class="w-3 h-3"></i>
                                    <?= $isUploaded ? 'Ganti' : 'Upload'; ?>
                                </label>
                                <div class="mt-2">
                                    <span class="text-xs text-gray-400" id="status_<?= $kode; ?>_<?= $idRef; ?>"></span>
                                    <div class="hidden mt-1" id="progress_<?= $kode; ?>_<?= $idRef; ?>">
                                        <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500 transition-all"
                                                id="progressBar_<?= $kode; ?>_<?= $idRef; ?>" style="width: 0%"></div>
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

<script>
    function uploadDocModal(input, jenis, idSiswa) {
        const file = input.files[0];
        if (!file) return;

        const statusEl = document.getElementById('status_' + jenis + '_' + idSiswa);
        const progressContainer = document.getElementById('progress_' + jenis + '_' + idSiswa);
        const progressBar = document.getElementById('progressBar_' + jenis + '_' + idSiswa);

        // Validate file
        const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            statusEl.textContent = '❌ Format tidak didukung';
            statusEl.className = 'text-xs text-red-500';
            return;
        }

        /* REMOVED LIMIT AS REQUESTED
        if (file.size > 2 * 1024 * 1024) {
            statusEl.textContent = '❌ Max 2MB';
            statusEl.className = 'text-xs text-red-500';
            return;
        }
        */

        statusEl.textContent = '⏳ 0%';
        statusEl.className = 'text-xs text-blue-500';
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';

        const formData = new FormData();
        formData.append('file_dokumen', file);
        formData.append('jenis_dokumen', jenis);
        formData.append('id_siswa', idSiswa);

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
                        statusEl.textContent = '✓ Berhasil!';
                        statusEl.className = 'text-xs text-green-500';
                        // Refresh only the modal content
                        setTimeout(() => refreshDocumentModal(idSiswa), 500);
                    } else {
                        statusEl.textContent = '❌ ' + (result.message || 'Gagal');
                        statusEl.className = 'text-xs text-red-500';
                    }
                } catch (e) {
                    statusEl.textContent = '❌ Error';
                    statusEl.className = 'text-xs text-red-500';
                }
            } else {
                statusEl.textContent = '❌ Server error';
                statusEl.className = 'text-xs text-red-500';
            }
        });

        xhr.addEventListener('error', function () {
            progressContainer.classList.add('hidden');
            statusEl.textContent = '❌ Gagal';
            statusEl.className = 'text-xs text-red-500';
        });

        xhr.open('POST', '<?= BASEURL; ?>/admin/uploadDokumenSiswa/' + idSiswa, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    }

    function previewDoc(url, title, isPdfOrDrive) {
        // Create preview modal if not exists
        let previewModal = document.getElementById('previewDocModal');
        if (!previewModal) {
            previewModal = document.createElement('div');
            previewModal.id = 'previewDocModal';
            previewModal.className = 'fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-[99999]';
            previewModal.innerHTML = `
                <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h4 class="font-bold text-gray-800" id="previewDocTitle">Preview</h4>
                        <button onclick="closePreviewDoc()" class="p-2 hover:bg-gray-100 rounded-lg text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-gray-100" id="previewDocContent"></div>
                </div>
            `;
            document.body.appendChild(previewModal);

            // Close on backdrop click
            previewModal.addEventListener('click', function (e) {
                if (e.target === previewModal) closePreviewDoc();
            });
        }

        const titleEl = document.getElementById('previewDocTitle');
        const contentEl = document.getElementById('previewDocContent');

        titleEl.textContent = title || 'Preview Dokumen';

        // Check if Google Drive URL and convert to preview format
        const isDrive = url.includes('drive.google.com');

        if (isDrive) {
            // Convert Google Drive URL to embeddable preview format
            let previewUrl = url;

            // Handle different Google Drive URL formats
            // Format 1: https://drive.google.com/file/d/FILE_ID/view
            // Format 2: https://drive.google.com/open?id=FILE_ID
            // Convert to: https://drive.google.com/file/d/FILE_ID/preview

            if (url.includes('/file/d/')) {
                // Extract file ID and create preview URL
                const match = url.match(/\/file\/d\/([^\/]+)/);
                if (match) {
                    previewUrl = `https://drive.google.com/file/d/${match[1]}/preview`;
                }
            } else if (url.includes('open?id=')) {
                const match = url.match(/id=([^&]+)/);
                if (match) {
                    previewUrl = `https://drive.google.com/file/d/${match[1]}/preview`;
                }
            } else if (url.includes('uc?id=')) {
                const match = url.match(/id=([^&]+)/);
                if (match) {
                    previewUrl = `https://drive.google.com/file/d/${match[1]}/preview`;
                }
            }

            contentEl.innerHTML = `<iframe src="${previewUrl}" class="w-full h-[70vh] border-0 rounded" allow="autoplay"></iframe>`;
        } else {
            // Local file
            const ext = url.split('.').pop().toLowerCase().split('?')[0];
            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            const isPdf = ext === 'pdf';

            if (isImage) {
                contentEl.innerHTML = `<img src="${url}" class="max-w-full max-h-[70vh] object-contain rounded shadow" alt="${title}">`;
            } else if (isPdf) {
                contentEl.innerHTML = `<iframe src="${url}#toolbar=1" class="w-full h-[70vh] border-0 rounded"></iframe>`;
            } else {
                contentEl.innerHTML = `
                    <div class="text-center">
                        <p class="text-gray-600 mb-4">Tidak dapat preview file ini</p>
                        <a href="${url}" target="_blank" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Buka di Tab Baru</a>
                    </div>
                `;
            }
        }

        previewModal.classList.remove('hidden');
        previewModal.style.display = 'flex';
    }

    function closePreviewDoc() {
        const modal = document.getElementById('previewDocModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePreviewDoc();
    });
</script>