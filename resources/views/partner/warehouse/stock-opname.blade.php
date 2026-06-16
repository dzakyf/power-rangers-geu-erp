@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Warehouse</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Warehouse</a></li>
        <li class="breadcrumb-item active"><span class="active">Stock Opname</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <h5>Stock Opname</h5>
            </div>
            <div class="col-md-auto ml-auto">
                <select id="statusFilter" class="form-control form-control-sm" style="width:160px;" onchange="applyFilter()">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT">Draft</option>
                    <option value="COUNTED">Counted</option>
                    <option value="ADJUSTED">Adjusted</option>
                    <option value="CLOSED">Closed</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="opnameGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
    <div class="m-3">
        <input type="button" value="Buat Opname" class="btn btn-success" onclick="redirectToCreate()" />
        <input type="button" id="detailBtn" value="Detail" class="btn btn-info" onclick="openDetail()" />
        <input type="button" value="Export Excel" class="btn btn-warning" onclick="opnameGrid.toExcel('./export/generate.php');" />
    </div>
</div>


<script>
    var opnameGrid;
    var selectedOpnameId = null;

    $(document).ready(function() {
        initGrid();

        $(window).on('resize', function() {
            if (opnameGrid) opnameGrid.setSizes();
        });
    });

    function initGrid() {
        opnameGrid = new dhtmlXGridObject('opnameGrid');
        opnameGrid.setImagePath("./dhtmlx/imgs/");
        opnameGrid.setHeader("No,Opname Number,Kode Hub,Lokasi,System Qty,Physical Qty,Difference,Status,Dibuat Pada");
        opnameGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#rspan");
        opnameGrid.setInitWidths("50,120,100,*,100,100,100,100,120");
        opnameGrid.setColAlign("center,left,left,left,right,right,right,center,left");
        opnameGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        opnameGrid.setSkin("dhx_skyblue");
        opnameGrid.attachEvent("onRowSelect", function(id) {
            selectedOpnameId = opnameGrid.getRowAttribute(id, 'ID');
        });
        opnameGrid.attachEvent("onRowDblClick", function(id) {
            selectedOpnameId = opnameGrid.getRowAttribute(id, 'ID');
            openDetail();
        });
        opnameGrid.init();
        loadData();
    }

    function loadData() {
        var url = 'partner-warehouse-stock-opname-data?format=xml';
        var status = $('#statusFilter').val();
        if (status) url += '&status=' + status;
        opnameGrid.load(url);
    }

    function applyFilter() {
        loadData();
    }

    function redirectToCreate() {
        window.location.href = 'partner-warehouse-stock-opname-new';
    }

    function openDetail() {
        if (!selectedOpnameId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        window.location.href = 'partner-warehouse-stock-opname-detail?ID=' + selectedOpnameId;
    }
</script>
@endsection
