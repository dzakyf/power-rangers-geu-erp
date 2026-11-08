@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Master Position & Kompensasi Lembur</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1">Master Data</a></li>
                    <li class="breadcrumb-item active"><span class="active">Position</span></li>
                </ol>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="btnSyncModal" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="fe fe-refresh-cw me-1"></i> Sinkronisasi Karyawan Existing
                </button>
            </div>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Info Banner UX -->
    <div
        class="alert alert-info border-0 shadow-sm rounded-3 py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="fe fe-info fs-4 me-2 text-primary"></i>
            <span class="small">
                <strong>Kolom Kompensasi Lembur:</strong> Secara standar sistem bernilai <strong><code>PAID (Default - Uang
                        Lembur)</code></strong> kecuali diubah menjadi <strong><code>TIME_OFF (Day Off / Cuti
                        Pengganti)</code></strong>. Nilai ini menjadi acuan default pengajuan lembur pada jabatan tersebut.
            </span>
        </div>
        <div class="form-check form-switch m-0 small fw-semibold">
            <input class="form-check-input" type="checkbox" id="chkAutoSyncExisting">
            <label class="form-check-label text-dark" for="chkAutoSyncExisting">
                Otomatis update karyawan existing saat klik "Save Changes"
            </label>
        </div>
    </div>

    <!-- Main Grid Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div
            class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-dark"><i class="fe fe-grid me-2 text-primary"></i>Daftar Master Posisi & Aturan
                Kompensasi</h6>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="RefreshBM" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fe fe-rotate-cw me-1"></i> Refresh
                </button>
                <button type="button" id="ekselPODListDet" class="btn btn-outline-warning btn-sm rounded-pill px-3"
                    onclick="MaterPosition.toExcel('./export/generate.php');">
                    <i class="fe fe-download me-1"></i> Export Excel
                </button>
                <button type="button" id="DeleteBM" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fe fe-trash-2 me-1"></i> Delete
                </button>
                <button type="button" id="AddBM" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fe fe-plus me-1"></i> Add Position
                </button>
                <button type="button" id="SaveBM" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fe fe-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="MaterPosition" style="width:100%; height:550px; background-color:white; overflow:hidden;"></div>
        </div>
    </div>

    <!-- Modal Sinkronisasi Karyawan Existing -->
    <div class="modal fade" id="modalSyncExisting" tabindex="-1" aria-labelledby="modalSyncExistingLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="modalSyncExistingLabel">
                        <i class="fe fe-users me-2 text-primary"></i>Sinkronisasi Karyawan Existing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">
                        Pilih posisi terpilih di grid, lalu putuskan apakah seluruh karyawan existing pada posisi tersebut
                        akan diseragamkan kompensasinya mengikuti master atau di-reset mengikuti aturan default.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Posisi Terpilih</label>
                        <input type="text" id="sync_position_name" class="form-control bg-light rounded-3" readonly
                            value="- Belum Memilih Baris Posisi -">
                        <input type="hidden" id="sync_position_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Kompensasi untuk Karyawan Existing</label>
                        <select id="sync_compensation_type" class="form-select rounded-3">
                            <option value="FOLLOW_MASTER">Sesuai Nilai di Master Posisi Ini</option>
                            <option value="PAID">PAID - Uang Lembur Matriks</option>
                            <option value="TIME_OFF">TIME_OFF - Cuti Pengganti / Day Off</option>
                            <option value="DEFAULT">RESET (Kosongkan Override &amp; Ikuti Standar Master)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnExecuteSync" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fe fe-check-circle me-1"></i> Terapkan ke Karyawan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var MaterPosition = new dhtmlXGridObject('MaterPosition');
        MaterPosition.setImagePath("./dhtmlx/imgs/");
        MaterPosition.setHeader(
            "No,Directorat,Department,Subsection,Position,Initial Code,Initial,Level,Parent Position,Location,Company,Type,Status,Kompensasi Lembur"
        );
        MaterPosition.attachHeader(
            "#rspan,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter"
        );
        MaterPosition.setInitWidths("50,130,150,130,210,95,95,65,200,90,75,110,90,140");
        MaterPosition.setColAlign(
            "center,left,left,left,left,center,center,center,left,center,center,center,center,center");
        MaterPosition.setColTypes("ro,combo,combo,ed,ed,ed,ed,ed,combo,ed,ed,coro,coro,coro");
        MaterPosition.setColSorting("int,str,str,str,str,str,str,int,str,str,str,str,str,str");
        MaterPosition.setEditable(true);
        MaterPosition.enableEditEvents(true, true, true);
        MaterPosition.setSkin("dhx_skyblue");

        // Pilihan Opsi untuk kolom coro (Type, Status, Kompensasi Lembur)
        var comboType = MaterPosition.getCombo(11);
        comboType.put("STRUCTURAL", "STRUCTURAL");
        comboType.put("FUNCTIONAL", "FUNCTIONAL");

        var comboStatus = MaterPosition.getCombo(12);
        comboStatus.put("ACTIVE", "ACTIVE");
        comboStatus.put("INACTIVE", "INACTIVE");

        var comboCompensation = MaterPosition.getCombo(13);
        comboCompensation.put("PAID", "PAID (Default - Uang Lembur)");
        comboCompensation.put("TIME_OFF", "TIME_OFF (Day Off / Cuti Pengganti)");

        var PMaterPosition = new dataProcessor("master-position-update");
        PMaterPosition.setTransactionMode({
            mode: "POST",
            total: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });
        PMaterPosition.setUpdateMode("Off");
        PMaterPosition.init(MaterPosition);
        MaterPosition.init();

        // Remote Server Combos untuk relasi dinamis
        comboDir = MaterPosition.getColumnCombo(1);
        comboDir.enableFilteringMode(true);
        comboDir.load("xml-combo-directorat");

        comboDept = MaterPosition.getColumnCombo(2);
        comboDept.enableFilteringMode(true);
        comboDept.load("xml-combo-department");

        comboParent = MaterPosition.getColumnCombo(8);
        comboParent.enableFilteringMode(true);
        comboParent.load("xml-combo-parent-position");

        reloadgroup();

        function reloadgroup() {
            MaterPosition.clearAndLoad("xml-hrd-master-position");
        }

        $("#SaveBM").click(function() {
            var autoSync = $("#chkAutoSyncExisting").is(":checked") ? "1" : "0";
            PMaterPosition.serverProcessor = "master-position-update?sync_existing=" + autoSync;
            PMaterPosition.sendData();
            alert("Perubahan Master Position berhasil disimpan!");
            setTimeout(() => {
                reloadgroup();
            }, 600);
        });

        $("#DeleteBM").click(function() {
            if (confirm("Apakah Anda yakin ingin menghapus data posisi terpilih?")) {
                MaterPosition.deleteSelectedRows();
                PMaterPosition.sendData();
                alert("Data posisi berhasil dihapus!");
                reloadgroup();
            }
        });

        $("#AddBM").click(function() {
            var idbi = MaterPosition.uid();
            // [No, Directorat, Department, Subsection, Position, Initial Code, Initial, Level, Parent Position, Location, Company, Type, Status, Kompensasi]
            MaterPosition.addRow(idbi, [MaterPosition.getRowsNum() + 1, '', '', '', '', '', '', '4', '', 'GEU-HO',
                'GEU', 'STRUCTURAL', 'ACTIVE', 'PAID'
            ], 0);
            MaterPosition.selectRow(0);
        });

        $("#RefreshBM").click(function() {
            reloadgroup();
        });

        // Modal Sinkronisasi Karyawan Existing
        $("#btnSyncModal").click(function() {
            var selectedId = MaterPosition.getSelectedRowId();
            if (!selectedId) {
                alert("Silakan pilih / klik salah satu baris posisi pada tabel terlebih dahulu.");
                return;
            }
            var posName = MaterPosition.cells(selectedId, 4).getValue() || MaterPosition.cells(selectedId, 6)
                .getValue();
            var currentComp = MaterPosition.cells(selectedId, 13).getValue();

            $("#sync_position_id").val(selectedId);
            $("#sync_position_name").val(posName + " (ID: " + selectedId + ", Saat ini: " + (currentComp ||
                "PAID/Default") + ")");

            var modal = new bootstrap.Modal(document.getElementById('modalSyncExisting'));
            modal.show();
        });

        $("#btnExecuteSync").click(function() {
            var posId = $("#sync_position_id").val();
            var compChoice = $("#sync_compensation_type").val();
            if (!posId) return;

            var selectedCompInGrid = MaterPosition.cells(posId, 13).getValue();
            var targetComp = compChoice;
            if (compChoice === 'FOLLOW_MASTER') {
                targetComp = selectedCompInGrid || 'PAID';
            }

            if (!confirm("Apakah Anda yakin ingin menerapkan kompensasi '" + targetComp +
                    "' ke seluruh karyawan yang menduduki posisi ini?")) {
                return;
            }

            var btn = $(this);
            btn.prop("disabled", true).html("<i class='fe fe-loader me-1'></i> Memproses...");

            $.ajax({
                url: "{{ route('hrd.master.position.sync-existing') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {
                    position_id: posId,
                    compensation: targetComp
                },
                success: function(res) {
                    btn.prop("disabled", false).html(
                        "<i class='fe fe-check-circle me-1'></i> Terapkan ke Karyawan");
                    alert(res.message || "Sinkronisasi berhasil dijalankan!");
                    var modalEl = document.getElementById('modalSyncExisting');
                    var modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                },
                error: function(err) {
                    btn.prop("disabled", false).html(
                        "<i class='fe fe-check-circle me-1'></i> Terapkan ke Karyawan");
                    alert("Gagal melakukan sinkronisasi: " + (err.responseJSON ? err.responseJSON
                        .message : "Terjadi kesalahan sistem"));
                }
            });
        });
    </script>
@endsection
