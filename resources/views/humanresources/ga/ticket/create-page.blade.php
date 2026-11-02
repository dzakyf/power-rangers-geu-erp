@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-ticket-index') }}">Ticketing</a></li>
            <li class="breadcrumb-item active"><span class="active">Tiket Baru</span></li>
        </ol>
    </div>

    @include('humanresources.ga.ticket.create')
@endsection
