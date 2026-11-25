@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @include('humanresources.ga.ticket.partials.page-header', [
        'title' => 'Buat Tiket GA',
        'breadcrumbs' => [
            ['label' => 'Human Capital & GA', 'url' => '#'],
            ['label' => 'General Affairs', 'url' => '#'],
            ['label' => 'Ticketing', 'url' => url('hrd-ga-ticket-index')],
            ['label' => 'Tiket Baru'],
        ],
        'badgeIcon' => 'fa-plus-circle',
        'badgeLabel' => 'Formulir Pengajuan',
    ])

    @include('humanresources.ga.ticket.create')
@endsection
