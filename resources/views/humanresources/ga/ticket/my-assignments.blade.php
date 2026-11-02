@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">My Assignments</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="GAMyAssign" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" class="btn btn-info btn-sm text-white" onclick="GAMyAssignRefresh()">Refresh Data</button>
                <button type="button" class="btn btn-success btn-sm" onclick="GAMyAssignDetail()">Detail / Tindak Lanjut</button>
            </div>
        </div>
    </div>

    <div id="GAMYASSIGNDTL"></div>

<script>
function GAMyAssignRefresh() {
    GAMyAssign.clearAndLoad('hrd-ga-my-assignment-data');
}

function GAMyAssignDetail() {
    var id = GAMyAssign.getSelectedRowId();
    if (!id) {
        alert('Pilih baris tiket terlebih dahulu.');
        return false;
    }
    LoadM('GAMYASSIGNDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
}

window.GATicketReloadDetail = function (id) {
    LoadM('GAMYASSIGNDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
};
window.GATicketRefreshGrid = function () {
    GAMyAssign.clearAndLoad('hrd-ga-my-assignment-data');
};

GAMyAssign = new dhtmlXGridObject('GAMyAssign');
GAMyAssign.setImagePath('./dhtmlx/imgs/');
GAMyAssign.setHeader('No,No Tiket,Subjek,Kategori,Prioritas,Status,Resolve Due,Dibuat');
GAMyAssign.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter');
GAMyAssign.setInitWidths('50,150,260,150,90,110,150,150');
GAMyAssign.setColAlign('center,center,left,left,center,center,center,center');
GAMyAssign.setColSorting('int,str,str,str,str,str,str,str');
GAMyAssign.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro');
GAMyAssign.enableSmartRendering(true, 200);
GAMyAssign.setSkin('dhx_skyblue');
GAMyAssign.init();
GAMyAssign.attachEvent('onRowDblClicked', function (rowId) {
    if (rowId) {
        LoadM('GAMYASSIGNDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(rowId));
    }
    return true;
});
GAMyAssign.clearAndLoad('hrd-ga-my-assignment-data');
</script>
@endsection
