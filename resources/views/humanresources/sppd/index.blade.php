@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Perjalanan Dinas</a></li>
            <li class="breadcrumb-item active"><span class="active">Request SPPD</span></li>
        </ol>
    </div>

    <div id="SppdGrid" style="width:auto;height:380px;background-color:white;overflow:hidden;"></div>
    <div>
        <input type="button" id="SppdRefresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="SppdRefresh();" />
        <input type="button" id="SppdNew" class="btn btn-primary mr-2 mt-2" value="Form Request SPPD" onclick="SppdNew();" />
        @if (App\Http\Controllers\HCGA\SppdController::isApprover())
            <input type="button" id="SppdApproval" class="btn btn-warning mr-2 mt-2" value="Approval SPPD" onclick="SppdApproval();" />
        @endif
    </div>

<script>
SppdGrid = new dhtmlXGridObject('SppdGrid');
SppdGrid.setImagePath("./dhtmlx/imgs/");
SppdGrid.setHeader("No,SPPD No,Requester,Department,Destination,Route,Kegiatan,Schedule Date,Estimated Cost,Status,Doc Pengajuan,Doc Persetujuan");
SppdGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,&nbsp;,&nbsp;");
SppdGrid.setInitWidths("40,140,140,140,150,170,130,110,130,110,110,130");
SppdGrid.setColAlign("center,left,left,left,left,left,left,center,right,center,center,center");
SppdGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link,link");
SppdGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
SppdGrid.setSkin("dhx_skyblue");
SppdGrid.init();
SppdGrid.clearAndLoad('hrd-sppd-data');

function SppdRefresh() {
    SppdGrid.clearAndLoad('hrd-sppd-data');
}
function SppdNew() {
    location.href = 'hrd-sppd-new';
}
function SppdApproval() {
    location.href = 'hrd-sppd-approval-index';
}
SppdGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-sppd-detail?ID=' + id;
});
</script>
@endsection
