@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>PROCUREMENT DASHBOARD</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard1">Home</a></li>
            <li class="breadcrumb-item active"><span>Procurement Overview</span></li>
        </ol>
    </div>

    <!-- KONTROL / TOMBOL NAVIGASI CEPAT -->
    <div class="mb-3">
        <a href="{{ url('purchase-request-list') }}" class="btn btn-primary btn-sm font-weight-bold mr-2 shadow-sm">
            <i class="fas fa-list mr-1"></i> Kelola Purchase Request (PR)
        </a>
        <a href="{{ url('purchase-request-create') }}" class="btn btn-success btn-sm font-weight-bold shadow-sm">
            <i class="fas fa-plus mr-1"></i> Buat PR Baru
        </a>
    </div>

    <!-- KOTAK STATISTIK (WIDGET CARDS STANDAR ADMINLTE) -->
    <div class="row">
        <!-- Card 1: Master Produk -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalProduct) }}</h3>
                    <p>Master Produk</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <a href="{{ url('product-list') }}" class="small-box-footer">
                    Kelola Produk <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: Data Supplier / Vendor -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalSupplier) }}</h3>
                    <p>Data Supplier / Vendor</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
                <a href="{{ url('supplier-list') }}" class="small-box-footer">
                    Kelola Supplier <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 3: Total Purchase Request -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner text-dark">
                    <h3>{{ number_format($totalPR) }}</h3>
                    <p class="font-weight-bold">Total Purchase Request</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <a href="{{ url('purchase-request-list') }}" class="small-box-footer text-dark">
                    Lihat List PR <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 4: Approved / Pending PR -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $approvedPR }} <sup style="font-size: 14px">/ {{ $pendingPR }} Pending</sup></h3>
                    <p>Approved / Pending PR</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ url('purchase-request-list') }}" class="small-box-footer">
                    Cek Status Flow <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- INFORMASI TAMBAHAN / RINGKASAN STATUS -->
    <div class="row">
        <!-- Tabel Ringkasan Status -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark py-2">
                    <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                        <i class="fas fa-chart-pie mr-1 text-warning"></i> Ringkasan Status Dokumen PR
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped table-sm mb-0 align-middle" style="font-size: 12px;">
                        <tbody>
                            <tr>
                                <td class="pl-3 py-3">
                                    <a href="{{ url('purchase-request-list') }}" class="text-dark font-weight-bold d-block">
                                        <i class="fas fa-clock text-warning mr-2"></i> Status PENDING (Menunggu Review)
                                    </a>
                                </td>
                                <td class="text-right pr-3 align-middle">
                                    <span class="badge badge-warning p-1 px-2 font-weight-bold" style="font-size: 11px;">{{ $pendingPR }} Dokumen</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="pl-3 py-3">
                                    <a href="{{ url('purchase-request-list') }}" class="text-dark font-weight-bold d-block">
                                        <i class="fas fa-check-circle text-success mr-2"></i> Status APPROVED (Disetujui / Lanjut RFQ)
                                    </a>
                                </td>
                                <td class="text-right pr-3 align-middle">
                                    <span class="badge badge-success p-1 px-2 font-weight-bold" style="font-size: 11px;">{{ $approvedPR }} Dokumen</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="pl-3 py-3">
                                    <a href="{{ url('purchase-request-list') }}" class="text-dark font-weight-bold d-block">
                                        <i class="fas fa-times-circle text-danger mr-2"></i> Status REJECTED (Ditolak)
                                    </a>
                                </td>
                                <td class="text-right pr-3 align-middle">
                                    <span class="badge badge-danger p-1 px-2 font-weight-bold" style="font-size: 11px;">{{ $rejectedPR }} Dokumen</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel Informasi Modul ERP Procurement -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary py-2">
                    <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
                        <i class="fas fa-info-circle mr-1"></i> Informasi Sistem ERP Procurement
                    </h6>
                </div>
                <div class="card-body" style="font-size: 12px; line-height: 1.6;">
                    <p class="mb-2 text-muted">Modul ERP PT. Green Energi Utama dirancang untuk mengintegrasikan alur pengadaan barang secara modular:</p>
                    <ul class="pl-3 mb-0">
                        <li><strong>PR (Purchase Request):</strong> Pengajuan kebutuhan material oleh departemen.</li>
                        <li><strong>RFQ (Request for Quotation):</strong> Permintaan penawaran harga ke vendor.</li>
                        <li><strong>QTN (Quotation Matrix):</strong> Perbandingan harga penawaran terendah hingga tertinggi.</li>
                        <li><strong>PO (Purchase Order):</strong> Penerbitan pesanan pembelian final ke vendor terpilih.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection