@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Vehicle Maintenance</span>
            </li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="VehicleMaintenance" style="width:auto;height:350px;background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselVM" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="VehicleMaintenance.toExcel('./export/generate.php');" disabled />
                <input type="button" id="VM_Refresh" class="btn btn-info mt-2 mr-2" value="Refresh Data" onclick="VehicleMaintenanceRefresh();" />
                <input type="button" id="VM_New" class="btn btn-primary mt-2 mr-2" value="Form Perawatan Kendaraan" onclick="VehicleMaintenanceNew();" />
            </div>
        </div>
    </div>

    <div id="VMDTL"></div>
    <div id="VMDTL1"></div>

<script>
function VehicleMaintenanceNew() {
    LoadM('VMDTL1', 'hrd-vehicle-maintenance-create');
}

function VehicleMaintenanceDetail() {
    var id = VehicleMaintenance.getSelectedRowId();
    if (id == null) {
        alert('Pilih Baris terlebih dulu');
        return false;
    }
    var url = 'hrd-vehicle-maintenance-detail?ID=' + id;
    LoadM('VMDTL', url);
    return false;
}

function VehicleMaintenanceRefresh() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}

VehicleMaintenance = new dhtmlXGridObject('VehicleMaintenance');
VehicleMaintenance.setImagePath("./dhtmlx/imgs/");
VehicleMaintenance.setHeader("No,Request No,Jenis Perawatan,Plat Nomor,Nama Kendaraan,Kilometer,Estimasi Biaya,Status");
VehicleMaintenance.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
VehicleMaintenance.setInitWidths("60,160,130,100,220,90,120,110");
VehicleMaintenance.setColAlign("center,center,center,center,left,right,right,center");
VehicleMaintenance.setDateFormat("%Y-%m-%d");
VehicleMaintenance.setColSorting("int,str,str,str,str,int,str,str");
VehicleMaintenance.setColTypes("ro,ed,ro,ro,ro,ro,ro,ro");
VehicleMaintenance.setNumberFormat("0,000",5,".",",");
VehicleMaintenance.enableSmartRendering(true, 150);
VehicleMaintenance.setSkin("dhx_skyblue");
VehicleMaintenance.init();
VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');

function refreshdata() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}
</script>
@endsection
