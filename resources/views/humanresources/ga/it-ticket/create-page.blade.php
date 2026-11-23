@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>IT Support &amp; Helpdesk</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('hrd-it-ticket-index') }}">IT Support</a></li>
            <li class="breadcrumb-item active"><span class="active">Tiket IT Baru</span></li>
        </ol>
    </div>

    @include('humanresources.ga.it-ticket.create')
@endsection
