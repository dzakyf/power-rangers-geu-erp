<!-- Hapus extends dan section layout utama, langsung tulis kodenya -->
<div class="main-content-header">
    <h1>PROCUREMENT</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard1">Purchase Order</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/purchasing-order-rfq">RFQ Quotation</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Input QTN Detail</span>
        </li>
    </ol>
</div> 

<div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-edit mr-2"></i>Form Input Penawaran untuk No. RFQ: {{ $rfq->RFQ_No }} (PR: {{ $rfq->PR_No }})
        </h6>
    </div>
    <div class="card-body bg-light">
        <form action="{{ url('qtn-store') }}" method="POST">
            @csrf
            <input type="hidden" name="rfq_no" value="{{ $rfq->RFQ_No }}">
            <input type="hidden" name="pr_no" value="{{ $rfq->PR_No }}">
            <input type="hidden" name="supplier_id" value="{{ $rfq->Supplier_ID }}">

            <div class="row mb-3" style="font-size: 12px;">
                <div class="col-md-6">
                    <div><strong>Vendor / Supplier:</strong> <span class="text-dark font-weight-bold">{{ $rfq->Company_Name ?? $rfq->Supplier_ID }}</span></div>
                    <div><strong>Tanggal RFQ:</strong> {{ date('d/m/Y', strtotime($rfq->RFQ_Date)) }}</div>
                </div>
            </div>
            <hr class="my-2">

            <!-- TABEL RINCIAN ITEM DARI purchase_request_dtl -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white mb-3" style="font-size: 12px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>Nama Item / Deskripsi</th>
                            <th>Spesifikasi</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga Penawaran Satuan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pr_items as $it)
                            <tr class="item-row">
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="item_desc[]" value="{{ $it->Product_Desc }}" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="item_spec[]" value="{{ $it->Specification }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center item-qty" name="item_qty[]" value="{{ $it->Qty }}" readonly style="width: 70px;">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-center" name="item_unit[]" value="{{ $it->Unit }}" readonly style="width: 80px;">
                                </td>
                                <td>
                                    <input type="number" step="any" class="form-control form-control-sm font-weight-bold text-success item-price" name="item_price[]" placeholder="Masukkan harga penawaran..." required>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada rincian item ditemukan untuk PR ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Penawaran & Susun Matriks
                </button>
                <a href="{{ url('purchasing-order-rfq') }}" class="btn btn-secondary btn-sm font-weight-bold px-3 py-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar RFQ
                </a>
            </div>
        </form>
    </div>
</div>