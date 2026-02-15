<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-secondary-800">Buat RPP Baru</h2>
                <p class="text-secondary-600">
                    <span class="font-medium"><?= htmlspecialchars($data['penugasan']['nama_mapel'] ?? '-'); ?></span> — 
                    <span class="font-medium"><?= htmlspecialchars($data['penugasan']['nama_kelas'] ?? '-'); ?></span>
                </p>
            </div>
            <a href="<?= BASEURL; ?>/guru/dashboard" class="btn-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> Kembali
            </a>
        </div>

        <?php Flasher::flash(); ?>

        <div class="glass-effect rounded-xl p-6 border border-white/20 shadow-lg">
            <form action="<?= BASEURL; ?>/guru/simpanRPPDinamis" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_mapel" value="<?= htmlspecialchars($data['penugasan']['id_mapel']); ?>">
                <input type="hidden" name="id_kelas" value="<?= htmlspecialchars($data['penugasan']['id_kelas']); ?>">
                <input type="hidden" name="id_penugasan" value="<?= htmlspecialchars($data['id_penugasan'] ?? ''); ?>">

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Alokasi Waktu</label>
                        <input type="text" name="alokasi_waktu" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Contoh: 2 x 45 menit">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Tanggal RPP</label>
                        <input type="date" name="tanggal_rpp" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" value="<?= date('Y-m-d'); ?>">
                    </div>
                </div>

                <!-- Dynamic Template Sections -->
                <?php if (!empty($data['template'])): ?>
                    <?php foreach ($data['template'] as $sectionData): ?>
                        <?php $section = $sectionData['section']; $fields = $sectionData['fields']; ?>
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <span class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold px-3 py-1 rounded-lg mr-3">
                                    <?= htmlspecialchars($section['kode_section']); ?>
                                </span>
                                <h3 class="text-lg font-semibold text-secondary-800"><?= htmlspecialchars($section['nama_section']); ?></h3>
                            </div>
                            
                            <div class="space-y-4 pl-4 border-l-2 border-indigo-200">
                                <?php foreach ($fields as $field): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 mb-1">
                                            <?= htmlspecialchars($field['nama_field']); ?>
                                            <?php if ($field['is_required']): ?>
                                                <span class="text-red-500">*</span>
                                            <?php endif; ?>
                                        </label>
                                        
                                        <?php if ($field['tipe_input'] === 'textarea'): ?>
                                            <div class="rich-editor-wrapper">
                                                <div id="editor_<?= $field['id_field']; ?>" class="rich-editor" style="min-height: 150px; background: white;"></div>
                                                <textarea 
                                                    name="field_<?= $field['id_field']; ?>" 
                                                    id="hidden_<?= $field['id_field']; ?>"
                                                    class="hidden" 
                                                    <?= $field['is_required'] ? 'required' : ''; ?>
                                                ><?= htmlspecialchars($field['nilai'] ?? ''); ?></textarea>
                                            </div>
                                        <?php elseif ($field['tipe_input'] === 'text'): ?>
                                            <input 
                                                type="text" 
                                                name="field_<?= $field['id_field']; ?>" 
                                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                                placeholder="<?= htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                value="<?= htmlspecialchars($field['nilai'] ?? ''); ?>"
                                                <?= $field['is_required'] ? 'required' : ''; ?>
                                            >
                                        <?php elseif ($field['tipe_input'] === 'number'): ?>
                                            <input 
                                                type="number" 
                                                name="field_<?= $field['id_field']; ?>" 
                                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                                placeholder="<?= htmlspecialchars($field['placeholder'] ?? ''); ?>"
                                                value="<?= htmlspecialchars($field['nilai'] ?? ''); ?>"
                                                <?= $field['is_required'] ? 'required' : ''; ?>
                                            >
                                        <?php elseif ($field['tipe_input'] === 'date'): ?>
                                            <input 
                                                type="date" 
                                                name="field_<?= $field['id_field']; ?>" 
                                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                                value="<?= htmlspecialchars($field['nilai'] ?? ''); ?>"
                                                <?= $field['is_required'] ? 'required' : ''; ?>
                                            >
                                        <?php elseif ($field['tipe_input'] === 'file'): ?>
                                            <input 
                                                type="file" 
                                                name="field_file_<?= $field['id_field']; ?>" 
                                                id="file_<?= $field['id_field']; ?>"
                                                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                                                <?= $field['is_required'] ? 'required' : ''; ?>
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                                            >
                                            <p class="text-xs text-secondary-500 mt-1">Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG (max 5MB)</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-6 text-center text-secondary-500">
                        Template RPP belum dikonfigurasi. Hubungi admin untuk mengatur template.
                    </div>
                <?php endif; ?>

                <!-- File Upload dihapus sesuai permintaan -->

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" name="status" value="draft" class="btn-primary">
                        <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Simpan Draft
                    </button>
                    <button type="submit" name="status" value="submitted" class="btn-success">
                        <i data-lucide="send" class="w-4 h-4 inline mr-1"></i> Simpan & Ajukan Review
                    </button>
                    <a href="<?= BASEURL; ?>/guru/dashboard" class="btn-secondary">
                        <i data-lucide="x" class="w-4 h-4 inline mr-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Initialize Quill Rich Text Editors
        const textareaFields = document.querySelectorAll('.rich-editor');
        const quillInstances = {};

        textareaFields.forEach(editorDiv => {
            const fieldId = editorDiv.id.replace('editor_', '');
            const hiddenTextarea = document.getElementById('hidden_' + fieldId);
            
            // Initialize Quill with toolbar
            const quill = new Quill('#' + editorDiv.id, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'align': [] }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'Tulis di sini...'
            });

            // Set initial content from hidden textarea
            if (hiddenTextarea.value) {
                quill.root.innerHTML = hiddenTextarea.value;
            }

            // Sync Quill content to hidden textarea on change
            quill.on('text-change', function() {
                hiddenTextarea.value = quill.root.innerHTML;
            });

            quillInstances[fieldId] = quill;
        });

        // Before form submit, sync all editors
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                Object.keys(quillInstances).forEach(fieldId => {
                    const quill = quillInstances[fieldId];
                    const hiddenTextarea = document.getElementById('hidden_' + fieldId);
                    hiddenTextarea.value = quill.root.innerHTML;
                });
            });
        }
    });
</script>

<style>
.btn-success {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
    transition: all 0.15s ease;
    cursor: pointer;
}
.btn-success:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
}

/* Quill Editor Styling */
.rich-editor-wrapper {
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    overflow: hidden;
    background: white;
}
.rich-editor-wrapper:focus-within {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
}
.ql-toolbar {
    background: #f8fafc !important;
    border: none !important;
    border-bottom: 1px solid #e5e7eb !important;
}
.ql-container {
    border: none !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    font-size: 14px !important;
}
.ql-editor {
    min-height: 150px;
    padding: 1rem !important;
}
.ql-editor.ql-blank::before {
    color: #9ca3af;
    font-style: normal;
}
</style>