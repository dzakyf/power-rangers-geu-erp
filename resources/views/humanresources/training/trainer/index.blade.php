@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Master Pemateri / Trainer</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">HRD</a></li>
        <li class="breadcrumb-item active"><span class="active">Master Pemateri</span></li>
    </ol>
</div>

@include('humanresources.training._nav', ['activeTab' => 'trainer'])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pemateri / Trainer Training</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="openTrainerForm()">+ Tambah Pemateri</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="editSelectedTrainer()">Edit</button>
            <button type="button" class="btn btn-info btn-sm" onclick="toggleSelectedTrainer()">Aktif / Nonaktifkan</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="refreshTrainerGrid()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div id="TrainerGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div id="divTrainerContainer" class="mt-3"></div>

<script>
    var TrainerGrid = new dhtmlXGridObject('TrainerGrid');
    TrainerGrid.setImagePath("./dhtmlx/imgs/");
    TrainerGrid.setHeader("No.,Nama Pemateri,Tipe,Instansi / Perusahaan,Email,No. HP,Sertifikasi,Status");
    TrainerGrid.setInitWidths("50,220,100,180,170,130,170,90");
    TrainerGrid.setColAlign("center,left,center,left,left,left,left,center");
    TrainerGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    TrainerGrid.setColSorting("int,str,str,str,str,str,str,str");
    TrainerGrid.setSkin("dhx_skyblue");
    TrainerGrid.init();
    TrainerGrid.attachEvent("onRowDblClicked", function(id) {
        openTrainerForm(id);
    });

    function refreshTrainerGrid() {
        TrainerGrid.clearAndLoad('xml-hrd-training-trainer-index');
    }

    function openTrainerForm(id) {
        var url = id ? ('hrd-training-trainer-form?id=' + id) : 'hrd-training-trainer-form';
        LoadM('divTrainerContainer', url);
    }

    function editSelectedTrainer() {
        var id = TrainerGrid.getSelectedId();
        if (!id) {
            alert('Pilih pemateri terlebih dahulu.');
            return;
        }
        openTrainerForm(id);
    }

    function toggleSelectedTrainer() {
        var id = TrainerGrid.getSelectedId();
        if (!id) {
            alert('Pilih pemateri terlebih dahulu.');
            return;
        }
        if (!confirm('Ubah status aktif/nonaktif pemateri ini?')) return;
        $.post('{{ url("hrd-training-trainer-toggle") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }, function(res) {
            alert(res.message || 'Status diperbarui.');
            refreshTrainerGrid();
        }).fail(function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengubah status.');
        });
    }

    refreshTrainerGrid();
</script>
@endsection
