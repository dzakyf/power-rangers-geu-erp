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
    @include('humanresources.mobile.partials.theme')
</head>
<body>
    @php
        $resolvedBackUrl = mobile_back_url(route('mobile.ess'));
        $fromParams = mobile_from_params();
    @endphp
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ $resolvedBackUrl }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Pengajuan Izin &amp; Cuti</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="fw-bold mb-1"><i class="fas fa-paper-plane me-1"></i> Form Izin &amp; Cuti</div>
            <p class="small mb-0 opacity-75">Pengajuan otomatis terhubung ke Approval Engine HCGA.</p>
        </div>
        <!-- Card Form Pengajuan -->
        <div class="finora-card mb-3">
            <h6 class="fw-bold mb-3">Buat Pengajuan Baru</h6>
            <form id="leave-form">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Kategori Izin / Cuti</label>
                    <select name="leave_type" id="leave_type" class="form-select rounded-3" required>
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
                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Alasan / Keterangan</label>
                    <textarea name="reason" id="reason" class="form-control rounded-3" rows="3" placeholder="Tuliskan alasan pengajuan..." required></textarea>
                </div>

                <button type="submit" class="btn finora-btn-primary w-100 rounded-pill py-2 fw-semibold" id="btn-submit">
                    <i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Riwayat Pengajuan -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small text-uppercase text-muted">Riwayat Pengajuan Terakhir</span>
            <span class="finora-pill">{{ count($leaves) }}</span>
        </div>
        @if(count($leaves) === 0)
            <div class="finora-card text-center py-4 text-muted small">Belum ada pengajuan izin/cuti.</div>
        @else
            @foreach($leaves as $lv)
                @php
                    $lvTone = $lv->status == 'APPROVED' ? 'tone-mint' : ($lv->status == 'REJECTED' ? 'tone-coral' : 'tone-amber');
                    $lvBadge = $lv->status == 'APPROVED' ? 'bg-success text-white' : ($lv->status == 'REJECTED' ? 'bg-danger text-white' : 'bg-warning text-dark');
                @endphp
                <div class="finora-card mb-2">
                    <div class="d-flex align-items-start gap-3">
                        <span class="rounded-4 d-inline-flex align-items-center justify-content-center flex-shrink-0 {{ $lvTone }}" style="width: 40px; height: 40px;">
                            <i class="fas fa-calendar-day"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small">{{ $lv->leave_type }}</span>
                                <span class="badge {{ $lvBadge }} rounded-pill" style="font-size: 10px;">{{ $lv->status }}</span>
                            </div>
                            <small class="text-muted d-block">{{ date('d M Y', strtotime($lv->start_date)) }} s/d {{ date('d M Y', strtotime($lv->end_date)) }} ({{ $lv->total_days }} hari)</small>
                            <small class="text-dark d-block mt-1">{{ $lv->reason }}</small>
                            @if($lv->approval_request_id)
                                <small class="text-success d-block mt-1 fw-semibold" style="font-size: 10px;"><i class="fa fa-check-double me-1"></i>Approval Ref #{{ $lv->approval_request_id }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

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

