<div class="ml-2">
    <div class="JudulContent" style="color:#699;">DETAIL / EDIT TANK DRAINING</div>
    <div class="card">
        <div class="card-body pt-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">Informasi dan perubahan transaksi pengeluaran minyak dari tangki.</p>
                <div>
                    <span class="badge badge-light border p-2">Dibuat oleh: <strong>{{ $draining->created_by ?? '-' }}</strong> pada {{ $draining->created_at ? $draining->created_at->format('d/m/Y H:i') : '-' }}</span>
                    @if($draining->updated_by)
                        <span class="badge badge-light border p-2 ml-1">Diperbarui: <strong>{{ $draining->updated_by }}</strong> pada {{ $draining->updated_at ? $draining->updated_at->format('d/m/Y H:i') : '-' }}</span>
                    @endif
                </div>
            </div>

            <form id="formTankDrainingEdit" onsubmit="submitUpdateDraining(event, {{ $draining->id }})" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Draining Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_draining_code" class="font-weight-bold">Kode Draining <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="edit_draining_code" name="draining_code" value="{{ $draining->draining_code }}" required maxlength="100" />
                        </div>
                    </div>

                    <!-- Draining Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_draining_date" class="font-weight-bold">Tanggal Draining <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="edit_draining_date" name="draining_date" value="{{ $draining->draining_date ? $draining->draining_date->format('Y-m-d') : date('Y-m-d') }}" required />
                        </div>
                    </div>

                    <!-- Tangki Asal -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_tank_id" class="font-weight-bold">Tangki Asal <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="edit_tank_id" name="tank_id" required onchange="onEditSourceTankChange()">
                                <option value="">-- Pilih Tangki Asal --</option>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" {{ $tank->id == $draining->tank_id ? 'selected' : '' }}>
                                        {{ $tank->name }} (Kapasitas: {{ number_format((float) $tank->capacity, 2, '.', ',') }} KG)
                                    </option>
                                @endforeach
                            </select>
                            <div id="editSourceTankStockInfo" class="mt-1">
                                <span class="badge badge-info p-1" id="editBadgeSourceStock" style="cursor:pointer;" onclick="var av = ($('#edit_tank_id').val() == editOriginalSourceId ? (editCurrentSourceStock + editOriginalQty) : editCurrentSourceStock); if(av > 0) $('#edit_qty').val(cnsFormatNumber(av, 2));" title="Klik untuk isi seluruh stok tangki">Stok Saat Ini: {{ number_format((float) $currentStock, 2, '.', ',') }} KG</span>
                            </div>
                        </div>
                    </div>

                    <!-- Qty Draining -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_qty" class="font-weight-bold">Qty Draining (KG) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="edit_qty" name="qty" value="{{ number_format((float) $draining->qty, 2, '.', ',') }}" required />
                            <small class="form-text text-muted">Jumlah minyak UCO yang dikeluarkan dalam satuan KG.</small>
                        </div>
                    </div>

                    <!-- Tujuan Draining -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_target_type" class="font-weight-bold">Tujuan Draining <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="edit_target_type" name="target_type" required onchange="onEditTargetTypeChange()">
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="MOVING" {{ $draining->target_type === 'MOVING' ? 'selected' : '' }}>Moving (Pindah Tangki / Relokasi)</option>
                                <option value="PENJUALAN" {{ $draining->target_type === 'PENJUALAN' ? 'selected' : '' }}>Penjualan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tujuan Moving Section -->
                    <div class="col-md-6" id="editSectionMovingTarget" style="{{ $draining->target_type === 'MOVING' ? '' : 'display:none;' }}">
                        <div class="form-group">
                            <label class="font-weight-bold">Tipe Tujuan Moving <span class="text-danger">*</span></label>
                            <select class="text-black form-control mb-2" id="edit_moving_target_type" name="moving_target_type" onchange="onEditMovingTypeChange()">
                                <option value="TANK" {{ $draining->moving_target_type === 'TANK' || !$draining->moving_target_type ? 'selected' : '' }}>Pindah ke Tangki Lain (Master Tangki)</option>
                                <option value="EXTERNAL" {{ $draining->moving_target_type === 'EXTERNAL' ? 'selected' : '' }}>Lokasi / Gudang Eksternal</option>
                            </select>

                            <div id="editDivTargetTank" style="{{ $draining->moving_target_type === 'EXTERNAL' ? 'display:none;' : '' }}">
                                <label for="edit_moving_target_tank_id" class="small font-weight-bold">Tangki Tujuan <span class="text-danger">*</span></label>
                                <select class="text-black form-control" id="edit_moving_target_tank_id" name="moving_target_tank_id">
                                    <option value="">-- Pilih Tangki Tujuan --</option>
                                    @foreach($tanks as $tank)
                                        <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" {{ $tank->id == $draining->moving_target_tank_id ? 'selected' : '' }}>
                                            {{ $tank->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="editDivTargetExternal" style="{{ $draining->moving_target_type === 'EXTERNAL' ? '' : 'display:none;' }}">
                                <label for="edit_moving_target_external" class="small font-weight-bold">Nama Lokasi / Gudang Tujuan <span class="text-danger">*</span></label>
                                <select class="text-black form-control" id="edit_moving_target_external" name="moving_target_external" style="width: 100%;">
                                    <option value="">-- Pilih Gudang Tujuan --</option>
                                    @if(!empty($draining->moving_target_external))
                                        <option value="{{ $draining->moving_target_external }}" selected>{{ $draining->moving_target_external }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Penjualan / Sales Order Section -->
                    <div class="col-md-6" id="editSectionSalesOrder" style="{{ $draining->target_type === 'PENJUALAN' ? '' : 'display:none;' }}">
                        <div class="form-group">
                            <label for="edit_sales_order_id" class="font-weight-bold">Referensi Sales Order (Opsional)</label>
                            <select class="text-black form-control" id="edit_sales_order_id" name="sales_order_id">
                                <option value="">-- Tanpa / Belum Ada Sales Order --</option>
                                @foreach($salesOrders as $so)
                                    <option value="{{ $so->id }}" {{ $so->id == $draining->sales_order_id ? 'selected' : '' }}>
                                        SO: {{ $so->so_no }} (Tgl: {{ $so->so_date ? date('d/m/Y', strtotime($so->so_date)) : '-' }} | PIC: {{ $so->buyer_pic ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Dapat dikosongkan jika belum memiliki nomor Sales Order.</small>
                        </div>
                    </div>

                    <!-- Dokumen BAST Draining -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_bast_document" class="font-weight-bold">
                                Dokumen BAST Draining
                                @if(empty($draining->bast_document_path))
                                    <span class="text-danger">*</span>
                                @else
                                    <span class="text-muted font-weight-normal">(Opsional untuk ganti file)</span>
                                @endif
                            </label>
                            @if(!empty($draining->bast_document_path))
                                <div class="mb-2">
                                    <a href="{{ route('cns-tank-draining.bast', ['id' => $draining->id], false) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-file-text-o"></i> Lihat BAST ({{ $draining->bast_document_name ?: 'Dokumen BAST' }})
                                    </a>
                                </div>
                            @else
                                <div class="mb-2">
                                    <span class="badge badge-warning p-1">Dokumen BAST belum tersedia. Wajib upload saat update.</span>
                                </div>
                            @endif
                            <input type="file" class="text-black form-control-file" id="edit_bast_document" name="bast_document" accept=".pdf,.jpg,.jpeg,.png" {{ empty($draining->bast_document_path) ? 'required' : '' }} />
                            <small class="form-text text-muted">Format: PDF, JPG, JPEG, atau PNG (maksimal 10 MB).</small>
                        </div>
                    </div>

                    <!-- Keterangan / Notes -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_notes" class="font-weight-bold">Keterangan</label>
                            <textarea class="text-black form-control" id="edit_notes" name="notes" rows="2" placeholder="Catatan tambahan transaksi draining...">{{ $draining->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <div id="editDrainingPendingWarning" class="alert alert-danger py-2 px-3 mt-2" style="{{ $pendingDrainAdjustment ? '' : 'display:none;' }}">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    <span id="editDrainingPendingWarningText">
                        @if($pendingDrainAdjustment)
                            Tangki ini memiliki Drain Adjustment ({{ $pendingDrainAdjustment->adjustment_code }}) berstatus PENDING (Qty Timbang: {{ number_format((float) $pendingDrainAdjustment->qty_timbang, 2, '.', ',') }} KG). Selesaikan approval terlebih dahulu sebelum memperbarui draining.
                        @endif
                    </span>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary" id="btnSubmitEditDraining" {{ $pendingDrainAdjustment ? 'disabled' : '' }}>Update</button>
                        <button type="button" class="btn btn-secondary ml-1" onclick="cancelEditDrainingForm()">Cancel</button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger" id="btnDeleteDraining" onclick="deleteDraining({{ $draining->id }})">Hapus Draining</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var editOriginalSourceId = {{ $draining->tank_id }};
    var editOriginalQty = {{ (float) $draining->qty }};
    var editCurrentSourceStock = {{ (float) $currentStock }};
    var editHasBastDocument = {{ !empty($draining->bast_document_path) ? 'true' : 'false' }};

    function onEditSourceTankChange() {
        var tankId = $('#edit_tank_id').val();
        if (!tankId) {
            $('#editSourceTankStockInfo').hide();
            $('#editDrainingPendingWarning').hide();
            $('#btnSubmitEditDraining').prop('disabled', false);
            editCurrentSourceStock = 0;
            updateEditTargetTankOptions();
            return;
        }

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                editCurrentSourceStock = parseFloat(res.balance) || 0;
                var formatted = cnsFormatNumber(editCurrentSourceStock, 2);
                $('#editBadgeSourceStock').text('Stok Saat Ini: ' + formatted + ' KG');
                $('#editSourceTankStockInfo').show();
                var av = (tankId == editOriginalSourceId) ? (editCurrentSourceStock + editOriginalQty) : editCurrentSourceStock;
                $('#edit_qty').val(av > 0 ? cnsFormatNumber(av, 2) : '');

                if (res.pending_drain_adjustment) {
                    var p = res.pending_drain_adjustment;
                    $('#editDrainingPendingWarningText').text(
                        'Tangki ini memiliki Drain Adjustment (' + p.adjustment_code + ') berstatus PENDING (Qty Timbang: ' + cnsFormatNumber(p.qty_timbang, 2) + ' KG). Selesaikan approval terlebih dahulu sebelum memperbarui draining.'
                    );
                    $('#editDrainingPendingWarning').show();
                    $('#btnSubmitEditDraining').prop('disabled', true);
                } else {
                    $('#editDrainingPendingWarning').hide();
                    $('#btnSubmitEditDraining').prop('disabled', false);
                }
            }
        });

        updateEditTargetTankOptions();
    }

    function updateEditTargetTankOptions() {
        var sourceId = $('#edit_tank_id').val();
        $('#edit_moving_target_tank_id option').each(function() {
            var val = $(this).val();
            if (val && val == sourceId) {
                $(this).prop('disabled', true).hide();
            } else {
                $(this).prop('disabled', false).show();
            }
        });
        if ($('#edit_moving_target_tank_id').val() == sourceId) {
            $('#edit_moving_target_tank_id').val('');
        }
    }

    function onEditTargetTypeChange() {
        var targetType = $('#edit_target_type').val();
        if (targetType === 'MOVING') {
            $('#editSectionMovingTarget').show();
            $('#editSectionSalesOrder').hide();
            onEditMovingTypeChange();
        } else if (targetType === 'PENJUALAN') {
            $('#editSectionMovingTarget').hide();
            $('#editSectionSalesOrder').show();
        } else {
            $('#editSectionMovingTarget').hide();
            $('#editSectionSalesOrder').hide();
        }
    }

    function loadEditMovingExternalWarehouses(selectedVal) {
        var $sel = $('#edit_moving_target_external');
        if ($sel.data('loaded')) {
            return;
        }
        selectedVal = selectedVal || $sel.val() || '';
        if (!window._cnsWarehousesReq) {
            window._cnsWarehousesReq = $.get('cns-master-warehouses').fail(function() {
                window._cnsWarehousesReq = null;
            });
        }
        window._cnsWarehousesReq.done(function(res) {
            $sel.empty().append($('<option>', { value: '', text: '-- Pilih Gudang Tujuan --' }));
            var found = false;
            $.each((res && res.data) || [], function(_, w) {
                var name = (w && w.name) ? String(w.name) : '';
                if (!name) return;
                var isSelected = (selectedVal && selectedVal === name);
                if (isSelected) found = true;
                $sel.append($('<option>', { value: name, text: name, selected: isSelected }));
            });
            if (selectedVal && !found) {
                $sel.append($('<option>', { value: selectedVal, text: selectedVal, selected: true }));
            }
            $sel.data('loaded', true);
            if (typeof $.fn.select2 === 'function') {
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                $sel.select2({
                    placeholder: '-- Pilih Gudang Tujuan --',
                    allowClear: true,
                    width: '100%'
                });
            }
            $sel.val(selectedVal || '').trigger('change');
        });
    }

    function onEditMovingTypeChange() {
        var movingType = $('#edit_moving_target_type').val();
        if (movingType === 'TANK') {
            $('#editDivTargetTank').show();
            $('#editDivTargetExternal').hide();
        } else {
            $('#editDivTargetTank').hide();
            $('#editDivTargetExternal').show();
            loadEditMovingExternalWarehouses();
        }
    }

    function cancelEditDrainingForm() {
        $('#TankDrainingDet').empty();
    }

    function submitUpdateDraining(e, id) {
        e.preventDefault();

        var qty = cnsParseNumber($('#edit_qty').val(), 0);
        var tankId = $('#edit_tank_id').val();
        var targetType = $('#edit_target_type').val();

        if (!tankId) {
            alert('Pilih tangki asal terlebih dahulu.');
            return;
        }

        if (qty <= 0) {
            alert('Qty draining harus lebih besar dari 0.');
            return;
        }

        // Available stock considering current transaction's outbound
        var available = (tankId == editOriginalSourceId) ? (editCurrentSourceStock + editOriginalQty) : editCurrentSourceStock;
        if (qty > available) {
            alert('Qty draining (' + cnsFormatNumber(qty, 2) + ' KG) melebihi stok tangki yang tersedia (' + cnsFormatNumber(available, 2) + ' KG).');
            return;
        }

        if (targetType === 'MOVING') {
            var movingType = $('#edit_moving_target_type').val();
            if (movingType === 'TANK') {
                var targetTankId = $('#edit_moving_target_tank_id').val();
                if (!targetTankId) {
                    alert('Pilih tangki tujuan.');
                    return;
                }
                if (targetTankId == tankId) {
                    alert('Tangki tujuan tidak boleh sama dengan tangki asal.');
                    return;
                }
            } else if (movingType === 'EXTERNAL') {
                var ext = $.trim($('#edit_moving_target_external').val());
                if (!ext) {
                    alert('Pilih lokasi / gudang tujuan.');
                    return;
                }
            }
        }

        var bastInput = $('#edit_bast_document')[0];
        var bastFile = bastInput && bastInput.files ? bastInput.files[0] : null;
        if (!editHasBastDocument && !bastFile) {
            alert('Dokumen BAST draining wajib diupload.');
            return;
        }
        if (bastFile) {
            var bastExt = bastFile.name.split('.').pop().toLowerCase();
            if (['pdf', 'jpg', 'jpeg', 'png'].indexOf(bastExt) === -1) {
                alert('Dokumen BAST harus berformat PDF, JPG, JPEG, atau PNG.');
                return;
            }
            if (bastFile.size > 10 * 1024 * 1024) {
                alert('Ukuran dokumen BAST maksimal 10 MB.');
                return;
            }
        }

        var $btn = $('#btnSubmitEditDraining');
        $btn.prop('disabled', true).text('Menyimpan...');

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        var formData = new FormData();
        formData.append('_token', token);
        formData.append('draining_code', $('#edit_draining_code').val());
        formData.append('draining_date', $('#edit_draining_date').val());
        formData.append('tank_id', tankId);
        formData.append('qty', qty);
        formData.append('target_type', targetType);
        if (targetType === 'MOVING') {
            var movingTargetType = $('#edit_moving_target_type').val();
            formData.append('moving_target_type', movingTargetType);
            if (movingTargetType === 'TANK') {
                formData.append('moving_target_tank_id', $('#edit_moving_target_tank_id').val());
            } else {
                formData.append('moving_target_external', $('#edit_moving_target_external').val());
            }
        } else if (targetType === 'PENJUALAN' && $('#edit_sales_order_id').val()) {
            formData.append('sales_order_id', $('#edit_sales_order_id').val());
        }
        formData.append('notes', $('#edit_notes').val());
        if (bastFile) {
            formData.append('bast_document', bastFile);
        }

        $.ajax({
            url: 'cns-tank-draining-update/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                alert(res.message || 'Tank draining berhasil diperbarui.');
                $('#TankDrainingDet').empty();
                refreshdata();
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan saat memperbarui data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $btn.prop('disabled', false).text('Update');
            }
        });
    }

    function deleteDraining(id) {
        if (!confirm('Yakin ingin menghapus data Tank Draining ini? Mutasi stok di UCO Storage Pelindo akan otomatis dikembalikan.')) {
            return;
        }

        var $btn = $('#btnDeleteDraining');
        $btn.prop('disabled', true).text('Menghapus...');

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        $.ajax({
            url: 'cns-tank-draining-delete/' + id,
            type: 'POST',
            data: {
                _token: token
            },
            success: function(res) {
                alert(res.message || 'Data berhasil dihapus.');
                $('#TankDrainingDet').empty();
                refreshdata();
            },
            error: function(xhr) {
                var msg = 'Gagal menghapus data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $btn.prop('disabled', false).text('Hapus Draining');
            }
        });
    }

    updateEditTargetTankOptions();
    if ($('#edit_moving_target_type').val() === 'EXTERNAL') {
        loadEditMovingExternalWarehouses({!! json_encode($draining->moving_target_external ?? '') !!});
    }
</script>
