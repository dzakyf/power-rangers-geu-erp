@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Input Form KYC Baru</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Contract</a></li>
        <li class="breadcrumb-item">Customer Compliance</li>
        <li class="breadcrumb-item"><a href="{{ route('cns-customer-compliance-kyc.index') }}">KYC</a></li>
        <li class="breadcrumb-item active"><span class="active">Tambah Baru</span></li>
    </ol>
</div>

<form action="{{ route('cns-customer-compliance-kyc.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @include('cns.customer-compliance.kyc._form')

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between">
            <a href="{{ route('cns-customer-compliance-kyc.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Simpan Form KYC
            </button>
        </div>
    </div>
</form>
@endsection
