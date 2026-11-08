@extends('layout.mainlayout-hrd')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-check-circle me-2"></i>Approval Izin & Cuti Karyawan (Permission Approval)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Human Resources</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.portal') }}"
                            style="color: rgba(255, 255, 255, 0.8) !important;">Employee Portal</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">Permission Approval</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('attendance.portal') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-arrow-left me-1"></i> Kembali ke Portal
            </a>
            <a href="{{ url('/permission-all') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-list me-1"></i> Rekap Semua Data
            </a>
            <button type="button" class="btn btn-danger btn-sm rounded-pill shadow-sm px-3 fw-semibold" onclick="batalkansalary();">
                <i class="fe fe-x-circle me-1"></i> Tolak / Batalkan
            </button>
            <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm px-4 fw-semibold" onclick="approvesalary();">
                <i class="fe fe-check-circle me-1"></i> Approve Terpilih
            </button>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Card Grid Tabel Approval -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="fe fe-inbox text-primary me-2"></i>Daftar Pengajuan Izin & Cuti Menunggu Persetujuan</h6>
                <small class="text-muted">Centang permohonan lalu klik <strong>Approve Terpilih</strong> atau klik ganda baris untuk melihat detail & bukti</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm rounded-pill border px-3" onclick="refreshdata();" title="Refresh">
                    <i class="fe fe-refresh-cw me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="PermissionApproval.toExcel('./export/generate.php');" title="Export Excel">
                    <i class="fe fe-download me-1"></i> Excel
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="PermissionApproval" style="width:100%;height:540px; background-color:white;overflow:hidden" class="rounded-3 border"></div>
        </div>
    </div>	

    <div id="PermissionApprovalDet" class="mt-3"></div>

<script>
    function batalkansalary() {
        var PermissionApprovalID = PermissionApproval.getSelectedId();
        if (!PermissionApprovalID) {
            alert('Pilih baris izin yang ingin dibatalkan terlebih dahulu.');
            return;
        }

        dhtmlx.confirm('Apakah Anda yakin ingin menolak / membatalkan pengajuan izin ini?', 'Konfirmasi Pembatalan', function(r) {
            if (r == true) {
                LoadM('PermissionApprovalDet', 'permission-approval-cancel?ID=' + PermissionApprovalID);
            }
        });
    }

    function approvesalary() {
        dhtmlx.confirm('Konfirmasi persetujuan untuk seluruh permohonan izin yang dicentang?', 'Konfirmasi Approval', function(r) {
            if (r == true) {
                if (typeof $.blockUI !== 'undefined') {
                    $.blockUI({ message: '<div class="p-3 fw-semibold"><i class="fe fe-loader me-2"></i>Menyimpan persetujuan...</div>' });
                }
                PPermissionApproval.sendData();
                setTimeout(function(){
                    refreshdata();
                    if (typeof $.unblockUI !== 'undefined') {
                        $.unblockUI();
                    }
                }, 1500);
            }
        });
    }

    function PermissionApprovalKlik() {
        var PermissionApprovalID = PermissionApproval.getSelectedId();
        if (PermissionApprovalID != '' && PermissionApprovalID != null) {
            LoadM('PermissionApprovalDet', 'permission-approval-detail?ID=' + PermissionApprovalID);
            $('html, body').animate({
                scrollTop: $("#PermissionApprovalDet").offset().top - 80
            }, 500);
        }
        return true;
    }

    var PermissionApproval = new dhtmlXGridObject('PermissionApproval');
    PermissionApproval.setImagePath("./dhtmlx/imgs/");
    PermissionApproval.setHeader("Approve,Permission ID,NIK,Nama Karyawan,Kategori Karyawan,Jenis Izin,Sub Kategori / Alasan,Lokasi,Catatan,Tanggal Izin");
    PermissionApproval.attachHeader("#master_checkbox,#combo_filter,#text_filter,#text_filter,#combo_filter,#combo_filter,#text_filter,#combo_filter,#text_filter,#text_filter");
    PermissionApproval.setInitWidths("70,180,110,210,130,140,200,140,220,110");
    PermissionApproval.setColAlign("center,left,center,left,center,center,left,center,left,center");
    PermissionApproval.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    PermissionApproval.setColSorting("int,str,str,str,str,str,str,str,str,date");
    PermissionApproval.attachEvent("onRowDblClicked", PermissionApprovalKlik);
    PermissionApproval.setSkin("dhx_skyblue");
    PermissionApproval.init();

    var PPermissionApproval = new dataProcessor("permission-approval-update");
    PPermissionApproval.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PPermissionApproval.init(PermissionApproval);

    refreshdata();

    function refreshdata() {
        PermissionApproval.clearAndLoad("xml-permission-approval");
    }
</script>
@endsection
