<?php
// Decode field values
$fieldValues = [];
if (!empty($data['rpp']['rpp_field_values'])) {
    $fieldValues = json_decode($data['rpp']['rpp_field_values'], true) ?: [];
}

// Get status badge
function getStatusBadgeAdmin($status) {
    $badges = [
        'draft' => '<div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 border-2 border-gray-300">
                        <i data-lucide="file-edit" class="w-5 h-5 text-gray-600"></i>
                        <div>
                            <div class="font-bold text-gray-800 text-sm">DRAFT</div>
                            <div class="text-xs text-gray-600">Belum diajukan</div>
                        </div>
                    </div>',
        'submitted' => '<div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-100 border-2 border-blue-400">
                            <i data-lucide="send" class="w-5 h-5 text-blue-600"></i>
                            <div>
                                <div class="font-bold text-blue-800 text-sm">MENUNGGU REVIEW</div>
                                <div class="text-xs text-blue-600">Butuh persetujuan</div>
                            </div>
                        </div>',
        'approved' => '<div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-100 border-2 border-green-400">
                           <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600"></i>
                           <div>
                               <div class="font-bold text-green-800 text-sm">DISETUJUI</div>
                               <div class="text-xs text-green-600">RPP telah approved</div>
                           </div>
                       </div>',
        'revision' => '<div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-yellow-100 border-2 border-yellow-400">
                           <i data-lucide="alert-circle" class="w-5 h-5 text-yellow-600"></i>
                           <div>
                               <div class="font-bold text-yellow-800 text-sm">PERLU REVISI</div>
                               <div class="text-xs text-yellow-600">Dikembalikan ke guru</div>
                           </div>
                       </div>'
    ];
    return $badges[$status] ?? $badges['draft'];
}
?>

<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold mb-1">Review RPP</h2>
            <div class="text-sm text-secondary-600">
                Mata Pelajaran: <b><?= htmlspecialchars($data['rpp']['nama_mapel'] ?? '-') ?></b> — 
                Kelas: <b><?= htmlspecialchars($data['rpp']['nama_kelas'] ?? '-') ?></b> — 
                Guru: <b><?= htmlspecialchars($data['rpp']['nama_guru'] ?? '-') ?></b>
            </div>
        </div>
        <div><?= getStatusBadgeAdmin($data['rpp']['status'] ?? 'draft') ?></div>
    </div>

    <div class="glass-effect rounded-lg p-6">
        <!-- Info Dasar -->
        <div class="bg-primary-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-primary-800 mb-3 flex items-center gap-2">
                <i data-lucide="info" class="w-5 h-5"></i>
                Informasi Dasar
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-secondary-500">Alokasi Waktu:</span>
                    <p class="font-medium"><?= htmlspecialchars($data['rpp']['alokasi_waktu'] ?? '-') ?></p>
                </div>
                <div>
                    <span class="text-secondary-500">Tanggal RPP:</span>
                    <?php 
                    $tanggalRpp = $data['rpp']['tanggal_rpp'] ?? null;
                    if ($tanggalRpp) {
                        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $tgl = date('j', strtotime($tanggalRpp));
                        $bln = (int)date('n', strtotime($tanggalRpp));
                        $thn = date('Y', strtotime($tanggalRpp));
                        $tanggalFormatted = $tgl . ' ' . $bulanIndo[$bln] . ' ' . $thn;
                    } else {
                        $tanggalFormatted = '-';
                    }
                    ?>
                    <p class="font-medium"><?= $tanggalFormatted ?></p>
                </div>
            </div>
        </div>

        <!-- Dynamic Template Sections -->
        <?php if (!empty($data['sections'])): ?>
            <?php foreach ($data['sections'] as $idx => $section): ?>
                <div class="mb-6">
                    <h3 class="font-semibold text-lg border-b border-gray-200 pb-2 mb-3 flex items-center gap-2">
                        <span class="bg-primary-100 text-primary-700 px-2 py-1 rounded text-sm">
                            <?= chr(65 + $idx) ?>
                        </span>
                        <?= htmlspecialchars($section['nama_section']) ?>
                    </h3>
                    
                    <?php if (!empty($section['deskripsi'])): ?>
                        <p class="text-sm text-secondary-500 mb-3"><?= htmlspecialchars($section['deskripsi']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($section['fields'])): ?>
                        <div class="space-y-4 pl-4">
                            <?php foreach ($section['fields'] as $field): ?>
                                <?php 
                                $fieldKey = 'field_' . $field['id_field'];
                                $value = $fieldValues[$fieldKey] ?? '';
                                ?>
                                <div class="border-l-2 border-gray-200 pl-4">
                                    <label class="block text-sm font-medium text-secondary-700 mb-1">
                                        <?= htmlspecialchars($field['nama_field']) ?>
                                        <?php if ($field['is_required']): ?>
                                            <span class="text-red-500">*</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <?php if ($field['tipe_input'] === 'file' && !empty($value)): 
                                        $fileExt = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                                        $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isPdf = ($fileExt === 'pdf');
                                        $fileUrl = BASEURL . '/public/uploads/rpp/' . htmlspecialchars($value);
                                        $filePath = APPROOT . '/public/uploads/rpp/' . $value;
                                        $fileExists = file_exists($filePath);
                                    ?>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <?php if ($fileExists): ?>
                                                <i data-lucide="<?= $isImage ? 'image' : ($isPdf ? 'file-text' : 'file') ?>" class="w-4 h-4 text-primary-600"></i>
                                                <span class="text-sm text-secondary-700"><?= htmlspecialchars($value) ?></span>
                                                
                                                <?php if ($isImage || $isPdf): ?>
                                                    <button type="button" 
                                                            onclick="openPreview('<?= $fileUrl ?>', '<?= $isImage ? 'image' : 'pdf' ?>', '<?= htmlspecialchars($value) ?>')" 
                                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                                        <i data-lucide="eye" class="w-3 h-3"></i> Preview
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="<?= $fileUrl ?>" 
                                                   download="<?= htmlspecialchars($value) ?>"
                                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                                    <i data-lucide="download" class="w-3 h-3"></i> Unduh
                                                </a>
                                            <?php else: ?>
                                                <i data-lucide="file-x" class="w-4 h-4 text-red-400"></i>
                                                <span class="text-sm text-red-500"><?= htmlspecialchars($value) ?></span>
                                                <span class="text-xs text-red-400">(file tidak ditemukan)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif (!empty($value)): ?>
                                        <div class="bg-gray-50 rounded p-3 text-secondary-800 prose prose-sm max-w-none">
                                            <?php if ($field['tipe_input'] === 'textarea'): ?>
                                                <?= $value ?>
                                            <?php else: ?>
                                                <?= nl2br(htmlspecialchars($value)) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-secondary-400 italic">Belum diisi</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-secondary-400 italic pl-4">Tidak ada field di bagian ini</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback to legacy fields if no template sections -->
            <div class="space-y-4">
                <h3 class="font-semibold">A. Identifikasi</h3>
                <div class="mb-3"><?= nl2br(htmlspecialchars($data['rpp']['materi_pelajaran'] ?? '-')) ?></div>
                
                <h3 class="font-semibold">B. Desain Pembelajaran</h3>
                <div class="mb-3">Capaian Pembelajaran: <?= nl2br(htmlspecialchars($data['rpp']['capaian_pembelajaran'] ?? '-')) ?></div>
                <div class="mb-3">Tujuan Pembelajaran: <?= nl2br(htmlspecialchars($data['rpp']['tujuan_pembelajaran'] ?? '-')) ?></div>
            </div>
        <?php endif; ?>

        <!-- Review Actions -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="font-semibold text-lg mb-4">Tindakan Review</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Approve -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="font-medium text-green-800 mb-3 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Setujui RPP
                    </h4>
                    <p class="text-sm text-green-700 mb-3">RPP telah lengkap dan memenuhi standar.</p>
                    <form action="<?= BASEURL; ?>/admin/approveRPP/<?= htmlspecialchars($data['rpp']['id_rpp']); ?>" method="post">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Approve
                        </button>
                    </form>
                </div>

                <!-- Revisi -->
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="font-medium text-yellow-800 mb-3 flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                        Minta Revisi
                    </h4>
                    <form action="<?= BASEURL; ?>/admin/revisionRPP/<?= htmlspecialchars($data['rpp']['id_rpp']); ?>" method="post">
                        <div class="mb-3">
                            <label for="catatan" class="block text-sm font-medium text-yellow-800 mb-1">Catatan Revisi</label>
                            <textarea name="catatan" id="catatan" rows="3" 
                                      class="w-full p-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                      placeholder="Jelaskan bagian yang perlu diperbaiki..."></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Minta Revisi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="<?= BASEURL; ?>/admin/listRPPReview" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // Preview functions
    function openPreview(url, type, filename) {
        const modal = document.getElementById('previewModal');
        const title = document.getElementById('previewTitle');
        const body = document.getElementById('previewBody');
        
        title.textContent = filename;
        
        if (type === 'image') {
            body.innerHTML = `<img src="${url}" alt="${filename}" style="max-width: 80vw; max-height: calc(90vh - 80px);">`;
        } else if (type === 'pdf') {
            body.innerHTML = `<iframe src="${url}" title="${filename}"></iframe>`;
        }
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Reinitialize lucide icons in modal
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closePreview(event) {
        if (event && event.target !== event.currentTarget) return;
        
        const modal = document.getElementById('previewModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('previewBody').innerHTML = '';
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreview();
        }
    });
</script>

<style>
/* Styling untuk konten HTML dari rich text editor */
.prose {
    color: #374151;
    line-height: 1.6;
}
.prose p {
    margin-bottom: 0.75rem;
}
.prose h1, .prose h2, .prose h3 {
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.prose h1 { font-size: 1.5rem; }
.prose h2 { font-size: 1.25rem; }
.prose h3 { font-size: 1.1rem; }
.prose strong { font-weight: 600; }
.prose em { font-style: italic; }
.prose u { text-decoration: underline; }
.prose s { text-decoration: line-through; }
.prose ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.prose ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.prose li {
    margin-bottom: 0.25rem;
}
.prose a {
    color: #0ea5e9;
    text-decoration: underline;
}
.prose a:hover {
    color: #0284c7;
}

/* Preview Modal */
.preview-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.preview-modal.active {
    display: flex;
}
.preview-content {
    background: white;
    border-radius: 1rem;
    max-width: 90vw;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
}
.preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}
.preview-body {
    padding: 0;
    max-height: calc(90vh - 60px);
    overflow: auto;
}
.preview-body img {
    max-width: 100%;
    height: auto;
    display: block;
}
.preview-body iframe {
    width: 80vw;
    height: calc(90vh - 80px);
    border: none;
}
</style>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal" onclick="closePreview(event)">
    <div class="preview-content" onclick="event.stopPropagation()">
        <div class="preview-header">
            <span id="previewTitle" class="font-semibold text-secondary-800"></span>
            <button type="button" onclick="closePreview()" class="p-1 hover:bg-gray-200 rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="preview-body" id="previewBody"></div>
    </div>
</div>