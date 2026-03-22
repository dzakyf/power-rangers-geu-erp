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
            <span class="active">Approval</span>
        </li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat Pengajuan Transfer Kemitraan</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->

    <div class="col-12">
        {{-- <div class="form-group">
            <label for="Hub_Type">Unduh Template Pengajuan<span style="color: red;">*</span></label>
            <div class="partner-profit-sharing-download">
                <a href="partner-renewal-download">Download template</a>
            </div>
        </div> --}}
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
        {{-- <div class="form-group">
            <label for="File_Documents">Upload Pengajuan</label>
            <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
        </div> --}}
        <form id="transferMembershipForm" enctype="multipart/form-data">
            @csrf
 <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="New_Mitra_Name">Nama Calon Mitra Baru <span style="color: red;">*</span></label>
                        <input type="text" name="New_Mitra_Name" id="New_Mitra_Name" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="Type">Jenis <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="Type" id="Type" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="1">Pengalihan Kepemilikan</option>
                        <option value="2">Pengalihan Bagi Hasil</option>
                        <option value="3">Penggantian Pengelola</option>
                    </select>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="Reason">Alasan <span style="color: red;">*</span></label>
                        <input type="text" name="Reason" id="Reason" class="form-control" required />
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
        </form>
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
            target: "partner-termination-submit-application",
            params: {
                '_token': '{{ csrf_token() }}',
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('id');

        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.type = $("#Type").val();
            vault.uploader.config.params.year = $("#Year").val();
            vault.uploader.config.params.user_id = userId;
            vault.uploader.config.params.hub_type = $("#Hub_Type").val();
        }
    });
</script>
@endsection