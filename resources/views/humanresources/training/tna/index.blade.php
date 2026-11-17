@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Training Need Analysis (TNA)</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">TNA</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'tna'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pengajuan TNA</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="openTnaForm()">+ Buat TNA Baru</button>
            <button type="button" class="btn btn-info btn-sm" onclick="viewTnaDetail()">Detail / Approval</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="refreshTnaGrid()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div id="TnaGrid" style="width:100%;height:340px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div id="divTnaContainer" class="mt-3"></div>

<script>
    var TnaGrid = new dhtmlXGridObject('TnaGrid');
    TnaGrid.setImagePath("./dhtmlx/imgs/");
    TnaGrid.setHeader("No.,No. TNA,Departemen,Tahun,Judul TNA,Jml Item,Diajukan Oleh,Status");
    TnaGrid.setInitWidths("50,150,130,70,260,80,130,130");
    TnaGrid.setColAlign("center,left,left,center,left,center,left,center");
    TnaGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    TnaGrid.setColSorting("int,str,str,str,str,int,str,str");
    TnaGrid.setSkin("dhx_skyblue");
    TnaGrid.init();
    TnaGrid.attachEvent("onRowDblClicked", function(id) {
        LoadM('divTnaContainer', 'hrd-tna-detail?id=' + id);
    });

    function refreshTnaGrid() {
        TnaGrid.clearAndLoad('xml-hrd-tna-index');
    }

    function openTnaForm(id) {
        var url = id ? ('hrd-tna-create?id=' + id) : 'hrd-tna-create';
        LoadM('divTnaContainer', url);
    }

    function viewTnaDetail() {
        var id = TnaGrid.getSelectedId();
        if (!id) {
            alert('Pilih baris TNA terlebih dahulu.');
            return;
        }
        LoadM('divTnaContainer', 'hrd-tna-detail?id=' + id);
    }

    refreshTnaGrid();
</script>
@endsection
