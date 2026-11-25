<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$nama = Auth::user()->Name ?? '';

$quickLinks = [
    ['slug' => 'export_volume', 'label' => 'Export Volume', 'desc' => 'Ringkasan volume & KPI ekspor', 'url' => url('cns-dashboard-export-volume'), 'icon' => 'fe fe-bar-chart-2'],
    ['slug' => 'offering_letter', 'label' => 'Offering Letter', 'desc' => 'Dokumen penawaran kontrak', 'url' => url('cns-offering-letter'), 'icon' => 'fe fe-file-text'],
    ['slug' => 'sales_order', 'label' => 'Sales Order', 'desc' => 'Daftar order penjualan CNS', 'url' => url('cns-sales-order'), 'icon' => 'fe fe-shopping-bag'],
    ['slug' => 'purchase_order', 'label' => 'Purchase Order', 'desc' => 'Pengadaan & PO modul CNS', 'url' => url('cns-procurement-purchase-order'), 'icon' => 'fe fe-clipboard'],
    ['slug' => 'inbound_movement', 'label' => 'Inventory Tracking', 'desc' => 'Pantau stok & pergerakan tangki', 'url' => url('cns-inventory-tracking-index'), 'icon' => 'fe fe-database'],
    ['slug' => 'shipping_instruction', 'label' => 'Shipping Instruction', 'desc' => 'Instruksi pengapalan ekspor', 'url' => url('cns-export-shipping-instruction'), 'icon' => 'fe fe-anchor'],
];

$visibleLinks = array_values(array_filter($quickLinks, function ($item) {
    return !function_exists('cns_can') || cns_can($item['slug'], 'view');
}));

?>
@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', ['title' => 'Dashboard CNS', 'breadcrumbs' => []])

<div class="card cns-card">
    <div class="card-header">
        <div>
            <h5 class="card-title mb-1">Selamat Datang{{ $nama ? ', ' . $nama : '' }}</h5>
            <div class="text-muted small">Commercial &amp; Sustainability (CNS) Module</div>
        </div>
        @if ($LSP)
            <span class="badge bg-primary-transparent text-primary px-3 py-2">Lokasi: {{ $LSP }}</span>
        @endif
    </div>
    <div class="card-body">
        @if (count($visibleLinks) > 0)
            <div class="text-muted small mb-3">Akses cepat ke menu utama CNS:</div>
            <div class="row g-3">
                @foreach ($visibleLinks as $link)
                    <div class="col-xl-4 col-md-6">
                        <a href="{{ $link['url'] }}" class="d-flex align-items-center p-3 border rounded text-decoration-none text-dark h-100 bg-light">
                            <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-transparent text-primary me-3 flex-shrink-0" style="width: 42px; height: 42px; font-size: 1.1rem;">
                                <i class="{{ $link['icon'] }}"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">{{ $link['label'] }}</div>
                                <div class="text-muted small">{{ $link['desc'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">Gunakan menu navigasi di sidebar kiri untuk mengakses sub-modul CNS sesuai hak akses Anda.</p>
        @endif
    </div>
</div>
@endsection