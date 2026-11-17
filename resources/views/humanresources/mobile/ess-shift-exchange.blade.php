<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Pertukaran Shift - ESS Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <h1 class="fs-6 fw-bold m-0">Pertukaran Shift</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="fw-bold mb-1"><i class="fas fa-exchange-alt me-1"></i> Ganti &amp; Tukar Jadwal Shift</div>
            <p class="small mb-0 opacity-75">Ajukan perubahan shift mandiri atau bertukar jadwal dinas dengan rekan satu departemen.</p>
        </div>

        <div class="finora-card">
            <form id="mobile-changeshift-form" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipe Pengajuan Shift <span class="text-danger">*</span></label>
                    <select name="request_mode" id="m_cs_mode" class="form-select rounded-3" required onchange="toggleMobileShiftMode(this.value)">
                        <option value="CHANGE">Ganti Shift Mandiri (Ubah Jadwal Sendiri)</option>
                        <option value="EXCHANGE">Tukar Jadwal Dinas dengan Rekan Kerja</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Dinas yang Diubah <span class="text-danger">*</span></label>
                    <input type="date" name="original_date" id="m_cs_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required onchange="fetchMobileShiftInfo(this.value)">
                    <small class="text-muted d-block mt-1" id="m_cs_current_info" style="font-size: 11px;">
                        Shift saat ini: <strong class="text-success">{{ $schedule['shift_name'] ?? 'Regular' }} ({{ $schedule['default_start'] ?? '08:00' }} - {{ $schedule['default_end'] ?? '17:00' }})</strong>
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Shift Pengganti (Tujuan) <span class="text-danger">*</span></label>
                    <select name="target_shift_id" class="form-select rounded-3" required>
                        <option value="">-- Pilih Shift Baru --</option>
                        @foreach ($availableShifts as $sh)
                            <option value="{{ $sh->id }}">
                                {{ $sh->shift_name }} ({{ substr($sh->start_time, 0, 5) }} - {{ substr($sh->end_time, 0, 5) }}) {{ $sh->is_off ? '[LIBUR/OFF]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 m-peer-field" style="display: none;">
                    <label class="form-label small fw-bold">Rekan Kerja Pengganti <span class="text-danger">*</span></label>
                    <select name="peer_nik" id="m_cs_peer_nik" class="form-select rounded-3" onchange="fetchMobilePeerShift()">
                        <option value="">-- Pilih Rekan Satu Departemen --</option>
                        @foreach ($colleagues as $col)
                            <option value="{{ $col->Emp_ID }}">{{ $col->Name }} ({{ $col->Emp_ID }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1" id="m_cs_peer_info" style="font-size: 11px;"></small>
                </div>

                <div class="mb-3 m-peer-field" style="display: none;">
                    <label class="form-label small fw-bold">Tanggal Shift Rekan <span class="text-danger">*</span></label>
                    <input type="date" name="target_date" id="m_cs_target_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" onchange="fetchMobilePeerShift()">
                </div>

                <div class="mb-3 m-peer-field" style="display: none;">
                    <label class="form-label small fw-bold">Shift Baru untuk Rekan</label>
                    <select name="peer_target_shift_id" class="form-select rounded-3">
                        <option value="">-- Otomatis Mengisi Shift Pemohon --</option>
                        @foreach ($availableShifts as $sh)
                            <option value="{{ $sh->id }}">{{ $sh->shift_name }} ({{ substr($sh->start_time, 0, 5) }} - {{ substr($sh->end_time, 0, 5) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Lampiran Pendukung (Opsional)</label>
                    <input type="file" name="attachment_file" class="form-control rounded-3" accept=".pdf,image/png,image/jpeg,image/jpg">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Alasan Perubahan / Tukar Shift <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="Jelaskan keperluan pertukaran shift..." required></textarea>
                </div>

                <button type="submit" id="btn-submit-cs" class="finora-btn-primary w-100">
                    <i class="fas fa-paper-plane"></i>
                    <span>Kirim Pengajuan Shift</span>
                </button>
            </form>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

    <script>
        function toggleMobileShiftMode(mode) {
            document.querySelectorAll('.m-peer-field').forEach(function (el) {
                el.style.display = (mode === 'EXCHANGE') ? 'block' : 'none';
            });
        }

        function fetchMobileShiftInfo(date) {
            fetch("{{ route('attendance.changeshift.schedule') }}?date=" + encodeURIComponent(date))
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.shift_name) {
                        document.getElementById('m_cs_current_info').innerHTML =
                            'Shift saat ini: <strong class="text-success">' + res.shift_name + ' (' + res.default_start + ' - ' + res.default_end + ')</strong>';
                    }
                }).catch(function () {});
        }

        function fetchMobilePeerShift() {
            var nik = document.getElementById('m_cs_peer_nik').value;
            var dt = document.getElementById('m_cs_target_date').value;
            if (!nik || !dt) return;
            fetch("{{ route('attendance.changeshift.schedule') }}?date=" + encodeURIComponent(dt) + "&nik=" + encodeURIComponent(nik))
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.shift_name) {
                        document.getElementById('m_cs_peer_info').innerHTML =
                            'Shift rekan: <strong class="text-success">' + res.shift_name + ' (' + res.default_start + ' - ' + res.default_end + ')</strong>';
                    }
                }).catch(function () {});
        }

        document.getElementById('mobile-changeshift-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-submit-cs');
            btn.disabled = true;

            fetch("{{ route('attendance.request.changeshift') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: new FormData(this)
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, body: d }; }); })
            .then(function (res) {
                if (res.ok && (res.body.status === 'success' || res.body.success)) {
                    alert(res.body.message || 'Pengajuan pertukaran shift berhasil dikirim!');
                    window.location.href = "{{ $resolvedBackUrl }}";
                } else {
                    alert(res.body.message || 'Gagal mengirim pengajuan pertukaran shift.');
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
