@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <div>
        <h3 class="main-content-title tx-24 mg-b-5">Transfer Stok Antargudang / Lokasi</h3>
        <ol class="breadcrumb mg-b-0">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-inventory-ledger') }}">GA Inventory</a></li>
            <li class="breadcrumb-item active" aria-current="page">Transfer Stok</li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Form Pemindahan Stok</h5>
                <p class="text-muted small mb-3">Mencatat mutasi barang antar lokasi/gudang dan memperbarui buku besar stok GA.</p>

                <form id="formTransferStock" class="forms-sample">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="Product_Code">Barang / Item <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="Product_Code" name="Product_Code" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->Product_Code }}">{{ $p->Product_Code }} - {{ $p->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="From_Location">Lokasi Asal <span class="text-danger">*</span></label>
                                <select class="form-control" id="From_Location" name="From_Location" required>
                                    <option value="">-- Pilih Asal --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="From_WH_ID">Gudang Asal</label>
                                <select class="form-control" id="From_WH_ID" name="From_WH_ID">
                                    <option value="">(Utama)</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh }}">{{ $wh }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="To_Location">Lokasi Tujuan <span class="text-danger">*</span></label>
                                <select class="form-control" id="To_Location" name="To_Location" required>
                                    <option value="">-- Pilih Tujuan --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="To_WH_ID">Gudang Tujuan</label>
                                <select class="form-control" id="To_WH_ID" name="To_WH_ID">
                                    <option value="">(Utama)</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh }}">{{ $wh }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="Qty">Jumlah Ditransfer <span class="text-danger">*</span></label>
                        <input type="number" step="any" min="0.0001" class="form-control" id="Qty" name="Qty" required placeholder="0.00">
                    </div>

                    <div class="form-group mb-3">
                        <label for="Reason">Alasan / Catatan Transfer</label>
                        <textarea class="form-control" id="Reason" name="Reason" rows="3" placeholder="Contoh: Pemenuhan stok operasional cabang"></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" id="btnSubmitTransfer" class="btn btn-primary">
                            <i class="mdi mdi-check"></i> Proses Transfer
                        </button>
                        <a href="{{ url('hrd-ga-inventory-ledger') }}" class="btn btn-secondary">Kembali ke Ledger</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Panduan Transfer Stok</h5>
                <ul class="text-muted small">
                    <li class="mb-2"><strong>Dua Sisi Pembukuan:</strong> Sistem otomatis mencatat <code>TRANSFER_OUT</code> pada lokasi/gudang asal dan <code>TRANSFER_IN</code> pada lokasi/gudang tujuan.</li>
                    <li class="mb-2"><strong>Konsistensi Nilai:</strong> Harga satuan (WAC) pada saat pengeluaran akan dibawa utuh sebagai nilai perolehan di lokasi tujuan.</li>
                    <li class="mb-2"><strong>Validasi Saldo:</strong> Transfer akan ditolak jika stok fisik di lokasi asal tidak mencukupi.</li>
                </ul>
                <div id="transferAlert" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formTransferStock').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitTransfer');
        $btn.prop('disabled', true).text('Memproses...');

        $.ajax({
            url: '{{ url("hrd-ga-inventory-transfer-stock-save") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Proses Transfer');
                $('#transferAlert').html('<div class="alert alert-success">' + resp.message + '</div>');
                $('#formTransferStock')[0].reset();
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-check"></i> Proses Transfer');
                var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan sistem.';
                $('#transferAlert').html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    });
});
</script>
@endsection

