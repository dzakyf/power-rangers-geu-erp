@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Perawatan Kendaraan
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="hrd-vehicle-maintenance-index" style="color: rgba(255, 255, 255, 0.8) !important;">Vehicle Maintenance</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Approval Perawatan Kendaraan</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge bg-light text-dark px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-clipboard-check me-1"></i> Persetujuan Maintenance (Approval Engine)
        </span>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-body">
        <div id="VMApGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="button" id="VMApDetail" class="btn btn-primary btn-sm" onclick="VehicleMaintenanceApprovalDetail();">
                <i class="fa fa-check-circle me-1"></i> Detail &amp; Proses Persetujuan
            </button>
            <button type="button" id="VMApp_Refresh" class="btn btn-outline-secondary btn-sm" onclick="VehicleMaintenanceApprovalRefresh();">
                <i class="fa fa-sync-alt me-1"></i> Refresh Data
            </button>
            <a href="hrd-approval-inbox" class="btn btn-outline-info btn-sm">
                <i class="fa fa-inbox me-1"></i> Buka Approval Inbox Terpusat
            </a>
            <a href="hrd-vehicle-maintenance-index" class="btn btn-outline-dark btn-sm">
                <i class="fa fa-car me-1"></i> Daftar Vehicle Maintenance
            </a>
        </div>
    </div>
</div>

<div id="VMApDTL"></div>

<script>
function VehicleMaintenanceApprovalDetail() {
    var id = VehicleMaintenanceApproval.getSelectedRowId();
    if (id == null) {
        alert('Pilih Baris terlebih dahulu');
        return false;
    }
    var url = 'hrd-vehicle-maintenance-approval-detail?ID=' + encodeURIComponent(id);
    LoadM('VMApDTL', url);
    var el = document.getElementById('VMApDTL');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return false;
}

function VehicleMaintenanceApprovalRefresh() {
    VehicleMaintenanceApproval.clearAndLoad('hrd-vehicle-maintenance-approval-data');
}

VehicleMaintenanceApproval = new dhtmlXGridObject('VMApGrid');
VehicleMaintenanceApproval.setImagePath("./dhtmlx/imgs/");
VehicleMaintenanceApproval.setHeader(
    "No,Request No,Jenis Perawatan,Asset No,Nama Kendaraan,Plat Nomor,Estimasi Biaya,Status,Nama Pengaju,Dept Pengaju"
);
VehicleMaintenanceApproval.attachHeader(
    "&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter"
);
VehicleMaintenanceApproval.setInitWidths("55,175,130,115,210,100,120,145,145,115");
VehicleMaintenanceApproval.setColAlign("center,center,center,center,left,center,right,center,left,left");
VehicleMaintenanceApproval.setDateFormat("%Y-%m-%d");
VehicleMaintenanceApproval.setColSorting("int,str,str,str,str,str,str,str,str,str");
VehicleMaintenanceApproval.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
VehicleMaintenanceApproval.enableSmartRendering(true, 150);
VehicleMaintenanceApproval.setSkin("dhx_skyblue");
VehicleMaintenanceApproval.init();
VehicleMaintenanceApproval.clearAndLoad('hrd-vehicle-maintenance-approval-data');

function refreshdata() {
    VehicleMaintenanceApproval.clearAndLoad('hrd-vehicle-maintenance-approval-data');
}
</script>
@endsection
