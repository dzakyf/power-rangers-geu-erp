<style>
    /* Scope isolation untuk checkbox & elemen dhtmlXGrid agar tidak terpengaruh CSS global Adminor/Bootstrap */
    #WorkCalendarShift img,
    #WorkCalendarRosterPattern img,
    .gridbox_dhx_skyblue img {
        width: auto !important;
        height: auto !important;
        max-width: none !important;
        border-radius: 0 !important;
        vertical-align: middle !important;
        display: inline !important;
        box-shadow: none !important;
    }

    #WorkCalendarShift table.obj td,
    #WorkCalendarRosterPattern table.obj td {
        vertical-align: middle !important;
    }
</style>

<div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
    <!-- Header Card -->
    <div
        class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div
                class="d-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis rounded-3 p-3 shadow-sm border border-warning-subtle">
                <i class="fa fa-business-time fs-20"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="mb-0 fw-bold text-dark">
                        {{ $workCalendar->Name ?? 'Kalender Shifting' }}
                    </h5>
                    <span class="badge bg-warning text-dark border border-warning fw-semibold px-2 py-1 fs-11">
                        <i class="fa fa-layer-group me-1"></i> Mode Shifting / Roster
                    </span>
                    <span class="badge bg-light text-secondary border fw-medium px-2 py-1 fs-11">
                        <i class="fa fa-clock me-1"></i> {{ $workCalendar->Weekly_Hours ?? '40' }} Jam/Minggu
                    </span>
                </div>
                <p class="text-muted small mb-0">
                    Konfigurasi slot shift kerja dan perancangan template siklus perputaran kerja (roster) untuk
                    kalender ini.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('hrd-roster-schedule-matrix?calendar_id=' . ($workCalendar->ID ?? '')) }}"
                class="btn btn-sm btn-primary shadow-sm" target="_blank"
                title="Buka Matriks Jadwal Roster Karyawan di tab baru">
                <i class="fa fa-table-cells me-1"></i> Buka Matriks Roster
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm"
                onclick="$('#WorkCalendarDiv').html('');" title="Tutup panel ini">
                <i class="fa fa-xmark me-1"></i> Tutup
            </button>
        </div>
    </div>

    <!-- Quick Stat Pill Banner -->
    <div
        class="bg-light px-3 py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 fs-12">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">
                <i class="fa fa-info-circle text-primary me-1"></i>
                <strong>Panduan Alur:</strong> Buat/atur slot shift di <em>Tab 1</em>, lalu susun urutan rotasinya di
                <em>Tab 2</em>.
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="shift-slot-badge" class="badge bg-white text-dark border shadow-xs px-2 py-1">
                <i class="fa fa-circle-notch fa-spin text-primary me-1"></i> Memuat slot...
            </span>
            <span id="pattern-slot-badge" class="badge bg-white text-dark border shadow-xs px-2 py-1">
                <i class="fa fa-rotate text-success me-1"></i> {{ count($rosterPatterns ?? []) }} Pola Terdaftar
            </span>
        </div>
    </div>

    <!-- Nav Tabs Section -->
    <div class="card-body p-0">
        <ul class="nav nav-tabs nav-tabs-bordered px-3 pt-3 bg-white border-bottom" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold text-dark fs-13 py-2 px-3 border-bottom-0" id="tab-shifts"
                    data-bs-toggle="tab" data-bs-target="#pane-shifts" type="button" role="tab">
                    <i class="fa fa-clock text-primary me-2"></i>1. Slot Shift Kerja
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold text-dark fs-13 py-2 px-3 border-bottom-0" id="tab-patterns"
                    data-bs-toggle="tab" data-bs-target="#pane-patterns" type="button" role="tab"
                    onclick="reloadRosterPatternsGrid();">
                    <i class="fa fa-arrows-spin text-success me-2"></i>2. Pola Siklus Roster
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: DAFTAR SLOT SHIFT KERJA -->
            <div class="tab-pane fade show active p-3" id="pane-shifts" role="tabpanel">
                <div class="card border mb-0">
                    <div
                        class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="fs-12 text-muted">
                            <i class="fa fa-pen-to-square text-secondary me-1"></i>
                            Klik ganda pada sel untuk edit inline. Centang <strong>Cross-Day</strong> jika shift
                            berakhir esok hari, dan centang <strong>Is Off</strong> jika hari libur.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="reloadgroupshift();" title="Segarkan data slot shift">
                                <i class="fa fa-rotate me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#modalAddShiftNew">
                                <i class="fa fa-plus me-1"></i> Tambah Shift Baru
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="btnSaveCalendarShift"
                                onclick="simpanCalendarShift();">
                                <i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan Grid
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="WorkCalendarShift"
                            style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    </div>
                    <div
                        class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between fs-12 text-muted">
                        <div>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle me-2">
                                <i class="fa fa-lightbulb me-1"></i> Tips
                            </span>
                            Slot shift yang aktif di sini dapat dipilih saat menyusun pola siklus di tab sebelah.
                        </div>
                        <div id="shift-status-info" class="fw-semibold text-dark">
                            Status: Siap
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: POLA SIKLUS ROSTER -->
            <div class="tab-pane fade p-3" id="pane-patterns" role="tabpanel">
                <div class="card border mb-0">
                    <div
                        class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="fs-12 text-muted">
                            <i class="fa fa-diagram-project text-secondary me-1"></i>
                            Daftar pola siklus roster rotasi untuk kalender ini (misal: 4 hari kerja - 2 hari libur
                            berulang).
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="reloadRosterPatternsGrid();" title="Segarkan tabel pola">
                                <i class="fa fa-rotate me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#modalAddPatternNew">
                                <i class="fa fa-plus me-1"></i> Buat Pola Roster Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="WorkCalendarRosterPattern"
                            style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
                    </div>
                    <div
                        class="card-footer bg-light py-2 px-3 border-top fs-12 text-muted d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fa fa-circle-check text-success me-1"></i>
                            Pola yang dibuat di sini dapat langsung diterapkan ke karyawan pada menu <strong>Matriks
                                Roster</strong>.
                        </div>
                        <div id="pattern-status-info" class="fw-semibold text-dark">
                            Pola Siklus Roster
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Shift Baru -->
<div class="modal fade" id="modalAddShiftNew" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-2 px-3">
                <h6 class="modal-title fw-bold fs-14"><i class="fa fa-plus-circle me-2"></i>Tambah Shift Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <form id="formAddShiftModal">
                    <div class="row g-2 mb-2">
                        <div class="col-md-5 col-12">
                            <label class="form-label fs-12 fw-semibold mb-1">Kode Shift <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="new_shift_code"
                                class="form-control form-control-sm text-uppercase" placeholder="Contoh: DS, NS, S1"
                                required>
                        </div>
                        <div class="col-md-7 col-12">
                            <label class="form-label fs-12 fw-semibold mb-1">Nama Shift <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="new_shift_name" class="form-control form-control-sm"
                                placeholder="Contoh: Shift Pagi (08:00 - 17:00)" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Jam Masuk (24H) <span
                                    class="text-danger">*</span></label>
                            <input type="time" id="new_start_time" class="form-control form-control-sm"
                                value="08:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-12 fw-semibold mb-1">Jam Pulang (24H) <span
                                    class="text-danger">*</span></label>
                            <input type="time" id="new_end_time" class="form-control form-control-sm"
                                value="17:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-12 fw-semibold mb-1">Istirahat (Menit)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="new_break_minutes" class="form-control" value="60"
                                min="0">
                            <span class="input-group-text">Menit</span>
                        </div>
                    </div>
                    <div class="card bg-light border-0 p-2 mb-0">
                        <div class="d-flex align-items-center justify-content-around">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="new_is_cross_day"
                                    value="1">
                                <label class="form-check-label fs-12 fw-medium" for="new_is_cross_day">
                                    <i class="fa fa-moon text-dark me-1"></i> Lintas Hari (Cross-Day)
                                </label>
                            </div>
                            <div class="vr"></div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="new_is_off" value="1">
                                <label class="form-check-label fs-12 fw-medium text-danger" for="new_is_off">
                                    <i class="fa fa-mug-hot me-1"></i> Status Libur (OFF)
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success px-3" onclick="submitNewShiftModal()">
                    <i class="fa fa-floppy-disk me-1"></i> Simpan Shift
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pola Roster Baru -->
<div class="modal fade" id="modalAddPatternNew" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title fw-bold fs-14"><i class="fa fa-arrows-spin me-2"></i>Rancang Pola Siklus Roster
                    Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <form id="formAddPatternModal">
                    <input type="hidden" id="pattern_calendar_id" value="{{ $workCalendar->ID ?? '' }}">
                    <div class="row g-2 mb-2">
                        <div class="col-md-8 col-12">
                            <label class="form-label fs-12 fw-semibold mb-1">Nama Pola Roster <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="new_pattern_name" class="form-control form-control-sm"
                                placeholder="Contoh: Pola 4-2 (4 Hari Masuk - 2 Hari Off)" required>
                        </div>
                        <div class="col-md-4 col-12">
                            <label class="form-label fs-12 fw-semibold mb-1">Panjang Siklus (Hari) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="new_cycle_days" class="form-control" value="6"
                                    min="1" max="60" onchange="generateSequenceInputs()"
                                    onkeyup="generateSequenceInputs()">
                                <span class="input-group-text">Hari</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-12 fw-semibold mb-1">Keterangan / Deskripsi Operasional</label>
                        <input type="text" id="new_pattern_desc" class="form-control form-control-sm"
                            placeholder="Contoh: Alur rotasi divisi lapangan plant site 1">
                    </div>

                    <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 mb-0">
                        <div
                            class="card-header bg-transparent py-2 border-bottom border-primary-subtle d-flex align-items-center justify-content-between">
                            <span class="fs-12 fw-bold text-dark">
                                <i class="fa fa-list-ol text-primary me-1"></i> Rantai Penugasan Slot Shift Per Hari
                                Siklus
                            </span>
                            <span class="fs-11 text-muted">
                                Pilih slot kerja untuk tiap urutan hari:
                            </span>
                        </div>
                        <div class="card-body p-2">
                            <div id="sequenceContainer" class="row g-2">
                                <!-- Input dinamis hari 1 s/d N akan di-generate via JavaScript -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary px-3" onclick="submitNewPatternModal()">
                    <i class="fa fa-floppy-disk me-1"></i> Simpan Pola Roster
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var WorkCalendarShift, PWorkCalendarShift;
    var WorkCalendarRosterPattern;

    // Inisialisasi Grid 1: Master Shifts
    WorkCalendarShift = new dhtmlXGridObject('WorkCalendarShift');
    WorkCalendarShift.setImagePath("./dhtmlx/imgs/");
    WorkCalendarShift.setHeader("No, Kode Shift, Nama Shift, Start (24H), End (24H), Break (Min), Cross-Day?, Is Off?");
    WorkCalendarShift.setInitWidths("60,120,*,110,110,100,90,90");
    WorkCalendarShift.setColAlign("center,left,left,center,center,center,center,center");
    WorkCalendarShift.setColTypes("ro,ed,ed,ed,ed,ed,ch,ch");
    WorkCalendarShift.setColSorting("int,str,str,str,str,int,str,str");
    WorkCalendarShift.setEditable(true);
    WorkCalendarShift.setSkin("dhx_skyblue");

    PWorkCalendarShift = new dataProcessor("master-work-calendar-shift-update");
    PWorkCalendarShift.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PWorkCalendarShift.setUpdateMode("Off");
    PWorkCalendarShift.init(WorkCalendarShift);
    WorkCalendarShift.init();

    PWorkCalendarShift.attachEvent("onFullSync", function() {
        let btn = $('#btnSaveCalendarShift');
        btn.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan Grid');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: 'Perubahan master shift berhasil disimpan.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            alert('Perubahan master shift berhasil disimpan.');
        }
        reloadgroupshift();
    });

    function simpanCalendarShift() {
        let btn = $('#btnSaveCalendarShift');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        let count = WorkCalendarShift.getRowsNum();
        for (let i = 0; i < count; i++) {
            let rowId = WorkCalendarShift.getRowId(i);
            PWorkCalendarShift.setUpdated(rowId, true);
        }
        PWorkCalendarShift.sendData();
    }

    function reloadgroupshift() {
        $('#shift-status-info').html(
            '<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Memuat...</span>');
        $('#shift-slot-badge').html('<i class="fa fa-circle-notch fa-spin text-primary me-1"></i> Memuat slot...');
        WorkCalendarShift.clearAndLoad("xml-hrd-master-work-calendar-shift?task={{ $_GET['task'] ?? '' }}", function() {
            var count = WorkCalendarShift.getRowsNum();
            $('#shift-status-info').html('<span class="text-success"><i class="fa fa-check-circle me-1"></i> ' +
                count + ' slot shift aktif</span>');
            $('#shift-slot-badge').html('<i class="fa fa-clock text-primary me-1"></i> ' + count +
                ' Slot Shift');
            fetchMasterShiftsOptions();
            generateSequenceInputs();
        });
    }

    // Inisialisasi Grid 2: Roster Patterns
    WorkCalendarRosterPattern = new dhtmlXGridObject('WorkCalendarRosterPattern');
    WorkCalendarRosterPattern.setImagePath("./dhtmlx/imgs/");
    WorkCalendarRosterPattern.setHeader("No, Nama Pola Roster, Siklus (Hari), Rantai Alur Shift, Keterangan, Status");
    WorkCalendarRosterPattern.setInitWidths("60,220,110,*,180,100");
    WorkCalendarRosterPattern.setColAlign("center,left,center,left,left,center");
    WorkCalendarRosterPattern.setColTypes("ro,ro,ro,ro,ro,ro");
    WorkCalendarRosterPattern.setColSorting("int,str,int,str,str,str");
    WorkCalendarRosterPattern.setSkin("dhx_skyblue");
    WorkCalendarRosterPattern.init();

    function reloadRosterPatternsGrid() {
        $('#pattern-status-info').html(
            '<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Memuat...</span>');
        WorkCalendarRosterPattern.clearAndLoad(
            "xml-hrd-master-roster-patterns?calendar_id={{ $workCalendar->ID ?? '' }}",
            function() {
                var count = WorkCalendarRosterPattern.getRowsNum();
                $('#pattern-status-info').html(
                    '<span class="text-success"><i class="fa fa-check-circle me-1"></i> ' + count +
                    ' pola roster</span>');
                $('#pattern-slot-badge').html('<i class="fa fa-rotate text-success me-1"></i> ' + count +
                    ' Pola Terdaftar');
            });
    }

    // Generator Input Urutan Hari Siklus Roster
    var masterShiftOptions = [];

    function fetchMasterShiftsOptions() {
        // Ambil dari rows yang sedang ada di Grid
        masterShiftOptions = [];
        let count = WorkCalendarShift.getRowsNum();
        for (let i = 0; i < count; i++) {
            let rId = WorkCalendarShift.getRowId(i);
            let code = WorkCalendarShift.cells(rId, 1).getValue();
            let name = WorkCalendarShift.cells(rId, 2).getValue();
            masterShiftOptions.push({
                id: rId,
                code: code,
                name: name
            });
        }
    }

    function generateSequenceInputs() {
        fetchMasterShiftsOptions();
        let days = parseInt($('#new_cycle_days').val()) || 1;
        let html = '';

        for (let i = 1; i <= days; i++) {
            html += '<div class="col-md-3 col-6 mb-2">' +
                '<label class="form-label fs-11 text-muted mb-0">Hari ke-' + i + '</label>' +
                '<select class="form-select form-select-sm pattern-seq-select" data-seq="' + i + '">';
            masterShiftOptions.forEach(function(opt) {
                html += '<option value="' + opt.id + '">' + opt.code + ' (' + opt.name + ')</option>';
            });
            html += '</select></div>';
        }
        $('#sequenceContainer').html(html);
    }

    function submitNewShiftModal() {
        var code = $('#new_shift_code').val();
        var name = $('#new_shift_name').val();
        var start = $('#new_start_time').val();
        var end = $('#new_end_time').val();
        var brk = $('#new_break_minutes').val();
        var isCross = $('#new_is_cross_day').is(':checked') ? 1 : 0;
        var isOff = $('#new_is_off').is(':checked') ? 1 : 0;

        if (!code || !name) {
            alert('Kode dan Nama Shift wajib diisi.');
            return;
        }

        $.ajax({
            url: "{{ url('hrd-master-shifts-store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                shift_code: code,
                shift_name: name,
                start_time: start,
                end_time: end,
                break_minutes: brk,
                is_cross_day: isCross,
                is_off: isOff
            },
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('modalAddShiftNew')).hide();
                reloadgroupshift();
                alert(res.message);
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan shift.';
                alert('Error: ' + msg);
            }
        });
    }

    function submitNewPatternModal() {
        var name = $('#new_pattern_name').val();
        var calId = $('#pattern_calendar_id').val();
        var days = $('#new_cycle_days').val();
        var desc = $('#new_pattern_desc').val();

        var shiftIds = [];
        $('.pattern-seq-select').each(function() {
            shiftIds.push($(this).val());
        });

        if (!name || shiftIds.length == 0) {
            alert('Nama Pola dan Urutan Shift wajib ditentukan.');
            return;
        }

        $.ajax({
            url: "{{ url('hrd-master-roster-patterns-store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                calendar_id: calId,
                pattern_name: name,
                cycle_days: days,
                description: desc,
                shift_ids: shiftIds
            },
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('modalAddPatternNew')).hide();
                reloadRosterPatternsGrid();
                alert(res.message);
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan pola roster.';
                alert('Error: ' + msg);
            }
        });
    }

    // Load awal
    reloadgroupshift();
    setTimeout(function() {
        generateSequenceInputs();
    }, 500);
</script>
