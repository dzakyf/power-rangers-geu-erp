<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengajuan Izin & Cuti</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; padding-bottom: 80px; font-family: -apple-system, sans-serif; }
        .header-card { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 20px; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; height: 65px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-around; align-items: center; }
        .nav-item-mobile { text-align: center; color: #8e9aab; text-decoration: none; font-size: 11px; display: flex; flex-direction: column; align-items: center; }
        .nav-item-mobile.active { color: #1e3c72; font-weight: bold; }
        .nav-item-mobile i { font-size: 20px; margin-bottom: 3px; }
    </style>
</head>
<body>
    <div class="header-card">
        <h5 class="mb-1 fw-bold"><i class="fa fa-paper-plane me-2"></i>Form Izin & Cuti</h5>
        <small class="text-white-50">Pengajuan otomatis terhubung ke Approval Engine</small>
    </div>

    <div class="container px-3 mt-3">
        <!-- Card Form Pengajuan -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <h6 class="fw-bold mb-3">Buat Pengajuan Baru</h6>
            <form id="leave-form">
                <div class="mb-2">
                    <label class="form-label small text-muted">Kategori Izin / Cuti</label>
                    <select name="leave_type" id="leave_type" class="form-select" required>
                        @php
                            $kategoriList = DB::table('kategori_izin')->where('status', 'ACTIVE')->orderBy('izin_jenis_id', 'asc')->get();
                        @endphp
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->kat_izin_code ?? $kat->kat_izin_nama }}">
                                {{ $kat->kat_izin_nama }} (Maks {{ $kat->jatah_hari }} hari)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small text-muted">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted">Alasan / Keterangan</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Tuliskan alasan pengajuan..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold" id="btn-submit">
                    <i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Riwayat Pengajuan -->
        <h6 class="fw-bold mb-2">Riwayat Pengajuan Terakhir</h6>
        @if(count($leaves) === 0)
            <div class="text-center py-4 text-muted small">Belum ada pengajuan izin/cuti.</div>
        @else
            @foreach($leaves as $lv)
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">{{ $lv->leave_type }}</span>
                        <span class="badge {{ $lv->status == 'APPROVED' ? 'bg-success' : ($lv->status == 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $lv->status }}</span>
                    </div>
                    <small class="text-muted d-block">{{ date('d M Y', strtotime($lv->start_date)) }} s/d {{ date('d M Y', strtotime($lv->end_date)) }} ({{ $lv->total_days }} hari)</small>
                    <small class="text-dark mt-1">{{ $lv->reason }}</small>
                    @if($lv->approval_request_id)
                        <small class="text-primary mt-1" style="font-size: 10px;"><i class="fa fa-check-double me-1"></i>Approval Ref #{{ $lv->approval_request_id }}</small>
                    @endif
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
        <a href="{{ route('attendance.mobile.timesheet') }}" class="nav-item-mobile">
            <i class="fa fa-calendar-check"></i>
            <span>Timesheet</span>
        </a>
        <a href="{{ route('attendance.mobile.leave') }}" class="nav-item-mobile active">
            <i class="fa fa-paper-plane"></i>
            <span>Izin & Cuti</span>
        </a>
    </div>

    <script>
        document.getElementById('leave-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;

            const payload = {
                leave_type: document.getElementById('leave_type').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                reason: document.getElementById('reason').value,
            };

            fetch("{{ route('attendance.mobile.submitleave') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(({ status, ok, body }) => {
                if (ok && body.status === 'success') {
                    alert(body.message || 'Pengajuan izin/cuti berhasil dikirimkan!');
                    window.location.reload();
                } else {
                    let errMsg = body.message || (body.errors ? Object.values(body.errors).flat().join('\n') : 'Pengajuan gagal diproses.');
                    alert('Gagal Pengajuan:\n' + errMsg);
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan atau koneksi server terputus.');
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>

