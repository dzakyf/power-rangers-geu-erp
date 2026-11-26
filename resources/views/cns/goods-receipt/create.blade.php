@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Form Penerimaan Barang (Goods Receipt)',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Goods Receipt', 'url' => route('cns-procurement-goods-receipt.index')],
        ['label' => 'Penerimaan Barang Baru'],
    ],
])

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

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('cns-procurement-goods-receipt.store') }}" method="POST" enctype="multipart/form-data" id="formCreateGR">
    @csrf
    <div class="card cns-card">
        <div class="card-header">
            <h5 class="card-title"><i class="fa fa-file-text-o mr-1"></i> 1. Informasi PO &amp; Pengiriman</h5>
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
                        <input type="text" id="displaySupplier" class="text-black form-control form-control-sm bg-light" readonly placeholder="Otomatis dari PO" />
                    </div>

                    <div class="col-md-4">
                        <label for="receipt_date" class="form-label">Tanggal Penerimaan <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" id="receipt_date" class="text-black form-control form-control-sm" value="{{ old('receipt_date', date('Y-m-d')) }}" required />
                    </div>

                    <div class="col-md-4">
                        <label for="delivery_note_no" class="form-label">No. Surat Jalan Vendor</label>
                        <input type="text" name="delivery_note_no" id="delivery_note_no" class="text-black form-control form-control-sm" placeholder="Contoh: SJ/2026/XI/0012" value="{{ old('delivery_note_no') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="driver_name" class="form-label">Nama Supir / Kurir</label>
                        <input type="text" name="driver_name" id="driver_name" class="text-black form-control form-control-sm" placeholder="Nama pengantar barang" value="{{ old('driver_name') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="vehicle_no" class="form-label">Nomor Polisi Truk / Kendaraan</label>
                        <input type="text" name="vehicle_no" id="vehicle_no" class="text-black form-control form-control-sm" placeholder="Contoh: B 9123 XYZ" value="{{ old('vehicle_no') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="received_by" class="form-label">Petugas Penerima Gudang</label>
                        <input type="text" name="received_by" id="received_by" class="text-black form-control form-control-sm" value="{{ old('received_by', auth()->user()->Name ?? auth()->user()->username ?? 'Petugas Gudang') }}" />
                    </div>

                    <div class="col-md-4">
                        <label for="attachment" class="form-label">Upload Surat Jalan / Foto Bukti Fisik</label>
                        <input type="file" name="attachment" id="attachment" class="text-black form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" />
                        <small class="form-text text-muted">Format: JPG, PNG, atau PDF (maks. 10MB).</small>
                    </div>

                    <div class="col-md-4">
                        <label for="notes" class="form-label">Catatan Pengiriman</label>
                        <input type="text" name="notes" id="notes" class="text-black form-control form-control-sm" placeholder="Catatan kondisi fisik, segel, dll." value="{{ old('notes') }}" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card cns-card">
            <div class="card-header">
                <h5 class="card-title"><i class="fa fa-cubes mr-1"></i> 2. Rincian Item Barang yang Diterima</h5>
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
                                <th style="min-width: 200px; display: none;" class="col-serial-number">Serial Number <span class="text-danger">*</span></th>
                                <th style="min-width: 180px;">Catatan Item</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Silakan pilih Purchase Order di atas untuk memuat daftar item.</td>
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

<script>
    var isLoadingPoItems = false;
    var oldItems = @json(old('items', []));

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
            $(this).val(cnsFormatNumber(remain, 2)).trigger('change');
        });
    }

    function resetAllReceivedQty() {
        $('#itemsTableBody .qty-received-input').val(cnsFormatNumber(0, 2)).trigger('change');
    }

    function splitSerialTokens(raw) {
        return String(raw || '')
            .split(/[\r\n,;\t]+/)
            .map(function(s) { return $.trim(s); })
            .filter(function(s) { return s !== ''; });
    }

    function renderSerialBoxes(idx, qtyInt, presetValues) {
        var $subrow = $('#itemsTableBody .serial-number-subrow[data-item-idx="' + idx + '"]');
        if ($subrow.length === 0) return;

        var $grid = $subrow.find('.sn-boxes-grid');

        if (qtyInt <= 0) {
            $subrow.hide();
            $grid.find('.sn-photo-preview').each(function() {
                var oldBlob = $(this).data('blobUrl');
                if (oldBlob) {
                    URL.revokeObjectURL(oldBlob);
                }
            });
            $grid.empty();
            syncAllSerialNumbers();
            return;
        }

        var hasPreset = Array.isArray(presetValues);
        var existingCount = $grid.children('.sn-unit-box').length;

        if (existingCount > qtyInt) {
            $grid.children('.sn-unit-box').slice(qtyInt).each(function() {
                var oldBlob = $(this).find('.sn-photo-preview').data('blobUrl');
                if (oldBlob) {
                    URL.revokeObjectURL(oldBlob);
                }
            }).remove();
        }

        if (hasPreset) {
            $grid.children('.sn-unit-box').each(function(pos) {
                if (presetValues[pos] != null) {
                    $(this).find('.sn-box-input').val(presetValues[pos]);
                }
            });
        }

        for (var i = existingCount; i < qtyInt; i++) {
            var val = hasPreset && presetValues[i] != null ? presetValues[i] : '';
            var boxHtml = '';
            boxHtml += '<div class="col-6 col-md-4 col-lg-3 sn-unit-box" data-sn-pos="' + i + '">';
            boxHtml += '<div class="border rounded bg-white p-2 h-100">';
            boxHtml += '<div class="input-group input-group-sm">';
            boxHtml += '<span class="input-group-text text-muted" style="min-width:38px;justify-content:center;">#' + (i + 1) + '</span>';
            boxHtml += '<input type="text" class="text-black form-control form-control-sm sn-box-input" data-item-idx="' + idx + '" data-sn-pos="' + i + '" value="' + escapeHtml(val) + '" placeholder="Scan / ketik SN #' + (i + 1) + '" autocomplete="off" />';
            boxHtml += '</div>';
            boxHtml += '<input type="file" name="items[' + idx + '][serial_photos][' + i + ']" class="text-black form-control form-control-sm sn-photo-input mt-1" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-item-idx="' + idx + '" data-sn-pos="' + i + '" />';
            boxHtml += '<img class="sn-photo-preview mt-1 rounded border" style="display:none;max-height:48px;max-width:100%;object-fit:cover;cursor:pointer;" alt="Preview Foto Flexibag #' + (i + 1) + '" />';
            boxHtml += '</div>';
            boxHtml += '</div>';
            $grid.append(boxHtml);
        }

        $subrow.show();
        syncAllSerialNumbers();
    }

    function syncAllSerialNumbers() {
        var counts = {};
        var $allInputs = $('#itemsTableBody .sn-box-input');

        $allInputs.each(function() {
            var val = $.trim($(this).val());
            if (val !== '') {
                var key = val.toLowerCase();
                counts[key] = (counts[key] || 0) + 1;
            }
        });

        $allInputs.each(function() {
            var val = $.trim($(this).val());
            var isDup = val !== '' && counts[val.toLowerCase()] > 1;
            $(this).toggleClass('is-invalid', isDup);
        });

        $('#itemsTableBody tr.item-main-row[data-flexibag="1"]').each(function() {
            var $row = $(this);
            var idx = $row.attr('data-item-idx');
            var qtyInt = Math.max(0, Math.floor(cnsParseNumber($row.find('.qty-received-input').val(), 0)));
            var $subrow = $('#itemsTableBody .serial-number-subrow[data-item-idx="' + idx + '"]');
            var values = [];
            var hasDup = false;

            $subrow.find('.sn-box-input').each(function() {
                var val = $.trim($(this).val());
                if (val !== '') {
                    values.push(val);
                    if (counts[val.toLowerCase()] > 1) {
                        hasDup = true;
                    }
                }
            });

            $row.find('.serial-number-input').val(values.join('\n'));

            var $badge = $row.find('.sn-status-badge');
            $badge.removeClass('bg-success bg-warning bg-danger bg-secondary text-dark');
            if (qtyInt <= 0) {
                $badge.addClass('bg-secondary').text('0 / 0');
            } else if (hasDup) {
                $badge.addClass('bg-danger').text(values.length + ' / ' + qtyInt + ' (Duplikat)');
            } else if (values.length === qtyInt) {
                $badge.addClass('bg-success').text(values.length + ' / ' + qtyInt + ' Lengkap');
            } else {
                $badge.addClass('bg-warning text-dark').text(values.length + ' / ' + qtyInt);
            }
            $subrow.find('.sn-dup-warning').toggle(hasDup);
        });
    }

    function onPoChanged(poNo) {
        if (!poNo) {
            isLoadingPoItems = false;
            $('#displaySupplier').val('');
            $('#itemQuickActions').hide();
            $('.col-serial-number').hide();
            $('#itemsTableBody').html('<tr><td colspan="10" class="text-center text-muted py-4">Silakan pilih Purchase Order di atas untuk memuat daftar item.</td></tr>');
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

                var hasFlexibag = (res.items || []).some(function(item) {
                    return /flexibag/i.test(item.item_name || '');
                });
                $('.col-serial-number').toggle(hasFlexibag);

                var rowsHtml = '';
                if (!res.items || res.items.length === 0) {
                    rowsHtml = '<tr><td colspan="' + (hasFlexibag ? 10 : 9) + '" class="text-center text-warning py-3">Tidak ada item dalam PO ini.</td></tr>';
                    $('#itemQuickActions').hide();
                } else {
                    res.items.forEach(function(item, idx) {
                        var oldItem = oldItems[idx] || null;
                        var defaultQty = oldItem && oldItem.qty_received != null
                            ? cnsParseNumber(oldItem.qty_received, 0)
                            : (item.qty_remain > 0 ? item.qty_remain : 0);
                        var maxQty = item.qty_remain > 0 ? item.qty_remain : item.qty_ordered;
                        var itemName = escapeHtml(item.item_name || 'Item Non-UCO');
                        var category = escapeHtml(item.category || 'General');
                        var unit = escapeHtml(item.unit || 'PCS');
                        var itemCode = escapeHtml(item.item_code || '');
                        var isFlexibag = /flexibag/i.test(item.item_name || '');

                        rowsHtml += '<tr class="item-main-row" data-item-idx="' + idx + '" data-flexibag="' + (isFlexibag ? '1' : '0') + '">';
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
                        rowsHtml += '<input type="text" inputmode="decimal" data-max="' + escapeHtml(maxQty) + '" data-remain="' + escapeHtml(item.qty_remain > 0 ? item.qty_remain : 0) + '" name="items[' + idx + '][qty_received]" value="' + escapeHtml(cnsFormatNumber(defaultQty, 2)) + '" class="text-black form-control form-control-sm text-end fw-bold qty-received-input cns-decimal" required />';
                        rowsHtml += '</td>';
                        if (hasFlexibag) {
                            rowsHtml += '<td class="col-serial-number text-center">';
                            if (isFlexibag) {
                                rowsHtml += '<input type="hidden" name="items[' + idx + '][serial_number]" class="serial-number-input" value="" />';
                                rowsHtml += '<span class="badge bg-warning text-dark sn-status-badge">0 / 0</span>';
                            } else {
                                rowsHtml += '<span class="text-muted">-</span>';
                            }
                            rowsHtml += '</td>';
                        }
                        rowsHtml += '<td>';
                        rowsHtml += '<input type="text" name="items[' + idx + '][notes]" value="' + escapeHtml(oldItem && oldItem.notes ? oldItem.notes : '') + '" class="text-black form-control form-control-sm" placeholder="Kondisi barang / keterangan" />';
                        rowsHtml += '</td>';
                        rowsHtml += '</tr>';

                        if (isFlexibag) {
                            var colSpan = hasFlexibag ? 10 : 9;
                            rowsHtml += '<tr class="serial-number-subrow bg-light" data-item-idx="' + idx + '" style="display:none;">';
                            rowsHtml += '<td colspan="' + colSpan + '" class="p-2 ps-4">';
                            rowsHtml += '<div class="d-flex justify-content-between align-items-center mb-2">';
                            rowsHtml += '<small class="fw-semibold text-dark"><i class="fa fa-barcode me-1"></i> Serial Number &amp; Foto Unit ' + itemName + ' <span class="text-muted fw-normal">(Wajib 1 SN &amp; 1 foto JPG/PNG maks 10MB per unit; tekan Enter setelah scan untuk lanjut)</span></small>';
                            rowsHtml += '<small class="text-danger fw-semibold sn-dup-warning" style="display:none;"><i class="fa fa-exclamation-triangle me-1"></i> Ada Serial Number duplikat!</small>';
                            rowsHtml += '</div>';
                            rowsHtml += '<div class="row g-2 sn-boxes-grid" style="max-height:360px;overflow-y:auto;"></div>';
                            rowsHtml += '</td>';
                            rowsHtml += '</tr>';
                        }
                    });
                    $('#itemQuickActions').show();
                }
                $('#itemsTableBody').html(rowsHtml);
                cnsInitDecimalInputs('#itemsTableBody');

                if (res.items && res.items.length > 0) {
                    res.items.forEach(function(item, idx) {
                        if (/flexibag/i.test(item.item_name || '')) {
                            var $row = $('#itemsTableBody tr.item-main-row[data-item-idx="' + idx + '"]');
                            var qtyInt = Math.max(0, Math.floor(cnsParseNumber($row.find('.qty-received-input').val(), 0)));
                            var oldItem = oldItems[idx] || null;
                            var preset = oldItem && oldItem.serial_number ? splitSerialTokens(oldItem.serial_number) : [];
                            renderSerialBoxes(idx, qtyInt, preset);
                        }
                    });
                }
            } else {
                swal('Error', 'Gagal memuat item: ' + res.message, 'error');
            }
        }).fail(function() {
            isLoadingPoItems = false;
            $('#loadingPoItems').hide();
            swal('Error', 'Terjadi kesalahan koneksi saat memuat item PO.', 'error');
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

        $('#itemsTableBody').on('input change', '.qty-received-input', function() {
            var $row = $(this).closest('tr.item-main-row');
            if ($row.attr('data-flexibag') === '1') {
                var idx = $row.attr('data-item-idx');
                var qtyInt = Math.max(0, Math.floor(cnsParseNumber($(this).val(), 0)));
                renderSerialBoxes(idx, qtyInt);
            }
        });

        $('#itemsTableBody').on('input', '.sn-box-input', function() {
            syncAllSerialNumbers();
        });

        $('#itemsTableBody').on('keydown', '.sn-box-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var idx = $(this).attr('data-item-idx');
                var pos = parseInt($(this).attr('data-sn-pos'), 10);
                var $next = $('#itemsTableBody .sn-box-input[data-item-idx="' + idx + '"][data-sn-pos="' + (pos + 1) + '"]');
                if ($next.length > 0) {
                    $next.focus().select();
                }
            }
        });

        $('#itemsTableBody').on('paste', '.sn-box-input', function(e) {
            var clipboard = (e.originalEvent || e).clipboardData;
            var text = clipboard ? clipboard.getData('text') : '';
            if (!text || !/[\r\n,;\t]/.test(text)) {
                return;
            }
            e.preventDefault();
            var tokens = splitSerialTokens(text);
            if (tokens.length === 0) return;

            var idx = $(this).attr('data-item-idx');
            var startPos = parseInt($(this).attr('data-sn-pos'), 10);
            tokens.forEach(function(token, offset) {
                var $target = $('#itemsTableBody .sn-box-input[data-item-idx="' + idx + '"][data-sn-pos="' + (startPos + offset) + '"]');
                if ($target.length > 0) {
                    $target.val(token);
                }
            });
            syncAllSerialNumbers();
        });

        $('#itemsTableBody').on('change', '.sn-photo-input', function() {
            var input = this;
            var $input = $(input);
            var $preview = $input.closest('.sn-unit-box').find('.sn-photo-preview');
            var file = input.files && input.files[0] ? input.files[0] : null;

            var prevBlob = $preview.data('blobUrl');
            if (prevBlob) {
                URL.revokeObjectURL(prevBlob);
                $preview.removeData('blobUrl');
            }

            if (!file) {
                $input.removeClass('is-invalid');
                $preview.hide().attr('src', '');
                return;
            }

            var extValid = /\.(jpe?g|png)$/i.test(file.name || '');
            var mimeValid = !file.type || /^image\/(jpe?g|pjpeg|png|x-png)$/i.test(file.type);
            if (!extValid || !mimeValid) {
                input.value = '';
                $input.addClass('is-invalid');
                $preview.hide().attr('src', '');
                swal('Format Tidak Valid', 'Foto unit Flexibag harus berformat JPG, JPEG, atau PNG.', 'warning');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                input.value = '';
                $input.addClass('is-invalid');
                $preview.hide().attr('src', '');
                swal('Ukuran File Terlalu Besar', 'Ukuran foto unit Flexibag maksimal 10MB.', 'warning');
                return;
            }

            $input.removeClass('is-invalid');
            if (window.URL && typeof URL.createObjectURL === 'function') {
                var blobUrl = URL.createObjectURL(file);
                $preview.data('blobUrl', blobUrl).attr('src', blobUrl).show();
            } else if (window.FileReader) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    $preview.attr('src', evt.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#itemsTableBody').on('click', '.sn-photo-preview', function() {
            var $box = $(this).closest('.sn-unit-box');
            var input = $box.find('.sn-photo-input')[0];
            var pos = parseInt($box.attr('data-sn-pos'), 10);
            if (input && input.files && input.files[0] && typeof window.cnsOpenDocPreview === 'function') {
                window.cnsOpenDocPreview(input.files[0], 'Foto Flexibag #' + (pos + 1));
            }
        });

        $('#formCreateGR').on('submit', function(e) {
            if (isLoadingPoItems) {
                e.preventDefault();
                swal('Perhatian', 'Mohon tunggu, data item PO masih dimuat.', 'warning');
                return false;
            }

            var $qtyInputs = $('#itemsTableBody .qty-received-input');
            if ($qtyInputs.length === 0) {
                e.preventDefault();
                swal('Perhatian', 'Pilih Purchase Order yang memiliki item terlebih dahulu.', 'warning');
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
                swal('Perhatian', 'Minimal satu item harus memiliki Qty Diterima lebih dari 0.', 'warning').then(function() {
                    $qtyInputs.first().focus();
                });
                return false;
            }

            syncAllSerialNumbers();

            var snErrorMsg = null;
            var snErrorFocus = null;

            $('#itemsTableBody tr.item-main-row[data-flexibag="1"]').each(function() {
                var $row = $(this);
                var idx = $row.attr('data-item-idx');
                var rawQty = cnsParseNumber($row.find('.qty-received-input').val(), 0);
                if (rawQty <= 0) {
                    return true;
                }
                if (Math.abs(rawQty - Math.round(rawQty)) > 0.0001) {
                    snErrorMsg = 'Qty Diterima untuk item Flexibag harus bilangan bulat.';
                    snErrorFocus = $row.find('.qty-received-input');
                    return false;
                }

                var qtyInt = Math.round(rawQty);
                var $boxes = $('#itemsTableBody .sn-box-input[data-item-idx="' + idx + '"]');
                var emptyBox = null;
                var filledCount = 0;

                $boxes.each(function() {
                    if ($.trim($(this).val()) === '') {
                        if (!emptyBox) emptyBox = $(this);
                    } else {
                        filledCount++;
                    }
                });

                if (filledCount !== qtyInt) {
                    snErrorMsg = 'Jumlah Serial Number (' + filledCount + ') harus sama dengan Qty Diterima (' + qtyInt + ').';
                    snErrorFocus = emptyBox || $boxes.first();
                    return false;
                }
            });

            if (!snErrorMsg) {
                var $dupBox = $('#itemsTableBody .sn-box-input.is-invalid').first();
                if ($dupBox.length > 0) {
                    snErrorMsg = 'Terdapat Serial Number duplikat di form. Periksa kotak berwarna merah.';
                    snErrorFocus = $dupBox;
                }
            }

            if (!snErrorMsg) {
                $('#itemsTableBody tr.item-main-row[data-flexibag="1"]').each(function() {
                    var $row = $(this);
                    var idx = $row.attr('data-item-idx');
                    var rawQty = cnsParseNumber($row.find('.qty-received-input').val(), 0);
                    if (rawQty <= 0) {
                        return true;
                    }

                    var qtyInt = Math.round(rawQty);
                    var $photoInputs = $('#itemsTableBody .sn-photo-input[data-item-idx="' + idx + '"]');
                    var emptyPhoto = null;
                    var missingPos = null;

                    $photoInputs.each(function(pIdx) {
                        if (!this.files || this.files.length === 0) {
                            if (!emptyPhoto) {
                                emptyPhoto = $(this);
                                missingPos = pIdx + 1;
                            }
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if ($photoInputs.length !== qtyInt || emptyPhoto) {
                        snErrorMsg = 'Foto unit Flexibag #' + (missingPos || 1) + ' wajib diunggah (1 foto JPG/PNG maks 10MB per unit).';
                        snErrorFocus = emptyPhoto || $photoInputs.first();
                        return false;
                    }
                });
            }

            if (snErrorMsg) {
                e.preventDefault();
                swal('Perhatian', snErrorMsg, 'warning').then(function() {
                    if (snErrorFocus) snErrorFocus.focus();
                });
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
