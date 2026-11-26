<div class="card border-warning shadow-sm" style="border-radius: 10px; border-left: 5px solid #f59e0b;">
    <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-edit mr-2"></i>Edit RFQ, Item &amp; Vendor — {{ $header->RFQ_No }}
            <small class="ml-2 text-muted">(Ref PR: {{ $header->PR_No }})</small>
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#RFQEditPanel').empty()">
            <i class="fas fa-times mr-1"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <form id="formEditRFQ" onsubmit="saveEditedRFQ(event)">
            @csrf
            <input type="hidden" name="rfq_no" value="{{ $header->RFQ_No }}">

            <div class="row mb-3">
                <div class="col-md-5 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Vendor / Supplier *</label>
                    <select id="edit_rfq_supplier_id" name="supplier_id" class="form-control form-control-sm select2-rfq-edit" required style="width: 100%;">
                        <option value="">-- Pilih Vendor / Supplier --</option>
                        @foreach($suppliers as $sup)
                            @php
                                $supArr = (array) $sup;
                                $supId  = $supArr['Supplier_ID'] ?? ($supArr['supplier_id'] ?? '');
                                $supNm  = $supArr['Company_Name'] ?? ($supArr['company_name'] ?? $supId);
                            @endphp
                            <option value="{{ $supId }}" {{ (string) $supId === (string) $header->Supplier_ID ? 'selected' : '' }}>
                                {{ $supId }} - {{ $supNm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Tanggal RFQ *</label>
                    <input type="date" name="rfq_date" class="form-control form-control-sm"
                           value="{{ substr((string) $header->RFQ_Date, 0, 10) ?: date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="font-weight-bold text-dark mb-1">Status RFQ</label>
                    @php $curStatus = strtoupper(trim((string) $header->Status)); @endphp
                    <select name="status" class="form-control form-control-sm">
                        @foreach(['DRAFT', 'SENT', 'RESPONDED', 'AWARDED', 'CLOSED', 'CANCELLED'] as $st)
                            <option value="{{ $st }}" {{ $curStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="font-weight-bold text-dark mb-1">Catatan / Keterangan RFQ</label>
                <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan untuk vendor atau internal...">{{ $header->Note }}</textarea>
            </div>
				<div class="d-flex justify-content-between align-items-center mb-2">
				    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 12px;">
				        <i class="fas fa-boxes mr-1"></i> Daftar Item RFQ
				    </h6>
				    <div class="d-flex" style="gap: 6px;">
				        <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold" onclick="clearAllRFQEditRows()">
				            <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Item
				        </button>
				        <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" onclick="addRFQEditRow()">
				            <i class="fas fa-plus mr-1"></i> Tambah Baris Item
				        </button>
				    </div>
				</div>

            <div class="table-responsive mb-3">
    <table class="table table-bordered table-sm bg-white mb-0" id="rfqEditItemsTable" style="font-size: 11.5px;">
        <thead class="thead-light text-center">
            <tr>
                <th style="width: 22%;">Product Code</th>
                <th style="width: 22%;">Nama Item / Deskripsi</th>
                <th style="width: 17%;">Spesifikasi</th>
                <th style="width: 8%;">Qty</th>
                <th style="width: 8%;">Satuan</th>
                <th style="width: 13%;">Harga (Rp)</th>
                <th style="width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $i => $item)
            <tr data-row-index="{{ $i }}">
                <td>
                    <select name="items[{{ $i }}][product_code]" class="form-control form-control-sm rfq-edit-product-select" onchange="onRFQEditProductChange(this)">
                        <option value="">-- Ketik untuk mencari --</option>
                        @foreach($productOptions ?? [] as $opt)
                            @php
                                $optLabel = trim(($opt['code'] ?? '') . ' - ' . ($opt['name'] ?? ''), ' -');
                                $isSelected = ($opt['code'] !== '' && strtoupper((string) $opt['code']) === strtoupper((string) $item->Product_Code));
                            @endphp
                            <option value="{{ $opt['code'] }}"
                                    data-name="{{ $opt['name'] }}"
                                    data-spec="{{ $opt['spec'] }}"
                                    data-unit="{{ $opt['unit'] }}"
                                    data-price="{{ $opt['price'] }}"
                                    {{ $isSelected ? 'selected' : '' }}>
                                {{ $optLabel }}
                            </option>
                        @endforeach
                        @if(!empty($item->Product_Code) && !collect($productOptions ?? [])->contains(function($o) use ($item) { return strtoupper((string) ($o['code'] ?? '')) === strtoupper((string) $item->Product_Code); }))
                            <option value="{{ $item->Product_Code }}" selected>{{ $item->Product_Code }}</option>
                        @endif
                    </select>
                </td>
                <td>
                    <input type="text" name="items[{{ $i }}][product_desc]" class="form-control form-control-sm rfq-edit-desc" value="{{ $item->Product_Desc }}" required>
                </td>
                <td>
                    <input type="text" name="items[{{ $i }}][specification]" class="form-control form-control-sm rfq-edit-spec" value="{{ $item->Specification }}">
                </td>
                <td>
                    <input type="number" step="any" min="0" name="items[{{ $i }}][qty]" class="form-control form-control-sm text-right" value="{{ $item->Qty }}" required>
                </td>
                <td>
                    <input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm text-center rfq-edit-unit" value="{{ $item->Unit }}">
                </td>
                <td>
                    <input type="number" step="any" min="0" name="items[{{ $i }}][price]" class="form-control form-control-sm text-right rfq-edit-price" value="{{ $item->Price }}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRFQEditRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr data-row-index="0">
                <td>
                    <select name="items[0][product_code]" class="form-control form-control-sm rfq-edit-product-select" onchange="onRFQEditProductChange(this)">
                        <option value="">-- Ketik untuk mencari --</option>
                        @foreach($productOptions ?? [] as $opt)
                            @php $optLabel = trim(($opt['code'] ?? '') . ' - ' . ($opt['name'] ?? ''), ' -'); @endphp
                            <option value="{{ $opt['code'] }}"
                                    data-name="{{ $opt['name'] }}"
                                    data-spec="{{ $opt['spec'] }}"
                                    data-unit="{{ $opt['unit'] }}"
                                    data-price="{{ $opt['price'] }}">
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="items[0][product_desc]" class="form-control form-control-sm rfq-edit-desc" required></td>
                <td><input type="text" name="items[0][specification]" class="form-control form-control-sm rfq-edit-spec"></td>
                <td><input type="number" step="any" min="0" name="items[0][qty]" class="form-control form-control-sm text-right" value="1" required></td>
                <td><input type="text" name="items[0][unit]" class="form-control form-control-sm text-center rfq-edit-unit" value="PCS"></td>
                <td><input type="number" step="any" min="0" name="items[0][price]" class="form-control form-control-sm text-right rfq-edit-price" value="0"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRFQEditRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
            <div class="d-flex justify-content-end" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="$('#RFQEditPanel').empty()">Batal</button>
                <button type="submit" id="btnSaveEditedRFQ" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan RFQ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var rfqEditProducts = @json($productOptions ?? []);
var rfqEditRowCounter = {{ max(count($details), 1) + 10 }};

function saveEditedRFQ(e) {
    if (e) e.preventDefault();

    var $form = $('#formEditRFQ');
    var $btn  = $('#btnSaveEditedRFQ');
    var origHtml = $btn.html();

    // Kumpulkan ulang data items agar urutan index bersih (0, 1, 2...)
    var items = [];
    $('#rfqEditItemsTable tbody tr').each(function() {
        var $tr = $(this);
        var prodCode = $.trim($tr.find('.rfq-edit-product-select').val() || '');
        var prodDesc = $.trim($tr.find('.rfq-edit-desc').val() || '');
        var spec     = $.trim($tr.find('.rfq-edit-spec').val() || '');
        var qty      = parseFloat($tr.find('input[name*="[qty]"]').val() || 0);
        var unit     = $.trim($tr.find('.rfq-edit-unit').val() || 'PCS');
        var price    = parseFloat($tr.find('.rfq-edit-price').val() || 0);

        if (prodCode !== '' || prodDesc !== '') {
            items.push({
                product_code: prodCode,
                product_desc: prodDesc,
                specification: spec,
                qty: isNaN(qty) ? 0 : qty,
                unit: unit || 'PCS',
                price: isNaN(price) ? 0 : price
            });
        }
    });

    var payload = {
        _token: $form.find('input[name="_token"]').val(),
        rfq_no: $form.find('input[name="rfq_no"]').val(),
        supplier_id: $('#edit_rfq_supplier_id').val(),
        rfq_date: $form.find('input[name="rfq_date"]').val(),
        status: $form.find('select[name="status"]').val(),
        note: $form.find('textarea[name="note"]').val(),
        items: items
    };

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

    $.ajax({
        url: 'rfq-update', // <-- Sesuai Route::post('/rfq-update', ...) di web.php
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Berhasil', res.message || 'Perubahan RFQ berhasil disimpan.', 'success');
                } else {
                    alert(res.message || 'Perubahan RFQ berhasil disimpan.');
                }
                $('#RFQEditPanel').empty();
                refreshParentRFQGrid();
                // Reload grid RFQ jika ada fungsi reload grid di halaman induk
                if (typeof reloadRFQGrid === 'function') {
                    reloadRFQGrid();
                } else if (typeof rfqGrid !== 'undefined' && rfqGrid && typeof rfqGrid.clearAndLoad === 'function') {
                    rfqGrid.clearAndLoad(rfqGrid.xmlUrl);
                }
            } else {
                alert(res.message || 'Gagal menyimpan perubahan RFQ.');
            }
        },
        error: function(xhr) {
            var msg = 'Terjadi kesalahan saat menyimpan data RFQ.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire('Gagal', msg, 'error');
            } else {
                alert(msg);
            }
        },
        complete: function() {
            $btn.prop('disabled', false).html(origHtml);
        }
    });
}

function initRFQEditSelect2($context) {
    if (!$.fn.select2) return;
    var $targets = $context ? $context.find('.rfq-edit-product-select') : $('.rfq-edit-product-select');
    $targets.each(function() {
        var $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            placeholder: '-- Ketik untuk mencari --',
            allowClear: true
        }).off('change.rfqProd').on('change.rfqProd', function() {
            onRFQEditProductChange(this);
        });
    });
}

$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2-rfq-edit').select2({
            width: '100%',
            placeholder: '-- Cari & pilih vendor --',
            allowClear: true
        });
    }
    initRFQEditSelect2();
});

function buildRFQEditProductOptionsHtml(idx) {
    var html = '<select name="items[' + idx + '][product_code]" class="form-control form-control-sm rfq-edit-product-select">';
    html += '<option value="">-- Ketik untuk mencari --</option>';
    (rfqEditProducts || []).forEach(function(opt) {
        var label = ((opt.code || '') + (opt.name ? ' - ' + opt.name : '')).replace(/^ - | - $/g, '');
        html += '<option value="' + $('<div>').text(opt.code || '').html() + '"' +
            ' data-name="' + $('<div>').text(opt.name || '').html() + '"' +
            ' data-spec="' + $('<div>').text(opt.spec || '').html() + '"' +
            ' data-unit="' + $('<div>').text(opt.unit || 'PCS').html() + '"' +
            ' data-price="' + (opt.price || 0) + '">' +
            $('<div>').text(label).html() +
        '</option>';
    });
    html += '</select>';
    return html;
}

function onRFQEditProductChange(el) {
    var $sel = $(el);
    var $opt = $sel.find('option:selected');
    var $tr  = $sel.closest('tr');
    if (!$opt.length || !$sel.val()) return;

    var name  = $opt.data('name');
    var spec  = $opt.data('spec');
    var unit  = $opt.data('unit');
    var price = $opt.data('price');

    if (name !== undefined && String(name) !== '') {
        $tr.find('.rfq-edit-desc').val(name);
    }
    if (spec !== undefined && String(spec) !== '') {
        $tr.find('.rfq-edit-spec').val(spec);
    }
    if (unit !== undefined && String(unit) !== '') {
        $tr.find('.rfq-edit-unit').val(unit);
    }
    if (price !== undefined && Number(price) > 0) {
        $tr.find('.rfq-edit-price').val(price);
    }
}

function removeRFQEditRow(btn) {
    var $tr = $(btn).closest('tr');
    var $sel = $tr.find('.rfq-edit-product-select');
    if ($sel.length && $sel.hasClass('select2-hidden-accessible')) {
        $sel.select2('destroy');
    }
    $tr.remove();
}

function addRFQEditRow() {
    var idx = rfqEditRowCounter++;
    var rowHtml = '<tr data-row-index="' + idx + '">' +
        '<td>' + buildRFQEditProductOptionsHtml(idx) + '</td>' +
        '<td><input type="text" name="items[' + idx + '][product_desc]" class="form-control form-control-sm rfq-edit-desc" required></td>' +
        '<td><input type="text" name="items[' + idx + '][specification]" class="form-control form-control-sm rfq-edit-spec"></td>' +
        '<td><input type="number" step="any" min="0" name="items[' + idx + '][qty]" class="form-control form-control-sm text-right" value="1" required></td>' +
        '<td><input type="text" name="items[' + idx + '][unit]" class="form-control form-control-sm text-center rfq-edit-unit" value="PCS"></td>' +
        '<td><input type="number" step="any" min="0" name="items[' + idx + '][price]" class="form-control form-control-sm text-right rfq-edit-price" value="0"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRFQEditRow(this)"><i class="fas fa-trash"></i></button></td>' +
    '</tr>';

    var $newRow = $(rowHtml);
    $('#rfqEditItemsTable tbody').append($newRow);
    initRFQEditSelect2($newRow);
}

function clearAllRFQEditRows() {
    if (!confirm('Hapus semua baris item pada form edit RFQ ini?')) {
        return;
    }
    $('#rfqEditItemsTable tbody').empty();
}

function refreshParentRFQGrid() {
    // 1. Jika halaman induk punya fungsi reload khusus (misal loadRFQGrid / refreshRFQTable / reloadGrid)
    var fnNames = ['reloadRFQGrid', 'loadRFQGrid', 'refreshRFQGrid', 'reloadGrid', 'refreshGrid'];
    for (var i = 0; i < fnNames.length; i++) {
        if (typeof window[fnNames[i]] === 'function') {
            window[fnNames[i]]();
            return;
        }
    }

    // 2. Jika grid memakai objek DHTMLX Grid (cek beberapa nama variabel umum)
    var gridCandidates = ['rfqGrid', 'myGrid', 'gridRFQ', 'grid'];
    for (var j = 0; j < gridCandidates.length; j++) {
        var g = window[gridCandidates[j]];
        if (g && typeof g.clearAndLoad === 'function') {
            var xmlUrl = g.xmlUrl || g.sUrl || g.xml_url || 'xml-purchasing-order-rfq';
            // Tambah timestamp agar tidak ter-cache browser
            var sep = xmlUrl.indexOf('?') === -1 ? '?' : '&';
            g.clearAndLoad(xmlUrl + sep + '_t=' + new Date().getTime());
            return;
        }
    }

    // 3. Fallback klik tombol Refresh di toolbar atas bila ada
    var $refreshBtn = $('button:contains("Refresh"), a:contains("Refresh")').filter(function() {
        return $(this).closest('#RFQEditPanel').length === 0;
    }).first();
    if ($refreshBtn.length) {
        $refreshBtn.trigger('click');
    }
}
</script>