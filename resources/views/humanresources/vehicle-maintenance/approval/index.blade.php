@extends('layout.mainlayout')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Data Assets</a></li>
        <li class="breadcrumb-item active">
            <span class="active">Approval Perawatan Kendaraan</span>
        </li>
    </ol>
</div>

<div class="card mt-3">
    <div class="card-body">
        <input type="button" id="VMApp_Refresh" class="btn btn-info mt-2 mr-2"
            value="Refresh Data" onclick="VehicleMaintenanceApprovalRefresh();" />
        <input type="button" id="VMApDetail" class="btn btn-primary mt-2 mr-2"
            value="Detail Persetujuan" onclick="VehicleMaintenanceApprovalDetail();" />
    </div>
</div>

<div id="VMApGrid" style="width:auto;height:350px;background-color:white;overflow:hidden"></div>
<div id="VMApDTL"></div>

<script>
function VehicleMaintenanceApprovalDetail() {
    var id = VehicleMaintenanceApproval.getSelectedRowId();
    if (id == null) {
        alert('Pilih Baris terlebih dahulu');
        return false;
    }
    var url = 'hrd-vehicle-maintenance-approval-detail?ID=' + id;
    LoadM('VMApDTL', url);
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
