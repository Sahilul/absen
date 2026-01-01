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

                <!-- Foto Kehadiran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Kehadiran <span
                            class="text-red-500">*</span></label>

                    <div id="cameraSection">
                        <div class="relative bg-gray-900 rounded-xl overflow-hidden aspect-[4/3]">
                            <video id="cameraPreview" autoplay playsinline class="w-full h-full object-cover"></video>
                        </div>
                        <button type="button" id="captureBtn"
                            class="w-full mt-3 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium flex items-center justify-center gap-2">
                            📸 Ambil Foto
                        </button>
                    </div>

                    <div id="resultSection">
                        <div class="relative bg-gray-100 rounded-xl overflow-hidden aspect-[4/3]">
                            <img id="capturedImage" class="w-full h-full object-cover">
                        </div>
                        <button type="button" id="retakeBtn"
                            class="w-full mt-3 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-xl font-medium">
                            🔄 Ulangi Foto
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
        const resultSection = document.getElementById('resultSection');

        // Start camera
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                video.srcObject = stream;
            } catch (err) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
                console.error(err);
            }
        }

        // Capture photo
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            capturedImage.src = dataUrl;
            fotoBase64.value = dataUrl;

            cameraSection.style.display = 'none';
            resultSection.style.display = 'block';
            submitBtn.disabled = false;
        });

        // Retake photo
        retakeBtn.addEventListener('click', () => {
            cameraSection.style.display = 'block';
            resultSection.style.display = 'none';
            fotoBase64.value = '';
            submitBtn.disabled = true;
        });

        // Form submit validation
        form.addEventListener('submit', (e) => {
            if (!fotoBase64.value) {
                e.preventDefault();
                alert('Foto kehadiran wajib diambil!');
                return false;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Mengirim...';
        });

        // Init
        startCamera();
    </script>
</body>

</html>