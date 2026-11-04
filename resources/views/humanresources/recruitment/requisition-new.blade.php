<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = $location ?? Auth::user()->Location;
$Saya = $username ?? Auth::user()->username;

// Fallback jika dipanggil langsung tanpa controller
if (!isset($positions)) {
    $positions = DB::table('master_position')->orderBy('Position', 'asc')->get();
    $workCalendar = DB::table('work_calendars')->get();
    $reqCount =
        DB::table('requisition')
            ->where('Created_Date', 'LIKE', date('Y') . '%')
            ->count() + 1;
    $taskId = 'REQ/' . $LSP . '/' . date('Ymdhis') . '-' . sprintf('%02d', $reqCount);
    $locations = DB::table('location')->get();

    $policies = DB::table('approval_policy')->where('Request_Type', 'REQUISITION')->orderByRaw("CASE WHEN Status = 'ACTIVE' THEN 0 ELSE 1 END")->orderBy('ID', 'asc')->get();

    foreach ($policies as $pol) {
        $pol->steps = DB::table('approval_policy_step')->where('Policy_ID', $pol->ID)->orderBy('Stage_Order', 'asc')->orderBy('Level_Number', 'asc')->orderBy('ID', 'asc')->get();
    }
}

$Taskid = $taskId;
$locationList = $locations ?? [];

?>

<style>
    .req-new-stepper {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .req-new-step-pill {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
    }

    .req-new-step-pill .pill-num {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-weight: 700;
        font-size: 0.68rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.45rem;
    }
</style>

<div class="card shadow-sm border mt-3 mb-4" id="RequisitionNewFormCard">
    {{-- Card Header (Minimalis & Bersih ala Recruitment Interview) --}}
    <div
        class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                <i class="fa fa-file-signature fs-18"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    Formulir Permintaan Tenaga Kerja (PTK) Baru
                </h5>
                <small class="text-muted">
                    No. Dokumen: <strong class="text-primary">{{ $Taskid }}</strong> &bull; Pemohon: <strong
                        class="text-dark">{{ $Saya }}</strong> ({{ $LSP }})
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info-transparent text-info border border-info border-opacity-25 px-3 py-2 fs-12">
                <i class="fa fa-pen-to-square me-1"></i> DRAFT PENGAJUAN
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divlistRequisition').html('');"
                title="Tutup Formulir">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Dynamic Approval Step Preview Bar (Otomatis berubah saat memilih Posisi Jabatan) --}}
    <div class="bg-light p-3 border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="d-flex align-items-center">
                <i class="fa fa-diagram-project text-primary me-2"></i>
                <span class="fw-semibold text-dark small">Estimasi Alur Persetujuan (Approval Policy Preview):</span>
                <span id="previewPolicyBadge" class="badge bg-white text-dark border ms-2 small fw-normal">
                    Pilih Posisi Jabatan terlebih dahulu
                </span>
            </div>
            <div id="previewPositionMeta" class="small text-muted d-none">
                Departemen: <strong id="previewDeptText" class="text-dark">-</strong> &bull; Level: <strong
                    id="previewLevelText" class="text-dark">-</strong>
            </div>
        </div>

        <div id="previewStepperContainer" class="req-new-stepper">
            <span class="text-muted small">
                <i class="fa fa-circle-info text-info me-1"></i> Pilih <strong>Posisi Jabatan</strong> di bawah untuk
                melihat tahapan approver yang akan dilalui dokumen PTK ini.
            </span>
        </div>
    </div>

    {{-- Form Body --}}
    <form id="NewRec" onsubmit="return false;">
        <div class="card-body p-4" id="ContentPages">
            <div class="row g-3">
                {{-- SECTION 1: Posisi & Kebutuhan Formasi --}}
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-13">
                                <i class="fa fa-id-card text-primary me-1"></i> 1. Formasi &amp; Posisi Jabatan
                            </h6>
                            <span class="badge bg-light text-muted border fw-normal">Wajib Diisi</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Nomor Dokumen PTK <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm bg-light fw-bold text-primary"
                                    id="Task_ID" value="{{ $Taskid }}" readonly required />
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Site Penempatan <span class="text-danger">*</span>
                                </label>
                                <select name="Placement" id="Placement" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Lokasi Site --</option>
                                    @foreach ($locationList as $item)
                                        <option value="{{ $item->Location_ID }}"
                                            {{ $item->Location_ID == $LSP ? 'selected' : '' }}>
                                            {{ $item->Name }} ({{ $item->Location_ID }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Posisi Jabatan yang Diminta <span class="text-danger">*</span>
                                </label>
                                <select id="Job_Position" class="form-select form-select-sm" required>
                                    <option value="" data-dept="" data-level="">-- Pilih Posisi Pekerjaan --
                                    </option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->ID }}"
                                            data-dept="{{ $position->Department ?? '' }}"
                                            data-level="{{ $position->Level ?? 4 }}"
                                            data-initial="{{ $position->Initial ?? '' }}">
                                            {{ $position->Position }}
                                            {{ !empty($position->Department) ? '— [' . $position->Department . ']' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Prioritas <span class="text-danger">*</span>
                                </label>
                                <select id="Priority" class="form-select form-select-sm" required>
                                    <option value="NORMAL" selected>NORMAL</option>
                                    <option value="HIGH">HIGH</option>
                                    <option value="URGENT">URGENT</option>
                                </select>
                            </div>

                            <div class="col-sm-5">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Tipe Alokasi <span class="text-danger">*</span>
                                </label>
                                <select id="Allocation" class="form-select form-select-sm" required>
                                    <option value="NEW" selected>Karyawan Baru</option>
                                    <option value="REPLACEMENT">Replacement (Pengganti)</option>
                                </select>
                            </div>

                            <div class="col-sm-3">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Jumlah (Qty) <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="1" id="Qty"
                                    class="form-control form-control-sm text-center fw-bold" value="1" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Jadwal Kerja & Alasan Permintaan --}}
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-white">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-13">
                                <i class="fa fa-calendar-check text-primary me-1"></i> 2. Jadwal Kerja &amp; Alasan
                                Kebutuhan
                            </h6>
                            <span class="badge bg-light text-muted border fw-normal">Parameter Kerja</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Jadwal Waktu Kerja <span class="text-danger">*</span>
                                </label>
                                <select name="Work_Schedule" id="Work_Schedule" class="form-select form-select-sm"
                                    required>
                                    <option value="">-- Pilih Work Calendar --</option>
                                    @foreach ($workCalendar as $item)
                                        <option value="{{ $item->ID }}">{{ $item->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Tanggal Dibuka Lowongan <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i
                                            class="fa fa-calendar text-muted"></i></span>
                                    <input type="text" id="Start_Date"
                                        class="form-control form-control-sm bg-white" value="<?php echo date('d M Y'); ?>"
                                        readonly required />
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Alasan Permintaan <span class="text-danger">*</span>
                                </label>
                                <select name="Request_Type" id="Request_Type" class="form-select form-select-sm"
                                    required>
                                    <option value="1">Peningkatan Volume Kerja</option>
                                    <option value="2">Pengganti Tenaga Kerja Keluar</option>
                                    <option value="OTHER">Alasan Lainnya</option>
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Rincian Alasan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="Request_Desc" id="Request_Desc"
                                    class="form-control form-control-sm bg-light" value="Ada peningkatan volume kerja"
                                    readonly />
                            </div>

                            <div class="col-12">
                                <div class="p-2 px-3 bg-light rounded border small text-muted mt-1">
                                    <i class="fa fa-clock text-warning me-1"></i> Masa berlaku pengajuan PTK otomatis
                                    dihitung <strong>1 bulan</strong> setelah dari field tanggal target mulai bekerja.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Uraian Tugas & Persyaratan Khusus --}}
                <div class="col-12">
                    <div class="border rounded-3 p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-13">
                                <i class="fa fa-list-check text-primary me-1"></i> 3. Deskripsi Pekerjaan &amp;
                                Kualifikasi Kandidat
                            </h6>
                            <span class="text-muted small">Gunakan poin-poin agar mudah dibaca saat proses
                                rekrutmen</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Uraian Tugas &amp; Tanggung Jawab Pokok (Job Description) <span
                                        class="text-danger">*</span>
                                </label>
                                <textarea rows="4" id="Description" class="form-control form-control-sm"
                                    placeholder="Tuliskan rincian tugas dan tanggung jawab utama posisi ini..." required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark mb-1">
                                    Kualifikasi &amp; Persyaratan Khusus (Requirement) <span
                                        class="text-danger">*</span>
                                </label>
                                <textarea rows="4" id="Requirement" class="form-control form-control-sm"
                                    placeholder="Contoh: Pendidikan minimal, pengalaman kerja, keahlian teknis, sertifikasi..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Footer --}}
        <div
            class="card-footer bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
            <small class="text-muted">
                <i class="fa fa-shield-halved text-primary me-1"></i> Dokumen akan langsung diteruskan ke antrean
                <strong>Approval Inbox</strong> sesuai policy yang berlaku.
            </small>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm px-3" type="button"
                    onclick="$('#divlistRequisition').html('');">
                    <i class="fa fa-xmark me-1"></i> Batal
                </button>
                <button class="btn btn-outline-warning btn-sm px-3" type="reset" onclick="resetPtkFormPreview()">
                    <i class="fa fa-rotate-left me-1"></i> Reset
                </button>
                <button class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" type="button" id="SubmitBtn">
                    <i class="fa fa-paper-plane me-1"></i> Submit Pengajuan PTK
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Data Policy dari Backend untuk Preview Stepper Dinamis
    var requisitionPoliciesData = @json($policies ?? []);

    if (typeof dhtmlXCalendarObject !== 'undefined') {
        var myCalendar = new dhtmlXCalendarObject(["Start_Date"]);
        myCalendar.setDateFormat("%d %M %Y");
    }

    function notifyUser(title, msg, type) {
        if (typeof showAppNotification === 'function') {
            showAppNotification(title, msg, type);
        } else {
            alert(msg);
        }
    }

    function resetPtkFormPreview() {
        setTimeout(function() {
            $('#Request_Desc').val('Ada peningkatan volume kerja').prop('readonly', true).addClass('bg-light');
            updateDynamicPolicyPreview();
        }, 50);
    }

    /**
     * Render preview tahapan approval saat Posisi Jabatan dipilih
     */
    function updateDynamicPolicyPreview() {
        var $opt = $('#Job_Position').find('option:selected');
        var posId = $('#Job_Position').val();
        var dept = ($opt.data('dept') || '').toString().trim();
        var level = parseInt($opt.data('level') || '4', 10);

        var $badge = $('#previewPolicyBadge');
        var $meta = $('#previewPositionMeta');
        var $container = $('#previewStepperContainer');

        if (!posId) {
            $badge.className = 'badge bg-white text-dark border ms-2 small fw-normal';
            $badge.text('Pilih Posisi Jabatan terlebih dahulu');
            $meta.addClass('d-none');
            $container.html(
                '<span class="text-muted small"><i class="fa fa-circle-info text-info me-1"></i> Pilih <strong>Posisi Jabatan</strong> di bawah untuk melihat tahapan approver yang akan dilalui dokumen PTK ini.</span>'
            );
            return;
        }

        $('#previewDeptText').text(dept || 'Semua Dept');
        $('#previewLevelText').text('Level ' + level + (level <= 3 ? ' (Managerial)' : ' (Staff / Non-Staff)'));
        $meta.removeClass('d-none');

        // Cari policy yang cocok berdasarkan Department dan Position_Level
        var matched = null;
        for (var i = 0; i < requisitionPoliciesData.length; i++) {
            var pol = requisitionPoliciesData[i];
            if (pol.Department && pol.Department.toUpperCase() !== dept.toUpperCase()) {
                continue;
            }

            var conds = [];
            try {
                conds = typeof pol.Conditions === 'string' ? JSON.parse(pol.Conditions) : (pol.Conditions || []);
            } catch (e) {}

            var condMatch = true;
            if (Array.isArray(conds)) {
                for (var c = 0; c < conds.length; c++) {
                    var item = conds[c];
                    if (item.field === 'Position_Level') {
                        var arr = Array.isArray(item.value) ? item.value.map(Number) : [Number(item.value)];
                        if (item.operator === 'in' && arr.indexOf(level) === -1) condMatch = false;
                        if (item.operator === 'not_in' && arr.indexOf(level) !== -1) condMatch = false;
                    }
                }
            }

            if (condMatch) {
                matched = pol;
                break;
            }
        }

        if (!matched && requisitionPoliciesData.length > 0) {
            matched = requisitionPoliciesData[0];
        }

        if (!matched) {
            $badge.text('Policy Belum Tersedia');
            $container.html('<span class="text-muted small">Tidak ada policy REQUISITION yang ditemukan.</span>');
            return;
        }

        $badge.text(matched.Policy_Name + ' [' + matched.Status + ']');

        var html =
            '<div class="req-new-step-pill"><span class="pill-num bg-secondary"><i class="fa fa-pen" style="font-size:0.55rem;"></i></span><span><strong>Pemohon</strong> ({{ $Saya }})</span></div>';

        var steps = matched.steps || [];
        for (var s = 0; s < steps.length; s++) {
            var st = steps[s];
            var lbl = st.Label || st.Position_Code || ('Level ' + st.Level_Number);
            html += '<span class="text-muted small"><i class="fa fa-chevron-right"></i></span>';
            html += '<div class="req-new-step-pill"><span class="pill-num">' + st.Stage_Order +
                '</span><span><strong>' + lbl + '</strong> <small class="text-muted">(Lv.' + st.Level_Number +
                ')</small></span></div>';
        }

        html += '<span class="text-muted small"><i class="fa fa-chevron-right"></i></span>';
        html +=
            '<div class="req-new-step-pill border-success bg-success-transparent"><span class="pill-num bg-success"><i class="fa fa-check" style="font-size:0.55rem;"></i></span><span class="text-success fw-bold">APPROVED</span></div>';

        $container.html(html);
    }

    $('#Job_Position').on('change', function() {
        updateDynamicPolicyPreview();
    });

    $('#Request_Type').on('change', function() {
        var val = $(this).val();
        var $desc = $('#Request_Desc');
        if (val === 'OTHER') {
            $desc.val('').prop('readonly', false).removeClass('bg-light').attr('placeholder',
                'Tuliskan alasan permintaan...').focus();
        } else if (val === '1') {
            $desc.val('Ada peningkatan volume kerja').prop('readonly', true).addClass('bg-light');
        } else {
            $desc.val('Pengganti tenaga kerja keluar').prop('readonly', true).addClass('bg-light');
        }
    });

    $('#SubmitBtn').on('click', function(e) {
        e.preventDefault();

        var fields = [{
                id: 'Task_ID',
                name: 'Nomor PTK'
            },
            {
                id: 'Job_Position',
                name: 'Posisi Jabatan'
            },
            {
                id: 'Placement',
                name: 'Site Penempatan'
            },
            {
                id: 'Work_Schedule',
                name: 'Jadwal Waktu Kerja'
            },
            {
                id: 'Qty',
                name: 'Jumlah Kebutuhan'
            },
            {
                id: 'Start_Date',
                name: 'Target Mulai Bekerja'
            },
            {
                id: 'Description',
                name: 'Uraian Tugas Pokok'
            },
            {
                id: 'Requirement',
                name: 'Kualifikasi & Persyaratan'
            }
        ];

        $('.is-invalid').removeClass('is-invalid');
        for (var i = 0; i < fields.length; i++) {
            var $el = $('#' + fields[i].id);
            var val = $el.val();
            if (!val || $.trim(val) === '') {
                $el.addClass('is-invalid').focus();
                notifyUser('Data Belum Lengkap', 'Kolom "' + fields[i].name +
                    '" wajib diisi sebelum mengirim pengajuan PTK.', 'warning');
                return;
            }
        }

        var $btn = $(this);
        var oriHtml = $btn.html();
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        var formData = {
            Task_ID: $('#Task_ID').val(),
            Job_Position: $('#Job_Position').val(),
            Placement: $('#Placement').val(),
            Description: $('#Description').val(),
            Requirement: $('#Requirement').val(),
            Priority: $('#Priority').val(),
            Allocation: $('#Allocation').val(),
            Request_Type: $('#Request_Type').val(),
            Request_Desc: $('#Request_Desc').val(),
            Work_Schedule: $('#Work_Schedule').val(),
            Qty: $('#Qty').val(),
            Start_Date: $('#Start_Date').val()
        };

        $.ajax({
            type: 'POST',
            url: 'recruitment-requisition-new-submit',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(response) {
                $btn.prop('disabled', false).html(oriHtml);
                if (response === 'success') {
                    notifyUser('PTK Berhasil Diajukan',
                        'Dokumen PTK berhasil disimpan dan diteruskan ke tahapan Approval.',
                        'success');
                    $('#NewRec')[0].reset();
                    if (typeof refreshdata === 'function') {
                        refreshdata();
                    }
                    $('#divlistRequisition').html('');
                } else if (response === 'exist') {
                    notifyUser('Pengajuan Duplikat',
                        'Sudah ada dokumen PTK yang masih aktif/terbuka untuk posisi dan departemen yang sama.',
                        'warning');
                    $('#Job_Position').focus();
                } else {
                    notifyUser('Informasi', 'Respons server: ' + response, 'info');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(oriHtml);
                var payload = xhr.responseJSON || {};

                if (payload.message) {
                    notifyUser('Gagal Mengajukan PTK', payload.message, 'danger');
                    return;
                }

                if (xhr.status === 422 && payload.errors) {
                    $('.field-error').remove();
                    $('.is-invalid').removeClass('is-invalid');
                    $.each(payload.errors, function(field, messages) {
                        var el = $('#' + field);
                        if (el.length) {
                            el.addClass('is-invalid');
                            el.after('<div class="field-error text-danger small mt-1">' +
                                messages[0] + '</div>');
                        }
                    });
                    notifyUser('Validasi Gagal', 'Mohon periksa kembali kolom yang ditandai merah.',
                        'warning');
                    return;
                }

                notifyUser('Kesalahan Sistem', 'Terjadi kesalahan saat menyimpan PTK: ' + (xhr
                    .responseText || xhr.statusText), 'danger');
            }
        });
    });
</script>
