@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Asset Incoming Approval</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Incoming Approval</span>
            </li>
        </ol>
    </div>
    
    <div id="IncomingPartsDiv">
        <div class="card">
            <div class="card-header bg-primary text-white font-weight-bold">
                <i class="fas fa-check-circle mr-2"></i> ANTREAN APPROVAL PENERIMAAN ASET (GOOD RECEIVE)
            </div>
            <div class="card-body">
                <div id="IncomingParts" style="width:100%;height:400px; background-color:white;overflow:hidden"></div>
                <div class="mt-2">
                    <input type="button" id="ekselIncomingParts" class="btn btn-warning mr-2" value="Export Excel" onclick="IncomingParts.toExcel('./export/generate.php');" />
                    <input type="button" id="IncomingParts_Refresh" class="btn btn-info mr-2" value="Refresh Data" onclick="refreshdata();" />
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
var IncomingParts = new dhtmlXGridObject('IncomingParts');
IncomingParts.setImagePath("./codebase/imgs/");
IncomingParts.setHeader("No, GR No, PO No, Reference, PR No, Status, Apply To, Date Created");
IncomingParts.setInitWidths("50,200,200,180,180,150,150,*");
IncomingParts.setColAlign("center,left,left,left,left,center,left,center");
IncomingParts.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro");
IncomingParts.setColSorting("int,str,str,str,str,str,str,str");
IncomingParts.attachEvent("onRowDblClicked", IncomingPartsDoubleClick);
IncomingParts.init();

refreshdata();

function refreshdata(){
    IncomingParts.clearAndLoad('hrd-asset-incoming-approval-data');
}

function IncomingPartsDoubleClick() {
    var selectedId = IncomingParts.getSelectedId();
    if (selectedId) {
        LoadM('IncomingPartsDiv', 'hrd-asset-incoming-approval-detail?ID=' + encodeURI(selectedId));
    }
}
</script>
@endsection
