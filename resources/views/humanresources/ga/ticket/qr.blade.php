@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">QR Tiket</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            @if ($point)
                <div class="alert alert-success">
                    <h5 class="mb-1">{{ $point->Name }}</h5>
                    <div>Kode: <strong>{{ $point->QR_Code }}</strong></div>
                    <div>Lokasi: {{ $point->Building ?: '-' }} / {{ $point->Floor ?: '-' }} / {{ $point->Room ?: '-' }}</div>
                </div>
                <a class="btn btn-primary" href="{{ url('hrd-ga-ticket-create?qr_point_id=' . $point->ID) }}">Buat Tiket di Lokasi Ini</a>
            @else
                <div class="alert alert-warning">
                    QR code {{ $code ?: '-' }} tidak dikenali atau tidak aktif. Anda tetap dapat membuat tiket secara manual.
                </div>
                <a class="btn btn-primary" href="{{ url('hrd-ga-ticket-create') }}">Buat Tiket Manual</a>
            @endif

            <hr />
            <form method="get" action="{{ url('hrd-ga-ticket-qr') }}" class="d-flex flex-wrap align-items-center gap-2">
                <label class="mb-0 fw-bold">Input kode QR manual</label>
                <input type="text" name="code" value="{{ $code }}" class="form-control" style="width: auto; min-width: 240px;" placeholder="Contoh: GA-HO-LT1-TOILET" />
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
            <p class="text-muted mt-3 mb-0">Fallback manual disediakan jika kamera/QR tidak dapat digunakan.</p>
        </div>
    </div>
@endsection
