@extends('layout.mainlayout-mitra')
@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Proyeksi</span>
        </li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat proyeksi</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->

    <div class="col-12">
        <div class="form-group">
            <label for="Hub_Type">Template Proyeksi<span style="color: red;">*</span></label>
            <div class="">
                <a href="projection-template">Download template</a>
            </div>
        </div>
        <!-- <div class="form-group">
            <label for="Hub_Type">Tipe Hub <span style="color: red;">*</span></label>
            <select name="Hub_Type" id="Hub_Type" class="form-control" required>
                <option value="">-- Pilih Jenis--</option>
                <option value="booth">Booth</option>
                <option value="mobil" disabled>Mobil</option>
            </select>
        </div> -->
        <!-- <div class="form-group">
            <label for="Type">Tipe Proyeksi <span style="color: red;">*</span></label>
            <select name="Type" id="Type" class="form-control" required>
                <option value="">-- Pilih Jenis--</option>
                <option value="low">Rendah</option>
                <option value="high">Tinggi</option>
            </select>
        </div> -->
        <!-- <div class="form-group">
            <label for="Year">Tahun Proyeksi<span style="color: red;">*</span></label>
            <select name="Year" id="Year" class="form-control" required>
                <option value="">-- Pilih Jenis--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div> -->
        <div class="form-group">
            <label for="File_Documents">Upload Proyeksi</label>
            <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
        </div>
    </div>

</div>

<script>
    let type = null;
    let year = null;
    let userId = null;

    $("#Type, #Year").on('change', function() {
        $("#File_Documents").data('type', $("#Type").val());
        $("#File_Documents").data('year', $("#Year").val());
    });

    var vault = new dhx.Vault("File_Documents", {
        uploader: {
            target: "projection-upload",
            params: {
                '_token': '{{ csrf_token() }}',
                'type': $("#Type").val(),
                'year': $('#Year').val()
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('id');
        console.log(userId)
        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.type = $("#Type").val();
            vault.uploader.config.params.year = $("#Year").val();
            vault.uploader.config.params.user_id = userId;
            vault.uploader.config.params.hub_type = $("#Hub_Type").val();
        }
    });
</script>
@endsection