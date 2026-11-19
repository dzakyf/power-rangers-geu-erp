@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Jadwal &amp; Request Training</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Jadwal Training</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'schedule'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Jadwal Training</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="newTrainingSchedule()">+ Buat Jadwal Training</button>
            <button type="button" class="btn btn-info btn-sm" onclick="openSelectedTraining()">Detail / Kurikulum / Peserta</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="closeSelectedTraining()">Close Training</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div id="TrainingLists" style="width:100%;height:340px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div id="divlistTraining" class="mt-3"></div>

<script>
    var TrainingLists = new dhtmlXGridObject('TrainingLists');
    TrainingLists.setImagePath("./dhtmlx/imgs/");
    TrainingLists.setHeader("No.,Task No,Nama Training,Batch,Tipe,Metode,Pemateri,Mulai,Selesai,KKM,Peserta,Status");
    TrainingLists.setInitWidths("45,140,220,95,90,90,130,95,95,70,70,120");
    TrainingLists.setColAlign("center,left,left,center,center,center,left,center,center,center,center,center");
    TrainingLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    TrainingLists.setColSorting("int,str,str,str,str,str,str,str,str,int,int,str");
    TrainingLists.setSkin("dhx_skyblue");
    TrainingLists.init();
    TrainingLists.attachEvent("onRowDblClicked", function(id) {
        LoadM('divlistTraining', 'hrd-training-detail?ID=' + id);
    });

    function refreshdata() {
        TrainingLists.clearAndLoad('xml-hrd-training-index');
    }

    function newTrainingSchedule() {
        var tnaId = new URLSearchParams(window.location.search).get('tna_id');
        var url = tnaId ? ('hrd-training-new?tna_id=' + encodeURIComponent(tnaId)) : 'hrd-training-new';
        LoadM('divlistTraining', url);
    }

    function openSelectedTraining() {
        var id = TrainingLists.getSelectedId();
        if (!id) {
            alert('Pilih jadwal training terlebih dahulu.');
            return;
        }
        LoadM('divlistTraining', 'hrd-training-detail?ID=' + id);
    }

    function closeSelectedTraining() {
        var id = TrainingLists.getSelectedId();
        if (!id) {
            alert('Pilih jadwal training terlebih dahulu.');
            return;
        }
        if (!confirm('Tutup (CLOSED) jadwal training ini?')) return;
        $.post('{{ url("hrd-training-close") }}', {
            _token: '{{ csrf_token() }}',
            ID: id
        }, function(res) {
            alert(res.message);
            refreshdata();
        });
    }

    refreshdata();
    @if(request()->filled('tna_id'))
        newTrainingSchedule();
    @endif
</script>
@endsection
