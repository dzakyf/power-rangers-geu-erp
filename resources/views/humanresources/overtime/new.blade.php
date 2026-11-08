@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Perbaikan styling checkbox di tabel modal agar tidak absolute/melayang ke luar */
        #tableFilterEmp input[type="checkbox"],
        .check-emp-item,
        #checkSelectAllEmp {
            position: static !important;
            margin: 0 !important;
            vertical-align: middle !important;
            cursor: pointer;
            width: 17px !important;
            height: 17px !important;
            opacity: 1 !important;
            display: inline-block !important;
        }

        #tableFilterEmp th,
        #tableFilterEmp td {
            vertical-align: middle !important;
        }
    </style>
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-plus-circle me-2"></i>Pengajuan Lembur Batch / Grid (New Overtime)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">New Overtime Batch</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Form Portal Mandiri
            </a>
            <a href="{{ url('/overtime-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Rekap Semua Data
            </a>
            <a href="{{ url('/overtime-approval') }}"
                class="btn btn-warning btn-sm rounded-pill shadow-sm px-3 fw-semibold">
                <i class="fe fe-check-square me-1"></i> Approval Lembur
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Card Action Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="AddBM" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                        <i class="fe fe-user-plus me-1"></i> Tambah Karyawan
                    </button>
                    <button type="button" id="btnBatchCriteria"
                        class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm"
                        style="background: linear-gradient(135deg, #0d6efd, #0056b3); border: none;">
                        <i class="fe fe-users me-1"></i> Tambah Kolektif / Kriteria
                    </button>
                    <button type="button" id="btnRecalculateAll"
                        class="btn btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm"
                        title="Hitung ulang otomatis seluruh baris sesuai UU Cipta Kerja & PP 35/2021">
                        <i class="fe fe-cpu me-1"></i> Hitung Ulang Otomatis (PP 35/2021)
                    </button>
                    <button type="button" id="SaveBM" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm">
                        <i class="fe fe-save me-1"></i> Simpan Draft
                    </button>
                    <button type="button" id="DeleteBM" class="btn btn-outline-danger rounded-pill px-3 shadow-sm">
                        <i class="fe fe-trash-2 me-1"></i> Hapus Baris
                    </button>
                    <button type="button" id="refreshdata" class="btn btn-light rounded-pill px-3 border shadow-sm"
                        title="Refresh">
                        <i class="fe fe-refresh-cw me-1"></i> Refresh
                    </button>
                </div>
                <div>
                    <button type="button" id="SubmitApproval"
                        class="btn btn-info rounded-pill px-4 fw-semibold text-white shadow-sm">
                        <i class="fe fe-send me-1"></i> Submit untuk Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Karyawan Kolektif Berdasarkan Kriteria -->
    <div class="modal fade" id="modalBatchCriteria" tabindex="-1" aria-labelledby="modalBatchCriteriaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="modalBatchCriteriaLabel">
                            <i class="fe fe-filter text-primary me-2"></i>Tambah Karyawan Lembur Kolektif
                        </h5>
                        <p class="text-muted small mb-0">Filter karyawan berdasarkan Departemen, Posisi/Jabatan, atau
                            Site/Lokasi Kerja</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Alert Pedoman Regulasi -->
                    <div
                        class="alert alert-primary bg-primary-transparent border-0 rounded-3 p-3 mb-4 d-flex align-items-center">
                        <i class="fe fe-info fs-18 text-white me-2 flex-shrink-0"></i>
                        <div class="small text-white">
                            <strong>Otomatisasi PP No. 35 Tahun 2021 & UU Cipta Kerja:</strong>
                            Tarif lembur dihitung otomatis <code>1/173 &times; Gaji Pokok/Upah Tetap</code>, dengan formula
                            jam kerja (1.5x jam pertama, 2x berikutnya) dan hari libur (2x 8 jam pertama, 3x jam ke-9, 4x
                            jam ke-10+).
                        </div>
                    </div>

                    <!-- Form Filter Kriteria -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Departemen / Divisi</label>
                            <select id="filterDepartment" class="form-select rounded-3">
                                <option value="ALL">-- Semua Departemen --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Posisi / Jabatan</label>
                            <select id="filterPosition" class="form-select rounded-3">
                                <option value="ALL">-- Semua Posisi --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Site / Lokasi Kerja</label>
                            <select id="filterLocation" class="form-select rounded-3">
                                <option value="ALL">-- Semua Site/Lokasi --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pengaturan Template Waktu Lembur Massal -->
                    <div class="card border bg-light rounded-3 p-3 mb-3">
                        <h6 class="fw-bold text-dark mb-2 small"><i class="fe fe-clock text-primary me-1"></i>Template
                            Waktu & Tanggal Lembur Bersama:</h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Tgl Lembur</label>
                                <input type="date" id="batchDate" class="form-control form-control-sm rounded-2"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Pasca Masuk</label>
                                <input type="time" id="batchAfterIn" class="form-control form-control-sm rounded-2"
                                    value="17:00">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Pasca Keluar</label>
                                <input type="time" id="batchAfterOut" class="form-control form-control-sm rounded-2"
                                    value="21:00">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small text-muted mb-1">Uraian Tugas Lembur</label>
                                <input type="text" id="batchTaskDesc" class="form-control form-control-sm rounded-2"
                                    value="Pekerjaan lembur operasional bersama" placeholder="Keterangan lembur...">
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Hasil Filter & Seleksi Karyawan -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-dark">Daftar Karyawan Cocok (<span id="empCountBadge"
                                class="text-primary">0</span> orang):</span>
                            <div class="d-flex align-items-center gap-1 m-0">
                                <input class="form-check-input m-0" type="checkbox" id="checkSelectAllEmp" checked>
                                <label class="form-check-label small fw-semibold cursor-pointer mb-0 ms-1"
                                    for="checkSelectAllEmp">Pilih Semua</label>
                            </div>
                        </div>

                        <div class="border rounded-3 p-0 table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover table-sm align-middle mb-0" id="tableFilterEmp">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40" class="text-center">#</th>
                                        <th>NIK</th>
                                        <th>Nama Karyawan</th>
                                        <th>Dept</th>
                                        <th>Posisi</th>
                                        <th>Site</th>
                                        <th class="text-end">Tarif/Jam (PP 35)</th>
                                    </tr>
                                </thead>
                                <tbody id="empTableBody">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Memuat data kriteria...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-3"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="btnInsertSelectedEmp"
                            class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="fe fe-plus-circle me-1"></i> Tambahkan ke Form Lembur (<span
                                id="selectedCountText">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Grid Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-edit text-primary me-2"></i>Daftar Pengajuan Lembur
                    Baru
                    (Sesi Berjalan)</h6>
                <span class="badge bg-light text-muted border">Task ID:
                    <strong>{{ 'OV/' . (Auth::user()->Location ?? 'HO') . '/' . date('ymd') }}</strong></span>
            </div>
            <div class="card-body p-3">
                <div id="Overtime" style="width:100%;height:540px; background-color:white;overflow:hidden"
                    class="rounded-3 border"></div>
            </div>
        </div>

        <div id="OvertimeDiv"></div>

        <?php
        $LSP = Auth::user()->Location ?? 'HO';
        $taskid = "OV/$LSP/" . date('ymd') . '/' . date('his');
        ?>

        <script>
            var Overtime = new dhtmlXGridObject('Overtime');
            Overtime.setImagePath("./dhtmlx/imgs/");
            Overtime.setHeader(
                "No,Task ID,NIK,Nama Karyawan,Tgl Mulai,Tgl Selesai,Pra Masuk,Pra Keluar,Pasca Masuk,Pasca Keluar,Libur?,Total Jam,Jam Hitung,Tarif/Rate,Periode Gaji,Nominal Lembur,Referensi / No OT,Uraian Tugas,Grup Gaji"
            );
            Overtime.attachHeader(
                "#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter"
            );
            Overtime.setInitWidths("60,140,110,230,100,100,90,90,90,90,70,90,90,110,110,130,160,250,150");
            Overtime.setColAlign(
                "center,left,center,left,center,center,center,center,center,center,center,center,center,right,center,right,left,left,left"
            );
            Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ch,ro,ro,edn,ro,ron,ro,ed,ro");
            Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int,str,int,str,str,str");
            Overtime.setEditable(true);
            Overtime.enableEditEvents(true, false, true);
            Overtime.setDateFormat("%Y-%m-%d");
            Overtime.setNumberFormat("0,000", 13, ".", ",");
            Overtime.setNumberFormat("0,000", 15, ".", ",");
            Overtime.setSkin("dhx_skyblue");

            var POvertime = new dataProcessor("overtime-new-update");
            POvertime.setTransactionMode({
                mode: "POST",
                total: true,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                payload: {
                    "ID": "<?php echo $taskid; ?>"
                }
            });
            POvertime.setUpdateMode("Off");
            POvertime.init(Overtime);
            Overtime.init();
            Reload_Overtime();

            // Event onEditCell untuk kalkulasi otomatis realtime di sisi client & API
            Overtime.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
                // Stage 2: saat selesai edit cell
                if (stage == 2 && nValue != oValue) {
                    // Kolom terkait waktu: 4 (Tgl Mulai), 5 (Tgl Selesai), 6 (Pra Masuk), 7 (Pra Keluar), 8 (Pasca Masuk), 9 (Pasca Keluar), 10 (Libur), 13 (Tarif/Rate)
                    if ([4, 5, 6, 7, 8, 9, 10, 13].indexOf(cInd) !== -1) {
                        setTimeout(function() {
                            recalculateRowRealtime(rId);
                        }, 50);
                    }
                }
                return true;
            });

            /**
             * Hitung ulang baris secara otomatis sesuai UU Cipta Kerja & PP 35/2021
             */
            function recalculateRowRealtime(rId) {
                var nik = Overtime.cells(rId, 2).getValue();
                var startDate = Overtime.cells(rId, 4).getValue();
                var endDate = Overtime.cells(rId, 5).getValue() || startDate;
                var earlyIn = Overtime.cells(rId, 6).getValue() || '00:00';
                var earlyOut = Overtime.cells(rId, 7).getValue() || '00:00';
                var afterIn = Overtime.cells(rId, 8).getValue() || '17:00';
                var afterOut = Overtime.cells(rId, 9).getValue() || '21:00';
                var customRate = Overtime.cells(rId, 13).getValue();

                if (!nik) return;

                $.ajax({
                    url: "{{ url('/overtime-calculate-realtime') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nik: nik,
                        start_date: startDate,
                        end_date: endDate,
                        early_in: earlyIn,
                        early_out: earlyOut,
                        after_in: afterIn,
                        after_out: afterOut,
                        rate: customRate
                    },
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            var d = res.data;
                            Overtime.cells(rId, 10).setValue(d.holiday);
                            Overtime.cells(rId, 11).setValue(d.decimal_hours);
                            Overtime.cells(rId, 12).setValue(d.jumlah_jam);
                            Overtime.cells(rId, 13).setValue(d.rate);
                            Overtime.cells(rId, 15).setValue(d.amounts);
                            if (d.salary_group) {
                                Overtime.cells(rId, 18).setValue(d.salary_group);
                            }
                            POvertime.setUpdated(rId, true);
                        }
                    }
                });
            }

            function Reload_Overtime() {
                Overtime.clearAndLoad("xml-overtime-new?taskid=<?php echo $taskid; ?>");
            }

            var winCOA;
            var WCOA;

            function WindoWCOA(text, url, width, height) {
                winCOA = new dhtmlXWindows();
                winCOA.attachViewportTo('PagesContent');
                WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
                WCOA.keepInViewport(false);
                WCOA.setText(text);
                WCOA.attachURL(url, true);
                WCOA.button("minmax1").hide();
                WCOA.button("park").hide();
                WCOA.centerOnScreen();
                WCOA.allowMove();
            }

            $("#refreshdata").click(function() {
                Reload_Overtime();
            });

            $("#SaveBM").click(function() {
                POvertime.sendData();
                alert("Data draft lembur berhasil disimpan!");
                setTimeout(function() {
                    Reload_Overtime();
                }, 1500);
            });

            $("#DeleteBM").click(function() {
                if (confirm("Apakah Anda yakin ingin membatalkan lembur baris terpilih?")) {
                    Overtime.deleteSelectedRows();
                    POvertime.sendData();
                    setTimeout(function() {
                        Reload_Overtime();
                    }, 1000);
                }
            });

            $("#AddBM").click(function() {
                var idbi = Overtime.uid();
                Overtime.addRow(idbi, [
                    Overtime.getRowsNum() + 1,
                    "<?php echo $taskid; ?>" + "-" + new Date().getTime(),
                    '',
                    '',
                    '{{ date('Y-m-d') }}',
                    '{{ date('Y-m-d') }}',
                    '00:00',
                    '00:00',
                    '17:00',
                    '21:00',
                    0,
                    0,
                    0,
                    0,
                    '',
                    0,
                    '',
                    'Pekerjaan lembur operasional',
                    ''
                ], 0);
                WindoWCOA("Pilih Karyawan", "new-select-employee?id=" + idbi, 650, 350);
                Overtime.selectRow(0);
            });

            $("#SubmitApproval").click(function() {
                dhtmlx.confirm('Apakah Anda yakin ingin mengajukan data lembur ini untuk disetujui?',
                    'Konfirmasi Pengajuan Lembur',
                    function(r) {
                        if (r == true) {
                            for (var i = 0; i < Overtime.getRowsNum(); i++) {
                                var rowIdMov = Overtime.getRowId(i);
                                POvertime.setUpdated(rowIdMov, true);
                                Overtime.cells(rowIdMov, 0).setValue('SB');
                            }
                            POvertime.sendData();
                            setTimeout(function() {
                                alert("Data pengajuan lembur berhasil diajukan!");
                                location.href = 'overtime-new';
                            }, 1500);
                        }
                    });
            });

            // ==========================================
            // LOGIKA MODAL TAMBAH KOLEKTIF BERDASARKAN KRITERIA
            // ==========================================
            var cachedEmployees = [];
            var criteriaLoaded = false;

            $("#btnBatchCriteria").click(function() {
                var modal = new bootstrap.Modal(document.getElementById('modalBatchCriteria'));
                modal.show();

                if (!criteriaLoaded) {
                    loadFilterCriteria();
                } else {
                    fetchFilteredEmployees();
                }
            });

            function loadFilterCriteria() {
                $.ajax({
                    url: "{{ url('/overtime-criteria-options') }}",
                    type: "GET",
                    success: function(res) {
                        if (res.status === 'success') {
                            // Populate Departemen
                            var $dept = $('#filterDepartment');
                            $dept.find('option:not(:first)').remove();
                            (res.departments || []).forEach(function(d) {
                                $dept.append('<option value="' + d + '">' + d + '</option>');
                            });

                            // Populate Posisi
                            var $pos = $('#filterPosition');
                            $pos.find('option:not(:first)').remove();
                            (res.positions || []).forEach(function(p) {
                                $pos.append('<option value="' + p + '">' + p + '</option>');
                            });

                            // Populate Lokasi/Site
                            var $loc = $('#filterLocation');
                            $loc.find('option:not(:first)').remove();
                            (res.locations || []).forEach(function(l) {
                                $loc.append('<option value="' + l + '">' + l + '</option>');
                            });

                            criteriaLoaded = true;
                            fetchFilteredEmployees();
                        }
                    }
                });
            }

            $('#filterDepartment, #filterPosition, #filterLocation').change(function() {
                fetchFilteredEmployees();
            });

            function fetchFilteredEmployees() {
                var dept = $('#filterDepartment').val();
                var pos = $('#filterPosition').val();
                var loc = $('#filterLocation').val();

                $('#empTableBody').html(
                    '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin me-2"></i>Mencocokkan karyawan...</td></tr>'
                );

                $.ajax({
                    url: "{{ url('/overtime-employees-by-criteria') }}",
                    type: "GET",
                    data: {
                        department: dept,
                        position: pos,
                        location: loc
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            cachedEmployees = res.data || [];
                            renderEmployeeTable(cachedEmployees);
                        }
                    },
                    error: function() {
                        $('#empTableBody').html(
                            '<tr><td colspan="7" class="text-center text-danger py-3">Gagal memuat data karyawan.</td></tr>'
                        );
                    }
                });
            }

            function renderEmployeeTable(employees) {
                var html = '';
                $('#empCountBadge').text(employees.length);

                if (employees.length === 0) {
                    html =
                        '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada karyawan aktif yang cocok dengan kriteria filter terpilih.</td></tr>';
                    $('#empTableBody').html(html);
                    updateSelectedCount();
                    return;
                }

                employees.forEach(function(emp, idx) {
                    var rateFormatted = new Intl.NumberFormat('id-ID').format(emp.Overtime_Rate || 0);
                    html += '<tr>' +
                        '<td class="text-center"><input type="checkbox" class="form-check-input check-emp-item" value="' +
                        emp.NIK + '" checked></td>' +
                        '<td class="fw-semibold">' + emp.NIK + '</td>' +
                        '<td>' + emp.Name + '</td>' +
                        '<td><span class="badge bg-light text-dark border">' + (emp.Department || '-') +
                        '</span></td>' +
                        '<td>' + (emp.Position || '-') + '</td>' +
                        '<td>' + (emp.Location || 'HO') + '</td>' +
                        '<td class="text-end fw-bold text-success">Rp ' + rateFormatted + '</td>' +
                        '</tr>';
                });

                $('#empTableBody').html(html);
                updateSelectedCount();
            }

            // Toggle Check All
            $('#checkSelectAllEmp').change(function() {
                var checked = $(this).is(':checked');
                $('.check-emp-item').prop('checked', checked);
                updateSelectedCount();
            });

            $(document).on('change', '.check-emp-item', function() {
                updateSelectedCount();
            });

            function updateSelectedCount() {
                var count = $('.check-emp-item:checked').length;
                $('#selectedCountText').text(count);
            }

            // Tambahkan Karyawan Terpilih ke Grid Overtime
            $('#btnInsertSelectedEmp').click(function() {
                var selectedNiks = [];
                $('.check-emp-item:checked').each(function() {
                    selectedNiks.push($(this).val());
                });

                if (selectedNiks.length === 0) {
                    alert('Silakan pilih minimal 1 karyawan untuk ditambahkan ke form lembur.');
                    return;
                }

                var tglLembur = $('#batchDate').val() || '{{ date('Y-m-d') }}';
                var afterIn = $('#batchAfterIn').val() || '17:00';
                var afterOut = $('#batchAfterOut').val() || '21:00';
                var taskDesc = $('#batchTaskDesc').val() || 'Pekerjaan lembur operasional bersama';

                var selectedEmpData = cachedEmployees.filter(function(emp) {
                    return selectedNiks.indexOf(emp.NIK) !== -1;
                });

                // Loop dan tambahkan row ke dhtmlXGrid
                selectedEmpData.forEach(function(emp) {
                    var idbi = Overtime.uid();
                    var newTaskId = "<?php echo $taskid; ?>" + "-" + new Date().getTime() + "-" + Math.floor(Math
                        .random() * 1000);

                    Overtime.addRow(idbi, [
                        Overtime.getRowsNum() + 1,
                        newTaskId,
                        emp.NIK,
                        emp.Name,
                        tglLembur,
                        tglLembur,
                        '00:00',
                        '00:00',
                        afterIn,
                        afterOut,
                        0,
                        0,
                        0,
                        emp.Overtime_Rate || 0,
                        '',
                        0,
                        '',
                        taskDesc,
                        emp.Salary_Group || ''
                    ], 0);

                    POvertime.setUpdated(idbi, true);

                    // Hitung otomatis jam dan nominal sesuai PP 35/2021
                    recalculateRowRealtime(idbi);
                });

                // Tutup Modal
                var modalEl = document.getElementById('modalBatchCriteria');
                var modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                alert('Berhasil menambahkan ' + selectedEmpData.length + ' karyawan ke formulir lembur!');
            });

            // Tombol Hitung Ulang Semua Baris di Grid
            $('#btnRecalculateAll').click(function() {
                var totalRows = Overtime.getRowsNum();
                if (totalRows === 0) {
                    alert('Belum ada baris data lembur di dalam tabel.');
                    return;
                }

                for (var i = 0; i < totalRows; i++) {
                    var rId = Overtime.getRowId(i);
                    recalculateRowRealtime(rId);
                }

                alert('Proses kalkulasi otomatis PP 35/2021 untuk seluruh baris sedang dijalankan!');
            });
        </script>
    @endsection
