<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Request Kehadiran / Koreksi - ESS Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
        .corr-radio-card {
            background: #FFFFFF;
            border: 1.5px solid var(--border-soft);
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin-bottom: 8px;
        }
        .corr-radio-card.active {
            border-color: var(--emerald-primary);
            background: var(--emerald-soft);
        }
    </style>
</head>
<body>
    @php
        $resolvedBackUrl = mobile_back_url(route('mobile.ess'));
        $fromParams = mobile_from_params();
    @endphp
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ $resolvedBackUrl }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Request Kehadiran</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="fw-bold mb-1"><i class="fas fa-clock me-1"></i> Koreksi Jam Kehadiran</div>
            <p class="small mb-0 opacity-75">Ajukan perbaikan jam masuk / pulang apabila lupa melakukan tap absensi atau sedang dinas luar.</p>
        </div>

        <div class="finora-card">
            <form id="mobile-correction-form" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Jenis Koreksi <span class="text-danger">*</span></label>
                    <label class="corr-radio-card active" for="m_corr_both">
                        <input class="form-check-input m-0" type="radio" name="correction_type" id="m_corr_both" value="BOTH" checked onchange="toggleMobileCorr()">
                        <div>
                            <div class="fw-bold small">Jam Masuk &amp; Pulang</div>
                            <div class="text-muted" style="font-size: 11px;">Koreksi kedua waktu kehadiran</div>
                        </div>
                    </label>
                    <label class="corr-radio-card" for="m_corr_in">
                        <input class="form-check-input m-0" type="radio" name="correction_type" id="m_corr_in" value="IN" onchange="toggleMobileCorr()">
                        <div>
                            <div class="fw-bold small">Jam Masuk Saja</div>
                            <div class="text-muted" style="font-size: 11px;">Lupa Clock In</div>
                        </div>
                    </label>
                    <label class="corr-radio-card" for="m_corr_out">
                        <input class="form-check-input m-0" type="radio" name="correction_type" id="m_corr_out" value="OUT" onchange="toggleMobileCorr()">
                        <div>
                            <div class="fw-bold small">Jam Pulang Saja</div>
                            <div class="text-muted" style="font-size: 11px;">Lupa Clock Out</div>
                        </div>
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Absensi <span class="text-danger">*</span></label>
                    <input type="date" name="attendance_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6" id="m_corr_in_wrap">
                        <label class="form-label small fw-bold">Jam Masuk <span class="text-danger">*</span></label>
                        <input type="time" name="corrected_clock_in" id="m_corr_in_val" class="form-control rounded-3" value="08:00" required>
                    </div>
                    <div class="col-6" id="m_corr_out_wrap">
                        <label class="form-label small fw-bold">Jam Pulang <span class="text-danger">*</span></label>
                        <input type="time" name="corrected_clock_out" id="m_corr_out_val" class="form-control rounded-3" value="17:00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Bukti Pendukung (Opsional, Maks 5MB)</label>
                    <input type="file" name="evidence_attachment" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Alasan Koreksi <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="Jelaskan alasan koreksi kehadiran..." required></textarea>
                </div>

                <button type="submit" id="btn-submit-corr" class="finora-btn-primary w-100">
                    <i class="fas fa-paper-plane"></i>
                    <span>Kirim Request Kehadiran</span>
                </button>
            </form>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

    <script>
        function toggleMobileCorr() {
            var val = document.querySelector('input[name="correction_type"]:checked').value;
            document.querySelectorAll('.corr-radio-card').forEach(function (el) { el.classList.remove('active'); });
            document.querySelector('input[name="correction_type"]:checked').closest('.corr-radio-card').classList.add('active');

            var inWrap = document.getElementById('m_corr_in_wrap');
            var outWrap = document.getElementById('m_corr_out_wrap');
            var inInput = document.getElementById('m_corr_in_val');
            var outInput = document.getElementById('m_corr_out_val');

            inWrap.style.display = (val === 'OUT') ? 'none' : 'block';
            inInput.required = (val !== 'OUT');
            outWrap.style.display = (val === 'IN') ? 'none' : 'block';
            outInput.required = (val !== 'IN');
        }

        document.getElementById('mobile-correction-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-submit-corr');
            btn.disabled = true;

            fetch("{{ route('attendance.mobile.submitcorrection') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: new FormData(this)
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, body: d }; }); })
            .then(function (res) {
                if (res.ok && (res.body.success || res.body.status === 'success')) {
                    alert(res.body.message || 'Request kehadiran berhasil dikirim.');
                    window.location.href = "{{ $resolvedBackUrl }}";
                } else {
                    alert(res.body.message || 'Gagal mengirim request kehadiran.');
                    btn.disabled = false;
                }
            })
            .catch(function () {
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
