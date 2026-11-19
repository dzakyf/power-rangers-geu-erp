@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Vehicle Maintenance Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Vehicle Maintenance</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-car me-1"></i> Perawatan Kendaraan
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="VehicleMaintenance" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="VM_New" class="btn btn-primary btn-sm" onclick="VehicleMaintenanceNew();">
                    <i class="fa fa-plus me-1"></i> Form Perawatan Kendaraan
                </button>
                <button type="button" id="VM_Detail" class="btn btn-info btn-sm text-white" onclick="VehicleMaintenanceDetail();">
                    <i class="fa fa-eye me-1"></i> Detail Perawatan
                </button>
                <button type="button" id="ekselVM" class="btn btn-warning btn-sm text-dark" onclick="VehicleMaintenance.toExcel('./export/generate.php');" disabled>
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="VM_Refresh" class="btn btn-outline-secondary btn-sm" onclick="VehicleMaintenanceRefresh();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
                <a href="hrd-vehicle-operations" class="btn btn-success btn-sm fw-semibold">
                    <i class="fa fa-truck me-1"></i> Logbook &amp; Fuel Tracking
                </a>
            </div>
        </div>
    </div>

    <div id="VMDTL"></div>
    <div id="VMDTL1"></div>

<script>
function scrollToVmPanel(id) {
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function VehicleMaintenanceNew() {
    LoadM('VMDTL1', 'hrd-vehicle-maintenance-create');
    scrollToVmPanel('VMDTL1');
}

function VehicleMaintenanceDetail() {
    var id = VehicleMaintenance.getSelectedRowId();
    if (id == null) {
        alert('Pilih Baris terlebih dulu');
        return false;
    }
    var url = 'hrd-vehicle-maintenance-detail?ID=' + encodeURIComponent(id);
    LoadM('VMDTL', url);
    scrollToVmPanel('VMDTL');
    return false;
}

function VehicleMaintenanceRefresh() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}

VehicleMaintenance = new dhtmlXGridObject('VehicleMaintenance');
VehicleMaintenance.setImagePath("./dhtmlx/imgs/");
VehicleMaintenance.setHeader("No,Request No,Asset No,Jenis Perawatan,Plat Nomor,Nama Kendaraan,Kilometer,Estimasi Biaya,Status");
VehicleMaintenance.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
VehicleMaintenance.setInitWidths("60,170,130,110,100,220,90,120,110");
VehicleMaintenance.setColAlign("center,center,center,center,center,left,right,right,center");
VehicleMaintenance.setDateFormat("%Y-%m-%d");
VehicleMaintenance.setColSorting("int,str,str,str,str,str,int,str,str");
VehicleMaintenance.setColTypes("ro,ed,ro,ro,ro,ro,ro,ro,ro");
VehicleMaintenance.setNumberFormat("0,000",6,".",",");
VehicleMaintenance.enableSmartRendering(true, 150);
VehicleMaintenance.setSkin("dhx_skyblue");
VehicleMaintenance.init();
VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');

function refreshdata() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}
</script>
@endsection
