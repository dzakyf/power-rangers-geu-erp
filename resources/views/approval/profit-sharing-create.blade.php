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
    <h5>Buat pengajuan bagi hasil</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->
    <form id="transferMembershipForm" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mitra_name">Nama Calon Mitra Baru <span style="color: red;">*</span></label>
                    <input type="text" name="mitra_name" id="mitra_name" class="form-control" required />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mitra_name">Periode Bagi Hasil <span style="color: red;">*</span></label>
                    <input type="date" name="mitra_name" id="mitra_name" class="form-control" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label for="type">Jenis <span style="color: red;">*</span></label>
                <select name="type" id="type" class="form-control" required>
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
                    <label for="reason">Alasan <span style="color: red;">*</span></label>
                    <input type="text" name="reason" id="reason" class="form-control" required />
                </div>
            </div>
        </div> -->
        <div class="col-12">
            <!-- <div class="form-group">
            <label for="Hub_Type">Unduh Template Pengajuan<span style="color: red;">*</span></label>
            <div class="partner-profit-sharing-download">
                <a href="partner-profit-sharing-download">Download template</a>
            </div>
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

        </div>

        <!-- <button type="button" class="btn btn-primary" id="previewBtn">Preview Document</button> -->
        <button type="button" class="btn btn-primary" id="submitBtn" style="display:none;">Finalisasi Document</button>
    </form>

    <div class="form-group">
        <label for="File_Documents">Upload Tanda Tangan Pengajuan</label>
        {{-- <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div> --}}
        {{-- <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" /> --}}


        @php
        $id = request()->route('id') ?? $partner->id;
        @endphp

        @include('ui-components.signature-canvas', [
        'saveUrl' => "/geu/hcga/public/partner-profit-sharing-preview-application?id={$id}",
        'saveDocumentUrl' => "/geu/hcga/public/partner-profit-sharing-submit-application?id={$id}"]);
    </div>



</div>

<script>

</script>
@endsection