<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Foto Selfie Wajib - HCGA Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
        .camera-stage {
            position: relative;
            background: #0F172A;
            border-radius: 18px;
            overflow: hidden;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #webcam-video {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            transform: scaleX(-1);
            display: block;
        }
        #photo-result-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            display: none;
        }
        #captured-canvas {
            display: none;
        }
    </style>
</head>
<body>
    @php
        $resolvedBackUrl = mobile_back_url(route('mobile.dashboard'));
        $fromParams = mobile_from_params();
    @endphp
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('attendance.mobile.location', array_merge(['action' => $action], $fromParams)) }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">{{ $action === 'clock-in' ? 'Clock In' : 'Clock Out' }} &bull; Foto Selfie</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    Langkah 2 dari 2 (Wajib Selfie)
                </span>
                <span class="badge bg-light text-success rounded-pill px-3 py-1" style="font-size: 11px;">
                    <i class="fas fa-map-pin me-1"></i> {{ number_format($lat, 5) }}, {{ number_format($lng, 5) }}
                </span>
            </div>
            <div class="fw-bold mb-1"><i class="fas fa-camera me-1"></i> Ambil Foto Selfie Anda</div>
            <p class="small mb-0 opacity-75">Presensi hanya dapat dikirim setelah Anda mengambil foto selfie melalui kamera depan.</p>
        </div>

        <div class="finora-card">
            <div class="camera-stage mb-3">
                <video id="webcam-video" autoplay playsinline muted></video>
                <img id="photo-result-img" alt="Preview Selfie">
                <canvas id="captured-canvas"></canvas>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Catatan Tambahan (Opsional)</label>
                <input type="text" id="selfie-notes" class="form-control rounded-3" placeholder="Contoh: Hadir di lokasi proyek / dinas...">
            </div>

            <div id="camera-actions-capture">
                <button type="button" class="btn finora-btn-primary w-100 rounded-pill py-3 fw-bold" id="btn-take-selfie" onclick="captureSelfie()">
                    <i class="fas fa-camera me-2"></i> Ambil Foto Selfie
                </button>
            </div>

            <div id="camera-actions-submit" style="display: none;">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill py-3 px-4 fw-bold" id="btn-retake-selfie" onclick="retakeSelfie()">
                        <i class="fas fa-redo me-1"></i> Ulang
                    </button>
                    <button type="button" class="btn finora-btn-primary flex-fill rounded-pill py-3 fw-bold" id="btn-submit-attendance" onclick="submitAttendance()">
                        <i class="fas fa-check-circle me-1"></i> Kirim {{ $action === 'clock-in' ? 'Clock In' : 'Clock Out' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'attendance'])

    <script>
        const flowAction = @json($action);
        const userLat = @json($lat);
        const userLng = @json($lng);
        const submitUrl = flowAction === 'clock-in'
            ? @json(route('attendance.mobile.clockin'))
            : @json(route('attendance.mobile.clockout'));
        const dashboardUrl = @json($resolvedBackUrl);
        const locationUrl = @json(route('attendance.mobile.location', array_merge(['action' => $action], $fromParams)));

        let videoStream = null;
        let selfieBase64 = null;

        document.addEventListener('DOMContentLoaded', function() {
            startCameraLive();
        });

        function startCameraLive() {
            const video = document.getElementById('webcam-video');
            const img = document.getElementById('photo-result-img');

            selfieBase64 = null;
            video.style.display = 'block';
            img.style.display = 'none';
            document.getElementById('camera-actions-capture').style.display = 'block';
            document.getElementById('camera-actions-submit').style.display = 'none';

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Kamera tidak didukung oleh browser ini.');
                return;
            }

            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            })
            .then((stream) => {
                videoStream = stream;
                video.srcObject = stream;
            })
            .catch((err) => {
                alert('Gagal mengakses kamera depan: ' + err.message + '\nPastikan izin kamera telah diberikan.');
            });
        }

        function stopCameraLive() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function captureSelfie() {
            const video = document.getElementById('webcam-video');
            const canvas = document.getElementById('captured-canvas');
            const img = document.getElementById('photo-result-img');

            if (!video.videoWidth) {
                alert('Kamera belum siap. Harap tunggu 1-2 detik.');
                return;
            }

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            selfieBase64 = canvas.toDataURL('image/jpeg', 0.82);
            img.src = selfieBase64;
            video.style.display = 'none';
            img.style.display = 'block';
            stopCameraLive();

            document.getElementById('camera-actions-capture').style.display = 'none';
            document.getElementById('camera-actions-submit').style.display = 'block';
        }

        function retakeSelfie() {
            startCameraLive();
        }

        function submitAttendance() {
            if (!selfieBase64) {
                alert('Foto selfie wajib diambil terlebih dahulu!');
                return;
            }

            const btnSubmit = document.getElementById('btn-submit-attendance');
            const btnRetake = document.getElementById('btn-retake-selfie');
            btnSubmit.disabled = true;
            btnRetake.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengirim Presensi...';

            let gpsMeta = {};
            try {
                const rawMeta = sessionStorage.getItem('hcga_att_gps_meta');
                if (rawMeta) {
                    gpsMeta = JSON.parse(rawMeta) || {};
                }
            } catch (e) {}

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: userLat,
                    longitude: userLng,
                    accuracy: gpsMeta.accuracy !== undefined ? gpsMeta.accuracy : null,
                    is_mock: gpsMeta.is_mock ? 1 : 0,
                    gps_meta: gpsMeta,
                    photo: selfieBase64,
                    client: 'mobile',
                    is_mobile: 1,
                    notes: document.getElementById('selfie-notes').value || ''
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(({ status, ok, body }) => {
                if (ok && body.status === 'success') {
                    alert('Berhasil:\n' + body.message);
                    window.location.href = dashboardUrl;
                } else {
                    const msg = body.message || 'Presensi gagal diproses.';
                    alert('Gagal Presensi (' + status + '):\n' + msg);
                    btnSubmit.disabled = false;
                    btnRetake.disabled = false;
                    btnSubmit.innerHTML = `<i class="fas fa-check-circle me-1"></i> Kirim ${flowAction === 'clock-in' ? 'Clock In' : 'Clock Out'}`;

                    if (msg.toLowerCase().includes('radius') || msg.toLowerCase().includes('jangkauan') || msg.toLowerCase().includes('gps')) {
                        window.location.href = locationUrl;
                    }
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan koneksi server. Silakan coba kembali.');
                btnSubmit.disabled = false;
                btnRetake.disabled = false;
                btnSubmit.innerHTML = `<i class="fas fa-check-circle me-1"></i> Kirim ${flowAction === 'clock-in' ? 'Clock In' : 'Clock Out'}`;
            });
        }
    </script>
</body>
</html>
