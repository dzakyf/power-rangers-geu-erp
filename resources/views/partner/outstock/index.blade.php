@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Outstock</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OUTSTOCK</div>
    <div class="card">
        <div class="card-body">
            <div id="OutstockGrid" style="width:auto;height:320px;background-color:white;overflow:hidden" class="mb-2"></div>
            <div>
                <input type="button" value="Buat Draft" class="btn btn-success mt-2 mr-2" onclick="window.location.href='partner-outstock-create'" />
                <input type="button" value="Detail" class="btn btn-info mt-2 mr-2" id="btnDetail" />
                <input type="button" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="OutstockGrid.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>

<script>
    var OutstockGrid;
    var selectedId = null;

    $(document).ready(function() {
        initGrid();

        $('#btnDetail').on('click', function() {
            if (!selectedId) {
                alert('Pilih data terlebih dulu');
                return;
            }
            window.location.href = 'partner-outstock-detail?id=' + selectedId;
        });
    });

    function initGrid() {
        OutstockGrid = new dhtmlXGridObject('OutstockGrid');
        OutstockGrid.setImagePath('./dhtmlx/imgs/');
        OutstockGrid.setHeader("No, Code, Tanggal, Warehouse Asal, Transfer Price, Fleet, Status");
        OutstockGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        OutstockGrid.setInitWidths("60,120,100,*,130,150,100");
        OutstockGrid.setColAlign("center,left,left,left,right,left,left");
        OutstockGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
        OutstockGrid.setSkin("dhx_skyblue");
        OutstockGrid.attachEvent("onRowSelect", function(id) {
            selectedId = OutstockGrid.getRowAttribute(id, 'ID');
        });
        OutstockGrid.init();
        OutstockGrid.load('partner-outstock-data?format=xml');
    }
</script>
@endsection
