@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf
    <!-- Breadcrumb Header -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Master Work Calendar
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human
                            Capital</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Master
                            Data</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Work Calendar</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-calendar-days me-1"></i> Working Schedules &amp; Shifts
            </span>
        </div>
    </div>

    <!-- Card Container for Grid -->
    <div class="card shadow-sm border-0 mb-4">
        <div
            class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-calendar-check text-primary me-2"></i>Daftar Work Calendar
                </h6>
                <small class="text-muted">Kelola jadwal kalender kerja karyawan tipe Office maupun Shift.</small>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reloadgroup()"
                    title="Muat ulang tabel">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" id="AddBM" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Tambah Calendar
                </button>
                <button type="button" id="EditBM" class="btn btn-sm btn-info text-white">
                    <i class="fa fa-pen-to-square me-1"></i> Edit
                </button>
                <button type="button" id="DetailBM" class="btn btn-sm btn-dark">
                    <i class="fa fa-clock me-1"></i> Detail Days / Shifts
                </button>
                <a href="{{ url('hrd-roster-schedule-matrix') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fa fa-table-cells me-1"></i> Matriks Roster Shift
                </a>
                <button type="button" id="ekselPODListDet" class="btn btn-sm btn-success text-white"
                    onclick="WorkCalendar.toExcel('./export/generate.php');" title="Export tabel ke Excel">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="WorkCalendar" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
        </div>
        <div
            class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted fs-12">
            <div>
                <i class="fa fa-circle-info text-info me-1"></i> Pilih salah satu baris untuk melakukan edit atau mengatur
                rincian hari kerja / shift.
            </div>
            <div id="grid-status-info" class="fw-semibold">
                Status: Siap
            </div>
        </div>
    </div>

    <!-- Container Detail / Form -->
    <div id="WorkCalendarDiv"></div>

    <script>
        var WorkCalendar, PWorkCalendar;

        WorkCalendar = new dhtmlXGridObject('WorkCalendar');
        WorkCalendar.setImagePath("./dhtmlx/imgs/");
        WorkCalendar.setHeader("No, Name, Employee Type, Weekly Hours");
        WorkCalendar.attachHeader("#rspan,#text_filter,#select_filter,#text_filter");
        WorkCalendar.setInitWidths("70,*,200,180");
        WorkCalendar.setColAlign("center,left,center,center");
        WorkCalendar.setColTypes("ro,ro,ro,ro");
        WorkCalendar.setColSorting("int,str,str,int");
        WorkCalendar.enableKeyboardSupport(true);
        WorkCalendar.setSkin("dhx_skyblue");
        WorkCalendar.init();

        function reloadgroup() {
            $('#grid-status-info').html(
                '<span class="text-primary"><i class="fa fa-spinner fa-spin me-1"></i> Memuat data...</span>');
            WorkCalendar.clearAndLoad("xml-hrd-master-work-calendar", function() {
                var count = WorkCalendar.getRowsNum();
                $('#grid-status-info').html('<span class="text-success"><i class="fa fa-check me-1"></i> Total ' +
                    count + ' calendar terdaftar</span>');
            });
        }

        function showLoadingPanel(title) {
            $('#WorkCalendarDiv').html(
                '<div class="card shadow-sm border-0 mb-4">' +
                '<div class="card-body text-center py-5">' +
                '<div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">' +
                '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<h6 class="text-secondary fw-semibold">' + title + '...</h6>' +
                '<p class="text-muted fs-13 mb-0">Mohon tunggu sebentar selagi sistem memproses data.</p>' +
                '</div>' +
                '</div>'
            );
            $('html, body').animate({
                scrollTop: $("#WorkCalendarDiv").offset().top - 80
            }, 300);
        }

        $("#AddBM").click(function() {
            showLoadingPanel('Memuat Formulir Tambah Calendar');
            LoadM('WorkCalendarDiv', 'hrd-master-work-calendar-new');
        });

        $("#EditBM").click(function() {
            var WorkCalendarID = WorkCalendar.getSelectedId();
            if (!WorkCalendarID) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Baris',
                        text: 'Silakan klik salah satu baris calendar yang ingin diedit terlebih dahulu.',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    alert('Silakan pilih salah satu baris yang ingin diedit.');
                }
                return;
            }
            showLoadingPanel('Memuat Formulir Edit Calendar');
            LoadM('WorkCalendarDiv', 'hrd-master-work-calendar-edit?id=' + WorkCalendarID);
        });

        $("#DetailBM").click(function() {
            var WorkCalendarID = WorkCalendar.getSelectedId();
            if (!WorkCalendarID) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Baris',
                        text: 'Silakan pilih salah satu baris calendar untuk melihat detail jam kerja / shift.',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    alert('Silakan pilih baris terlebih dahulu untuk melihat detail.');
                }
                return;
            }

            var empType = WorkCalendar.cells(WorkCalendarID, 2).getValue();
            showLoadingPanel('Memuat Rincian Hari Kerja / Shift');

            if (empType != 'OFFICE') {
                LoadM('WorkCalendarDiv', 'hrd-master-work-calendar-detail-shift?task=' + WorkCalendarID);
            } else {
                LoadM('WorkCalendarDiv', 'hrd-master-work-calendar-detail?task=' + WorkCalendarID);
            }
        });

        // Load awal
        reloadgroup();
    </script>
@endsection
