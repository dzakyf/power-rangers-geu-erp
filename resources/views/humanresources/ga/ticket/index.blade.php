@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">Ticketing</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-2">
                    <select id="GA_Filter_Status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE">Aktif</option>
                        <option value="OPEN">Open</option>
                        <option value="TRIAGED">Triaged</option>
                        <option value="ASSIGNED">Assigned</option>
                        <option value="IN_PROGRESS">In Progress</option>
                        <option value="ON_HOLD">On Hold</option>
                        <option value="RESOLVED">Resolved</option>
                        <option value="CLOSED">Closed</option>
                        <option value="REJECTED">Rejected</option>
                        <option value="REOPENED">Reopened</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GA_Filter_Priority" class="form-control form-control-sm">
                        <option value="">Semua Prioritas</option>
                        <option value="LOW">Low</option>
                        <option value="NORMAL">Normal</option>
                        <option value="HIGH">High</option>
                        <option value="URGENT">Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="GA_Filter_Category" class="form-control form-control-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->ID }}">{{ $category->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="GA_Filter_Search" class="form-control form-control-sm" placeholder="Cari no tiket / subjek / reporter" />
                </div>
                <div class="col-md-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="GATicketApplyFilter()">Terapkan Filter</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="GATicketResetFilter()">Reset</button>
                </div>
            </div>

            <div id="GATicket" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" class="btn btn-info btn-sm text-white" onclick="GATicketRefresh()">Refresh Data</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="GATicketNew()">Tiket Baru</button>
                <button type="button" class="btn btn-success btn-sm" onclick="GATicketDetail()">Detail / Tindak Lanjut</button>
                <button type="button" class="btn btn-warning btn-sm text-dark" onclick="GATicketScanSla()">Scan SLA</button>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-ticket-master-index') }}">Master Tiket</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-ga-qr-point-index') }}">QR Point</a>
            </div>
        </div>
    </div>

    <div id="GATICKETDTL1"></div>
    <div id="GATICKETDTL"></div>

<script>
function GATicketQuery() {
    var params = [];
    var status = document.getElementById('GA_Filter_Status').value;
    var priority = document.getElementById('GA_Filter_Priority').value;
    var category = document.getElementById('GA_Filter_Category').value;
    var search = document.getElementById('GA_Filter_Search').value;
    if (status) params.push('status=' + encodeURIComponent(status));
    if (priority) params.push('priority=' + encodeURIComponent(priority));
    if (category) params.push('category_id=' + encodeURIComponent(category));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function GATicketApplyFilter() {
    GATicket.clearAndLoad('hrd-ga-ticket-data' + GATicketQuery());
}

function GATicketResetFilter() {
    document.getElementById('GA_Filter_Status').value = '';
    document.getElementById('GA_Filter_Priority').value = '';
    document.getElementById('GA_Filter_Category').value = '';
    document.getElementById('GA_Filter_Search').value = '';
    GATicket.clearAndLoad('hrd-ga-ticket-data');
}

function GATicketRefresh() {
    GATicket.clearAndLoad('hrd-ga-ticket-data' + GATicketQuery());
}

function GATicketNew() {
    LoadM('GATICKETDTL1', 'hrd-ga-ticket-create?partial=1');
}

function GATicketDetail() {
    var id = GATicket.getSelectedRowId();
    if (!id) {
        alert('Pilih baris tiket terlebih dahulu.');
        return false;
    }
    LoadM('GATICKETDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
}

window.GATicketReloadDetail = function (id) {
    LoadM('GATICKETDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
};

window.GATicketRefreshGrid = function () {
    GATicketRefresh();
};

function GATicketScanSla() {
    $.post('hrd-ga-ticket-sla-scan', {_token: '{{ csrf_token() }}'}, function (response) {
        alert(response.message || 'Scan SLA selesai.');
        GATicketRefresh();
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Scan SLA gagal.');
    });
}

GATicket = new dhtmlXGridObject('GATicket');
GATicket.setImagePath('./dhtmlx/imgs/');
GATicket.setHeader('No,No Tiket,Subjek,Kategori,Prioritas,Status,Assignee,Response Due,Resolve Due,Dibuat,Reporter');
GATicket.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
GATicket.setInitWidths('50,150,220,130,80,100,140,140,140,140,140');
GATicket.setColAlign('center,center,left,left,center,center,left,center,center,center,left');
GATicket.setColSorting('int,str,str,str,str,str,str,str,str,str,str');
GATicket.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
GATicket.enableSmartRendering(true, 200);
GATicket.setSkin('dhx_skyblue');
GATicket.init();
GATicket.attachEvent('onRowDblClicked', function (rowId) {
    if (rowId) {
        LoadM('GATICKETDTL', 'hrd-ga-ticket-detail?partial=1&ID=' + encodeURIComponent(rowId));
    }
    return true;
});
GATicket.clearAndLoad('hrd-ga-ticket-data');
</script>
@endsection
