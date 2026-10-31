@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Reimbursement</a></li>
            <li class="breadcrumb-item active"><span class="active">Reimbursement Management</span></li>
        </ol>
    </div>

    <div id="ReimbursementGrid" style="width:auto;height:380px;background-color:white;overflow:hidden;"></div>
    <div>
        <input type="button" id="ReimbursementRefresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="ReimbursementRefresh();" />
        <input type="button" id="ReimbursementNew" class="btn btn-primary mr-2 mt-2" value="Form Request Reimbursement" onclick="ReimbursementNew();" />
    </div>

<script>
ReimbursementGrid = new dhtmlXGridObject('ReimbursementGrid');
ReimbursementGrid.setImagePath("./dhtmlx/imgs/");
ReimbursementGrid.setHeader("No,Reimbursement No,Date,Department,Description,Total,Status,Submitted Date,Created By");
ReimbursementGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ReimbursementGrid.setInitWidths("40,180,100,140,220,130,140,150,110");
ReimbursementGrid.setColAlign("center,left,center,left,left,right,center,center,center");
ReimbursementGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
ReimbursementGrid.setColSorting("int,str,str,str,str,str,str,str,str");
ReimbursementGrid.setSkin("dhx_skyblue");
ReimbursementGrid.init();
ReimbursementGrid.clearAndLoad('hrd-reimbursement-data');

function ReimbursementRefresh() {
    ReimbursementGrid.clearAndLoad('hrd-reimbursement-data');
}
function ReimbursementNew() {
    location.href = 'hrd-reimbursement-new';
}
ReimbursementGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-reimbursement-detail?ID=' + id;
});
</script>
@endsection
