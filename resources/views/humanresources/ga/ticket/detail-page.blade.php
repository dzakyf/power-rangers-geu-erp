@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @include('humanresources.ga.ticket.partials.page-header', [
        'title' => 'Detail Tiket GA',
        'breadcrumbs' => [
            ['label' => 'Human Capital & GA', 'url' => '#'],
            ['label' => 'General Affairs', 'url' => '#'],
            ['label' => 'Ticketing', 'url' => url('hrd-ga-ticket-index')],
            ['label' => 'Detail Tiket'],
        ],
        'badgeIcon' => 'fa-file-alt',
        'badgeLabel' => 'Monitoring & Tindak Lanjut',
    ])

    @include('humanresources.ga.ticket.detail')
@endsection
