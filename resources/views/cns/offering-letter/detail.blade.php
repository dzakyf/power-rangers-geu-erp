@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Detail Offering Letter: ' . $letter->offer_no,
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Pricing & Quotation'],
        ['label' => 'Offering Letter', 'url' => route('cns-offering-letter.index')],
        ['label' => $letter->offer_no],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">
            Detail Offering Letter: {{ $letter->offer_no }}
            @if($letter->status === \App\CnsOfferingLetter::STATUS_APPROVED)
                <span class="badge rounded-pill cns-status-badge bg-success ml-2">APPROVED</span>
            @else
                <span class="badge rounded-pill cns-status-badge bg-warning text-dark ml-2">DRAFT</span>
            @endif
        </h5>
        <div>
            <a href="{{ route('cns-offering-letter.print', ['id' => $letter->id]) }}" target="_blank" class="btn btn-sm btn-secondary">Print / PDF</a>
            <a href="{{ route('cns-offering-letter.index') }}" class="btn btn-sm btn-light">Kembali</a>
        </div>
    </div>
    <div class="card-body">
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
            <form action="{{ route('cns-offering-letter.approve', ['id' => $letter->id]) }}" method="POST" class="mt-2" onsubmit="var f=this; if(f.dataset.confirmed==='1'){return true;} swal({title:'Konfirmasi',text:'Approve Offering Letter ini? Offer Validity akan otomatis diset +14 hari kalender.',icon:'warning',buttons:['Batal','Ya']}).then(function(ok){if(ok){f.dataset.confirmed='1';f.submit();}}); return false;">
                @csrf
                <button type="submit" class="btn btn-success">Approve Offering Letter</button>
            </form>
        @endif
    </div>
</div>
@endsection
