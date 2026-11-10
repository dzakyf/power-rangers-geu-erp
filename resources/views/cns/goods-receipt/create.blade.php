@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Procurement</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cns-procurement-goods-receipt.index') }}">Goods Receipt</a></li>
        <li class="breadcrumb-item active"><span class="active">Penerimaan Barang Baru</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FORM PENERIMAAN BARANG (GOODS RECEIPT)</div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('cns-procurement-goods-receipt.store') }}" method="POST" enctype="multipart/form-data" class="w-100" id="formCreateGR">
        @csrf
        <div class="card mb-3">
            <div class="card-header bg-light font-weight-bold">
                <i class="fa fa-file-text-o mr-1"></i> 1. Informasi PO
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="po_no" class="font-weight-bold">Pilih Purchase Order (PO) <span class="text-danger">*</span></label>
                        <select name="po_no" id="po_no" class="form-control form-control-sm" required onchange="onPoChanged(this.value)">
                            <option value="">-- Pilih Purchase Order --</option>
                            @foreach($pendingPos as $po)
                                <option value="{{ $po->PO_No }}" {{ old('po_no') == $po->PO_No ? 'selected' : '' }}>
                                    {{ $po->PO_No }} ({{ $po->PO_Date ? date('d/m/Y', strtotime($po->PO_Date)) : '-' }}) - {{ $po->Supplier }} [{{ $po->Status_Receive ?: 'WAITING' }}]
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hanya PO yang berstatus APPROVED dan belum tuntas (WAITING / PARTIAL).</small>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Nama Supplier / Vendor</label>
                        <input type="text" id="displaySupplier" class="form-control form-control-sm" readonly placeholder="Otomatis dari PO" />
                    </div>

                    <div class="form-group col-md-4">
                        <label for="receipt_date" class="font-weight-bold">Tanggal Penerimaan <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" id="receipt_date" class="form-control form-control-sm" value="{{ old('receipt_date', date('Y-m-d')) }}" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="delivery_note_no" class="font-weight-bold">No. Surat Jalan Vendor</label>
                        <input type="text" name="delivery_note_no" id="delivery_note_no" class="form-control form-control-sm" placeholder="Contoh: SJ/2026/XI/0012" value="{{ old('delivery_note_no') }}" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="driver_name" class="font-weight-bold">Nama Supir / Kurir</label>
                        <input type="text" name="driver_name" id="driver_name" class="form-control form-control-sm" placeholder="Nama pengantar barang" value="{{ old('driver_name') }}" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="vehicle_no" class="font-weight-bold">Nomor Polisi Truk / Kendaraan</label>
                        <input type="text" name="vehicle_no" id="vehicle_no" class="form-control form-control-sm" placeholder="Contoh: B 9123 XYZ" value="{{ old('vehicle_no') }}" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="received_by" class="font-weight-bold">Petugas Penerima Gudang</label>
                        <input type="text" name="received_by" id="received_by" class="form-control form-control-sm" value="{{ old('received_by', auth()->user()->Name ?? auth()->user()->username ?? 'Petugas Gudang') }}" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="attachment" class="font-weight-bold">Upload Surat Jalan / Foto Bukti Fisik</label>
                        <input type="file" name="attachment" id="attachment" class="form-control-file form-control-sm" accept=".jpg,.jpeg,.png,.pdf" />
                        <small class="text-muted">Format: JPG, PNG, atau PDF (maks. 10MB).</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="notes" class="font-weight-bold">Catatan Pengiriman</label>
                        <input type="text" name="notes" id="notes" class="form-control form-control-sm" placeholder="Catatan kondisi fisik, segel, dll." value="{{ old('notes') }}" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light font-weight-bold d-flex justify-content-between align-items-center">
                <span><i class="fa fa-cubes mr-1"></i> 2. Rincian Item Barang yang Diterima</span>
                <span id="loadingPoItems" style="display:none;" class="text-primary small"><i class="fa fa-spinner fa-spin"></i> Mengambil data item PO...</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0" id="itemsTable">
                        <thead class="bg-secondary text-white small text-center">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th style="width: 110px;">Qty Order</th>
                                <th style="width: 110px;">Sudah Terima</th>
                                <th style="width: 110px;">Sisa Datang</th>
                                <th style="width: 80px;">Satuan</th>
                                <th style="width: 150px;" class="bg-primary text-white">Qty Diterima Saat Ini *</th>
                                <th>Catatan Item</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Silakan pilih Purchase Order di atas untuk memuat daftar item.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('cns-procurement-goods-receipt.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
            <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmit">
                <i class="fa fa-save mr-1"></i> Simpan sebagai DRAFT
            </button>
        </div>
    </form>
</div>

<script>
    function onPoChanged(poNo) {
        if (!poNo) {
            $('#displaySupplier').val('');
            $('#itemsTableBody').html('<tr><td colspan="9" class="text-center text-muted py-4">Silakan pilih Purchase Order di atas untuk memuat daftar item.</td></tr>');
            return;
        }

        $('#loadingPoItems').show();
        var url = "{{ route('cns-procurement-goods-receipt.po-items') }}?po_no=" + encodeURIComponent(poNo);

        $.getJSON(url, function(res) {
            $('#loadingPoItems').hide();
            if (res.status === 'success') {
                $('#displaySupplier').val(res.po.supplier_name);

                var rowsHtml = '';
                if (!res.items || res.items.length === 0) {
                    rowsHtml = '<tr><td colspan="9" class="text-center text-warning py-3">Tidak ada item dalam PO ini.</td></tr>';
                } else {
                    res.items.forEach(function(item, idx) {
                        var defaultQty = item.qty_remain > 0 ? item.qty_remain : 0;
                        rowsHtml += '<tr>';
                        rowsHtml += '<td class="text-center align-middle">' + (idx + 1) + '</td>';
                        rowsHtml += '<td class="align-middle font-weight-bold">' + item.item_name + '</td>';
                        rowsHtml += '<td class="align-middle text-center">' + (item.category || 'General') + '</td>';
                        rowsHtml += '<td class="align-middle text-right">' + Number(item.qty_ordered).toLocaleString() + '</td>';
                        rowsHtml += '<td class="align-middle text-right text-muted">' + Number(item.qty_received_prior).toLocaleString() + '</td>';
                        rowsHtml += '<td class="align-middle text-right font-weight-bold text-info">' + Number(item.qty_remain).toLocaleString() + '</td>';
                        rowsHtml += '<td class="align-middle text-center">' + item.unit + '</td>';
                        rowsHtml += '<td class="align-middle">';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][po_dtl_id]" value="' + item.po_dtl_id + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][item_code]" value="' + (item.item_code || '') + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][item_name]" value="' + item.item_name + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][category]" value="' + (item.category || 'General') + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][unit]" value="' + item.unit + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][qty_ordered]" value="' + item.qty_ordered + '" />';
                        rowsHtml += '<input type="number" step="any" min="0" max="' + (item.qty_remain > 0 ? item.qty_remain : item.qty_ordered) + '" name="items[' + idx + '][qty_received]" value="' + defaultQty + '" class="form-control form-control-sm text-right font-weight-bold" required />';
                        rowsHtml += '</td>';
                        rowsHtml += '<td class="align-middle">';
                        rowsHtml += '<input type="text" name="items[' + idx + '][notes]" class="form-control form-control-sm" placeholder="Kondisi barang / keterangan" />';
                        rowsHtml += '</td>';
                        rowsHtml += '</tr>';
                    });
                }
                $('#itemsTableBody').html(rowsHtml);
            } else {
                alert('Gagal memuat item: ' + res.message);
            }
        }).fail(function() {
            $('#loadingPoItems').hide();
            alert('Terjadi kesalahan koneksi saat memuat item PO.');
        });
    }

    $(document).ready(function() {
        var initialPo = $('#po_no').val();
        if (initialPo) {
            onPoChanged(initialPo);
        }
    });
</script>
@endsection
