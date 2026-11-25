@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                My IT Assignments
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('hrd-it-ticket-index') }}" style="color: rgba(255, 255, 255, 0.8) !important;">IT Support</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">My IT Assignments</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a class="btn btn-outline-light btn-sm text-light" href="{{ url('hrd-it-ticket-index') }}">
                <!-- i class="fa fa-arrow-left me-1"></i --> Dashboard IT Support
            </a>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="ITMyAssign" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" class="btn btn-info btn-sm text-white" onclick="ITMyAssignRefresh()">Refresh Data</button>
                <button type="button" class="btn btn-success btn-sm" onclick="ITMyAssignDetail()">Detail / Tindak Lanjut</button>
            </div>
        </div>
    </div>

    <div id="ITMYASSIGNDTL"></div>

<script>
function ITMyAssignRefresh() {
    ITMyAssign.clearAndLoad('hrd-it-my-assignment-data');
}

function ITMyAssignDetail() {
    var id = ITMyAssign.getSelectedRowId();
    if (!id) {
        alert('Pilih baris tiket IT terlebih dahulu.');
        return false;
    }
    LoadM('ITMYASSIGNDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
}

window.ITTicketReloadDetail = function (id) {
    LoadM('ITMYASSIGNDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
};
window.ITTicketRefreshGrid = function () {
    ITMyAssign.clearAndLoad('hrd-it-my-assignment-data');
};

ITMyAssign = new dhtmlXGridObject('ITMyAssign');
ITMyAssign.setImagePath('./dhtmlx/imgs/');
ITMyAssign.setHeader('No,No Tiket,Tipe Kebutuhan,Action Type,Subjek,Prioritas,Status,Resolve Due,Dibuat');
ITMyAssign.attachHeader('&nbsp;,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter');
ITMyAssign.setInitWidths('50,150,145,105,250,90,145,140,140');
ITMyAssign.setColAlign('center,center,center,center,left,center,center,center,center');
ITMyAssign.setColSorting('int,str,str,str,str,str,str,str,str');
ITMyAssign.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro');
ITMyAssign.enableSmartRendering(true, 200);
ITMyAssign.setSkin('dhx_skyblue');
ITMyAssign.init();
ITMyAssign.attachEvent('onRowDblClicked', function (rowId) {
    if (rowId) {
        LoadM('ITMYASSIGNDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(rowId));
    }
    return true;
});
ITMyAssign.clearAndLoad('hrd-it-my-assignment-data');
</script>
@endsection
