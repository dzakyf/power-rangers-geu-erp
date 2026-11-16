@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Progress & Konfirmasi Rekrutmen</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Timeline 8 Tahap</span>
        </div>
    </div>
</div>

<div id="progressAlert" class="alert d-none" role="alert"></div>

@if(!empty($notifications))
<div class="row mb-3">
    <div class="col-12">
        @foreach($notifications as $notif)
        <div class="alert alert-{{ $notif['color'] }} shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-4 mb-3" style="border-left: 5px solid;">
            <div class="d-flex align-items-center">
                <div class="me-3 text-{{ $notif['color'] }}" style="font-size: 26px;">
                    <i class="fa {{ $notif['icon'] }}"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">
                        {{ $notif['title'] }}
                        <span class="badge bg-{{ $notif['color'] }} text-white ms-2">{{ $notif['badge'] }}</span>
                    </h5>
                    <p class="mb-0">
                        @if($notif['schedule'])
                        <span class="me-3">
                            <i class="fa fa-clock me-1"></i> <strong>{{ date('d M Y - H:i', strtotime($notif['schedule'])) }} WIB</strong>
                        </span>
                        @endif
                        @if($notif['location'])
                        <span class="me-3">
                            <i class="fa fa-map-marker-alt me-1"></i> {{ $notif['location'] }}
                        </span>
                        @endif
                    </p>
                    @if($notif['note'])
                    <small class="text-muted d-block mt-1 fst-italic">{{ $notif['note'] }}</small>
                    @endif
                </div>
            </div>
            @if($notif['type'] !== 'offering')
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success fw-bold btn-confirm-schedule"
                        data-stage="{{ $notif['type'] }}" data-title="{{ $notif['title'] }}" data-status="CONFIRMED">
                    <i class="fa fa-check me-1"></i> Konfirmasi Hadir
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark fw-bold btn-confirm-schedule"
                        data-stage="{{ $notif['type'] }}" data-title="{{ $notif['title'] }}" data-status="RESCHEDULE_REQUEST">
                    <i class="fa fa-calendar-alt me-1"></i> Ajukan Reschedule
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@if(!empty($canDownloadOffering))
<div class="card shadow-sm border-0 mb-4 border-start border-4 border-success">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 fw-bold text-success"><i class="fa fa-file-signature me-2"></i>Tahap Offering Letter (Download & Unggah Dokumen Bermaterai)</h6>
        <a href="{{ url('/candidate/offering-download') }}" target="_blank" class="btn btn-sm btn-success fw-bold">
            <i class="fa fa-download me-1"></i> Download Offering Letter (PDF)
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Silakan unduh dokumen <strong>Offering Letter</strong> di atas, cetak/bubuhkan <strong>materai dan tanda tangan</strong> Anda, lalu unggah kembali hasil scan/foto dokumen yang sudah ditandatangani melalui form berikut.
        </p>

        @if(!empty($signedOfferingPath))
        <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3 mb-3">
            <span><i class="fa fa-check-circle me-1"></i> Dokumen Offering Letter bertanda tangan &amp; bermaterai sudah pernah diunggah.</span>
            <a href="{{ asset($signedOfferingPath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-eye me-1"></i> Lihat Dokumen Terunggah
            </a>
        </div>
        @endif

        <form id="signedOfferingForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">File Offering Letter (PDF/JPG/PNG, maks. 5 MB)</label>
                    <input type="file" name="file" id="offeringSignedFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="candidate_note" id="offeringSignedNote" class="form-control" placeholder="Contoh: Sudah ditandatangani & bermaterai Rp10.000">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" id="btnUploadOffering" class="btn btn-primary fw-bold">
                        <i class="fa fa-upload me-1"></i> Unggah
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 fw-bold text-primary"><i class="fa fa-list-ol me-2"></i>Status Tahapan Rekrutmen HCGA</h6>
        <span class="badge bg-info text-white py-1 px-3">Posisi: {{ $rec->Position ?? '-' }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 240px;">Tahapan Rekrutmen</th>
                        <th style="width: 170px;">Tanggal / Jadwal</th>
                        <th style="width: 180px;">Status Evaluasi</th>
                        <th>Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stages as $s)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $s['no'] }}</td>
                        <td class="fw-bold text-dark">{{ $s['stage'] }}</td>
                        <td>
                            @if($s['date'])
                                <i class="fa fa-calendar me-1 text-muted"></i>
                                <span>{{ date('d M Y', strtotime($s['date'])) }}</span>
                                @if(strlen($s['date']) > 10 && date('H:i', strtotime($s['date'])) !== '00:00')
                                    <small class="text-muted d-block">{{ date('H:i', strtotime($s['date'])) }} WIB</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array($s['raw_status'], ['ACCEPT', 'HIRED', 'CLOSED']) || $s['status'] === 'Completed')
                                <span class="badge bg-success text-white px-2 py-1"><i class="fa fa-check me-1"></i>{{ $s['status'] }}</span>
                            @elseif(in_array($s['raw_status'], ['REJECT', 'CANCELED', 'DECLINED']))
                                <span class="badge bg-danger text-white px-2 py-1"><i class="fa fa-times me-1"></i>{{ $s['status'] }}</span>
                            @elseif($s['raw_status'] === 'SEEK')
                                <span class="badge bg-info text-white px-2 py-1"><i class="fa fa-spinner fa-spin me-1"></i>{{ $s['status'] }}</span>
                            @elseif($s['raw_status'] === 'PENDING')
                                <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-clock me-1"></i>{{ $s['status'] }}</span>
                            @else
                                <span class="badge bg-light text-muted px-2 py-1">{{ $s['status'] }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $s['note'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Data tahapan rekrutmen belum tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fa fa-calendar-check me-2"></i>Ketersediaan Bergabung (Non-Sensitif)</h6>
            </div>
            <div class="card-body">
                <form id="availabilityForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kesiapan Mulai Bekerja / Notice Period</label>
                        <select id="notice_period" class="form-select" required>
                            <option value="">-- Pilih Kesiapan --</option>
                            <option value="Siap Segera (Immediate)">Siap Segera (Immediate)</option>
                            <option value="2 Minggu (2 Weeks Notice)">2 Minggu (2 Weeks Notice)</option>
                            <option value="1 Bulan (1 Month Notice)">1 Bulan (1 Month Notice)</option>
                            <option value="Lebih dari 1 Bulan (> 1 Month)">Lebih dari 1 Bulan (&gt; 1 Month)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan untuk Tim HCGA</label>
                        <textarea id="availability_note" class="form-control" rows="3" placeholder="Contoh: Saat ini masih bekerja hingga akhir bulan..."></textarea>
                    </div>
                    <button type="submit" id="btnSaveAvailability" class="btn btn-primary btn-sm fw-bold">
                        <i class="fa fa-save me-1"></i> Simpan Ketersediaan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fa fa-history me-2"></i>Riwayat Konfirmasi &amp; Aktivitas Anda</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Tahap / Jenis</th>
                                <th>Status</th>
                                <th>Catatan / Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($confirmations as $conf)
                            <tr>
                                <td class="small text-muted">{{ $conf->created_at ? date('d M Y H:i', strtotime($conf->created_at)) : '-' }}</td>
                                <td class="small fw-semibold text-uppercase">{{ str_replace('_', ' ', $conf->stage_type) }}</td>
                                <td>
                                    <span class="badge bg-secondary text-white">{{ $conf->confirmation_status }}</span>
                                </td>
                                <td class="small">
                                    @if($conf->notice_period)
                                        <div><strong>Notice Period:</strong> {{ $conf->notice_period }}</div>
                                    @endif
                                    @if($conf->preferred_date)
                                        <div><strong>Usulan Tanggal:</strong> {{ date('d M Y H:i', strtotime($conf->preferred_date)) }}</div>
                                    @endif
                                    @if($conf->candidate_note)
                                        <div class="text-muted">{{ $conf->candidate_note }}</div>
                                    @endif
                                    @if($conf->signed_offering_file)
                                        <a href="{{ asset($conf->signed_offering_file) }}" target="_blank" class="btn btn-xs btn-outline-success mt-1">Lihat File</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">Belum ada riwayat konfirmasi yang dikirim.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Jadwal / Reschedule -->
<div class="modal fade" id="modalScheduleConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalScheduleTitle">Konfirmasi Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scheduleConfirmForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="conf_stage_type">
                    <input type="hidden" id="conf_status">

                    <div id="rescheduleDateWrap" class="mb-3 d-none">
                        <label class="form-label fw-semibold">Usulan Tanggal &amp; Jam Pengganti</label>
                        <input type="datetime-local" id="conf_preferred_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan / Alasan</label>
                        <textarea id="conf_note" class="form-control" rows="3" placeholder="Tuliskan konfirmasi kehadiran atau alasan pengajuan reschedule..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitScheduleConfirm" class="btn btn-primary">Kirim Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function showProgressAlert(type, msg) {
        $('#progressAlert').removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(msg);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $('.btn-confirm-schedule').on('click', function() {
        var stage = $(this).data('stage');
        var title = $(this).data('title');
        var status = $(this).data('status');

        $('#conf_stage_type').val(stage);
        $('#conf_status').val(status);
        $('#conf_note').val('');
        $('#conf_preferred_date').val('');

        if (status === 'RESCHEDULE_REQUEST') {
            $('#modalScheduleTitle').text('Ajukan Reschedule: ' + title);
            $('#rescheduleDateWrap').removeClass('d-none');
        } else {
            $('#modalScheduleTitle').text('Konfirmasi Hadir: ' + title);
            $('#rescheduleDateWrap').addClass('d-none');
        }

        var modalEl = document.getElementById('modalScheduleConfirm');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    $('#scheduleConfirmForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btnSubmitScheduleConfirm');
        btn.prop('disabled', true);

        $.post('{{ url("/candidate/progress-confirm") }}', {
            _token: '{{ csrf_token() }}',
            stage_type: $('#conf_stage_type').val(),
            confirmation_status: $('#conf_status').val(),
            preferred_date: $('#conf_preferred_date').val(),
            candidate_note: $('#conf_note').val()
        }, function(res) {
            showProgressAlert('success', res.message);
            setTimeout(function() { location.reload(); }, 800);
        }, 'json').fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengirim konfirmasi.';
            showProgressAlert('danger', msg);
            btn.prop('disabled', false);
        });
    });

    $('#availabilityForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btnSaveAvailability');
        btn.prop('disabled', true);

        $.post('{{ url("/candidate/availability-update") }}', {
            _token: '{{ csrf_token() }}',
            notice_period: $('#notice_period').val(),
            candidate_note: $('#availability_note').val()
        }, function(res) {
            showProgressAlert('success', res.message);
            setTimeout(function() { location.reload(); }, 800);
        }, 'json').fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan ketersediaan.';
            showProgressAlert('danger', msg);
            btn.prop('disabled', false);
        });
    });

    $('#signedOfferingForm').on('submit', function(e) {
        e.preventDefault();
        var fileInput = document.getElementById('offeringSignedFile');
        if (!fileInput.files.length) return;

        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('file', fileInput.files[0]);
        fd.append('candidate_note', $('#offeringSignedNote').val());

        var btn = $('#btnUploadOffering');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Mengunggah...');

        $.ajax({
            url: '{{ url("/candidate/offering-upload") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                showProgressAlert('success', res.message);
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                var msg = 'Gagal mengunggah Offering Letter.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                showProgressAlert('danger', msg);
                btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Unggah');
            }
        });
    });
});
</script>
@endsection
