<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Titik Lokasi Presensi - HCGA Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @include('humanresources.mobile.partials.theme')
    <style>
        #osm-map {
            height: 360px;
            width: 100%;
            border-radius: 18px;
            border: 1px solid var(--border-soft);
            z-index: 1;
        }
        .gps-status-box {
            background: var(--emerald-soft);
            border: 1px solid rgba(4, 120, 87, 0.22);
            border-radius: 14px;
            padding: 12px 14px;
            color: var(--emerald-deep);
        }
    </style>
</head>
<body>
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('mobile.dashboard') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">{{ $action === 'clock-in' ? 'Clock In' : 'Clock Out' }} &bull; Lokasi</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    Langkah 1 dari 2
                </span>
                <span class="badge bg-light text-success rounded-pill px-3 py-1" style="font-size: 11px;" id="live-clock">--:--:--</span>
            </div>
            <div class="fw-bold mb-1"><i class="fas fa-map-marker-alt me-1"></i> Konfirmasi Titik Lokasi GPS</div>
            <p class="small mb-0 opacity-75">Pastikan posisi GPS Anda akurat sebelum melanjutkan ke tahap pengambilan foto selfie wajib.</p>
        </div>

        <div class="finora-card">
            <div id="osm-map" class="mb-3"></div>

            <div class="gps-status-box mb-3" id="map-coord-box">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="small" id="map-coord-text">
                        <i class="fas fa-spinner fa-spin me-1"></i> Mencari koordinat GPS Anda...
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="locateUser()">
                        <i class="fas fa-crosshairs me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <button type="button" class="btn finora-btn-primary w-100 rounded-pill py-3 fw-bold" id="btn-next-selfie" disabled onclick="goToSelfiePage()">
                Next: Foto Selfie Wajib <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'attendance'])

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const flowAction = @json($action);
        const selfieBaseUrl = @json(route('attendance.mobile.selfie'));
        const officeLocations = @json($locations ?? []);

        let userLat = null;
        let userLng = null;
        let mapInstance = null;
        let userMarker = null;

        setInterval(() => {
            const now = new Date();
            document.getElementById('live-clock').innerText = now.toTimeString().split(' ')[0];
        }, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            locateUser();
        });

        function initMap() {
            mapInstance = L.map('osm-map').setView([-6.2088, 106.8456], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapInstance);

            if (Array.isArray(officeLocations)) {
                officeLocations.forEach(loc => {
                    const lat = parseFloat(loc.latitude || loc.lat || 0);
                    const lng = parseFloat(loc.longitude || loc.lng || 0);
                    const rad = parseFloat(loc.radius_meters || loc.radius || 100);
                    if (lat && lng) {
                        L.circle([lat, lng], {
                            color: '#047857',
                            fillColor: '#10B981',
                            fillOpacity: 0.14,
                            radius: rad
                        }).addTo(mapInstance).bindPopup(loc.location_name || loc.name || 'Area Kantor');
                    }
                });
            }
        }

        function buildGpsTelemetry(pos, prevPos) {
            const c = pos.coords || {};
            const acc = (typeof c.accuracy === 'number') ? c.accuracy : null;
            const nowMs = Date.now();
            const tsDrift = pos.timestamp ? (Math.abs(nowMs - pos.timestamp) > 300000) : false;

            let nativeMock = false;
            try {
                if (window.Android && typeof window.Android.isMockLocation === 'function') {
                    nativeMock = Boolean(window.Android.isMockLocation());
                }
            } catch (e) {}

            let staticZeroJitter = false;
            if (prevPos && prevPos.coords) {
                const sameLat = Number(prevPos.coords.latitude).toFixed(7) === Number(c.latitude).toFixed(7);
                const sameLng = Number(prevPos.coords.longitude).toFixed(7) === Number(c.longitude).toFixed(7);
                const noSensors = (c.altitude === null && c.speed === null && c.heading === null);
                if (sameLat && sameLng && noSensors && acc !== null && acc <= 5) {
                    staticZeroJitter = true;
                }
            }

            return {
                accuracy: acc,
                altitude: c.altitude !== null && c.altitude !== undefined ? c.altitude : null,
                speed: c.speed !== null && c.speed !== undefined ? c.speed : null,
                heading: c.heading !== null && c.heading !== undefined ? c.heading : null,
                is_mock: nativeMock,
                timestamp_drift: tsDrift,
                static_zero_jitter: staticZeroJitter,
                user_agent: navigator.userAgent || ''
            };
        }

        function locateUser() {
            const coordEl = document.getElementById('map-coord-text');
            const btnNext = document.getElementById('btn-next-selfie');
            btnNext.disabled = true;
            coordEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengunci & memverifikasi integritas GPS Anda...';

            if (!navigator.geolocation) {
                coordEl.innerHTML = '<span class="text-danger">Browser tidak mendukung Geolocation.</span>';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos1) => {
                    const applyPosition = (pos2) => {
                        const finalPos = pos2 || pos1;
                        userLat = finalPos.coords.latitude;
                        userLng = finalPos.coords.longitude;
                        const acc = finalPos.coords.accuracy ? Math.round(finalPos.coords.accuracy) : null;
                        const telemetry = buildGpsTelemetry(finalPos, pos2 ? pos1 : null);

                        try {
                            sessionStorage.setItem('hcga_att_gps_meta', JSON.stringify(telemetry));
                        } catch (e) {}

                        mapInstance.setView([userLat, userLng], 17);
                        if (userMarker) {
                            userMarker.setLatLng([userLat, userLng]);
                        } else {
                            userMarker = L.marker([userLat, userLng]).addTo(mapInstance);
                        }
                        userMarker.bindPopup('<b>Posisi Anda Sekarang</b>').openPopup();

                        let statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size:10px;">GPS Terverifikasi</span>';
                        if (telemetry.is_mock || (telemetry.accuracy !== null && telemetry.accuracy <= 0)) {
                            statusBadge = '<span class="badge bg-danger text-white ms-1" style="font-size:10px;">Terindikasi Mock GPS</span>';
                        } else if ((acc && acc > 500) || telemetry.static_zero_jitter || telemetry.timestamp_drift) {
                            statusBadge = '<span class="badge bg-warning text-dark ms-1" style="font-size:10px;">Anomali Ringan GPS</span>';
                        }

                        coordEl.innerHTML = `<strong>Koordinat Terkunci:</strong> ${userLat.toFixed(5)}, ${userLng.toFixed(5)} ${statusBadge}` +
                            (acc ? `<div class="text-muted" style="font-size:11px;">Akurasi GPS ±${acc} meter</div>` : '');
                        btnNext.disabled = Boolean(telemetry.is_mock);
                    };

                    // Ambil sampel kedua singkat untuk cek jitter sensor GPS
                    setTimeout(() => {
                        navigator.geolocation.getCurrentPosition(
                            (pos2) => applyPosition(pos2),
                            () => applyPosition(null),
                            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                        );
                    }, 450);
                },
                (err) => {
                    coordEl.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Gagal GPS: ${err.message}. Pastikan izin lokasi aktif.</span>`;
                    btnNext.disabled = true;
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        }

        function goToSelfiePage() {
            if (userLat === null || userLng === null) {
                alert('Titik lokasi GPS belum terkunci.');
                return;
            }
            try {
                sessionStorage.setItem('hcga_att_lat', String(userLat));
                sessionStorage.setItem('hcga_att_lng', String(userLng));
            } catch (e) {}

            const url = `${selfieBaseUrl}?action=${encodeURIComponent(flowAction)}&lat=${encodeURIComponent(userLat)}&lng=${encodeURIComponent(userLng)}`;
            window.location.href = url;
        }
    </script>
</body>
</html>
