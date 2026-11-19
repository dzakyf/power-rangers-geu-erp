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
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Approval Perawatan Kendaraan</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-clipboard-check me-1"></i> Persetujuan Maintenance
        </span>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-body">
        <div id="VMApGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="button" id="VMApDetail" class="btn btn-primary btn-sm" onclick="VehicleMaintenanceApprovalDetail();">
                <i class="fa fa-check-circle me-1"></i> Detail Persetujuan
            </button>
            <button type="button" id="VMApp_Refresh" class="btn btn-outline-secondary btn-sm" onclick="VehicleMaintenanceApprovalRefresh();">
                <i class="fa fa-sync-alt me-1"></i> Refresh Data
            </button>
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
    "&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
);
VehicleMaintenanceApproval.setInitWidths("60,160,130,100,220,90,120,140,150,120");
VehicleMaintenanceApproval.setColAlign("center,center,center,center,left,right,right,center,left,left");
VehicleMaintenanceApproval.setDateFormat("%Y-%m-%d");
VehicleMaintenanceApproval.setColSorting("int,str,str,str,str,int,str,str,str,str");
VehicleMaintenanceApproval.setColTypes("ro,ed,ro,ro,ro,ro,ro,ro,ro,ro");
VehicleMaintenanceApproval.enableSmartRendering(true, 150);
VehicleMaintenanceApproval.setSkin("dhx_skyblue");
VehicleMaintenanceApproval.init();
VehicleMaintenanceApproval.clearAndLoad('hrd-vehicle-maintenance-approval-data');

function refreshdata() {
    VehicleMaintenanceApproval.clearAndLoad('hrd-vehicle-maintenance-approval-data');
}
</script>
@endsection
