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

<style>
    #formCreateGR .form-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #344054;
    }
    #formCreateGR .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.2rem !important;
        font-size: 0.76562rem !important;
        line-height: 1.5 !important;
        padding: 0 !important;
    }
    #formCreateGR .select2-container--default .select2-selection--single .select2-selection__rendered {
        display: block !important;
        height: auto !important;
        font-size: 0.76562rem !important;
        line-height: 1.5 !important;
        padding: 0.25rem 28px 0.25rem 0.5rem !important;
        margin: 0 !important;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #formCreateGR .select2-container--default .select2-selection--single .select2-selection__placeholder {
        font-size: 0.76562rem !important;
        line-height: 1.5 !important;
    }
    #formCreateGR .select2-container--default .select2-selection--single .select2-selection__clear {
        line-height: 1.5 !important;
        margin-right: 4px;
    }
    #formCreateGR .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
    }
    .gr-select2-dropdown .select2-search__field,
    .gr-select2-dropdown .select2-results__option {
        font-size: 0.76562rem;
    }
    #itemsTable {
        font-size: 12px;
        margin-bottom: 0;
    }
    #itemsTable thead th {
        background: linear-gradient(#e2efff, #d3e7ff) !important;
        color: #000000 !important;
        font-weight: 700;
        font-size: 12px;
        line-height: 1.25;
        padding: 6px 8px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        border-color: #a4bed4 !important;
    }
    #itemsTable thead th.col-qty-input {
        background: linear-gradient(#d3e7ff, #bfd9ff) !important;
        border-bottom: 2px solid #174375 !important;
    }
    #itemsTable tbody td {
        padding: 6px 8px;
        vertical-align: middle;
        border-color: #dee2e6;
    }
    #itemsTable tbody td.col-qty-input {
        background-color: #f4f8ff;
    }
    #itemsTable tbody input.form-control-sm {
        height: 28px;
        padding: 2px 6px;
        font-size: 12px;
    }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FORM PENERIMAAN BARANG (GOODS RECEIPT)</div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('cns-procurement-goods-receipt.store') }}" method="POST" enctype="multipart/form-data" class="w-100 px-0" id="formCreateGR">
        @csrf
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light fw-bold py-2">
                <i class="fa fa-file-text-o me-1"></i> 1. Informasi PO &amp; Pengiriman
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="po_no" class="form-label">Pilih Purchase Order (PO) <span class="text-danger">*</span></label>
                        <select name="po_no" id="po_no" class="form-select form-select-sm" required onchange="onPoChanged(this.value)">
                            <option value="">-- Pilih Purchase Order --</option>
                            @foreach($pendingPos as $po)
                                <option value="{{ $po->PO_No }}" {{ old('po_no') == $po->PO_No ? 'selected' : '' }}>
                                    {{ $po->PO_No }} ({{ $po->PO_Date ? date('d/m/Y', strtotime($po->PO_Date)) : '-' }}) - {{ $po->Supplier }} [{{ $po->Status_Receive ?: 'WAITING' }}]
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hanya PO status APPROVED dan belum tuntas (WAITING / PARTIAL).</small>
                    </div>

                    <div class="col-md-4">
                        <label for="displaySupplier" class="form-label">Nama Supplier / Vendor</label>
                        <input type="text" id="displaySupplier" class="form-control form-control-sm bg-light" readonly placeholder="Otomatis dari PO" />
                    </div>

                    <div class="col-md-4">
                        <label for="receipt_date" class="form-label">Tanggal Penerimaan <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" id="receipt_date" class="form-control form-control-sm" value="{{ old('receipt_date', date('Y-m-d')) }}" required />
                    </div>

                    <div class="col-md-4">
                        <label for="delivery_note_no" class="form-label">No. Surat Jalan Vendor</label>
                        <input type="text" name="delivery_note_no" id="delivery_note_no" class="form-control form-control-sm" placeholder="Contoh: SJ/2026/XI/0012" value="{{ old('delivery_note_no') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="driver_name" class="form-label">Nama Supir / Kurir</label>
                        <input type="text" name="driver_name" id="driver_name" class="form-control form-control-sm" placeholder="Nama pengantar barang" value="{{ old('driver_name') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="vehicle_no" class="form-label">Nomor Polisi Truk / Kendaraan</label>
                        <input type="text" name="vehicle_no" id="vehicle_no" class="form-control form-control-sm" placeholder="Contoh: B 9123 XYZ" value="{{ old('vehicle_no') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="received_by" class="form-label">Petugas Penerima Gudang</label>
                        <input type="text" name="received_by" id="received_by" class="form-control form-control-sm" value="{{ old('received_by', auth()->user()->Name ?? auth()->user()->username ?? 'Petugas Gudang') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="attachment" class="form-label">Upload Surat Jalan / Foto Bukti Fisik</label>
                        <input type="file" name="attachment" id="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" />
                        <small class="form-text text-muted">Format: JPG, PNG, atau PDF (maks. 10MB).</small>
                    </div>

                    <div class="col-md-4">
                        <label for="notes" class="form-label">Catatan Pengiriman</label>
                        <input type="text" name="notes" id="notes" class="form-control form-control-sm" placeholder="Catatan kondisi fisik, segel, dll." value="{{ old('notes') }}" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light fw-bold py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fa fa-cubes me-1"></i> 2. Rincian Item Barang yang Diterima</span>
                <div class="d-flex align-items-center gap-2">
                    <span id="loadingPoItems" style="display:none;" class="text-primary small fw-normal">
                        <i class="fa fa-spinner fa-spin me-1"></i> Mengambil data item PO...
                    </span>
                    <div id="itemQuickActions" style="display:none;" class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="fillAllRemainingQty()">
                            <i class="fa fa-check-square-o me-1"></i> Isi Semua Sisa
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetAllReceivedQty()">
                            <i class="fa fa-eraser me-1"></i> Reset 0
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>Nama Barang</th>
                                <th style="width: 130px;">Kategori</th>
                                <th style="width: 105px;">Qty Order</th>
                                <th style="width: 110px;">Sudah Terima</th>
                                <th style="width: 105px;">Sisa Datang</th>
                                <th style="width: 75px;">Satuan</th>
                                <th style="width: 155px;" class="col-qty-input">Qty Diterima Saat Ini <span class="text-danger">*</span></th>
                                <th style="min-width: 180px;">Catatan Item</th>
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
            <a href="{{ route('cns-procurement-goods-receipt.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4" id="btnSubmit">
                <i class="fa fa-save me-1"></i> Simpan sebagai DRAFT
            </button>
        </div>
    </form>
</div>

<script>
    var isLoadingPoItems = false;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatQty(val) {
        return cnsFormatNumber(val, 2);
    }

    function fillAllRemainingQty() {
        $('#itemsTableBody .qty-received-input').each(function() {
            var remain = cnsParseNumber($(this).attr('data-remain'), 0);
            $(this).val(cnsFormatNumber(remain, 2));
        });
    }

    function resetAllReceivedQty() {
        $('#itemsTableBody .qty-received-input').val(cnsFormatNumber(0, 2));
    }

    function onPoChanged(poNo) {
        if (!poNo) {
            isLoadingPoItems = false;
            $('#displaySupplier').val('');
            $('#itemQuickActions').hide();
            $('#itemsTableBody').html('<tr><td colspan="9" class="text-center text-muted py-4">Silakan pilih Purchase Order di atas untuk memuat daftar item.</td></tr>');
            return;
        }

        isLoadingPoItems = true;
        $('#loadingPoItems').show();
        $('#itemQuickActions').hide();
        var url = "{{ route('cns-procurement-goods-receipt.po-items') }}?po_no=" + encodeURIComponent(poNo);

        $.getJSON(url, function(res) {
            isLoadingPoItems = false;
            $('#loadingPoItems').hide();
            if (res.status === 'success') {
                $('#displaySupplier').val(res.po.supplier_name || '-');

                var rowsHtml = '';
                if (!res.items || res.items.length === 0) {
                    rowsHtml = '<tr><td colspan="9" class="text-center text-warning py-3">Tidak ada item dalam PO ini.</td></tr>';
                    $('#itemQuickActions').hide();
                } else {
                    res.items.forEach(function(item, idx) {
                        var defaultQty = item.qty_remain > 0 ? item.qty_remain : 0;
                        var maxQty = item.qty_remain > 0 ? item.qty_remain : item.qty_ordered;
                        var itemName = escapeHtml(item.item_name || 'Item Non-UCO');
                        var category = escapeHtml(item.category || 'General');
                        var unit = escapeHtml(item.unit || 'PCS');
                        var itemCode = escapeHtml(item.item_code || '');

                        rowsHtml += '<tr>';
                        rowsHtml += '<td class="text-center">' + (idx + 1) + '</td>';
                        rowsHtml += '<td class="fw-semibold">' + itemName + '</td>';
                        rowsHtml += '<td class="text-center">' + category + '</td>';
                        rowsHtml += '<td class="text-end">' + formatQty(item.qty_ordered) + '</td>';
                        rowsHtml += '<td class="text-end text-muted">' + formatQty(item.qty_received_prior) + '</td>';
                        rowsHtml += '<td class="text-end fw-bold text-info">' + formatQty(item.qty_remain) + '</td>';
                        rowsHtml += '<td class="text-center">' + unit + '</td>';
                        rowsHtml += '<td class="col-qty-input">';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][po_dtl_id]" value="' + escapeHtml(item.po_dtl_id) + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][item_code]" value="' + itemCode + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][item_name]" value="' + itemName + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][category]" value="' + category + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][unit]" value="' + unit + '" />';
                        rowsHtml += '<input type="hidden" name="items[' + idx + '][qty_ordered]" value="' + escapeHtml(item.qty_ordered) + '" />';
                        rowsHtml += '<input type="text" inputmode="decimal" data-max="' + escapeHtml(maxQty) + '" data-remain="' + escapeHtml(defaultQty) + '" name="items[' + idx + '][qty_received]" value="' + escapeHtml(cnsFormatNumber(defaultQty, 2)) + '" class="form-control form-control-sm text-end fw-bold qty-received-input cns-decimal" required />';
                        rowsHtml += '</td>';
                        rowsHtml += '<td>';
                        rowsHtml += '<input type="text" name="items[' + idx + '][notes]" class="form-control form-control-sm" placeholder="Kondisi barang / keterangan" />';
                        rowsHtml += '</td>';
                        rowsHtml += '</tr>';
                    });
                    $('#itemQuickActions').show();
                }
                $('#itemsTableBody').html(rowsHtml);
                cnsInitDecimalInputs('#itemsTableBody');
            } else {
                alert('Gagal memuat item: ' + res.message);
            }
        }).fail(function() {
            isLoadingPoItems = false;
            $('#loadingPoItems').hide();
            alert('Terjadi kesalahan koneksi saat memuat item PO.');
        });
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            $('#po_no').select2({
                width: '100%',
                placeholder: '-- Pilih Purchase Order --',
                allowClear: true,
                dropdownCssClass: 'gr-select2-dropdown'
            });
        }

        $('#formCreateGR').on('submit', function(e) {
            if (isLoadingPoItems) {
                e.preventDefault();
                alert('Mohon tunggu, data item PO masih dimuat.');
                return false;
            }

            var $qtyInputs = $('#itemsTableBody .qty-received-input');
            if ($qtyInputs.length === 0) {
                e.preventDefault();
                alert('Pilih Purchase Order yang memiliki item terlebih dahulu.');
                return false;
            }

            var hasPositiveQty = false;
            $qtyInputs.each(function() {
                if (cnsParseNumber($(this).val(), 0) > 0) {
                    hasPositiveQty = true;
                    return false;
                }
            });

            if (!hasPositiveQty) {
                e.preventDefault();
                alert('Minimal satu item harus memiliki Qty Diterima lebih dari 0.');
                $qtyInputs.first().focus();
                return false;
            }

            $('#btnSubmit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');
        });

        var initialPo = $('#po_no').val();
        if (initialPo) {
            onPoChanged(initialPo);
        }
    });
</script>
@endsection
