<div class="ml-2">
    <div class="JudulContent" style="color:#699;">NEW TANK DRAINING</div>
    <div class="card">
        <div class="card-body pt-2">
            <p class="text-muted mb-3">Isi formulir berikut untuk mencatat transaksi pengeluaran (draining) dari tangki timbun.</p>

            <form id="formTankDrainingNew" onsubmit="submitNewDraining(event)" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Draining Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_draining_code" class="font-weight-bold">Kode Draining <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="text-black form-control" id="new_draining_code" name="draining_code" value="{{ $drainingCode }}" required maxlength="100" />
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="reloadDrainingCode()" title="Generate ulang kode">
                                        <i class="fa fa-refresh"></i> Baru
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Kode otomatis di-generate sistem, tetap dapat diedit jika diperlukan.</small>
                        </div>
                    </div>

                    <!-- Draining Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_draining_date" class="font-weight-bold">Tanggal Draining <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="new_draining_date" name="draining_date" value="{{ date('Y-m-d') }}" required />
                        </div>
                    </div>

                    <!-- Tangki Asal -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_tank_id" class="font-weight-bold">Tangki Asal <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_tank_id" name="tank_id" required onchange="onSourceTankChange()">
                                <option value="">-- Pilih Tangki Asal --</option>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}">{{ $tank->name }} (Kapasitas: {{ number_format((float) $tank->capacity, 2, '.', ',') }} KG)</option>
                                @endforeach
                            </select>
                            <div id="sourceTankStockInfo" class="mt-1" style="display:none;">
                                <span class="badge badge-info p-1" id="badgeSourceStock" style="cursor:pointer;" onclick="$('#new_qty').val(currentSourceStock > 0 ? cnsFormatNumber(currentSourceStock, 2) : '')" title="Klik untuk isi seluruh stok tangki">Stok Tersedia: 0.00 KG</span>
                            </div>
                        </div>
                    </div>

                    <!-- Qty Draining -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_qty" class="font-weight-bold">Qty Draining (KG) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="new_qty" name="qty" placeholder="Contoh: 25,000.00" required />
                            <small class="form-text text-muted">Jumlah minyak UCO yang dikeluarkan dalam satuan KG.</small>
                        </div>
                    </div>

                    <!-- Tujuan Draining -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_target_type" class="font-weight-bold">Tujuan Draining <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_target_type" name="target_type" required onchange="onTargetTypeChange()">
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="MOVING">Moving (Pindah Tangki / Relokasi)</option>
                                <option value="PENJUALAN">Penjualan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tujuan Moving Section -->
                    <div class="col-md-6" id="sectionMovingTarget" style="display:none;">
                        <div class="form-group">
                            <label class="font-weight-bold">Tipe Tujuan Moving <span class="text-danger">*</span></label>
                            <select class="text-black form-control mb-2" id="new_moving_target_type" name="moving_target_type" onchange="onMovingTypeChange()">
                                <option value="TANK">Pindah ke Tangki Lain (Master Tangki)</option>
                                <option value="EXTERNAL">Lokasi / Gudang Eksternal</option>
                            </select>

                            <div id="divTargetTank">
                                <label for="new_moving_target_tank_id" class="small font-weight-bold">Tangki Tujuan <span class="text-danger">*</span></label>
                                <select class="text-black form-control" id="new_moving_target_tank_id" name="moving_target_tank_id">
                                    <option value="">-- Pilih Tangki Tujuan --</option>
                                    @foreach($tanks as $tank)
                                        <option value="{{ $tank->id }}" data-name="{{ $tank->name }}">{{ $tank->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="divTargetExternal" style="display:none;">
                                <label for="new_moving_target_external" class="small font-weight-bold">Nama Lokasi / Gudang Tujuan <span class="text-danger">*</span></label>
                                <input type="text" class="text-black form-control" id="new_moving_target_external" name="moving_target_external" placeholder="Contoh: Gudang Penampungan B, Truk Tangki Vendor" maxlength="255" />
                            </div>
                        </div>
                    </div>

                    <!-- Penjualan / Sales Order Section -->
                    <div class="col-md-6" id="sectionSalesOrder" style="display:none;">
                        <div class="form-group">
                            <label for="new_sales_order_id" class="font-weight-bold">Referensi Sales Order (Opsional)</label>
                            <select class="text-black form-control" id="new_sales_order_id" name="sales_order_id">
                                <option value="">-- Tanpa / Belum Ada Sales Order --</option>
                                @foreach($salesOrders as $so)
                                    <option value="{{ $so->id }}">SO: {{ $so->so_no }} (Tgl: {{ $so->so_date ? date('d/m/Y', strtotime($so->so_date)) : '-' }} | PIC: {{ $so->buyer_pic ?? '-' }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Dapat dikosongkan jika belum memiliki nomor Sales Order.</small>
                        </div>
                    </div>

                    <!-- Dokumen BAST Draining -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="new_bast_document" class="font-weight-bold">Dokumen BAST Draining <span class="text-danger">*</span></label>
                            <input type="file" class="text-black form-control-file" id="new_bast_document" name="bast_document" accept=".pdf,.jpg,.jpeg,.png" required />
                            <small class="form-text text-muted">Wajib diupload. Format: PDF, JPG, JPEG, atau PNG (maksimal 10 MB).</small>
                        </div>
                    </div>

                    <!-- Keterangan / Notes -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="new_notes" class="font-weight-bold">Keterangan</label>
                            <textarea class="text-black form-control" id="new_notes" name="notes" rows="2" placeholder="Catatan tambahan transaksi draining..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSubmitNewDraining">Submit</button>
                    <button type="button" class="btn btn-secondary ml-1" onclick="cancelDrainingForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var currentSourceStock = 0;

    function reloadDrainingCode() {
        $.get('cns-tank-draining-generate-code', function(res) {
            if (res && res.success) {
                $('#new_draining_code').val(res.code);
            }
        });
    }

    function onSourceTankChange() {
        var tankId = $('#new_tank_id').val();
        if (!tankId) {
            $('#sourceTankStockInfo').hide();
            currentSourceStock = 0;
            $('#new_qty').val('');
            updateTargetTankOptions();
            return;
        }

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                currentSourceStock = parseFloat(res.balance) || 0;
                var formatted = cnsFormatNumber(currentSourceStock, 2);
                $('#badgeSourceStock').text('Stok Tersedia: ' + formatted + ' KG');
                $('#sourceTankStockInfo').show();
                $('#new_qty').val(currentSourceStock > 0 ? cnsFormatNumber(currentSourceStock, 2) : '');
            }
        });

        updateTargetTankOptions();
    }

    function updateTargetTankOptions() {
        var sourceId = $('#new_tank_id').val();
        $('#new_moving_target_tank_id option').each(function() {
            var val = $(this).val();
            if (val && val == sourceId) {
                $(this).prop('disabled', true).hide();
            } else {
                $(this).prop('disabled', false).show();
            }
        });
        if ($('#new_moving_target_tank_id').val() == sourceId) {
            $('#new_moving_target_tank_id').val('');
        }
    }

    function onTargetTypeChange() {
        var targetType = $('#new_target_type').val();
        if (targetType === 'MOVING') {
            $('#sectionMovingTarget').show();
            $('#sectionSalesOrder').hide();
            onMovingTypeChange();
        } else if (targetType === 'PENJUALAN') {
            $('#sectionMovingTarget').hide();
            $('#sectionSalesOrder').show();
        } else {
            $('#sectionMovingTarget').hide();
            $('#sectionSalesOrder').hide();
        }
    }

    function onMovingTypeChange() {
        var movingType = $('#new_moving_target_type').val();
        if (movingType === 'TANK') {
            $('#divTargetTank').show();
            $('#divTargetExternal').hide();
        } else {
            $('#divTargetTank').hide();
            $('#divTargetExternal').show();
        }
    }

    function cancelDrainingForm() {
        $('#TankDrainingDet').empty();
    }

    function submitNewDraining(e) {
        e.preventDefault();

        var qty = cnsParseNumber($('#new_qty').val(), 0);
        var tankId = $('#new_tank_id').val();
        var targetType = $('#new_target_type').val();

        if (!tankId) {
            alert('Pilih tangki asal terlebih dahulu.');
            return;
        }

        if (qty <= 0) {
            alert('Qty draining harus lebih besar dari 0.');
            return;
        }

        if (currentSourceStock > 0 && qty > currentSourceStock) {
            alert('Qty draining (' + cnsFormatNumber(qty, 2) + ' KG) melebihi stok tangki yang tersedia (' + cnsFormatNumber(currentSourceStock, 2) + ' KG).');
            return;
        }

        if (targetType === 'MOVING') {
            var movingType = $('#new_moving_target_type').val();
            if (movingType === 'TANK') {
                var targetTankId = $('#new_moving_target_tank_id').val();
                if (!targetTankId) {
                    alert('Pilih tangki tujuan.');
                    return;
                }
                if (targetTankId == tankId) {
                    alert('Tangki tujuan tidak boleh sama dengan tangki asal.');
                    return;
                }
            } else if (movingType === 'EXTERNAL') {
                var ext = $.trim($('#new_moving_target_external').val());
                if (!ext) {
                    alert('Masukkan nama lokasi / gudang tujuan.');
                    return;
                }
            }
        }

        var bastInput = $('#new_bast_document')[0];
        var bastFile = bastInput && bastInput.files ? bastInput.files[0] : null;
        if (!bastFile) {
            alert('Dokumen BAST draining wajib diupload.');
            return;
        }
        var bastExt = bastFile.name.split('.').pop().toLowerCase();
        if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(bastExt) === -1) {
            alert('Dokumen BAST harus berformat PDF, JPG, JPEG, atau PNG.');
            return;
        }
        if (bastFile.size > 10 * 1024 * 1024) {
            alert('Ukuran dokumen BAST maksimal 10 MB.');
            return;
        }

        var $btn = $('#btnSubmitNewDraining');
        $btn.prop('disabled', true).text('Menyimpan...');

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        var formData = new FormData();
        formData.append('_token', token);
        formData.append('draining_code', $('#new_draining_code').val());
        formData.append('draining_date', $('#new_draining_date').val());
        formData.append('tank_id', tankId);
        formData.append('qty', qty);
        formData.append('target_type', targetType);
        if (targetType === 'MOVING') {
            var movingTargetType = $('#new_moving_target_type').val();
            formData.append('moving_target_type', movingTargetType);
            if (movingTargetType === 'TANK') {
                formData.append('moving_target_tank_id', $('#new_moving_target_tank_id').val());
            } else {
                formData.append('moving_target_external', $('#new_moving_target_external').val());
            }
        } else if (targetType === 'PENJUALAN' && $('#new_sales_order_id').val()) {
            formData.append('sales_order_id', $('#new_sales_order_id').val());
        }
        formData.append('notes', $('#new_notes').val());
        formData.append('bast_document', bastFile);

        $.ajax({
            url: 'cns-tank-draining-store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                alert(res.message || 'Tank draining berhasil disimpan.');
                $('#TankDrainingDet').empty();
                refreshdata();
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $btn.prop('disabled', false).text('Submit');
            }
        });
    }
</script>
