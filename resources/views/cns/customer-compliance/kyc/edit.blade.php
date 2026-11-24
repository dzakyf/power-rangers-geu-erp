@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Edit Form KYC: ' . $kyc->kyc_no,
    'breadcrumbs' => [
        ['label' => 'Customer Compliance'],
        ['label' => 'KYC', 'url' => route('cns-customer-compliance-kyc.index')],
        ['label' => $kyc->kyc_no, 'url' => route('cns-customer-compliance-kyc.detail', ['id' => $kyc->id])],
        ['label' => 'Edit'],
    ],
])

<form action="{{ route('cns-customer-compliance-kyc.update', ['id' => $kyc->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf

    @include('cns.customer-compliance.kyc._form')

    <div class="card cns-card mb-4">
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
