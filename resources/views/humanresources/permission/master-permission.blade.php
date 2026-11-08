@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Master Permission & Kategori Izin / Cuti</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="/dashboard1">Master Data</a></li>
                    <li class="breadcrumb-item active"><span class="active">Kategori Izin & Cuti</span></li>
                </ol>
            </div>
            <div class="d-flex gap-2">
                <a href="/portal-attendant" target="_blank"
                    class="btn btn-outline-primary btn-sm shadow-sm rounded-pill px-3">
                    <i class="fe fe-external-link me-1"></i> Buka Portal Absensi
                </a>
            </div>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Summary Stats KPI Cards -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div
                        class="avatar avatar-md bg-primary-transparent text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="fe fe-list fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Kategori</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $totalKategori ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div
                        class="avatar avatar-md bg-success-transparent text-success rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="fe fe-check-circle fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Status Aktif</span>
                        <h4 class="fw-bold mb-0 text-success">{{ $totalAktif ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div
                        class="avatar avatar-md bg-warning-transparent text-warning rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="fe fe-scissors fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Potong Cuti Tahunan</span>
                        <h4 class="fw-bold mb-0 text-warning">{{ $potongCuti ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div
                        class="avatar avatar-md bg-info-transparent text-info rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="fe fe-paperclip fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Wajib Lampiran File</span>
                        <h4 class="fw-bold mb-0 text-info">{{ $wajibLampiran ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Guidance Banner -->
    <div
        class="alert alert-info border-0 shadow-sm rounded-4 py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="fe fe-info fs-4 me-2 text-primary"></i>
            <span class="small">
                <strong>Integrasi Absensi & Cuti:</strong> Kolom <code>Kode Izin</code> digunakan sistem absensi
                (<code>Portal Attendant</code> &amp; Layanan Mandiri Karyawan). Pastikan <strong>Potong Cuti</strong>
                (<code>YES/NO</code>) dan <strong>Wajib Lampiran</strong> (<code>YES/NO</code>) telah sesuai regulasi
                internal perusahaan sebelum disimpan.
            </span>
        </div>
    </div>

    <!-- Main Grid Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div
            class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fe fe-grid me-2 text-primary"></i>Daftar Pengaturan Kategori Cuti & Izin
                </h6>
                <small class="text-muted">Klik dua kali pada sel untuk mengubah data langsung pada tabel (Inline
                    Edit).</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="RefreshBM" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fe fe-rotate-cw me-1"></i> Refresh
                </button>
                <button type="button" id="ekselPODListDet" class="btn btn-outline-warning btn-sm rounded-pill px-3"
                    onclick="Permission.toExcel('./export/generate.php');">
                    <i class="fe fe-download me-1"></i> Export Excel
                </button>
                <button type="button" id="DeleteBM" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fe fe-trash-2 me-1"></i> Delete
                </button>
                <button type="button" id="AddBM" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fe fe-plus me-1"></i> Add Category
                </button>
                <button type="button" id="SaveBM" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fe fe-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="Permission" style="width:100%; height:550px; background-color:white; overflow:hidden;"></div>
        </div>
        <div class="card-footer bg-light py-2 px-3 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fe fe-check me-1 text-success"></i>Perubahan data tersimpan aman ke database melalui controller
                <code>MasterPermissionController</code>.
            </small>
            <small class="text-muted">
                Format: [No, Kode Izin, Nama Izin, Group Jenis, Jatah Hari, Potong Cuti, Wajib Lampiran, Status]
            </small>
        </div>
    </div>

    <script>
        var Permission = new dhtmlXGridObject('Permission');
        Permission.setImagePath("./dhtmlx/imgs/");

        // Header Kolom Lengkap sesuai Tabel kategori_izin
        Permission.setHeader(
            "No,Kode Izin,Nama Kategori Izin / Cuti,Group Jenis Izin,Jatah (Hari),Potong Cuti?,Wajib Lampiran?,Status");
        Permission.attachHeader(
            "#rspan,#text_filter,#text_filter,#combo_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter");
        Permission.setInitWidths("60,180,*,160,100,120,130,110");
        Permission.setColAlign("center,left,left,center,right,center,center,center");
        // ro: read-only, ed: editable text, combo: dynamic combo from URL, coro: static select combo
        Permission.setColTypes("ro,ed,ed,combo,ed,coro,coro,coro");
        Permission.setColSorting("int,str,str,str,int,str,str,str");
        Permission.setEditable(true);
        Permission.enableMultiline(true);
        Permission.enableSmartRendering(true, 100);
        Permission.setSkin("dhx_skyblue");

        // Opsi dropdown statis untuk kolom coro:
        // Col 5: Potong Cuti Tahunan (is_deduct_annual_leave)
        var comboDeduct = Permission.getCombo(5);
        comboDeduct.put("YES", "YES (Potong)");
        comboDeduct.put("NO", "NO (Tidak)");

        // Col 6: Wajib Lampiran Surat / Bukti (is_need_attachment)
        var comboAttachment = Permission.getCombo(6);
        comboAttachment.put("YES", "YES (Wajib)");
        comboAttachment.put("NO", "NO (Opsional)");

        // Col 7: Status Kategori (status)
        var comboStatus = Permission.getCombo(7);
        comboStatus.put("ACTIVE", "ACTIVE (Aktif)");
        comboStatus.put("INACTIVE", "INACTIVE (Nonaktif)");

        // Inisialisasi DataProcessor
        var PPermission = new dataProcessor("hrd-master-permission-submit");
        PPermission.setTransactionMode({
            mode: "POST",
            total: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });
        PPermission.setUpdateMode("Off");
        PPermission.init(Permission);

        Permission.enableEditEvents(true, true, true);
        Permission.init();

        // Opsi dropdown dinamis untuk Group Jenis Izin (Col 3: izin_jenis_id)
        var combocurr = Permission.getColumnCombo(3);
        combocurr.enableFilteringMode(true);
        combocurr.load("xml-combo-permission-group");

        // Load data pertama kali
        setTimeout(function() {
            reloadmasterpermission();
        }, 300);

        function reloadmasterpermission() {
            Permission.clearAndLoad("xml-hrd-master-permission");
        }

        // Action Buttons
        $("#SaveBM").click(function() {
            PPermission.sendData();
            alert("Perubahan Master Kategori Izin/Cuti berhasil disimpan!");
            setTimeout(function() {
                reloadmasterpermission();
            }, 600);
        });

        $("#DeleteBM").click(function() {
            var selectedId = Permission.getSelectedRowId();
            if (!selectedId) {
                alert("Silakan pilih baris kategori izin yang ingin dihapus terlebih dahulu.");
                return;
            }
            if (confirm(
                    "Apakah Anda yakin ingin menghapus kategori izin terpilih? Tindakan ini dapat memengaruhi histori absensi terkait."
                )) {
                Permission.deleteSelectedRows();
                PPermission.sendData();
                alert("Data kategori izin berhasil dihapus!");
                setTimeout(function() {
                    reloadmasterpermission();
                }, 600);
            }
        });

        $("#AddBM").click(function() {
            var idbi = Permission.uid();
            // Default row data: [No, Kode Izin, Nama Izin, Group Jenis, Jatah Hari, Potong Cuti?, Wajib Lampiran?, Status]
            Permission.addRow(idbi, [
                Permission.getRowsNum() + 1,
                'IZIN_BARU',
                'Kategori Izin Baru',
                '1',
                '1',
                'NO',
                'NO',
                'ACTIVE'
            ], 0);
            Permission.selectRow(0);
        });

        $("#RefreshBM").click(function() {
            reloadmasterpermission();
        });
    </script>
@endsection
