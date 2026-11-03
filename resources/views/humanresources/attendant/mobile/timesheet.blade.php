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
</head>
<body>
    <div class="header-card">
        <h5 class="mb-1 fw-bold"><i class="fa fa-calendar-check me-2"></i>Timesheet Karyawan</h5>
        <small class="text-white-50">Rekap riwayat kehadiran per bulan</small>
        
        <form method="GET" action="{{ route('attendance.mobile.timesheet') }}" class="mt-3">
            <div class="input-group">
                <input type="month" name="month" class="form-control" value="{{ $month }}">
                <button class="btn btn-warning" type="submit"><i class="fa fa-filter"></i></button>
            </div>
        </form>
    </div>

    <div class="container px-3 mt-3">
        @if(count($entries) === 0)
            <div class="text-center py-5 text-muted">
                <i class="fa fa-clipboard-list fa-3x mb-3"></i>
                <p>Belum ada data kehadiran pada periode ini.</p>
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
                <div class="timesheet-row d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">{{ date('d M Y (D)', strtotime($row->Scan_Date)) }}</div>
                        <span class="badge bg-light text-dark border">{{ $row->Category ?? 'KERJA' }}</span>
                        @if($row->Late_Minutes > 0)
                            <span class="badge bg-danger text-white"><i class="fa fa-clock me-1"></i>Terlambat: {{ formatDurationMinutes($row->Late_Minutes) }}</span>
                        @endif
                        @if(!empty($row->Early_Leave_Minutes) && $row->Early_Leave_Minutes > 0)
                            <span class="badge bg-warning text-dark"><i class="fa fa-person-walking-arrow-right me-1"></i>Pulang Cepat: {{ formatDurationMinutes($row->Early_Leave_Minutes) }}</span>
                        @endif
                    </div>
                    <div class="text-end">
                        <div>
                            <span class="text-success fw-bold">{{ $row->Start_Time ?? '--:--' }}</span>
                            <span class="text-muted mx-1">-</span>
                            <span class="text-danger fw-bold">{{ $row->End_Time ?? '--:--' }}</span>
                        </div>
                        @if($row->Overtime_Minutes > 0)
                            <small class="d-block text-primary fw-semibold" style="font-size: 11px;">
                                <i class="fa fa-business-time me-1"></i>Lembur: +{{ formatDurationMinutes($row->Overtime_Minutes) }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <a href="{{ route('attendance.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-fingerprint"></i>
            <span>Presensi</span>
        </a>
        <a href="{{ route('attendance.mobile.timesheet') }}" class="nav-item-mobile active">
            <i class="fa fa-calendar-check"></i>
            <span>Timesheet</span>
        </a>
        <a href="{{ route('attendance.mobile.leave') }}" class="nav-item-mobile">
            <i class="fa fa-paper-plane"></i>
            <span>Izin & Cuti</span>
        </a>
    </div>
</body>
</html>

