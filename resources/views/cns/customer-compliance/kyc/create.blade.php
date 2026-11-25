@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Input Form KYC Baru',
    'breadcrumbs' => [
        ['label' => 'Customer Compliance'],
        ['label' => 'KYC', 'url' => route('cns-customer-compliance-kyc.index')],
        ['label' => 'Tambah Baru'],
    ],
])

<form action="{{ route('cns-customer-compliance-kyc.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @include('cns.customer-compliance.kyc._form')

    <div class="card cns-card mb-4">
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
