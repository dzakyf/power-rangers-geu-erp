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
    @include('humanresources.mobile.partials.theme')
</head>
<body>

    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('mobile.ess') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Presensi Kehadiran</h1>
            <a href="{{ route('attendance.mobile.timesheet') }}" class="finora-back-btn" title="Timesheet"><i class="fas fa-calendar-alt"></i></a>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill mb-1" style="font-size: 11px;">NIK: {{ $user->Emp_ID }}</span>
                    <div class="fs-6 fw-bold">{{ $user->name ?? $user->username }}</div>
                    <small class="opacity-75">{{ $user->Department ?? 'HCGA Employee' }}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-success rounded-pill" style="font-size: 11px;"><i class="fa fa-calendar-alt me-1"></i> {{ date('d M Y') }}</span>
                    <h3 class="mb-0 mt-1 fw-bold" id="live-clock">--:--:--</h3>
                </div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="small opacity-90"><i class="fas fa-business-time me-1"></i> Jadwal Kerja Hari Ini</span>
                <span class="badge bg-white text-success rounded-pill fw-bold">{{ $schedule['default_start'] }} - {{ $schedule['default_end'] }}</span>
            </div>
        </div>

        <!-- Finora Action Cards: Clock In & Clock Out -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                @php $clockInDisabled = ($todayEntry && $todayEntry->Start_Time); @endphp
                <button type="button" id="btn-clock-in"
                        onclick="window.location.href='{{ route('attendance.mobile.location', ['action' => 'clock-in']) }}'"
                        {{ $clockInDisabled ? 'disabled' : '' }}
                        class="btn w-100 p-3 text-start shadow-sm d-flex flex-column justify-content-between {{ $clockInDisabled ? 'bg-white text-muted border opacity-75' : 'text-white border-0' }}"
                        style="border-radius: 18px; min-height: 114px; {{ $clockInDisabled ? '' : 'background: linear-gradient(135deg, #064E3B 0%, #047857 100%); box-shadow: 0 8px 18px rgba(4, 120, 87, 0.22) !important;' }}">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="rounded-3 d-inline-flex align-items-center justify-content-center"
                              style="width: 38px; height: 38px; background: {{ $clockInDisabled ? '#F1F5F9' : 'rgba(255,255,255,0.2)' }};">
                            <i class="fas fa-sign-in-alt"></i>
                        </span>
                        <i class="fas fa-arrow-right small opacity-75"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 15px; line-height: 1.2;">Clock In</div>
                        <div style="font-size: 11px; opacity: 0.85;">{{ $clockInDisabled ? 'Tercatat ' . substr((string)$todayEntry->Start_Time, 0, 5) : 'Tap untuk Masuk' }}</div>
                    </div>
                </button>
            </div>
            <div class="col-6">
                @php $clockOutDisabled = (!$todayEntry || !$todayEntry->Start_Time || $todayEntry->End_Time); @endphp
                <button type="button" id="btn-clock-out"
                        onclick="window.location.href='{{ route('attendance.mobile.location', ['action' => 'clock-out']) }}'"
                        {{ $clockOutDisabled ? 'disabled' : '' }}
                        class="btn w-100 p-3 text-start shadow-sm d-flex flex-column justify-content-between {{ $clockOutDisabled ? 'bg-white text-muted border opacity-75' : 'bg-white text-danger border border-danger border-opacity-25' }}"
                        style="border-radius: 18px; min-height: 114px;">
                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                        <span class="rounded-3 d-inline-flex align-items-center justify-content-center {{ $clockOutDisabled ? 'bg-light text-muted' : 'tone-coral' }}"
                              style="width: 38px; height: 38px;">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                        <i class="fas fa-arrow-right small opacity-75"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 15px; line-height: 1.2;">Clock Out</div>
                        <div style="font-size: 11px; opacity: 0.85;">{{ ($todayEntry && $todayEntry->End_Time) ? 'Tercatat ' . substr((string)$todayEntry->End_Time, 0, 5) : 'Tap untuk Pulang' }}</div>
                    </div>
                </button>
            </div>
        </div>

        @if(strtolower(Auth::user()->username) === 'admin')
            <div class="finora-card mb-3 py-2 text-center">
                <small class="text-danger fw-bold d-block mb-1" style="font-size: 10px;">Dev Admin Reset Tool:</small>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary py-0" style="font-size: 10px;" onclick="resetDevMobile('in')">Reset In</button>
                    <button type="button" class="btn btn-outline-secondary py-0" style="font-size: 10px;" onclick="resetDevMobile('out')">Reset Out</button>
                    <button type="button" class="btn btn-outline-danger py-0" style="font-size: 10px;" onclick="resetDevMobile('both')">Reset Both</button>
                </div>
            </div>
        @endif

        <!-- Ringkasan Kehadiran Hari Ini (Finora Transaction List Style) -->
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

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small text-uppercase text-muted">Status Kehadiran Hari Ini</span>
            <a href="{{ route('attendance.mobile.timesheet') }}" class="small fw-semibold text-success text-decoration-none">
                Lihat Timesheet <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="finora-card mb-3">
            <!-- Row Jam Masuk -->
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-4 d-inline-flex align-items-center justify-content-center tone-mint" style="width: 44px; height: 44px;">
                        <i class="fas fa-arrow-down-to-bracket"></i>
                    </span>
                    <div>
                        <div class="fw-bold small">Jam Masuk</div>
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                            @if($todayEntry && $todayEntry->Clock_In_Status)
                                <span class="badge {{ $todayEntry->Clock_In_Status == 'ON_TIME' ? 'bg-success text-white' : 'bg-danger text-white' }} rounded-pill" style="font-size: 10px;">
                                    {{ $todayEntry->Clock_In_Status }}
                                    @if(!empty($todayEntry->Late_Minutes) && $todayEntry->Late_Minutes > 0)
                                        (+{{ formatDurationMinutesHome($todayEntry->Late_Minutes) }})
                                    @endif
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 11px;">Belum melakukan Clock In</span>
                            @endif
                            @if($todayEntry && !empty($todayEntry->Clock_In_Photo))
                                <a href="javascript:void(0)" onclick="previewSelfieModal('{{ asset($todayEntry->Clock_In_Photo) }}', 'Foto Selfie Clock-In')" class="badge bg-light text-success border rounded-pill text-decoration-none" style="font-size: 10px;">
                                    <i class="fa fa-camera me-1"></i>Foto
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-6 text-success">{{ ($todayEntry && $todayEntry->Start_Time) ? substr((string)$todayEntry->Start_Time, 0, 5) : '--:--' }}</div>
                    <small class="text-muted" style="font-size: 10px;">WIB</small>
                </div>
            </div>

            <!-- Row Jam Pulang -->
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-4 d-inline-flex align-items-center justify-content-center tone-coral" style="width: 44px; height: 44px;">
                        <i class="fas fa-arrow-up-from-bracket"></i>
                    </span>
                    <div>
                        <div class="fw-bold small">Jam Pulang</div>
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                            @if($todayEntry && !empty($todayEntry->End_Time) && !empty($todayEntry->Clock_Out_Status))
                                <span class="badge {{ $todayEntry->Clock_Out_Status == 'NORMAL' ? 'bg-success text-white' : 'bg-warning text-dark' }} rounded-pill" style="font-size: 10px;">
                                    {{ $todayEntry->Clock_Out_Status }}
                                    @if(!empty($todayEntry->Early_Leave_Minutes) && $todayEntry->Early_Leave_Minutes > 0)
                                        (-{{ formatDurationMinutesHome($todayEntry->Early_Leave_Minutes) }})
                                    @endif
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 11px;">Belum melakukan Clock Out</span>
                            @endif
                            @if($todayEntry && !empty($todayEntry->Clock_Out_Photo))
                                <a href="javascript:void(0)" onclick="previewSelfieModal('{{ asset($todayEntry->Clock_Out_Photo) }}', 'Foto Selfie Clock-Out')" class="badge bg-light text-danger border rounded-pill text-decoration-none" style="font-size: 10px;">
                                    <i class="fa fa-camera me-1"></i>Foto
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-6 text-danger">{{ (!empty($todayEntry->End_Time)) ? substr((string)$todayEntry->End_Time, 0, 5) : '--:--' }}</div>
                    <small class="text-muted" style="font-size: 10px;">WIB</small>
                </div>
            </div>
        </div>

        <!-- Shortcut Layanan Terkait -->
        <div class="row g-2">
            <div class="col-6">
                <a href="{{ route('attendance.mobile.timesheet') }}" class="finora-card d-flex align-items-center gap-2 text-decoration-none text-dark p-3">
                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center tone-emerald" style="width: 36px; height: 36px;">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div>
                        <div class="fw-bold" style="font-size: 12px;">Timesheet</div>
                        <div class="text-muted" style="font-size: 10px;">Rekap bulanan</div>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('attendance.mobile.leave') }}" class="finora-card d-flex align-items-center gap-2 text-decoration-none text-dark p-3">
                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center tone-amber" style="width: 36px; height: 36px;">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                    <div>
                        <div class="fw-bold" style="font-size: 12px;">Izin &amp; Cuti</div>
                        <div class="text-muted" style="font-size: 10px;">Ajukan form</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- MODAL PREVIEW FOTO SELFIE MOBILE -->
    <div class="modal fade" id="previewSelfieModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-1">
                    <h6 class="modal-title fw-bold" id="previewSelfieTitle">Foto Bukti Kehadiran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <img id="previewSelfieImg" src="" class="img-fluid rounded-3 shadow-sm border" alt="Selfie" style="max-height: 380px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'attendance'])

    <!-- Scripts: Bootstrap 5 -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';"></script>
    <script>
        // Jam Real-time
        setInterval(() => {
            const now = new Date();
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                clockEl.innerText = now.toTimeString().split(' ')[0];
            }
        }, 1000);

        // Modal Preview Foto Selfie
        function previewSelfieModal(url, title) {
            document.getElementById('previewSelfieTitle').innerText = title || 'Foto Bukti Kehadiran';
            document.getElementById('previewSelfieImg').src = url;
            const modal = new bootstrap.Modal(document.getElementById('previewSelfieModal'));
            modal.show();
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
