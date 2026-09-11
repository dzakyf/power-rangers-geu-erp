@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
            <li class="breadcrumb-item active"><span class="active">Approval SPPD</span></li>
        </ol>
    </div>

    <div id="SppdApprovalGrid" style="width:auto;height:380px;background-color:white;overflow:hidden;"></div>
    <div>
        <input type="button" id="SppdApprovalRefresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="SppdApprovalRefresh();" />
        <a href="hrd-sppd-index" class="btn btn-secondary mr-2 mt-2">Kembali</a>
    </div>

<script>
SppdApprovalGrid = new dhtmlXGridObject('SppdApprovalGrid');
SppdApprovalGrid.setImagePath("./dhtmlx/imgs/");
SppdApprovalGrid.setHeader("No,SPPD No,Requester,Department,Type,Destination,Rute,Biaya,Status,Tanggal Pengajuan");
SppdApprovalGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
SppdApprovalGrid.setInitWidths("40,150,150,150,110,150,180,140,110,150");
SppdApprovalGrid.setColAlign("center,left,left,left,left,left,left,right,center,center");
SppdApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
SppdApprovalGrid.setColSorting("int,str,str,str,str,str,str,str,str,str");
SppdApprovalGrid.setSkin("dhx_skyblue");
SppdApprovalGrid.init();
SppdApprovalGrid.clearAndLoad('hrd-sppd-approval-data');

function SppdApprovalRefresh() {
    SppdApprovalGrid.clearAndLoad('hrd-sppd-approval-data');
}
SppdApprovalGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-sppd-approval-detail?ID=' + id;
});
</script>
@endsection
