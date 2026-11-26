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

<div class="alert alert-danger py-2 small mb-3">
    <i class="fa fa-exclamation-triangle mr-1"></i>
    <strong>Mode Emergency:</strong> PO ini diterbitkan langsung tanpa melalui dokumen PR, RFQ, dan Quotation. Wajib mencantumkan alasan/justifikasi kebutuhan darurat.
</div>

<div class="row mb-2">
    <div class="col-md-3">
        <label class="small font-weight-bold">No. PO (Otomatis)</label>
        <input type="text" name="po_no" class="form-control form-control-sm font-weight-bold bg-light" value="{{ $generated_po_no }}" readonly>
    </div>
    <div class="col-md-2">
        <label class="small font-weight-bold">Tanggal PO <span class="text-danger">*</span></label>
        <input type="date" name="po_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
    </div>

    <!-- Searchable Text Filter: Vendor / Supplier -->
    <div class="col-md-4">
        <label class="small font-weight-bold">Vendor / Supplier <span class="text-danger">*</span></label>
        <div class="searchable-wrap" data-type="supplier">
            <input type="hidden" name="supplier_id" class="searchable-value" required>
            <input type="text" class="form-control form-control-sm searchable-input" placeholder="Ketik kode atau nama vendor..." autocomplete="off">
            <span class="searchable-clear" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
            <div class="searchable-menu"></div>
        </div>
    </div>

    <!-- Searchable Text Filter: Department -->
    <div class="col-md-3">
        <label class="small font-weight-bold">Department <span class="text-danger">*</span></label>
        <div class="searchable-wrap" data-type="department">
            <input type="hidden" name="department" class="searchable-value" value="{{ $userDepartment }}" required>
            <input type="text" class="form-control form-control-sm searchable-input" value="{{ $userDepartment }}" placeholder="Ketik nama department..." autocomplete="off">
            <span class="searchable-clear" style="{{ !empty($userDepartment) ? 'display:block;' : '' }}" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
            <div class="searchable-menu"></div>
        </div>
    </div>
</div>

<div class="row mb-3">
	<div class="col-md-3">
        <label class="small font-weight-bold">Tipe Supplies <span class="text-danger">*</span></label>
        <select name="supplies_type" class="form-control form-control-sm" required>
            <option value="NON OPS">NON OPS</option>
            <option value="OPS">OPS</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Kategori Pembelian</label>
        <select name="purchase_category" class="form-control form-control-sm">
            <option value="NON ASSET">NON ASSET</option>
            <option value="ASSET">ASSET</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Sub Kategori</label>
        <select name="purchase_sub_category" class="form-control form-control-sm">
            <option value="BARANG">BARANG</option>
            <option value="JASA">JASA</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Termin Pembayaran (TOP)</label>
        <select name="term_payment" class="form-control form-control-sm">
            <option value="CASH / COD">CASH / COD</option>
            <option value="NET 7">NET 7</option>
            <option value="NET 14">NET 14</option>
            <option value="NET 30" selected>NET 30</option>
        </select>
    </div>
    
</div>

<!-- Tambahan: Site Tujuan, No HP PIC, Email PIC & Otomatis Lead Time Emergency -->
<div class="row mb-3">
	<div class="col-md-3">
        <label class="small font-weight-bold">PIC Pemohon / Penerima</label>
        <input type="text" name="pic" class="form-control form-control-sm" value="{{ $username }}">
    </div>
    <div class="col-md-4">
        <label class="small font-weight-bold">Site Tujuan / Alamat Pengiriman <span class="text-danger">*</span></label>
        <input type="text" name="site_destination" class="form-control form-control-sm" value="{{ $defaultSite ?? 'GEU-HO' }}" placeholder="Contoh: GEU-HO / Site Malang..." required>
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">No. HP PIC</label>
        <input type="text" name="pic_phone" class="form-control form-control-sm" value="{{ $userPhone ?? '' }}" placeholder="08xxxxxxxxxx">
    </div>
    <div class="col-md-3">
        <label class="small font-weight-bold">Email PIC</label>
        <input type="email" name="pic_email" class="form-control form-control-sm" value="{{ $userEmail ?? '' }}" placeholder="pic@perusahaan.com">
    </div>
    <div class="col-md-2">
        <label class="small font-weight-bold">Lead Time Emergency</label>
        <div class="form-control form-control-sm bg-light font-weight-bold text-danger" id="Emg_Leadtime_Label" style="font-size: 11.5px;">
            &le; 1 Hari Kerja
        </div>
        <small class="text-muted d-block" style="font-size: 10.5px;">
            Target: <strong id="Emg_Target_Date_View">-</strong>
        </small>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-2">
    <span class="small font-weight-bold text-dark">Rincian Item PO Emergency</span>
    <button type="button" id="BtnAddEmergencyItem" class="btn btn-outline-primary btn-xs font-weight-bold">
        <i class="fa fa-plus mr-1"></i> Tambah Baris Item
    </button>
</div>

<div class="table-responsive mb-3" style="overflow: visible;">
    <table class="table table-bordered table-sm mb-0" id="EmergencyPOItemsTable">
        <thead class="thead-light">
            <tr>
                <th style="width:240px;">Master Produk (Ketik Filter)</th>
                <th>Deskripsi Barang / Jasa <span class="text-danger">*</span></th>
                <th style="width:160px;">Spesifikasi</th>
                <th style="width:80px;" class="text-right">Qty <span class="text-danger">*</span></th>
                <th style="width:70px;" class="text-center">Unit</th>
                <th style="width:130px;" class="text-right">Harga Satuan <span class="text-danger">*</span></th>
                <th style="width:125px;" class="text-right">Subtotal</th>
                <th style="width:42px;" class="text-center">#</th>
            </tr>
        </thead>
        <tbody>
            <tr class="emg-item-row">
                <td>
                    <!-- Searchable Text Filter: Master Produk -->
                    <div class="searchable-wrap" data-type="product">
                        <input type="hidden" name="items[0][product_code]" class="searchable-value emg-code">
                        <input type="text" class="form-control form-control-sm searchable-input" placeholder="Ketik kode / nama produk..." autocomplete="off">
                        <span class="searchable-clear" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
                        <div class="searchable-menu"></div>
                    </div>
                </td>
                <td>
                    <input type="text" name="items[0][product_desc]" class="form-control form-control-sm emg-desc" placeholder="Nama barang/jasa..." required>
                </td>
                <td>
                    <input type="text" name="items[0][specification]" class="form-control form-control-sm emg-spec" placeholder="Spesifikasi...">
                </td>
                <td>
                    <input type="number" step="0.01" min="0.01" name="items[0][qty]" class="form-control form-control-sm text-right emg-qty" value="1" required>
                </td>
                <td>
                    <input type="text" name="items[0][unit]" class="form-control form-control-sm text-center emg-unit" value="PCS">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[0][price]" class="form-control form-control-sm text-right emg-price" value="0" required>
                </td>
                <td class="text-right align-middle font-weight-bold emg-row-sub">0</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-xs btn-del-emg-row" title="Hapus Baris">
                        <i class="fa fa-times"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-7">
        <label class="small font-weight-bold">Alasan / Justifikasi PO Emergency <span class="text-danger">*</span></label>
        <textarea name="emergency_reason" rows="4" class="form-control form-control-sm" required placeholder="Jelaskan alasan kenapa transaksi ini harus bypass PR/RFQ/QTN..."></textarea>
    </div>
    <div class="col-md-5">
    <div class="border rounded p-2 bg-light small">
        <div class="d-flex justify-content-between mb-1">
            <span>Subtotal Item:</span>
            <strong id="Emg_Subtotal_View">0</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span>Diskon (Rp):</span>
            <input type="number" step="0.01" min="0" name="discount" id="Emg_Discount" class="form-control form-control-sm text-right" style="width:140px;" value="0">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center" style="gap: 6px;">
                <span>PPN (%):</span>
                <input type="number" step="0.01" min="0" max="100" name="ppn_percent" id="Emg_PPN_Percent" class="form-control form-control-sm text-right" style="width:72px;" value="11">
            </div>
            <strong id="Emg_PPN_Amount_View">0</strong>
            <input type="hidden" name="ppn" id="Emg_PPN" value="0">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Ongkir / Delivery Fee (Rp):</span>
            <input type="number" step="0.01" min="0" name="delivery_fee" id="Emg_Delivery" class="form-control form-control-sm text-right" style="width:140px;" value="0">
        </div>
        <hr class="my-1">
        <div class="d-flex justify-content-between font-weight-bold text-danger">
            <span>Grand Total (Rp):</span>
            <span id="Emg_GrandTotal_View">0</span>
        </div>
    </div>
	</div>
</div>

@php
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

<script>
function updateEmergencyLeadtime() {
    const poDateVal = $('input[name="po_date"]').val();
    if (!poDateVal) {
        $('#Emg_Target_Date_View').text('-');
        return;
    }

    const parts = poDateVal.split('-');
    if (parts.length !== 3) return;

    const target = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    // Tambah 1 hari kerja (melewati Sabtu=6 dan Minggu=0)
    target.setDate(target.getDate() + 1);
    while (target.getDay() === 0 || target.getDay() === 6) {
        target.setDate(target.getDate() + 1);
    }

    const dd = String(target.getDate()).padStart(2, '0');
    const mm = String(target.getMonth() + 1).padStart(2, '0');
    const yyyy = target.getFullYear();
    $('#Emg_Target_Date_View').text(`${dd}/${mm}/${yyyy} (Max +1 HK)`);
}

$(document).off('change input', 'input[name="po_date"]').on('change input', 'input[name="po_date"]', updateEmergencyLeadtime);
updateEmergencyLeadtime();

	
(function() {
    const DATA_SOURCES = {
        supplier: @json($supplierJson),
        department: @json($deptJson),
        product: @json($productJson)
    };

    let rowIdx = 1;

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

    function renderDropdown(wrap, query) {
        const type = wrap.data('type');
        const list = DATA_SOURCES[type] || [];
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

    // Focus atau ketik pada kolom searchable -> tampilkan hasil filter
    $(document).off('focus input', '.searchable-input').on('focus input', '.searchable-input', function(e) {
        const wrap = $(this).closest('.searchable-wrap');
        $('.searchable-menu').not(wrap.find('.searchable-menu')).hide();

        if (e.type === 'input') {
            wrap.find('.searchable-value').val('');
            wrap.find('.searchable-clear').toggle($(this).val().length > 0);
        }
        renderDropdown(wrap, $(this).val());
    });

    // Klik salah satu item pada dropdown hasil pencarian
    $(document).off('mousedown', '.searchable-item').on('mousedown', '.searchable-item', function(e) {
        e.preventDefault();
        const item = $(this);
        const wrap = item.closest('.searchable-wrap');
        const type = wrap.data('type');

        wrap.find('.searchable-value').val(item.data('value'));
        wrap.find('.searchable-input').val(item.data('label'));
        wrap.find('.searchable-clear').show();
        wrap.find('.searchable-menu').hide();

        // Jika tipe Master Produk -> isi otomatis Deskripsi, Spesifikasi, dan Unit
        if (type === 'product') {
            const row = wrap.closest('tr');
            row.find('.emg-desc').val(item.data('desc') || item.data('spec') || item.data('value'));
            row.find('.emg-spec').val(item.data('spec') || '');
            row.find('.emg-unit').val(item.data('unit') || 'PCS');
        }
    });

    // Tombol X untuk menghapus pilihan & reset filter
    $(document).off('click', '.searchable-clear').on('click', '.searchable-clear', function() {
        const wrap = $(this).closest('.searchable-wrap');
        wrap.find('.searchable-value').val('');
        wrap.find('.searchable-input').val('').focus();
        $(this).hide();
        renderDropdown(wrap, '');
    });

    // Tutup dropdown saat klik di luar area searchable
    $(document).off('click.searchableClose').on('click.searchableClose', function(e) {
        if (!$(e.target).closest('.searchable-wrap').length) {
            $('.searchable-menu').hide();
        }
    });

    // Kalkulasi subtotal & grand total
   function recalcEmergencyPO() {
    let subtotal = 0;
    $('#EmergencyPOItemsTable tbody tr.emg-item-row').each(function() {
        const qty = parseFloat($(this).find('.emg-qty').val()) || 0;
        const price = parseFloat($(this).find('.emg-price').val()) || 0;
        const sub = qty * price;
        subtotal += sub;
        $(this).find('.emg-row-sub').text(fmtIdr(sub));
    });

    const disc = parseFloat($('#Emg_Discount').val()) || 0;
    const ppnPct = parseFloat($('#Emg_PPN_Percent').val()) || 0;
    const dpp = Math.max(0, subtotal - disc);
    const ppnAmount = dpp * (ppnPct / 100);
    const deliv = parseFloat($('#Emg_Delivery').val()) || 0;
    const grand = dpp + ppnAmount + deliv;

    $('#Emg_Subtotal_View').text(fmtIdr(subtotal));
    $('#Emg_PPN').val(ppnAmount.toFixed(2));
    $('#Emg_PPN_Amount_View').text(fmtIdr(ppnAmount));
    $('#Emg_GrandTotal_View').text(fmtIdr(grand));
}

$(document).off('input', '.emg-qty, .emg-price, #Emg_Discount, #Emg_PPN_Percent, #Emg_Delivery')
    .on('input', '.emg-qty, .emg-price, #Emg_Discount, #Emg_PPN_Percent, #Emg_Delivery', recalcEmergencyPO);

// Hitung awal saat form dimuat (karena PPN (%) default 11)
recalcEmergencyPO();

    $(document).off('input', '.emg-qty, .emg-price, #Emg_Discount, #Emg_PPN, #Emg_Delivery')
        .on('input', '.emg-qty, .emg-price, #Emg_Discount, #Emg_PPN, #Emg_Delivery', recalcEmergencyPO);

    // Tambah baris item baru
    $(document).off('click', '#BtnAddEmergencyItem').on('click', '#BtnAddEmergencyItem', function() {
        const idx = rowIdx++;
        const tr = `
            <tr class="emg-item-row">
                <td>
                    <div class="searchable-wrap" data-type="product">
                        <input type="hidden" name="items[${idx}][product_code]" class="searchable-value emg-code">
                        <input type="text" class="form-control form-control-sm searchable-input" placeholder="Ketik kode / nama produk..." autocomplete="off">
                        <span class="searchable-clear" title="Hapus pilihan"><i class="fa fa-times-circle"></i></span>
                        <div class="searchable-menu"></div>
                    </div>
                </td>
                <td><input type="text" name="items[${idx}][product_desc]" class="form-control form-control-sm emg-desc" placeholder="Nama barang/jasa..." required></td>
                <td><input type="text" name="items[${idx}][specification]" class="form-control form-control-sm emg-spec" placeholder="Spesifikasi..."></td>
                <td><input type="number" step="0.01" min="0.01" name="items[${idx}][qty]" class="form-control form-control-sm text-right emg-qty" value="1" required></td>
                <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm text-center emg-unit" value="PCS"></td>
                <td><input type="number" step="0.01" min="0" name="items[${idx}][price]" class="form-control form-control-sm text-right emg-price" value="0" required></td>
                <td class="text-right align-middle font-weight-bold emg-row-sub">0</td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-xs btn-del-emg-row" title="Hapus Baris"><i class="fa fa-times"></i></button>
                </td>
            </tr>
        `;
        $('#EmergencyPOItemsTable tbody').append(tr);
    });

    // Hapus baris item
    $(document).off('click', '.btn-del-emg-row').on('click', '.btn-del-emg-row', function() {
        if ($('#EmergencyPOItemsTable tbody tr.emg-item-row').length <= 1) {
            alert('Minimal harus ada 1 item pada PO Emergency.');
            return;
        }
        $(this).closest('tr.emg-item-row').remove();
        recalcEmergencyPO();
    });

    // Validasi sebelum submit: pastikan Vendor & Department dipilih dari daftar
    $(document).off('submit.validateSearchable', '#FormCreateEmergencyPO').on('submit.validateSearchable', '#FormCreateEmergencyPO', function(e) {
    const supVal = $(this).find('.searchable-wrap[data-type="supplier"] .searchable-value').val();
    const deptVal = $(this).find('.searchable-wrap[data-type="department"] .searchable-value').val();
    const submitBtn = $('#BtnSubmitEmergencyPO');

    if (!supVal) {
        e.preventDefault();
        e.stopImmediatePropagation();
        submitBtn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Terbitkan PO Emergency');
        alert('Silakan pilih Vendor / Supplier dari daftar filter terlebih dahulu.');
        $(this).find('.searchable-wrap[data-type="supplier"] .searchable-input').focus();
        return false;
    }

    if (!deptVal) {
        e.preventDefault();
        e.stopImmediatePropagation();
        submitBtn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Terbitkan PO Emergency');
        alert('Silakan pilih Department dari daftar filter terlebih dahulu.');
        $(this).find('.searchable-wrap[data-type="department"] .searchable-input').focus();
        return false;
    }
});
})();
</script>