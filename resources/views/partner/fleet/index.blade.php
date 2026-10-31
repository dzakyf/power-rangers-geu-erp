@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Fleet</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FLEET</div>
    <div class="card">
        <div class="card-body">
            <div id="FleetGrid" style="width:auto;height:320px;background-color:white;overflow:hidden" class="mb-2"></div>
            <div>
                <input type="button" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="window.location.href='master-fleet-create'" />
                <input type="button" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="FleetGrid.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>

<script>
    var selectedId = null;

    $(document).ready(function() {
        initGrid();
    });

    function initGrid() {
        let tHeader = ['No', null, 'Police Number', 'Warehouse', 'Fleet Brand', 'Description', 'Status', 'Dibuat Pada'];
        let tWidth = tHeader.map((e, i) => i === 1 ? 0 : '150');
        let tFilter = tHeader.map((e, i) => i <= 1 ? '#rspan' : '#text_filter');

        FleetGrid = new dhtmlXGridObject('FleetGrid');
        FleetGrid.setImagePath('./dhtmlx/imgs/');
        FleetGrid.setHeader(tHeader.join(','));
        FleetGrid.setInitWidths(tWidth.join(','));
        FleetGrid.attachHeader(tFilter);
        FleetGrid.setColTypes(tHeader.map(() => 'ro').join(','));
        FleetGrid.enableSmartRendering(true, 150);
        FleetGrid.attachEvent('onRowSelect', function(id) {
            selectedId = FleetGrid.getRowAttribute(id, 'ID');
        });
        FleetGrid.setSkin('dhx_skyblue');
        FleetGrid.init();
        FleetGrid.clearAndLoad('master-fleet-data?format=xml');
    }
</script>
@endsection