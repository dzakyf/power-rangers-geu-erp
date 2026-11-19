@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Module &amp; Program Training</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Module &amp; Program</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'program'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Katalog Module &amp; Program Training</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="openProgramForm()">+ Tambah Program</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="editSelectedProgram()">Edit</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="refreshProgramGrid()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div id="ProgramGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div id="divProgramContainer" class="mt-3"></div>

<script>
    var ProgramGrid = new dhtmlXGridObject('ProgramGrid');
    ProgramGrid.setImagePath("./dhtmlx/imgs/");
    ProgramGrid.setHeader("No.,Kode Program,Nama Program,Kategori,Tipe,Metode,Passing Grade,Status");
    ProgramGrid.setInitWidths("50,140,260,150,100,100,110,100");
    ProgramGrid.setColAlign("center,left,left,left,center,center,center,center");
    ProgramGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    ProgramGrid.setColSorting("int,str,str,str,str,str,int,str");
    ProgramGrid.setSkin("dhx_skyblue");
    ProgramGrid.init();
    ProgramGrid.attachEvent("onRowDblClicked", function(id) {
        openProgramForm(id);
    });

    function refreshProgramGrid() {
        ProgramGrid.clearAndLoad('xml-hrd-training-program-index');
    }

    function openProgramForm(id) {
        var url = id ? ('hrd-training-program-form?id=' + id) : 'hrd-training-program-form';
        LoadM('divProgramContainer', url);
    }

    function editSelectedProgram() {
        var id = ProgramGrid.getSelectedId();
        if (!id) {
            alert('Pilih program terlebih dahulu.');
            return;
        }
        openProgramForm(id);
    }

    refreshProgramGrid();
</script>
@endsection
