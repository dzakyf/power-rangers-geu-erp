@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-settlement-index">Request Settlement</a></li>
            <li class="breadcrumb-item active"><span class="active">Verifikasi Settlement</span></li>
        </ol>
    </div>

    <div id="SettlementApprovalGrid" style="width:auto;height:380px;background-color:white;overflow:hidden;"></div>
    <div>
        <input type="button" id="SettlementApprovalRefresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="SettlementApprovalRefresh();" />
        <a href="hrd-settlement-index" class="btn btn-secondary mr-2 mt-2">Kembali</a>
    </div>

<script>
SettlementApprovalGrid = new dhtmlXGridObject('SettlementApprovalGrid');
SettlementApprovalGrid.setImagePath("./dhtmlx/imgs/");
SettlementApprovalGrid.setHeader("No,No Settlement,Requester,Department,Jml Rincian,Total Biaya,Tanggal Pengajuan");
SettlementApprovalGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
SettlementApprovalGrid.setInitWidths("40,160,150,150,90,130,150");
SettlementApprovalGrid.setColAlign("center,left,left,left,center,right,center");
SettlementApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
SettlementApprovalGrid.setColSorting("int,str,str,str,str,str,str");
SettlementApprovalGrid.setSkin("dhx_skyblue");
SettlementApprovalGrid.init();
SettlementApprovalGrid.clearAndLoad('hrd-settlement-approval-data');

function SettlementApprovalRefresh() {
    SettlementApprovalGrid.clearAndLoad('hrd-settlement-approval-data');
}
SettlementApprovalGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-settlement-approval-detail?ID=' + id;
});
</script>
@endsection
