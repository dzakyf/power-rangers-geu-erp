<div class="ml-2">
    <div class="JudulContent" style="color:#699;">NEW DRAIN ADJUSTMENT</div>
    <div class="card">
        <div class="card-body pt-2">
            <p class="text-muted mb-3">Formulir penyesuaian stok tangki berdasarkan hasil timbang manual sebelum melakukan drain. Setelah di-approve, selisih akan otomatis menyesuaikan saldo sistem tangki.</p>

            <form id="formDrainAdjustmentNew" onsubmit="submitNewDrainAdjustment(event)" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_drain_adj_code" class="font-weight-bold">Kode Adjustment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="text-black form-control" id="new_drain_adj_code" name="adjustment_code" value="{{ $adjCode }}" required maxlength="50" />
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="reloadDrainAdjCode()" title="Generate ulang kode">
                                        <i class="fa fa-refresh"></i> Baru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_drain_adj_date" class="font-weight-bold">Tanggal Adjustment <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="new_drain_adj_date" name="adjustment_date" value="{{ date('Y-m-d') }}" required />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_drain_adj_tank_id" class="font-weight-bold">Tangki <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_drain_adj_tank_id" name="tank_id" required onchange="onDrainAdjTankChange()">
                                <option value="">-- Pilih Tangki --</option>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" data-capacity="{{ (float)$tank->capacity }}">
                                        {{ $tank->name }} (Kapasitas: {{ number_format((float) $tank->capacity, 2, '.', ',') }} KG)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Info Tangki</label>
                            <div id="drainAdjTankInfo" class="alert alert-secondary py-2 px-3 mb-0" style="font-size:12px;">
                                Silakan pilih tangki terlebih dahulu.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_drain_adj_qty_sistem" class="font-weight-bold">Qty Sistem (KG)</label>
                            <input type="text" class="text-black form-control" id="new_drain_adj_qty_sistem" readonly placeholder="0.00" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_drain_adj_qty_timbang" class="font-weight-bold">Qty Timbang Manual (KG) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="new_drain_adj_qty_timbang" name="qty_timbang" required oninput="calculateDrainAdjSelisih()" placeholder="Contoh: 19,500.00" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="new_drain_adj_selisih_qty" class="font-weight-bold">Selisih (Timbang - Sistem)</label>
                            <input type="text" class="text-black form-control" id="new_drain_adj_selisih_qty" readonly placeholder="0.00" />
                        </div>
                    </div>

                    <div class="col-md-12" id="drainAdjValidationWarning" style="display:none;">
                        <div class="alert alert-danger py-2 px-3">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            <span id="drainAdjValidationWarningText"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_drain_adj_document" class="font-weight-bold">Dokumen / Bukti Timbang (Opsional)</label>
                            <input type="file" class="text-black form-control-file" id="new_drain_adj_document" name="document" accept=".pdf,.jpg,.jpeg,.png" />
                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, PNG (Maks. 10 MB).</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_drain_adj_notes" class="font-weight-bold">Alasan / Catatan Penyesuaian</label>
                            <textarea class="text-black form-control" id="new_drain_adj_notes" name="notes" rows="2" placeholder="Tuliskan keterangan hasil timbang manual..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSaveDrainAdjustment">Simpan Drain Adjustment</button>
                    <button type="button" class="btn btn-secondary" onclick="$('#DrainAdjustmentDet').empty();">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var drainAdjTankCapacity = 0;
    var drainAdjTankSistem = 0;

    function reloadDrainAdjCode() {
        $.get('cns-drain-adjustment-generate-code', function(res) {
            if (res && res.success && res.code) {
                $('#new_drain_adj_code').val(res.code);
            }
        });
    }

    function onDrainAdjTankChange() {
        var tankId = $('#new_drain_adj_tank_id').val();
        if (!tankId) {
            $('#drainAdjTankInfo').removeClass('alert-info alert-warning alert-danger').addClass('alert-secondary')
                .html('Silakan pilih tangki terlebih dahulu.');
            drainAdjTankCapacity = 0;
            drainAdjTankSistem = 0;
            $('#new_drain_adj_qty_sistem').val('');
            calculateDrainAdjSelisih();
            return;
        }

        $('#drainAdjTankInfo').html('<i class="fa fa-spinner fa-spin"></i> Mengambil saldo sistem tangki...');

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                drainAdjTankCapacity = Number(res.capacity) || 0;
                drainAdjTankSistem = Number(res.balance) || 0;
                $('#new_drain_adj_qty_sistem').val(cnsFormatNumber(drainAdjTankSistem, 2));

                var infoHtml = '<strong>' + res.name + '</strong> | Kapasitas: <strong>' + cnsFormatNumber(drainAdjTankCapacity, 2) + ' KG</strong> | ' +
                    'Qty Sistem: <strong>' + cnsFormatNumber(drainAdjTankSistem, 2) + ' KG</strong>';

                $('#drainAdjTankInfo').removeClass('alert-secondary alert-warning alert-danger').addClass('alert-info').html(infoHtml);
                calculateDrainAdjSelisih();
            }
        });
    }

    function calculateDrainAdjSelisih() {
        var rawTimbang = String($('#new_drain_adj_qty_timbang').val()).replace(/,/g, '');
        var timbang = Number(rawTimbang) || 0;
        var selisih = timbang - drainAdjTankSistem;
        var prefix = selisih > 0 ? '+' : '';
        $('#new_drain_adj_selisih_qty').val(prefix + cnsFormatNumber(selisih, 2));
        validateDrainAdjInput(rawTimbang !== '');
    }

    function validateDrainAdjInput(hasTimbangInput) {
        var tankId = $('#new_drain_adj_tank_id').val();
        var rawTimbang = String($('#new_drain_adj_qty_timbang').val()).replace(/,/g, '').trim();
        var timbang = Number(rawTimbang);

        if (!tankId || rawTimbang === '' || isNaN(timbang)) {
            $('#drainAdjValidationWarning').hide();
            $('#btnSaveDrainAdjustment').prop('disabled', false);
            return true;
        }

        if (timbang < 0) {
            $('#drainAdjValidationWarningText').text('Qty Timbang tidak boleh kurang dari 0 KG.');
            $('#drainAdjValidationWarning').show();
            $('#btnSaveDrainAdjustment').prop('disabled', true);
            return false;
        }

        if (drainAdjTankCapacity > 0 && timbang > drainAdjTankCapacity) {
            $('#drainAdjValidationWarningText').text('Qty Timbang (' + cnsFormatNumber(timbang, 2) + ' KG) melebihi kapasitas tangki (' + cnsFormatNumber(drainAdjTankCapacity, 2) + ' KG).');
            $('#drainAdjValidationWarning').show();
            $('#btnSaveDrainAdjustment').prop('disabled', true);
            return false;
        }

        if (hasTimbangInput && Math.abs(timbang - drainAdjTankSistem) < 0.01) {
            $('#drainAdjValidationWarningText').text('Qty Timbang sama dengan Qty Sistem. Tidak diperlukan adjustment.');
            $('#drainAdjValidationWarning').show();
            $('#btnSaveDrainAdjustment').prop('disabled', true);
            return false;
        }

        $('#drainAdjValidationWarning').hide();
        $('#btnSaveDrainAdjustment').prop('disabled', false);
        return true;
    }

    function submitNewDrainAdjustment(e) {
        e.preventDefault();

        if (!validateDrainAdjInput(true)) {
            return;
        }

        var form = document.getElementById('formDrainAdjustmentNew');
        var formData = new FormData(form);
        formData.set('qty_timbang', String($('#new_drain_adj_qty_timbang').val()).replace(/,/g, ''));

        $('#btnSaveDrainAdjustment').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'cns-drain-adjustment-store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Drain Adjustment berhasil disimpan.');
                $('#DrainAdjustmentDet').empty();
                refreshDrainAdjData();
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan data.';
                alert(msg);
                $('#btnSaveDrainAdjustment').prop('disabled', false).html('Simpan Drain Adjustment');
            }
        });
    }

    $('#NewDrainAdjBtn').prop('disabled', false).html('New Adjustment');
    if ($('#DrainAdjustmentDet').length) {
        $('html, body').animate({ scrollTop: $('#DrainAdjustmentDet').offset().top - 70 }, 250);
    }
</script>
