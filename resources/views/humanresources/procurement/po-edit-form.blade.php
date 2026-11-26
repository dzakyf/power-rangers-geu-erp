@php
    $isEmergency = strtoupper((string) ($poHeader->Purchase_Type ?? '')) === 'EMERGENCY'
        || strtoupper((string) ($poHeader->PR_No ?? '')) === 'EMERGENCY';

    $rawRemark = (string) ($poHeader->Remark ?? '');
    $detectedSupplies = strtoupper((string) ($poHeader->Supplies_Type ?? ''));
    if (!in_array($detectedSupplies, ['OPS', 'NON OPS'], true)) {
        if (preg_match('/\[PO EMERGENCY\s*-\s*(NON OPS|OPS)\s*-/i', $rawRemark, $m)) {
            $detectedSupplies = strtoupper(trim($m[1]));
        } else {
            $detectedSupplies = 'NON OPS';
        }
    }

    $currentSupplier = $suppliers->firstWhere('Supplier_ID', $poHeader->Supplier);
    $currentSupplierLabel = $currentSupplier
        ? trim($currentSupplier->Supplier_ID . ' - ' . ($currentSupplier->Company_Name ?? ''))
        : (string) ($poHeader->Supplier ?? '');

    $currentCategory = strtoupper((string) ($poHeader->Purchase_Category ?? 'NON ASSET'));
    $currentSubCat   = strtoupper((string) ($poHeader->Purchase_Sub_Category ?? 'BARANG'));
    $currentTop      = (string) ($poHeader->Term_Payment ?? ($poHeader->Pay_Term ?? 'NET 30'));

    $initSubtotal = floatval($poHeader->Subtotal ?? 0);
    $initDiscount = floatval($poHeader->Discount ?? 0);
    $initPpn      = floatval($poHeader->PPN ?? 0);
    $initPph      = floatval($poHeader->PPH ?? 0);
    $initDelivery = floatval($poHeader->Delivery_Fee ?? 0);
    $initDpp      = max(0, $initSubtotal - $initDiscount);
    $initPpnPct   = $initDpp > 0 ? round(($initPpn / $initDpp) * 100, 2) : 11;

    $supplierJson = $suppliers->map(function($s) {
        return [
            'value' => $s->Supplier_ID,
            'label' => trim($s->Supplier_ID . ' - ' . ($s->Company_Name ?? '')),
        ];
    })->values();

    $deptJson = $departments->map(function($d) {
        return [
            'value' => $d->Department,
            'label' => $d->Department,
        ];
    })->values();

    $productJson = $materials->map(function($m) {
        $desc = trim((string) ($m->Description ?? ''));
        $spec = trim((string) ($m->Specification ?? ''));
        $displayText = $desc !== '' ? $desc : ($spec !== '' ? $spec : '-');
        return [
            'value' => $m->Product_Code,
            'label' => $m->Product_Code . ' - ' . $displayText,
            'desc'  => $desc !== '' ? $desc : $spec,
            'spec'  => $spec,
            'unit'  => trim((string) ($m->Unit_Name ?? 'PCS')) ?: 'PCS',
        ];
    })->values();
@endphp

<style>
    .searchable-wrap {
        position: relative;
    }
    .searchable-input {
        padding-right: 28px !important;
        background-color: #ffffff !important;
    }
    .searchable-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        cursor: pointer;
        font-size: 12px;
        display: none;
        z-index: 5;
    }
    .searchable-clear:hover {
        color: #ef4444;
    }
    .searchable-menu {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        max-height: 220px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.14);
        z-index: 1060;
        display: none;
    }
    .searchable-item {
        padding: 6px 10px;
        font-size: 12px;
        color: #1e293b;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        line-height: 1.35;
    }
    .searchable-item:last-child {
        border-bottom: none;
    }
    .searchable-item:hover,
    .searchable-item.active {
        background-color: #ecfdf5;
        color: #065f46;
    }
    .searchable-empty {
        padding: 8px 10px;
        font-size: 12px;
        color: #94a3b8;
        text-align: center;
    }
</style>

@if($isEmergency)
<div class="alert alert-danger py-2 small mb-3">
    <i class="fa fa-exclamation-triangle mr-1"></i>
    <strong>Mode Edit PO Emergency:</strong> Perubahan pada PO emergency ini akan memperbarui header, rincian item, serta catatan/justifikasi PO.
</div>
@endif

<div class="row mb-2">
    <div class="col-md-3">
        <label class="small font-weight-bold">No. PO</label>
        <input type="text" name="po_no" class="form-control form-control-sm font-weight-bold bg-light" value="{{ $poHeader->PO_No }}" readonly>
    </div>
    <div class="col-md-2">
        <label class="small font-weight-bold">Tanggal PO <span class="text-danger">*</span></label>
        <input type="date" name="po_date" class="form-control form-control-sm" value="{{ !empty($poHeader->PO_Date) ? substr((string) $poHeader->PO_Date, 0, 10) : date('Y-m-d') }}" required>
    </div>

    <!-- Searchable Text Filter: Vendor / Supplier -->
    <div class="col-md-4">
        <label class="small font-weight-bold">Vendor / Supplier <span class="text-danger">*</span></label>
        <div class="searchable-wrap" data-type="supplier">
            <input type="hidden" name="supplier_id" class="searchable-value" value="{{ $poHeader->Supplier }}" required>
            <input type="text" class="form-control form-control-sm searchable-input" value="{{ $currentSupplierLabel }}" placeholder="Ketik kode atau nama vendor..." autocomplete="off">
            <span class="searchable-clear" style="{{ !empty($currentSupplierLabel) ? 'display:block;' : '' }}" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
            <div class="searchable-menu"></div>
        </div>
    </div>

    <!-- Searchable Text Filter: Department -->
    <div class="col-md-3">
        <label class="small font-weight-bold">Department <span class="text-danger">*</span></label>
        <div class="searchable-wrap" data-type="department">
            <input type="hidden" name="department" class="searchable-value" value="{{ $poHeader->Department ?? '' }}" required>
            <input type="text" class="form-control form-control-sm searchable-input" value="{{ $poHeader->Department ?? '' }}" placeholder="Ketik nama department..." autocomplete="off">
            <span class="searchable-clear" style="{{ !empty($poHeader->Department) ? 'display:block;' : '' }}" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
            <div class="searchable-menu"></div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <label class="small font-weight-bold">Tipe Supplies <span class="text-danger">*</span></label>
        <select name="supplies_type" class="form-control form-control-sm" required>
            <option value="NON OPS" {{ $detectedSupplies === 'NON OPS' ? 'selected' : '' }}>NON OPS</option>
            <option value="OPS" {{ $detectedSupplies === 'OPS' ? 'selected' : '' }}>OPS</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Kategori Pembelian</label>
        <select name="purchase_category" class="form-control form-control-sm">
            <option value="NON ASSET" {{ $currentCategory === 'NON ASSET' ? 'selected' : '' }}>NON ASSET</option>
            <option value="ASSET" {{ $currentCategory === 'ASSET' ? 'selected' : '' }}>ASSET</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Sub Kategori</label>
        <select name="purchase_sub_category" class="form-control form-control-sm">
            <option value="BARANG" {{ $currentSubCat === 'BARANG' ? 'selected' : '' }}>BARANG</option>
            <option value="JASA" {{ $currentSubCat === 'JASA' ? 'selected' : '' }}>JASA</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Termin Pembayaran (TOP)</label>
        <select name="term_payment" class="form-control form-control-sm">
            <option value="CASH / COD" {{ $currentTop === 'CASH / COD' ? 'selected' : '' }}>CASH / COD</option>
            <option value="NET 7" {{ $currentTop === 'NET 7' ? 'selected' : '' }}>NET 7</option>
            <option value="NET 14" {{ $currentTop === 'NET 14' ? 'selected' : '' }}>NET 14</option>
            <option value="NET 30" {{ $currentTop === 'NET 30' ? 'selected' : '' }}>NET 30</option>
        </select>
    </div>
</div>

<!-- Site Tujuan, No HP PIC, Email PIC & Otomatis Lead Time Emergency -->
<div class="row mb-3">
    <div class="col-md-2">
        <label class="small font-weight-bold">PIC Pemohon / Penerima</label>
        <input type="text" name="pic" class="form-control form-control-sm" value="{{ $poHeader->PIC ?? $username }}">
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Site Tujuan / Alamat Pengiriman <span class="text-danger">*</span></label>
        <input type="text" name="site_destination" class="form-control form-control-sm" value="{{ $poHeader->PO_Dest ?? ($poHeader->PIC_Address ?? 'GEU-HO') }}" placeholder="Contoh: GEU-HO / Site Malang..." required>
    </div>
    <div class="col-md-2">
        <label class="small font-weight-bold">No. HP PIC</label>
        <input type="text" name="pic_phone" class="form-control form-control-sm" value="{{ $poHeader->PIC_Phone ?? '' }}" placeholder="08xxxxxxxxxx">
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Email PIC</label>
        <input type="email" name="pic_email" class="form-control form-control-sm" value="{{ $poHeader->PIC_Email ?? '' }}" placeholder="pic@perusahaan.com">
    </div>
    <div class="col-md-2">
        <label class="small font-weight-bold">Lead Time Emergency</label>
        <div class="form-control form-control-sm bg-light font-weight-bold text-danger" id="Edit_Emg_Leadtime_Label" style="font-size: 11.5px;">
            &le; 1 Hari Kerja
        </div>
        <small class="text-muted d-block" style="font-size: 10.5px;">
            Target: <strong id="Edit_Emg_Target_Date_View">-</strong>
        </small>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-2">
    <span class="small font-weight-bold text-dark">Daftar Item PO</span>
    <div class="d-flex align-items-center" style="gap: 6px;">
        <button type="button" id="BtnEditAddItem" class="btn btn-outline-primary btn-xs font-weight-bold">
            <i class="fa fa-plus mr-1"></i> Tambah Baris Item
        </button>
        <button type="button" id="BtnEditClearAllItems" class="btn btn-outline-danger btn-xs font-weight-bold">
            <i class="fa fa-trash mr-1"></i> Hapus Semua Item
        </button>
    </div>
</div>

<div class="table-responsive mb-3" style="overflow: visible;">
    <table class="table table-bordered table-sm mb-0" id="EditPOItemsTable">
        <thead class="thead-light">
            <tr>
                <th style="width:230px;">Master Produk (Ketik Filter)</th>
                <th>Deskripsi Barang / Jasa <span class="text-danger">*</span></th>
                <th style="width:150px;">Spesifikasi</th>
                <th style="width:80px;" class="text-right">Qty <span class="text-danger">*</span></th>
                <th style="width:70px;" class="text-center">Unit</th>
                <th style="width:125px;" class="text-right">Harga Satuan <span class="text-danger">*</span></th>
                <th style="width:120px;" class="text-right">Subtotal</th>
                <th style="width:42px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($poItems as $idx => $item)
                @php
                    $codeVal = trim((string) ($item->Product_Code ?? ''));
                    $descVal = trim((string) ($item->Product_Desc ?? ''));
                    $specVal = trim((string) ($item->Specification ?? ''));
                    $qtyVal  = floatval($item->Qty ?? 1);
                    $unitVal = trim((string) ($item->Unit ?? 'PCS')) ?: 'PCS';
                    $prcVal  = floatval($item->Price ?? 0);
                    $subVal  = $qtyVal * $prcVal;
                    $prodLabel = $codeVal !== '' ? ($codeVal . ' - ' . ($descVal ?: '-')) : $descVal;
                @endphp
                <tr class="edit-po-item-row">
                    <td>
                        <div class="searchable-wrap" data-type="product">
                            <input type="hidden" name="items[{{ $idx }}][product_code]" class="searchable-value po-item-code" value="{{ $codeVal }}">
                            <input type="text" class="form-control form-control-sm searchable-input" value="{{ $prodLabel }}" placeholder="Ketik kode / nama produk..." autocomplete="off">
                            <span class="searchable-clear" style="{{ $prodLabel !== '' ? 'display:block;' : '' }}" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
                            <div class="searchable-menu"></div>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="items[{{ $idx }}][product_desc]" class="form-control form-control-sm po-item-desc" value="{{ $descVal }}" placeholder="Nama barang/jasa..." required>
                    </td>
                    <td>
                        <input type="text" name="items[{{ $idx }}][specification]" class="form-control form-control-sm po-item-spec" value="{{ $specVal }}" placeholder="Spesifikasi...">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][qty]" class="form-control form-control-sm text-right po-item-qty" value="{{ $qtyVal }}" required>
                    </td>
                    <td>
                        <input type="text" name="items[{{ $idx }}][unit]" class="form-control form-control-sm text-center po-item-unit" value="{{ $unitVal }}">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][price]" class="form-control form-control-sm text-right po-item-price" value="{{ $prcVal }}" required>
                    </td>
                    <td class="text-right align-middle font-weight-bold po-item-sub">{{ number_format($subVal, 0, ',', '.') }}</td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-outline-danger btn-xs btn-del-edit-po-row" title="Hapus Baris">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="edit-po-empty-row">
                    <td colspan="8" class="text-center text-muted small py-3">
                        Semua item telah dihapus. Klik <strong>Tambah Baris Item</strong> untuk menambahkan item baru atau simpan dengan item kosong.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-7">
        <label class="small font-weight-bold">Catatan / Alasan / Justifikasi PO <span class="text-danger">*</span></label>
        <textarea name="remark" rows="5" class="form-control form-control-sm" required placeholder="Catatan PO / alasan justifikasi emergency...">{{ $rawRemark }}</textarea>
    </div>
    <div class="col-md-5">
        <div class="border rounded p-2 bg-light small">
            <div class="d-flex justify-content-between mb-1">
                <span>Subtotal Item:</span>
                <strong id="Edit_PO_Subtotal_View">{{ number_format($initSubtotal, 0, ',', '.') }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span>Diskon (Rp):</span>
                <input type="number" step="0.01" min="0" name="discount" id="Edit_PO_Discount" class="form-control form-control-sm text-right" style="width:140px;" value="{{ $initDiscount }}">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <span>PPN (%):</span>
                    <input type="number" step="0.01" min="0" max="100" name="ppn_percent" id="Edit_PO_PPN_Percent" class="form-control form-control-sm text-right" style="width:72px;" value="{{ $initPpnPct }}">
                </div>
                <strong id="Edit_PO_PPN_Amount_View">{{ number_format($initPpn, 0, ',', '.') }}</strong>
                <input type="hidden" name="ppn" id="Edit_PO_PPN" value="{{ $initPpn }}">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span>PPH (Rp):</span>
                <input type="number" step="0.01" min="0" name="pph" id="Edit_PO_PPH" class="form-control form-control-sm text-right" style="width:140px;" value="{{ $initPph }}">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Ongkir / Delivery Fee (Rp):</span>
                <input type="number" step="0.01" min="0" name="delivery_fee" id="Edit_PO_Delivery" class="form-control form-control-sm text-right" style="width:140px;" value="{{ $initDelivery }}">
            </div>
            <hr class="my-1">
            <div class="d-flex justify-content-between font-weight-bold text-danger">
                <span>Grand Total (Rp):</span>
                <span id="Edit_PO_GrandTotal_View">{{ number_format(floatval($poHeader->Total ?? 0), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<script>
function updateEditEmergencyLeadtime() {
    const poDateVal = $('input[name="po_date"]').val();
    if (!poDateVal) {
        $('#Edit_Emg_Target_Date_View').text('-');
        return;
    }

    const parts = poDateVal.split('-');
    if (parts.length !== 3) return;

    const target = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    target.setDate(target.getDate() + 1);
    while (target.getDay() === 0 || target.getDay() === 6) {
        target.setDate(target.getDate() + 1);
    }

    const dd = String(target.getDate()).padStart(2, '0');
    const mm = String(target.getMonth() + 1).padStart(2, '0');
    const yyyy = target.getFullYear();
    $('#Edit_Emg_Target_Date_View').text(`${dd}/${mm}/${yyyy} (Max +1 HK)`);
}

$(document).off('change input', 'input[name="po_date"]').on('change input', 'input[name="po_date"]', updateEditEmergencyLeadtime);
updateEditEmergencyLeadtime();

(function() {
    const EDIT_DATA_SOURCES = {
        supplier: @json($supplierJson),
        department: @json($deptJson),
        product: @json($productJson)
    };

    let editRowIdx = {{ count($poItems) > 0 ? count($poItems) : 1 }};

    function fmtIdr(val) {
        return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderEditDropdown(wrap, query) {
        const type = wrap.data('type');
        const list = EDIT_DATA_SOURCES[type] || [];
        const menu = wrap.find('.searchable-menu');
        const q = (query || '').toLowerCase().trim();

        const filtered = q === ''
            ? list.slice(0, 60)
            : list.filter(item => item.label.toLowerCase().includes(q)).slice(0, 60);

        if (filtered.length === 0) {
            menu.html('<div class="searchable-empty">Data tidak ditemukan</div>').show();
            return;
        }

        let html = '';
        filtered.forEach((item, i) => {
            html += `<div class="searchable-item" data-index="${i}" data-value="${escapeHtml(item.value)}" data-label="${escapeHtml(item.label)}" data-desc="${escapeHtml(item.desc || '')}" data-spec="${escapeHtml(item.spec || '')}" data-unit="${escapeHtml(item.unit || 'PCS')}">
                        ${escapeHtml(item.label)}
                     </div>`;
        });
        menu.html(html).show();
    }

    $(document).off('focus input', '.searchable-input').on('focus input', '.searchable-input', function(e) {
        const wrap = $(this).closest('.searchable-wrap');
        $('.searchable-menu').not(wrap.find('.searchable-menu')).hide();

        if (e.type === 'input') {
            wrap.find('.searchable-value').val('');
            wrap.find('.searchable-clear').toggle($(this).val().length > 0);
        }
        renderEditDropdown(wrap, $(this).val());
    });

    $(document).off('mousedown', '.searchable-item').on('mousedown', '.searchable-item', function(e) {
        e.preventDefault();
        const item = $(this);
        const wrap = item.closest('.searchable-wrap');
        const type = wrap.data('type');

        wrap.find('.searchable-value').val(item.data('value'));
        wrap.find('.searchable-input').val(item.data('label'));
        wrap.find('.searchable-clear').show();
        wrap.find('.searchable-menu').hide();

        if (type === 'product') {
            const row = wrap.closest('tr');
            row.find('.po-item-desc').val(item.data('desc') || item.data('spec') || item.data('value'));
            row.find('.po-item-spec').val(item.data('spec') || '');
            row.find('.po-item-unit').val(item.data('unit') || 'PCS');
        }
    });

    $(document).off('click', '.searchable-clear').on('click', '.searchable-clear', function() {
        const wrap = $(this).closest('.searchable-wrap');
        wrap.find('.searchable-value').val('');
        wrap.find('.searchable-input').val('').focus();
        $(this).hide();
        renderEditDropdown(wrap, '');
    });

    $(document).off('click.editSearchableClose').on('click.editSearchableClose', function(e) {
        if (!$(e.target).closest('.searchable-wrap').length) {
            $('.searchable-menu').hide();
        }
    });

    function recalcEditPO() {
        let subtotal = 0;
        $('#EditPOItemsTable tbody tr.edit-po-item-row').each(function() {
            const qty = parseFloat($(this).find('.po-item-qty').val()) || 0;
            const price = parseFloat($(this).find('.po-item-price').val()) || 0;
            const sub = qty * price;
            subtotal += sub;
            $(this).find('.po-item-sub').text(fmtIdr(sub));
        });

        const disc = parseFloat($('#Edit_PO_Discount').val()) || 0;
        const ppnPct = parseFloat($('#Edit_PO_PPN_Percent').val()) || 0;
        const dpp = Math.max(0, subtotal - disc);
        const ppnAmount = dpp * (ppnPct / 100);
        const pph = parseFloat($('#Edit_PO_PPH').val()) || 0;
        const deliv = parseFloat($('#Edit_PO_Delivery').val()) || 0;
        const grand = Math.max(0, dpp + ppnAmount - pph + deliv);

        $('#Edit_PO_Subtotal_View').text(fmtIdr(subtotal));
        $('#Edit_PO_PPN').val(ppnAmount.toFixed(2));
        $('#Edit_PO_PPN_Amount_View').text(fmtIdr(ppnAmount));
        $('#Edit_PO_GrandTotal_View').text(fmtIdr(grand));
    }

    $(document).off('input', '.po-item-qty, .po-item-price, #Edit_PO_Discount, #Edit_PO_PPN_Percent, #Edit_PO_PPH, #Edit_PO_Delivery')
        .on('input', '.po-item-qty, .po-item-price, #Edit_PO_Discount, #Edit_PO_PPN_Percent, #Edit_PO_PPH, #Edit_PO_Delivery', recalcEditPO);

    recalcEditPO();

    $(document).off('click', '#BtnEditAddItem').on('click', '#BtnEditAddItem', function() {
        $('#EditPOItemsTable tbody tr.edit-po-empty-row').remove();
        const idx = editRowIdx++;
        const tr = `
            <tr class="edit-po-item-row">
                <td>
                    <div class="searchable-wrap" data-type="product">
                        <input type="hidden" name="items[${idx}][product_code]" class="searchable-value po-item-code">
                        <input type="text" class="form-control form-control-sm searchable-input" placeholder="Ketik kode / nama produk..." autocomplete="off">
                        <span class="searchable-clear" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
                        <div class="searchable-menu"></div>
                    </div>
                </td>
                <td><input type="text" name="items[${idx}][product_desc]" class="form-control form-control-sm po-item-desc" placeholder="Nama barang/jasa..." required></td>
                <td><input type="text" name="items[${idx}][specification]" class="form-control form-control-sm po-item-spec" placeholder="Spesifikasi..."></td>
                <td><input type="number" step="0.01" min="0.01" name="items[${idx}][qty]" class="form-control form-control-sm text-right po-item-qty" value="1" required></td>
                <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm text-center po-item-unit" value="PCS"></td>
                <td><input type="number" step="0.01" min="0" name="items[${idx}][price]" class="form-control form-control-sm text-right po-item-price" value="0" required></td>
                <td class="text-right align-middle font-weight-bold po-item-sub">0</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-xs btn-del-edit-po-row" title="Hapus Baris"><i class="fa fa-times"></i></button>
                </td>
            </tr>
        `;
        $('#EditPOItemsTable tbody').append(tr);
    });

    $(document).off('click', '.btn-del-edit-po-row').on('click', '.btn-del-edit-po-row', function() {
        $(this).closest('tr.edit-po-item-row').remove();
        if ($('#EditPOItemsTable tbody tr.edit-po-item-row').length === 0) {
            $('#EditPOItemsTable tbody').html(`
                <tr class="edit-po-empty-row">
                    <td colspan="8" class="text-center text-muted small py-3">
                        Semua item telah dihapus. Klik <strong>Tambah Baris Item</strong> untuk menambahkan item baru atau simpan dengan item kosong.
                    </td>
                </tr>
            `);
        }
        recalcEditPO();
    });

    $(document).off('click', '#BtnEditClearAllItems').on('click', '#BtnEditClearAllItems', function() {
        $('#EditPOItemsTable tbody').html(`
            <tr class="edit-po-empty-row">
                <td colspan="8" class="text-center text-muted small py-3">
                    Semua item telah dihapus. Klik <strong>Tambah Baris Item</strong> untuk menambahkan item baru atau simpan dengan item kosong.
                </td>
            </tr>
        `);
        recalcEditPO();
    });
})();
</script>