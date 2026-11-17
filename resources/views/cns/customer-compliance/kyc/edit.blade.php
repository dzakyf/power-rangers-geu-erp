@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Edit Form KYC: {{ $kyc->kyc_no }}</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Contract</a></li>
        <li class="breadcrumb-item">Customer Compliance</li>
        <li class="breadcrumb-item"><a href="{{ route('cns-customer-compliance-kyc.index') }}">KYC</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cns-customer-compliance-kyc.detail', ['id' => $kyc->id]) }}">{{ $kyc->kyc_no }}</a></li>
        <li class="breadcrumb-item active"><span class="active">Edit</span></li>
    </ol>
</div>

<form action="{{ route('cns-customer-compliance-kyc.update', ['id' => $kyc->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf

    @include('cns.customer-compliance.kyc._form')

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between">
            <a href="{{ route('cns-customer-compliance-kyc.detail', ['id' => $kyc->id]) }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Batal / Kembali ke Detail
            </a>
            <button type="submit" class="btn btn-warning">
                <i class="fa fa-save"></i> Perbarui Form KYC
            </button>
        </div>
    </div>
</form>
@endsection
