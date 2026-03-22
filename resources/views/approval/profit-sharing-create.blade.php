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

    <div class="col-12">
        <div class="form-group">
            <label for="Hub_Type">Unduh Template Pengajuan<span style="color: red;">*</span></label>
            <div class="partner-profit-sharing-download">
                <a href="partner-profit-sharing-download">Download template</a>
            </div>
        </div>
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
            <label for="File_Documents">Upload Tanda Tangan Pengajuan</label>
            {{-- <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div> --}}
            {{-- <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" /> --}}

            @php
            $id = request()->route('id') ?? null;
            @endphp

            @include('ui-components.signature-canvas', [
            'saveUrl' => "/geu/hcga/public/partner-profit-sharing-preview-application?id={$id}",
            'saveDocumentUrl' => "/geu/hcga/public/partner-profit-sharing-submit-application?id={$id}"]);
        </div>
    </div>

</div>

<script>
   
</script>
@endsection