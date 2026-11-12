@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Procurement</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cns-procurement-goods-receipt.index') }}">Goods Receipt</a></li>
        <li class="breadcrumb-item active"><span class="active">{{ $gr->gr_no }}</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL GOODS RECEIPT (PENERIMAAN BARANG)</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            <i class="fa fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card w-100 mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-primary" style="font-size: 16px;">
                <i class="fa fa-file-text mr-1"></i> {{ $gr->gr_no }}
            </span>
            <div>
                @if($gr->status === 'DRAFT')
                    <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size: 13px;">STATUS: DRAFT</span>
                @elseif($gr->status === 'APPROVED')
                    <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">STATUS: APPROVED (STOK MASUK)</span>
                @elseif($gr->status === 'VOID')
                    <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 13px;">STATUS: VOID (DIBATALKAN)</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold text-muted" style="width: 170px;">No. Purchase Order</td>
                            <td>: <a href="{{ url('cns-procurement-purchase-order-detail?ID=' . $gr->po_no) }}" target="_blank" class="font-weight-bold">{{ $gr->po_no }}</a></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Supplier / Vendor</td>
                            <td>: {{ $supplier->Company_Name ?? ($po->Supplier ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Tanggal Penerimaan</td>
                            <td>: {{ $gr->receipt_date ? date('d/m/Y', strtotime($gr->receipt_date)) : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">No. Surat Jalan Vendor</td>
                            <td>: <span class="font-weight-bold">{{ $gr->delivery_note_no ?: '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Supir / No. Pol Truk</td>
                            <td>: {{ $gr->driver_name ?: '-' }} {{ $gr->vehicle_no ? ' / ' . $gr->vehicle_no : '' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold text-muted" style="width: 170px;">Petugas Penerima</td>
                            <td>: {{ $gr->received_by ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Dibuat Oleh</td>
                            <td>: {{ $gr->created_by ?: '-' }} ({{ $gr->created_at ? $gr->created_at->format('d/m/Y H:i') : '-' }})</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Disetujui Oleh</td>
                            <td>: {{ $gr->approved_by ?: '-' }} {{ $gr->approved_at ? '(' . $gr->approved_at->format('d/m/Y H:i') . ')' : '' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Bukti Dokumen / Foto</td>
                            <td>: 
                                @if($gr->attachment_path)
                                    <a href="{{ asset($gr->attachment_path) }}" target="_blank" class="btn btn-xs btn-outline-info">
                                        <i class="fa fa-paperclip mr-1"></i> Buka Lampiran Surat Jalan
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada lampiran</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Catatan Penerimaan</td>
                            <td>: {{ $gr->notes ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Item Diterima -->
    <div class="card w-100 mb-3">
        <div class="card-header bg-light font-weight-bold">
            <i class="fa fa-cubes mr-1"></i> Rincian Barang yang Diterima
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="bg-secondary text-white small text-center">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th>Nama Barang</th>
                            <th style="width: 150px;">Kategori</th>
                            <th style="width: 120px;">Qty Order</th>
                            <th style="width: 130px;" class="bg-primary text-white">Qty Diterima</th>
                            <th style="width: 85px;">Satuan</th>
                            <th>Keterangan Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gr->details as $idx => $item)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="font-weight-bold">{{ $item->item_name }}</td>
                                <td class="text-center">{{ $item->category ?: 'General' }}</td>
                                <td class="text-right text-muted">{{ number_format((float) $item->qty_ordered, 2, '.', ',') }}</td>
                                <td class="text-right font-weight-bold text-success" style="font-size: 14px;">
                                    {{ number_format((float) $item->qty_received, 2, '.', ',') }}
                                </td>
                                <td class="text-center">{{ $item->unit }}</td>
                                <td>{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Tidak ada data item barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($gr->status === 'APPROVED' && $gr->inventoryMutations && $gr->inventoryMutations->count() > 0)
        <!-- Mutasi Kartu Stok Tercipta -->
        <div class="card w-100 mb-3 border-success">
            <div class="card-header bg-success text-white font-weight-bold">
                <i class="fa fa-check mr-1"></i> Catatan Mutasi Kartu Stok (Inventory Non-UCO)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light small text-center">
                            <tr>
                                <th>Tanggal Mutasi</th>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Tipe</th>
                                <th>Qty Masuk</th>
                                <th>Saldo Akhir Kartu Stok</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gr->inventoryMutations as $mut)
                                <tr>
                                    <td class="text-center">{{ $mut->transaction_date ? date('d/m/Y', strtotime($mut->transaction_date)) : '-' }}</td>
                                    <td class="font-weight-bold">{{ $mut->item_name }}</td>
                                    <td class="text-center">{{ $mut->category }}</td>
                                    <td class="text-center"><span class="badge badge-success">IN</span></td>
                                    <td class="text-right font-weight-bold text-success">+{{ number_format((float) $mut->qty_in, 2, '.', ',') }} {{ $mut->unit }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format((float) $mut->current_balance, 2, '.', ',') }} {{ $mut->unit }}</td>
                                    <td>{{ $mut->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center w-100 mb-4">
        <a href="{{ route('cns-procurement-goods-receipt.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left mr-1"></i> Kembali ke Daftar GR
        </a>

        <div class="d-flex gap-2">
            @if($gr->status === 'APPROVED')
                <a href="{{ route('cns-procurement-goods-receipt.print', $gr->id) }}" target="_blank" class="btn btn-primary font-weight-bold mr-2">
                    <i class="fa fa-print mr-1"></i> Cetak Berita Acara Penerimaan (Slip)
                </a>

                @if($canApprove)
                    <button type="button" class="btn btn-outline-danger font-weight-bold" data-toggle="modal" data-target="#modalVoid">
                        <i class="fa fa-ban mr-1"></i> Void / Batalkan GR
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modal Void GR -->
<div class="modal fade" id="modalVoid" tabindex="-1" role="dialog" aria-labelledby="modalVoidLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('cns-procurement-goods-receipt.void', $gr->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalVoidLabel"><i class="fa fa-exclamation-triangle mr-1"></i> Konfirmasi Void Goods Receipt</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="font-weight-bold text-danger">PERHATIAN: Tindakan ini akan membatalkan transaksi Goods Receipt {{ $gr->gr_no }}.</p>
                    <p class="small text-muted">Sistem akan secara otomatis membuat transaksi pembalik (OUT) di kartu stok Inventory Non-UCO dan mengembalikan sisa kuantiti di PO terkait.</p>
                    <div class="form-group">
                        <label for="void_reason" class="font-weight-bold">Alasan Pembatalan (Void) <span class="text-danger">*</span></label>
                        <textarea name="reason" id="void_reason" class="form-control" rows="3" placeholder="Masukkan alasan pembatalan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Ya, Void Transaksi Ini</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
