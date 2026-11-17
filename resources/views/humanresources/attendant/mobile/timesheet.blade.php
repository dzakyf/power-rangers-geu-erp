<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Timesheet Mandiri</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; padding-bottom: 80px; font-family: -apple-system, sans-serif; }
        .header-card { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 20px; }
        .timesheet-row { background: #fff; border-radius: 12px; margin-bottom: 8px; padding: 12px 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; height: 65px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-around; align-items: center; }
        .nav-item-mobile { text-align: center; color: #8e9aab; text-decoration: none; font-size: 11px; display: flex; flex-direction: column; align-items: center; }
        .nav-item-mobile.active { color: #1e3c72; font-weight: bold; }
        .nav-item-mobile i { font-size: 20px; margin-bottom: 3px; }
    </style>
    @include('humanresources.mobile.partials.theme')
</head>
<body>
    @php
        $resolvedBackUrl = mobile_back_url(route('mobile.ess'));
        $fromParams = mobile_from_params();
    @endphp
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('attendance.mobile.index', $fromParams) }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Timesheet Karyawan</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="fw-bold mb-1"><i class="fas fa-calendar-check me-1"></i> Rekap Riwayat Kehadiran</div>
            <p class="small mb-2 opacity-75">Pantau catatan jam masuk, pulang, keterlambatan, dan lembur per bulan.</p>
            <form method="GET" action="{{ route('attendance.mobile.timesheet') }}">
                <div class="input-group input-group-sm">
                    <input type="month" name="month" class="form-control rounded-start-3 border-0" value="{{ $month }}">
                    <button class="btn btn-light text-success fw-bold rounded-end-3 px-3" type="submit"><i class="fa fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
        @if(count($entries) === 0)
            <div class="finora-card text-center py-5 text-muted">
                <i class="fa fa-clipboard-list fa-3x mb-3 text-success opacity-50"></i>
                <div class="fw-bold text-dark">Belum Ada Data Kehadiran</div>
                <p class="small mb-0">Tidak ditemukan catatan presensi pada periode ini.</p>
            </div>
        @else
            @php
                if (!function_exists('formatDurationMinutes')) {
                    function formatDurationMinutes($totalMinutes) {
                        $totalMinutes = (int) $totalMinutes;
                        if ($totalMinutes <= 0) return '0 menit';
                        $hours = floor($totalMinutes / 60);
                        $mins = $totalMinutes % 60;
                        if ($hours > 0 && $mins > 0) {
                            return "{$hours} jam {$mins} menit";
                        } elseif ($hours > 0) {
                            return "{$hours} jam";
                        }
                        return "{$mins} menit";
                    }
                }
            @endphp
            @foreach($entries as $row)
                @php
                    $isLate = ($row->Late_Minutes ?? 0) > 0;
                    $tone = $isLate ? 'tone-coral' : 'tone-mint';
                @endphp
                <div class="finora-card mb-2 d-flex justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="rounded-4 d-inline-flex align-items-center justify-content-center flex-shrink-0 {{ $tone }}" style="width: 42px; height: 42px;">
                            <i class="fas {{ $isLate ? 'fa-clock' : 'fa-calendar-check' }}"></i>
                        </span>
                        <div>
                            <div class="fw-bold small">{{ date('d M Y (D)', strtotime($row->Scan_Date)) }}</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <span class="badge bg-light text-dark border rounded-pill" style="font-size: 10px;">{{ $row->Category ?? 'KERJA' }}</span>
                                @if($row->Late_Minutes > 0)
                                    <span class="badge bg-danger text-white rounded-pill" style="font-size: 10px;">Telat {{ formatDurationMinutes($row->Late_Minutes) }}</span>
                                @endif
                                @if(!empty($row->Early_Leave_Minutes) && $row->Early_Leave_Minutes > 0)
                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 10px;">Pulang Cepat {{ formatDurationMinutes($row->Early_Leave_Minutes) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="small">
                            <span class="text-success fw-bold">{{ $row->Start_Time ? substr((string)$row->Start_Time, 0, 5) : '--:--' }}</span>
                            <span class="text-muted mx-1">-</span>
                            <span class="text-danger fw-bold">{{ $row->End_Time ? substr((string)$row->End_Time, 0, 5) : '--:--' }}</span>
                        </div>
                        @if($row->Overtime_Minutes > 0)
                            <small class="d-block text-success fw-semibold" style="font-size: 10px;">
                                <i class="fa fa-business-time me-1"></i>+{{ formatDurationMinutes($row->Overtime_Minutes) }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'attendance'])
</body>
</html>

