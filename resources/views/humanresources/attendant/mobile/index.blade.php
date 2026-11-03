<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Attendance & Timesheet</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet OpenStreetMap CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            padding: 24px 20px 28px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.2);
        }
        .clock-card {
            background: #fff;
            border-radius: 20px;
            margin-top: -20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 20px;
        }
        .btn-clock {
            width: 125px;
            height: 125px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            transition: all 0.2s ease-in-out;
            margin: 0 auto;
            border: none;
        }
        .btn-clock:active {
            transform: scale(0.95);
        }
        .btn-clock-in {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
        }
        .btn-clock-out {
            background: linear-gradient(135deg, #eb3349, #f45c43);
            color: white;
        }
        .btn-clock:disabled {
            background: #d6d8db !important;
            color: #6c757d !important;
            box-shadow: none !important;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: 65px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }
        .nav-item-mobile {
            text-align: center;
            color: #8e9aab;
            text-decoration: none;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .nav-item-mobile.active {
            color: #1e3c72;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
        /* Step Flow Modal & Camera Styling */
        #osm-map {
            height: 220px;
            width: 100%;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        #webcam-video {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            border-radius: 12px;
            background: #000;
            transform: scaleX(-1); /* mirror camera front view */
        }
        #captured-canvas {
            display: none;
        }
        .step-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 13px;
        }
        .step-active {
            background-color: #1e3c72;
            color: white;
        }
        .step-inactive {
            background-color: #e9ecef;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <!-- Header Profil & Jam Real-Time -->
    <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-light text-primary mb-1">NIK: {{ $user->Emp_ID }}</span>
                <h5 class="mb-0 fw-bold">{{ $user->name ?? $user->username }}</h5>
                <small class="text-white-50">{{ $user->Department ?? 'HCGA Employee' }}</small>
            </div>
            <div class="text-end">
                <span class="badge bg-warning text-dark"><i class="fa fa-calendar-alt me-1"></i> {{ date('d M Y') }}</span>
                <h3 class="mb-0 mt-1 fw-bold" id="live-clock">--:--:--</h3>
            </div>
        </div>
    </div>

    <div class="container px-3">
        <!-- Card Shift & Status Presensi -->
        <div class="clock-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small"><i class="fa fa-business-time me-1"></i> Jadwal Kerja Hari Ini</span>
                <span class="badge bg-info text-white">{{ $schedule['default_start'] }} - {{ $schedule['default_end'] }}</span>
            </div>

            <!-- Tombol Aksi Clock-In / Clock-Out -->
            <div class="row align-items-center py-2 mb-2">
                <div class="col-6 text-center">
                    <button class="btn-clock btn-clock-in" id="btn-clock-in" onclick="startAttendanceFlow('clock-in')" {{ ($todayEntry && $todayEntry->Start_Time) ? 'disabled' : '' }}>
                        <i class="fa fa-sign-in-alt fa-2x mb-1"></i>
                        <span>Clock In</span>
                        <small class="fw-normal" style="font-size: 10px;">{{ ($todayEntry && $todayEntry->Start_Time) ? $todayEntry->Start_Time : 'Masuk' }}</small>
                    </button>
                </div>
                <div class="col-6 text-center">
                    <button class="btn-clock btn-clock-out" id="btn-clock-out" onclick="startAttendanceFlow('clock-out')" {{ (!$todayEntry || !$todayEntry->Start_Time || $todayEntry->End_Time) ? 'disabled' : '' }}>
                        <i class="fa fa-sign-out-alt fa-2x mb-1"></i>
                        <span>Clock Out</span>
                        <small class="fw-normal" style="font-size: 10px;">{{ ($todayEntry && $todayEntry->End_Time) ? $todayEntry->End_Time : 'Pulang' }}</small>
                    </button>
                </div>
            </div>

            <div class="text-center">
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i>Tekan tombol di atas untuk memulai alur presensi GPS & Kamera.</small>
            </div>

            <!-- KHUSUS AKUN ADMIN: TOMBOL DEV RESET ATTENDANCE MOBILE -->
            @if(strtolower(Auth::user()->username) === 'admin')
                <div class="border-top pt-2 mt-2 text-center">
                    <small class="text-danger fw-bold d-block mb-1" style="font-size: 10px;">Dev Admin Reset Tool:</small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary py-0" style="font-size: 10px;" onclick="resetDevMobile('in')">Reset In</button>
                        <button type="button" class="btn btn-outline-secondary py-0" style="font-size: 10px;" onclick="resetDevMobile('out')">Reset Out</button>
                        <button type="button" class="btn btn-outline-danger py-0" style="font-size: 10px;" onclick="resetDevMobile('both')">Reset Both</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Ringkasan Kehadiran Hari Ini -->
        @php
            if (!function_exists('formatDurationMinutesHome')) {
                function formatDurationMinutesHome($totalMinutes) {
                    $totalMinutes = (int) $totalMinutes;
                    if ($totalMinutes <= 0) return '0 menit';
                    $hours = floor($totalMinutes / 60);
                    $mins = $totalMinutes % 60;
                    if ($hours > 0 && $mins > 0) {
                        return "{$hours}j {$mins}m";
                    } elseif ($hours > 0) {
                        return "{$hours} jam";
                    }
                    return "{$mins} menit";
                }
            }
        @endphp
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
            <h6 class="fw-bold mb-3"><i class="fa fa-id-card text-primary me-2"></i>Status Kehadiran Hari Ini</h6>
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="p-2 border rounded-3 bg-light">
                        <small class="text-muted d-block">Jam Masuk</small>
                        <span class="fw-bold text-success">{{ $todayEntry->Start_Time ?? '--:--' }}</span>
                        @if($todayEntry && $todayEntry->Clock_In_Status)
                            <span class="badge {{ $todayEntry->Clock_In_Status == 'ON_TIME' ? 'bg-success' : 'bg-danger' }} d-block mt-1" style="font-size: 9px;">
                                {{ $todayEntry->Clock_In_Status }}
                                @if(!empty($todayEntry->Late_Minutes) && $todayEntry->Late_Minutes > 0)
                                    (+{{ formatDurationMinutesHome($todayEntry->Late_Minutes) }})
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded-3 bg-light">
                        <small class="text-muted d-block">Jam Pulang</small>
                        <span class="fw-bold text-danger">{{ (!empty($todayEntry->End_Time)) ? $todayEntry->End_Time : '--:--' }}</span>
                        @if($todayEntry && !empty($todayEntry->End_Time) && !empty($todayEntry->Clock_Out_Status))
                            <span class="badge {{ $todayEntry->Clock_Out_Status == 'NORMAL' ? 'bg-success' : 'bg-warning text-dark' }} d-block mt-1" style="font-size: 9px;">
                                {{ $todayEntry->Clock_Out_Status }}
                                @if(!empty($todayEntry->Early_Leave_Minutes) && $todayEntry->Early_Leave_Minutes > 0)
                                    (-{{ formatDurationMinutesHome($todayEntry->Early_Leave_Minutes) }})
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL STEP FLOW PRESENSI (MAPS -> LIVE CAMERA -> SUBMIT) -->
    <div class="modal fade" id="attendanceFlowModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <!-- Modal Header -->
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="modalFlowTitle">Presensi Kehadiran</h6>
                    <button type="button" class="btn-close" onclick="closeAttendanceFlow()"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-3">
                    <!-- Step Indicator -->
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        <div class="text-center">
                            <span class="step-badge step-active" id="badge-step-1">1</span>
                            <small class="d-block mt-1" style="font-size: 11px;">Lokasi GPS</small>
                        </div>
                        <div style="width: 50px; height: 2px; background: #dee2e6; margin: 0 10px; margin-top: -15px;"></div>
                        <div class="text-center">
                            <span class="step-badge step-inactive" id="badge-step-2">2</span>
                            <small class="d-block mt-1" style="font-size: 11px;">Foto Selfie</small>
                        </div>
                    </div>

                    <!-- STEP 1: OPENSTREETMAP GPS LOCATION -->
                    <div id="step-1-container">
                        <div id="osm-map" class="mb-2"></div>
                        <div class="alert alert-light border py-2 px-3 small mb-3">
                            <i class="fa fa-map-pin text-danger me-1"></i>
                            <span id="map-coord-text">Mendeteksi lokasi GPS Anda...</span>
                        </div>
                        <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold" id="btn-next-to-camera" onclick="goToStepCamera()" disabled>
                            Lanjut ke Foto Selfie <i class="fa fa-arrow-right ms-1"></i>
                        </button>
                    </div>

                    <!-- STEP 2: LIVE WEBCAM SELFIE (ANTI-GALERI) -->
                    <div id="step-2-container" style="display: none;">
                        <div class="position-relative mb-3">
                            <video id="webcam-video" autoplay playsinline></video>
                            <img id="photo-result-img" class="camera-preview" style="display:none; width:100%; border-radius:12px;" alt="Hasil Selfie">
                            <canvas id="captured-canvas"></canvas>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-50 rounded-pill py-2" onclick="backToStepMap()">
                                <i class="fa fa-arrow-left me-1"></i> Kembali ke Peta
                            </button>
                            <button type="button" class="btn btn-success w-50 rounded-pill py-2 fw-bold shadow-sm" id="btn-snap-and-submit" onclick="takeSnapshotAndSubmit()">
                                <i class="fa fa-camera me-1"></i> Ambil Foto & Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar Webview -->
    <div class="bottom-nav">
        <a href="{{ route('attendance.mobile.index') }}" class="nav-item-mobile active">
            <i class="fa fa-fingerprint"></i>
            <span>Presensi</span>
        </a>
        <a href="{{ route('attendance.mobile.timesheet') }}" class="nav-item-mobile">
            <i class="fa fa-calendar-check"></i>
            <span>Timesheet</span>
        </a>
        <a href="{{ route('attendance.mobile.leave') }}" class="nav-item-mobile">
            <i class="fa fa-paper-plane"></i>
            <span>Izin & Cuti</span>
        </a>
    </div>

    <!-- Scripts: Bootstrap 5, Leaflet OpenStreetMap -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let currentFlowAction = 'clock-in';
        let userLat = null;
        let userLng = null;
        let selfieBase64 = null;
        let mapInstance = null;
        let userMarker = null;
        let videoStream = null;
        let attendanceModal = null;

        // Jam Real-time
        setInterval(() => {
            const now = new Date();
            document.getElementById('live-clock').innerText = now.toTimeString().split(' ')[0];
        }, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            attendanceModal = new bootstrap.Modal(document.getElementById('attendanceFlowModal'));
        });

        // 1. Mulai Flow Presensi
        function startAttendanceFlow(action) {
            currentFlowAction = action;
            document.getElementById('modalFlowTitle').innerText = action === 'clock-in' ? 'Clock In Masuk Kerja' : 'Clock Out Pulang Kerja';
            
            // Tampilkan Step 1
            backToStepMap();
            attendanceModal.show();

            // Refresh status tombol ambil foto & kirim
            const btnSubmit = document.getElementById('btn-snap-and-submit');
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i class="fa fa-camera me-1"></i> Ambil Foto & Kirim`;
            }

            // Init OpenStreetMap & Get Location
            setTimeout(() => {
                initOsmMap();
            }, 300);
        }

        // Init Map Leaflet OSM
        function initOsmMap() {
            if (!mapInstance) {
                mapInstance = L.map('osm-map').setView([-6.2088, 106.8456], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(mapInstance);
            } else {
                mapInstance.invalidateSize();
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        userLat = pos.coords.latitude;
                        userLng = pos.coords.longitude;

                        mapInstance.setView([userLat, userLng], 16);
                        if (userMarker) {
                            userMarker.setLatLng([userLat, userLng]);
                        } else {
                            userMarker = L.marker([userLat, userLng]).addTo(mapInstance);
                        }
                        userMarker.bindPopup("<b>Posisi Anda Sekarang</b>").openPopup();

                        document.getElementById('map-coord-text').innerHTML = `<strong>Koordinat:</strong> ${userLat.toFixed(5)}, ${userLng.toFixed(5)}`;
                        document.getElementById('btn-next-to-camera').disabled = false;
                    },
                    (err) => {
                        document.getElementById('map-coord-text').innerHTML = `<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Gagal GPS: ${err.message}. Pastikan izin lokasi aktif.</span>`;
                        document.getElementById('btn-next-to-camera').disabled = true;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                document.getElementById('map-coord-text').innerHTML = `<span class="text-danger">Browser tidak mendukung Geolocation.</span>`;
            }
        }

        // 2. Transisi ke Step 2 (Kamera Live)
        function goToStepCamera() {
            document.getElementById('step-1-container').style.display = 'none';
            document.getElementById('step-2-container').style.display = 'block';

            document.getElementById('badge-step-1').className = 'step-badge step-inactive';
            document.getElementById('badge-step-2').className = 'step-badge step-active';

            startCameraLive();
        }

        function backToStepMap() {
            stopCameraLive();
            document.getElementById('step-1-container').style.display = 'block';
            document.getElementById('step-2-container').style.display = 'none';

            document.getElementById('badge-step-1').className = 'step-badge step-active';
            document.getElementById('badge-step-2').className = 'step-badge step-inactive';

            if (mapInstance) {
                setTimeout(() => mapInstance.invalidateSize(), 200);
            }
        }

        // Buka Live Camera (Kamera depan wajib via facingMode user)
        function startCameraLive() {
            const video = document.getElementById('webcam-video');
            const img = document.getElementById('photo-result-img');
            const btnSubmit = document.getElementById('btn-snap-and-submit');
            
            video.style.display = 'block';
            img.style.display = 'none';
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="fa fa-camera me-1"></i> Ambil Foto & Kirim`;

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

        // Ambil Foto dan Langsung Kirim Presensi dalam 1 Tombol
        function takeSnapshotAndSubmit() {
            const video = document.getElementById('webcam-video');
            const canvas = document.getElementById('captured-canvas');
            const img = document.getElementById('photo-result-img');
            const btnSubmit = document.getElementById('btn-snap-and-submit');

            if (!video.videoWidth) {
                alert('Kamera belum siap. Harap tunggu 1-2 detik.');
                return;
            }

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            // Cerminkan canvas horizontal agar sesuai tampilan selfie mirror
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            selfieBase64 = canvas.toDataURL('image/jpeg', 0.8);
            img.src = selfieBase64;
            video.style.display = 'none';
            img.style.display = 'block';
            stopCameraLive();

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i class="fa fa-spinner fa-spin me-1"></i> Mengirim Presensi...`;

            const url = currentFlowAction === 'clock-in' ? "{{ route('attendance.mobile.clockin') }}" : "{{ route('attendance.mobile.clockout') }}";

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: userLat,
                    longitude: userLng,
                    photo: selfieBase64,
                    notes: ''
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(({ status, ok, body }) => {
                if (ok && body.status === 'success') {
                    alert('Berhasil:\n' + body.message);
                    closeAttendanceFlow();
                    window.location.reload();
                } else {
                    const msg = body.message || 'Presensi gagal diproses.';
                    alert('Gagal Presensi (' + status + '):\n' + msg);

                    // Recovery cerdas sesuai jenis error
                    if (msg.toLowerCase().includes('radius') || msg.toLowerCase().includes('jangkauan') || msg.toLowerCase().includes('gps')) {
                        backToStepMap();
                    } else {
                        // Jika kendala foto selfie, buka kamera kembali
                        startCameraLive();
                    }
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi server. Silakan coba kembali.');
                startCameraLive();
            });
        }

        function closeAttendanceFlow() {
            stopCameraLive();
            attendanceModal.hide();
        }

        // Reset Khusus Akun Admin Dev
        function resetDevMobile(type) {
            if(!confirm('Reset presensi hari ini?')) return;
            fetch("{{ route('attendance.admin.reset') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: type })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.status === 'success') window.location.reload();
            })
            .catch(() => alert('Gagal reset.'));
        }
    </script>
</body>
</html>
