<div class="ml-2">
    <div class="JudulContent" style="color:#699;">NEW INBOUND ADJUSTMENT</div>
    <div class="card">
        <div class="card-body pt-2">
            <p class="text-muted mb-3">Formulir penyesuaian kuantiti movement sebelum inbound karena melebihi kapasitas tangki penampung. Kuantiti hasil adjustment yang sudah di-approve akan otomatis menggantikan kuantiti pada saat input Inbound Movement.</p>

            <form id="formInboundAdjustmentNew" onsubmit="submitNewAdjustment(event)" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Adjustment Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_code" class="font-weight-bold">Kode Adjustment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="text-black form-control" id="new_adj_code" name="adjustment_code" value="{{ $adjCode }}" required maxlength="50" />
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="reloadAdjCode()" title="Generate ulang kode">
                                        <i class="fa fa-refresh"></i> Baru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_date" class="font-weight-bold">Tanggal Adjustment <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="new_adj_date" name="adjustment_date" value="{{ date('Y-m-d') }}" required />
                        </div>
                    </div>

                    <!-- Movement Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_movement" class="font-weight-bold">Pilih Nomor Movement (APIPI) <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_adj_movement" name="movement_kode" required onchange="onAdjMovementChange()">
                                <option value="">-- Pilih Movement --</option>
                                @foreach($movements as $m)
                                    <option value="{{ $m['kode'] }}"
                                            data-id="{{ $m['id'] }}"
                                            data-qty="{{ $m['total_qty'] }}"
                                            data-warehouse="{{ $m['dari_gudang'] }}"
                                            data-tank="{{ $m['tujuan_storage'] }}">
                                        {{ $m['kode'] }} | {{ $m['dari_gudang'] }} | {{ number_format($m['total_qty'], 2, '.', ',') }} KG
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="new_adj_movement_id" name="movement_id" />
                            <small class="form-text text-muted">Movement inbound dari APIPI yang perlu disesuaikan kuantitinya.</small>
                        </div>
                    </div>

                    <!-- Asal Warehouse -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_asal_warehouse" class="font-weight-bold">Asal Gudang</label>
                            <input type="text" class="text-black form-control" id="new_adj_asal_warehouse" name="asal_warehouse" readonly />
                        </div>
                    </div>

                    <!-- Tangki Tujuan -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_tank_id" class="font-weight-bold">Tangki Tujuan <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_adj_tank_id" name="tank_id" required onchange="onAdjTankChange()">
                                <option value="">-- Pilih Tangki --</option>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" data-capacity="{{ (float)$tank->capacity }}">
                                        {{ $tank->name }} (Kapasitas: {{ number_format((float) $tank->capacity, 2, '.', ',') }} KG)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Info Kapasitas Tangki -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Status Kapasitas Tangki</label>
                            <div id="adjTankCapacityInfo" class="alert alert-secondary py-2 px-3 mb-0" style="font-size:12px;">
                                Silakan pilih tangki tujuan terlebih dahulu.
                            </div>
                        </div>
                    </div>

                    <!-- Qty Movement Asli -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_adj_original_qty" class="font-weight-bold">Qty Movement Asli (KG) <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="new_adj_original_qty" name="original_qty" readonly required />
                        </div>
                    </div>

                    <!-- Qty Adjustment -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_adj_adjusted_qty" class="font-weight-bold">Qty Adjustment (Muat di Tangki) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="new_adj_adjusted_qty" name="adjusted_qty" required oninput="calculateAdjSelisih()" placeholder="Contoh: 20,000.00" />
                            <small class="form-text text-muted">Kuantiti yang akan masuk ke tangki tujuan.</small>
                        </div>
                    </div>

                    <!-- Selisih Qty (Loss/Variansi) -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_adj_selisih_qty" class="font-weight-bold">Selisih Kuantiti (KG)</label>
                            <input type="text" class="text-black form-control" id="new_adj_selisih_qty" name="selisih_qty" readonly placeholder="0.00" />
                            <small class="form-text text-muted">Selisih = Qty Asli - Qty Adjustment (dicatat sebagai variansi).</small>
                        </div>
                    </div>

                    <!-- Alert Validasi Kapasitas -->
                    <div class="col-md-12" id="adjCapacityWarning" style="display:none;">
                        <div class="alert alert-danger py-2 px-3">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            <span id="adjCapacityWarningText"></span>
                        </div>
                    </div>

                    <!-- Dokumen Pendukung (Opsional) -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_document" class="font-weight-bold">Dokumen / Bukti Pendukung (Opsional)</label>
                            <input type="file" class="text-black form-control-file" id="new_adj_document" name="document" accept=".pdf,.jpg,.jpeg,.png" />
                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, PNG (Maks. 10 MB).</small>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_adj_notes" class="font-weight-bold">Alasan / Catatan Penyesuaian</label>
                            <textarea class="text-black form-control" id="new_adj_notes" name="notes" rows="2" placeholder="Tuliskan keterangan/alasan penyesuaian kuantiti tangki..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSaveAdjustment">Simpan Inbound Adjustment</button>
                    <button type="button" class="btn btn-secondary" onclick="$('#InboundAdjustmentDet').empty();">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var currentTankRemaining = 0;
    var currentTankCapacity = 0;
    var currentTankStock = 0;

    function reloadAdjCode() {
        $.get('cns-inbound-adjustment-generate-code', function(res) {
            if (res && res.success && res.code) {
                $('#new_adj_code').val(res.code);
            }
        });
    }

    function onAdjMovementChange() {
        var $sel = $('#new_adj_movement option:selected');
        var kode = $sel.val();
        var id = $sel.data('id') || '';
        var qty = Number($sel.data('qty')) || 0;
        var warehouse = $sel.data('warehouse') || '';
        var targetTankName = $sel.data('tank') || '';

        $('#new_adj_movement_id').val(id);
        $('#new_adj_original_qty').val(qty > 0 ? cnsFormatNumber(qty, 2) : '');
        $('#new_adj_asal_warehouse').val(warehouse);

        if (targetTankName) {
            $('#new_adj_tank_id option').each(function() {
                if ($.trim($(this).data('name')).toLowerCase() === $.trim(targetTankName).toLowerCase()) {
                    $(this).prop('selected', true);
                    return false;
                }
            });
        }

        onAdjTankChange();
        calculateAdjSelisih();
    }

    function onAdjTankChange() {
        var tankId = $('#new_adj_tank_id').val();
        if (!tankId) {
            $('#adjTankCapacityInfo').removeClass('alert-info alert-warning alert-danger').addClass('alert-secondary')
                .html('Silakan pilih tangki tujuan terlebih dahulu.');
            currentTankRemaining = 0;
            currentTankCapacity = 0;
            currentTankStock = 0;
            validateAdjCapacity();
            return;
        }

        $('#adjTankCapacityInfo').html('<i class="fa fa-spinner fa-spin"></i> Memeriksa ketersediaan ruang tangki...');

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                currentTankCapacity = Number(res.capacity) || 0;
                currentTankStock = Number(res.balance) || 0;
                currentTankRemaining = Math.max(0, currentTankCapacity - currentTankStock);

                var infoHtml = '<strong>' + res.name + '</strong> | Kapasitas: <strong>' + cnsFormatNumber(currentTankCapacity, 2) + ' KG</strong> | ' +
                    'Stok Saat Ini: <strong>' + cnsFormatNumber(currentTankStock, 2) + ' KG</strong> | ' +
                    'Sisa Ruang: <strong class="text-success" style="cursor:pointer;" onclick="fillAdjMaxCapacity()" title="Klik untuk isi Qty Adjustment setara sisa ruang">' +
                    cnsFormatNumber(currentTankRemaining, 2) + ' KG (Klik untuk isi)</strong>';

                $('#adjTankCapacityInfo').removeClass('alert-secondary alert-warning alert-danger').addClass('alert-info').html(infoHtml);
                validateAdjCapacity();
            }
        });
    }

    function fillAdjMaxCapacity() {
        if (currentTankRemaining > 0) {
            var orig = Number(String($('#new_adj_original_qty').val()).replace(/,/g, '')) || 0;
            var fillVal = (orig > 0) ? Math.min(orig, currentTankRemaining) : currentTankRemaining;
            $('#new_adj_adjusted_qty').val(cnsFormatNumber(fillVal, 2));
            calculateAdjSelisih();
        }
    }

    function calculateAdjSelisih() {
        var orig = Number(String($('#new_adj_original_qty').val()).replace(/,/g, '')) || 0;
        var adj = Number(String($('#new_adj_adjusted_qty').val()).replace(/,/g, '')) || 0;
        var selisih = Math.max(0, orig - adj);
        $('#new_adj_selisih_qty').val(cnsFormatNumber(selisih, 2));
        validateAdjCapacity();
    }

    function validateAdjCapacity() {
        var adj = Number(String($('#new_adj_adjusted_qty').val()).replace(/,/g, '')) || 0;
        if (currentTankCapacity > 0 && adj > currentTankRemaining) {
            var over = adj - currentTankRemaining;
            $('#adjCapacityWarningText').html('Qty Adjustment (' + cnsFormatNumber(adj, 2) + ' KG) melebihi sisa ruang tangki yang tersedia (' + cnsFormatNumber(currentTankRemaining, 2) + ' KG) sebanyak ' + cnsFormatNumber(over, 2) + ' KG.');
            $('#adjCapacityWarning').show();
            $('#btnSaveAdjustment').prop('disabled', true);
            return false;
        } else {
            $('#adjCapacityWarning').hide();
            $('#btnSaveAdjustment').prop('disabled', false);
            return true;
        }
    }

    function submitNewAdjustment(e) {
        e.preventDefault();

        if (!validateAdjCapacity()) {
            alert('Qty adjustment melebihi sisa kapasitas tangki.');
            return;
        }

        var form = document.getElementById('formInboundAdjustmentNew');
        var formData = new FormData(form);

        var orig = String($('#new_adj_original_qty').val()).replace(/,/g, '');
        var adj = String($('#new_adj_adjusted_qty').val()).replace(/,/g, '');
        var selisih = String($('#new_adj_selisih_qty').val()).replace(/,/g, '');

        formData.set('original_qty', orig);
        formData.set('adjusted_qty', adj);
        formData.set('selisih_qty', selisih);

        $('#btnSaveAdjustment').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'cns-inbound-adjustment-store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Inbound Adjustment berhasil disimpan.');
                $('#InboundAdjustmentDet').empty();
                refreshAdjData();
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $('#btnSaveAdjustment').prop('disabled', false).html('Simpan Inbound Adjustment');
            }
        });
    }

    $('#NewAdjustmentBtn').prop('disabled', false).html('New Adjustment');
    if ($('#InboundAdjustmentDet').length) {
        $('html, body').animate({ scrollTop: $('#InboundAdjustmentDet').offset().top - 70 }, 250);
    }
</script>
