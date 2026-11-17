@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Document Control</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('cns-offering-letter.index') }}">Offering Letter</a></li>
        <li class="breadcrumb-item active"><span class="active">{{ $letter->offer_no }}</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold">
            Detail Offering Letter: {{ $letter->offer_no }}
            @if($letter->status === \App\CnsOfferingLetter::STATUS_APPROVED)
                <span class="badge badge-success ml-2">APPROVED</span>
            @else
                <span class="badge badge-warning ml-2">DRAFT</span>
            @endif
        </span>
        <div>
            <a href="{{ route('cns-offering-letter.print', ['id' => $letter->id]) }}" target="_blank" class="btn btn-sm btn-secondary">Print / PDF</a>
            <a href="{{ route('cns-offering-letter.index') }}" class="btn btn-sm btn-light">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        @if($letter->status === \App\CnsOfferingLetter::STATUS_APPROVED)
            <div class="alert alert-info">
                Disetujui oleh <strong>{{ $letter->approved_by }}</strong> pada {{ $letter->approved_at ? $letter->approved_at->format('d F Y H:i') : '-' }}.
                Berlaku hingga <strong>{{ $letter->valid_until ? $letter->valid_until->format('d F Y') : '-' }}</strong>.
            </div>
        @endif

        <form action="{{ route('cns-offering-letter.update', ['id' => $letter->id]) }}" method="POST">
            @csrf
            @include('cns.offering-letter._form', ['defaults' => []])

            @if($letter->status === \App\CnsOfferingLetter::STATUS_DRAFT)
                <div class="mt-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary mr-2">Simpan Perubahan</button>
                </div>
            @endif
        </form>

        @if($letter->status === \App\CnsOfferingLetter::STATUS_DRAFT)
            <form action="{{ route('cns-offering-letter.approve', ['id' => $letter->id]) }}" method="POST" class="mt-2" onsubmit="return confirm('Approve Offering Letter ini? Offer Validity akan otomatis diset +14 hari kalender.');">
                @csrf
                <button type="submit" class="btn btn-success">Approve Offering Letter</button>
            </form>
        @endif
    </div>
</div>
@endsection
