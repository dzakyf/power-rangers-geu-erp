@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Buat Offering Letter Baru',
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Pricing & Quotation'],
        ['label' => 'Offering Letter', 'url' => route('cns-offering-letter.index')],
        ['label' => 'New Offering Letter'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Buat Offering Letter Baru</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('cns-offering-letter.store') }}" method="POST">
            @csrf
            @include('cns.offering-letter._form')

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan (Draft)</button>
                <a href="{{ route('cns-offering-letter.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
