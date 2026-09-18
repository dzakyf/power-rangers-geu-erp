@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Travel, Expense & SPPD</a></li>
            <li class="breadcrumb-item active"><span class="active">Request Settlement</span></li>
        </ol>
    </div>

    <div id="SettlementGrid" style="width:auto;height:380px;background-color:white;overflow:hidden;"></div>
    <div>
        <input type="button" id="SettlementRefresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="SettlementRefresh();" />
        <input type="button" id="SettlementNew" class="btn btn-primary mr-2 mt-2" value="Form Request Settlement" onclick="SettlementNew();" />
        @if (App\Http\Controllers\HCGA\SettlementController::isHcgaVerifier())
            <input type="button" id="SettlementApproval" class="btn btn-warning mr-2 mt-2" value="Verifikasi Settlement" onclick="SettlementApproval();" />
        @endif
    </div>

<script>
SettlementGrid = new dhtmlXGridObject('SettlementGrid');
SettlementGrid.setImagePath("./dhtmlx/imgs/");
SettlementGrid.setHeader("No,No Settlement,Requester,Department,Ref SPPD,Jml Rincian,Total Biaya,Status,Invoice,Doc");
SettlementGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,&nbsp;,&nbsp;");
SettlementGrid.setInitWidths("40,160,150,140,150,90,130,110,100,120");
SettlementGrid.setColAlign("center,left,left,left,left,center,right,center,center,center");
SettlementGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,link,link");
SettlementGrid.setColSorting("int,str,str,str,str,str,str,str,str,str");
SettlementGrid.setSkin("dhx_skyblue");
SettlementGrid.init();
SettlementGrid.clearAndLoad('hrd-settlement-data');

function SettlementRefresh() {
    SettlementGrid.clearAndLoad('hrd-settlement-data');
}
function SettlementNew() {
    location.href = 'hrd-settlement-new';
}
function SettlementApproval() {
    location.href = 'hrd-settlement-approval-index';
}
SettlementGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-settlement-detail?ID=' + id;
});
</script>
@endsection
