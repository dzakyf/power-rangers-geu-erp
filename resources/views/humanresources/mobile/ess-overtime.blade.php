<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Pengajuan Lembur - ESS Mobile</title>
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
            <h1 class="fs-6 fw-bold m-0">Pengajuan Lembur</h1>
            <span style="width: 38px;"></span>
        </div>

        <!-- Realtime Estimation Hero Card (Finora Style) -->
        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small opacity-75">Estimasi Matriks Lembur</span>
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill" id="m_ot_region">Wilayah 1</span>
            </div>
            <div class="fs-3 fw-bold mb-1" id="m_ot_amount">Rp 0</div>
            <div class="small opacity-75" id="m_ot_breakdown">Sesuaikan jam lembur untuk melihat kalkulasi estimasi.</div>
            <div class="mt-2">
                <span class="badge bg-light text-success rounded-pill" id="m_ot_hours">0 Jam Lembur</span>
            </div>
        </div>

        <div class="finora-card">
            <form id="mobile-overtime-form" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Lembur <span class="text-danger">*</span></label>
                    <input type="date" name="overtime_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipe Hari Lembur <span class="text-danger">*</span></label>
                    <select name="day_type" id="m_ot_day_type" class="form-select rounded-3" required onchange="calcMobileOvertime()">
                        <option value="WORKDAY" selected>Hari Kerja Reguler</option>
                        <option value="HOLIDAY">Hari Libur Mingguan / Nasional</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Jenis Waktu Lembur <span class="text-danger">*</span></label>
                    <select name="overtime_type" id="m_ot_type" class="form-select rounded-3" required onchange="calcMobileOvertime()">
                        <option value="AFTER_SHIFT" selected>Lembur Setelah Shift (Pasca-Shift)</option>
                        <option value="BEFORE_SHIFT">Lembur Sebelum Shift (Pra-Shift)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilihan Kompensasi <span class="text-danger">*</span></label>
                    <select name="compensation_type" id="m_ot_comp" class="form-select rounded-3" required onchange="calcMobileOvertime()">
                        <option value="PAID" selected>Uang Lembur (Matriks Tarif)</option>
                        <option value="TIME_OFF">Tukar Cuti / Day Off</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Lokasi Kantor <span class="text-danger">*</span></label>
                    <select name="location_id" id="m_ot_loc" class="form-select rounded-3" onchange="syncMobileZip()">
                        <option value="">-- Pilih Lokasi Kerja --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" data-zip="{{ $loc->zipcode ?? '' }}">
                                {{ $loc->name }} ({{ $loc->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Kode Pos Wilayah Kerja</label>
                    <input type="text" name="zipcode" id="m_ot_zip" class="form-control rounded-3" placeholder="Contoh: 14240" oninput="calcMobileOvertime()">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="m_ot_start" class="form-control rounded-3" value="17:30" required onchange="calcMobileOvertime()">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="m_ot_end" class="form-control rounded-3" value="21:30" required onchange="calcMobileOvertime()">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Upload Hardcopy Formulir Lembur <span class="text-danger">*</span></label>
                    <input type="file" name="hardcopy_file" id="m_ot_file" class="form-control rounded-3" accept=".pdf,image/png,image/jpeg,image/jpg,.xlsx" required>
                    <small class="text-muted" style="font-size: 11px;">Wajib lampirkan formulir lembur bertanda tangan (PDF/Foto/Excel maks 5MB).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Uraian Tugas Lembur <span class="text-danger">*</span></label>
                    <textarea name="task_description" rows="3" class="form-control rounded-3" placeholder="Jelaskan rincian pekerjaan lembur..." required></textarea>
                </div>

                <button type="submit" id="btn-submit-ot" class="finora-btn-primary w-100">
                    <i class="fas fa-paper-plane"></i>
                    <span>Kirim Pengajuan Lembur</span>
                </button>
            </form>
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'ess'])

    <script>
        function syncMobileZip() {
            var sel = document.getElementById('m_ot_loc');
            var opt = sel.options[sel.selectedIndex];
            var zip = opt ? opt.getAttribute('data-zip') : '';
            if (zip) { document.getElementById('m_ot_zip').value = zip; }
            calcMobileOvertime();
        }

        function calcMobileOvertime() {
            var start = document.getElementById('m_ot_start').value;
            var end = document.getElementById('m_ot_end').value;
            if (!start || !end) return;

            fetch("{{ route('attendance.overtime.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    start_time: start,
                    end_time: end,
                    compensation_type: document.getElementById('m_ot_comp').value,
                    location_id: document.getElementById('m_ot_loc').value,
                    zipcode: document.getElementById('m_ot_zip').value
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.status === 'success' && res.data) {
                    var d = res.data;
                    document.getElementById('m_ot_amount').innerText = d.formatted_amount || 'Rp 0';
                    document.getElementById('m_ot_breakdown').innerText = d.breakdown || '';
                    document.getElementById('m_ot_hours').innerText = (d.duration_hours || 0) + ' Jam (' + (d.duration_minutes || 0) + ' Mnt)';
                    document.getElementById('m_ot_region').innerText = res.region === 'WILAYAH_2' ? 'Wilayah 2' : 'Wilayah 1';
                }
            })
            .catch(function () {});
        }

        document.addEventListener('DOMContentLoaded', calcMobileOvertime);

        document.getElementById('mobile-overtime-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-submit-ot');
            btn.disabled = true;

            fetch("{{ route('attendance.request.overtime') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: new FormData(this)
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, body: d }; }); })
            .then(function (res) {
                if (res.ok && res.body.status === 'success') {
                    alert(res.body.message || 'Pengajuan lembur berhasil dikirim!');
                    window.location.href = "{{ $resolvedBackUrl }}";
                } else {
                    alert(res.body.message || 'Gagal mengirim pengajuan lembur.');
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
