@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">Work Order</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-2">
                    <select id="GAWO_Filter_Status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="DRAFT">Draft</option>
                        <option value="OPEN">Open</option>
                        <option value="WAITING_APPROVAL">Waiting Approval</option>
                        <option value="APPROVED">Approved</option>
                        <option value="REJECTED">Rejected</option>
                        <option value="ASSIGNED">Assigned</option>
                        <option value="IN_PROGRESS">In Progress</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="CANCELLED">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="GAWO_Filter_Search" class="form-control form-control-sm" placeholder="Cari WO / tiket / judul" />
                </div>
                <div class="col-md-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GAWOApplyFilter()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GAWOResetFilter()">Reset</button>
                </div>
            </div>

            <div id="GAWorkOrder" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" class="btn btn-info btn-sm text-white" onclick="GAWORefresh()">Refresh Data</button>
                @if (can_menu('SI01010', 'create'))
                    <button type="button" class="btn btn-primary btn-sm" onclick="GAWONew()">Work Order Baru</button>
                @endif
                <button type="button" class="btn btn-success btn-sm" onclick="GAWODetail()">Detail / Tindak Lanjut</button>
            </div>
        </div>
    </div>

    <div id="GAWODTL"></div>

<script>
function GAWOQuery() {
    var params = [];
    var status = document.getElementById('GAWO_Filter_Status').value;
    var search = document.getElementById('GAWO_Filter_Search').value;
    if (status) params.push('status=' + encodeURIComponent(status));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GAWOApplyFilter() {
    GAWorkOrder.clearAndLoad('hrd-ga-work-order-data' + GAWOQuery());
}

function GAWOResetFilter() {
    document.getElementById('GAWO_Filter_Status').value = '';
    document.getElementById('GAWO_Filter_Search').value = '';
    GAWorkOrder.clearAndLoad('hrd-ga-work-order-data');
}

function GAWORefresh() {
    GAWorkOrder.clearAndLoad('hrd-ga-work-order-data' + GAWOQuery());
}

function GAWONew() {
    LoadM('GAWODTL', 'hrd-ga-work-order-create');
}

function GAWODetail() {
    var id = GAWorkOrder.getSelectedRowId();
    if (!id) {
        alert('Pilih baris work order terlebih dahulu.');
        return false;
    }
    LoadM('GAWODTL', 'hrd-ga-work-order-detail?ID=' + encodeURIComponent(id));
}

window.GAWOReloadDetail = function (id) {
    LoadM('GAWODTL', 'hrd-ga-work-order-detail?ID=' + encodeURIComponent(id));
};
window.GAWORefreshGrid = function () {
    GAWorkOrder.clearAndLoad('hrd-ga-work-order-data' + GAWOQuery());
};

GAWorkOrder = new dhtmlXGridObject('GAWorkOrder');
GAWorkOrder.setImagePath('./dhtmlx/imgs/');
GAWorkOrder.setHeader('No,No WO,No Tiket,Judul,Assignee,Status,Vendor Cost,Total Cost,Planned Start,Planned End,Dibuat');
GAWorkOrder.attachHeader('&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
GAWorkOrder.setInitWidths('50,170,140,240,150,120,110,110,110,110,140');
GAWorkOrder.setColAlign('center,center,center,left,left,center,right,right,center,center,center');
GAWorkOrder.setColSorting('int,str,str,str,str,str,str,str,str,str,str');
GAWorkOrder.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
GAWorkOrder.enableSmartRendering(true, 200);
GAWorkOrder.setSkin('dhx_skyblue');
GAWorkOrder.init();
GAWorkOrder.attachEvent('onRowDblClicked', function (rowId) {
    if (rowId) {
        LoadM('GAWODTL', 'hrd-ga-work-order-detail?ID=' + encodeURIComponent(rowId));
    }
    return true;
});
GAWorkOrder.clearAndLoad('hrd-ga-work-order-data');
</script>
@endsection
