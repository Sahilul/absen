<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Buku Tamu' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #cameraPreview {
            transform: scaleX(-1);
        }

        .capturing #cameraSection {
            display: none;
        }

        .capturing #resultSection {
            display: block;
        }

        #resultSection {
            display: none;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
    <?php $link = $data['link'] ?? []; ?>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 text-white text-center">
                <h1 class="text-xl font-bold">📝 Buku Tamu Digital</h1>
                <p class="text-indigo-100 text-sm mt-1"><?= htmlspecialchars($link['nama_lembaga'] ?? '') ?></p>
            </div>

            <form id="tamuForm" action="<?= BASEURL ?>/tamu/submit" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="token" value="<?= $data['token'] ?? '' ?>">
                <input type="hidden" name="foto_base64" id="fotoBase64">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_tamu" required
                        value="<?= htmlspecialchars($link['nama_tamu'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asal Instansi</label>
                    <input type="text" name="instansi" placeholder="Nama perusahaan/lembaga"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span
                            class="text-red-500">*</span></label>
                    <input type="tel" name="no_hp" required value="<?= htmlspecialchars($link['no_wa_tamu'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" placeholder="alamat@email.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan <span
                            class="text-red-500">*</span></label>
                    <textarea name="keperluan" required rows="3" placeholder="Tujuan kunjungan Anda..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($link['keperluan_prefill'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bertemu Dengan</label>
                    <input type="text" name="bertemu_dengan" placeholder="Nama orang yang akan ditemui"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Waktu Datang & Pulang -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Datang <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="waktu_datang" id="waktuDatang" required
                            class="w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pulang</label>
                        <input type="datetime-local" name="waktu_pulang" id="waktuPulang"
                            class="w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <p class="text-xs text-gray-400 mt-1">Kosongkan jika belum pulang</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- Foto Kehadiran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Kehadiran <span
                            class="text-red-500">*</span></label>

                    <!-- Photo Mode Selector -->
                    <div id="photoModeSelector" class="flex gap-2 mb-3">
                        <button type="button" id="modeCameraBtn"
                            class="flex-1 py-2 px-3 bg-indigo-100 text-indigo-700 rounded-lg font-medium text-sm flex items-center justify-center gap-2 border-2 border-indigo-500">
                            📸 Kamera
                        </button>
                        <button type="button" id="modeUploadBtn"
                            class="flex-1 py-2 px-3 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm flex items-center justify-center gap-2 border-2 border-transparent">
                            📁 Upload
                        </button>
                    </div>

                    <!-- Camera Mode -->
                    <div id="cameraSection">
                        <div class="relative bg-gray-900 rounded-xl overflow-hidden aspect-[4/3]">
                            <video id="cameraPreview" autoplay playsinline class="w-full h-full object-cover"></video>
                        </div>
                        <button type="button" id="captureBtn"
                            class="w-full mt-3 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium flex items-center justify-center gap-2">
                            📸 Ambil Foto
                        </button>
                    </div>

                    <!-- Upload Mode -->
                    <div id="uploadSection" class="hidden">
                        <label class="block w-full cursor-pointer">
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-500 transition-colors">
                                <div class="text-4xl mb-2">📁</div>
                                <p class="text-gray-600 font-medium">Klik untuk pilih foto</p>
                                <p class="text-gray-400 text-sm mt-1">JPG, PNG (max 5MB)</p>
                            </div>
                            <input type="file" id="fileInput" accept="image/*" class="hidden">
                        </label>
                    </div>

                    <!-- Result Section -->
                    <div id="resultSection">
                        <div class="relative bg-gray-100 rounded-xl overflow-hidden aspect-[4/3]">
                            <img id="capturedImage" class="w-full h-full object-cover">
                        </div>
                        <button type="button" id="retakeBtn"
                            class="w-full mt-3 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-xl font-medium">
                            🔄 Ganti Foto
                        </button>
                    </div>
                    <canvas id="canvas" class="hidden"></canvas>
                </div>

                <button type="submit" id="submitBtn" disabled
                    class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    ✅ Kirim
                </button>
            </form>
        </div>
    </div>

    <script>
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('canvas');
        const capturedImage = document.getElementById('capturedImage');
        const fotoBase64 = document.getElementById('fotoBase64');
        const captureBtn = document.getElementById('captureBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('tamuForm');
        const cameraSection = document.getElementById('cameraSection');
        const uploadSection = document.getElementById('uploadSection');
        const resultSection = document.getElementById('resultSection');
        const modeCameraBtn = document.getElementById('modeCameraBtn');
        const modeUploadBtn = document.getElementById('modeUploadBtn');
        const fileInput = document.getElementById('fileInput');
        const photoModeSelector = document.getElementById('photoModeSelector');

        let currentMode = 'camera';
        let cameraStream = null;

        // Start camera
        async function startCamera() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                video.srcObject = cameraStream;
            } catch (err) {
                console.error('Camera error:', err);
                // Auto switch to upload mode if camera not available
                switchToUploadMode();
            }
        }

        // Stop camera
        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
        }

        // Switch to camera mode
        function switchToCameraMode() {
            currentMode = 'camera';
            modeCameraBtn.classList.add('bg-indigo-100', 'text-indigo-700', 'border-indigo-500');
            modeCameraBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-transparent');
            modeUploadBtn.classList.remove('bg-indigo-100', 'text-indigo-700', 'border-indigo-500');
            modeUploadBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-transparent');

            cameraSection.classList.remove('hidden');
            uploadSection.classList.add('hidden');
            startCamera();
        }

        // Switch to upload mode
        function switchToUploadMode() {
            currentMode = 'upload';
            modeUploadBtn.classList.add('bg-indigo-100', 'text-indigo-700', 'border-indigo-500');
            modeUploadBtn.classList.remove('bg-gray-100', 'text-gray-600', 'border-transparent');
            modeCameraBtn.classList.remove('bg-indigo-100', 'text-indigo-700', 'border-indigo-500');
            modeCameraBtn.classList.add('bg-gray-100', 'text-gray-600', 'border-transparent');

            cameraSection.classList.add('hidden');
            uploadSection.classList.remove('hidden');
            stopCamera();
        }

        // Mode buttons
        modeCameraBtn.addEventListener('click', switchToCameraMode);
        modeUploadBtn.addEventListener('click', switchToUploadMode);

        // Capture photo from camera
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            showResult(dataUrl);
        });

        // Upload file
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB!');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                // Resize image if needed
                const img = new Image();
                img.onload = () => {
                    const maxSize = 800;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxSize || height > maxSize) {
                        if (width > height) {
                            height = (height / width) * maxSize;
                            width = maxSize;
                        } else {
                            width = (width / height) * maxSize;
                            height = maxSize;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    showResult(dataUrl);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        // Show result
        function showResult(dataUrl) {
            capturedImage.src = dataUrl;
            fotoBase64.value = dataUrl;

            cameraSection.classList.add('hidden');
            uploadSection.classList.add('hidden');
            photoModeSelector.classList.add('hidden');
            resultSection.style.display = 'block';
            submitBtn.disabled = false;
            stopCamera();
        }

        // Retake/Change photo
        retakeBtn.addEventListener('click', () => {
            resultSection.style.display = 'none';
            photoModeSelector.classList.remove('hidden');
            fotoBase64.value = '';
            submitBtn.disabled = true;
            fileInput.value = '';

            if (currentMode === 'camera') {
                cameraSection.classList.remove('hidden');
                startCamera();
            } else {
                uploadSection.classList.remove('hidden');
            }
        });

        // Form submit validation
        form.addEventListener('submit', (e) => {
            if (!fotoBase64.value) {
                e.preventDefault();
                alert('Foto kehadiran wajib diambil/upload!');
                return false;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Mengirim...';
        });

        // Init
        startCamera();

        // Set default waktu datang to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('waktuDatang').value = now.toISOString().slice(0, 16);
    </script>
</body>

</html>