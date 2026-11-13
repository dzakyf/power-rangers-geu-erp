<div class="ml-2">
    <div class="JudulContent" style="color:#699;">DETAIL INBOUND ADJUSTMENT: {{ $adjustment->adjustment_code }}</div>
    <div class="card">
        <div class="card-body pt-2">
            <!-- Status Badge Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light border rounded">
                <div>
                    <strong>Status: </strong>
                    @if($adjustment->approval_status === 'APPROVED')
                        <span class="badge badge-success px-2 py-1" style="font-size:13px;">APPROVED</span>
                        <span class="text-muted ml-2 small">Oleh: {{ $adjustment->approved_by }} ({{ $adjustment->approved_at ? $adjustment->approved_at->format('d/m/Y H:i') : '-' }})</span>
                    @elseif($adjustment->approval_status === 'REJECTED')
                        <span class="badge badge-danger px-2 py-1" style="font-size:13px;">REJECTED</span>
                        <span class="text-muted ml-2 small">Oleh: {{ $adjustment->approved_by }} ({{ $adjustment->approved_at ? $adjustment->approved_at->format('d/m/Y H:i') : '-' }})</span>
                    @else
                        <span class="badge badge-warning px-2 py-1" style="font-size:13px;">PENDING APPROVAL</span>
                        <span class="text-muted ml-2 small">Menunggu persetujuan Supervisor / Manager</span>
                    @endif
                </div>
                <div>
                    <span class="text-muted small">Dibuat oleh: <strong>{{ $adjustment->created_by }}</strong> pada {{ $adjustment->created_at ? $adjustment->created_at->format('d/m/Y H:i') : '-' }}</span>
                </div>
            </div>

            @if($adjustment->approval_status === 'REJECTED' && $adjustment->rejection_reason)
                <div class="alert alert-danger py-2 px-3 mb-3">
                    <strong><i class="fa fa-times-circle mr-1"></i> Alasan Penolakan:</strong>
                    {{ $adjustment->rejection_reason }}
                </div>
            @endif

            <form id="formInboundAdjustmentDetail" onsubmit="submitUpdateAdjustment(event, {{ $adjustment->id }})" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Adjustment Code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_code" class="font-weight-bold">Kode Adjustment <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="edit_adj_code" name="adjustment_code" value="{{ $adjustment->adjustment_code }}" required readonly />
                        </div>
                    </div>

                    <!-- Adjustment Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_date" class="font-weight-bold">Tanggal Adjustment <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="edit_adj_date" name="adjustment_date" value="{{ $adjustment->adjustment_date ? $adjustment->adjustment_date->format('Y-m-d') : date('Y-m-d') }}" required {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }} />
                        </div>
                    </div>

                    <!-- Movement Kode -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_movement_kode" class="font-weight-bold">Nomor Movement <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="edit_adj_movement_kode" name="movement_kode" value="{{ $adjustment->movement_kode }}" required readonly />
                            <input type="hidden" id="edit_adj_movement_id" name="movement_id" value="{{ $adjustment->movement_id }}" />
                        </div>
                    </div>

                    <!-- Asal Warehouse -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_asal_warehouse" class="font-weight-bold">Asal Gudang</label>
                            <input type="text" class="text-black form-control" id="edit_adj_asal_warehouse" name="asal_warehouse" value="{{ $adjustment->asal_warehouse }}" readonly />
                        </div>
                    </div>

                    <!-- Tangki Tujuan -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_tank_id" class="font-weight-bold">Tangki Tujuan <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="edit_adj_tank_id" name="tank_id" required onchange="onEditAdjTankChange()" {{ $adjustment->approval_status === 'APPROVED' ? 'disabled' : '' }}>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" {{ $tank->id == $adjustment->tank_id ? 'selected' : '' }}>
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
                            <div id="editAdjTankCapacityInfo" class="alert alert-info py-2 px-3 mb-0" style="font-size:12px;">
                                Memuat status kapasitas tangki...
                            </div>
                        </div>
                    </div>

                    <!-- Qty Movement Asli -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_adj_original_qty" class="font-weight-bold">Qty Movement Asli (KG)</label>
                            <input type="text" class="text-black form-control" id="edit_adj_original_qty" name="original_qty" value="{{ number_format((float) $adjustment->original_qty, 2, '.', ',') }}" readonly />
                        </div>
                    </div>

                    <!-- Qty Adjustment -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_adj_adjusted_qty" class="font-weight-bold">Qty Adjustment (Muat di Tangki) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="edit_adj_adjusted_qty" name="adjusted_qty" value="{{ number_format((float) $adjustment->adjusted_qty, 2, '.', ',') }}" required oninput="calculateEditAdjSelisih()" {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }} />
                        </div>
                    </div>

                    <!-- Selisih Qty -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_adj_selisih_qty" class="font-weight-bold">Selisih Kuantiti (KG)</label>
                            <input type="text" class="text-black form-control" id="edit_adj_selisih_qty" name="selisih_qty" value="{{ number_format((float) $adjustment->selisih_qty, 2, '.', ',') }}" readonly />
                        </div>
                    </div>

                    <!-- Alert Validasi Kapasitas -->
                    <div class="col-md-12" id="editAdjCapacityWarning" style="display:none;">
                        <div class="alert alert-danger py-2 px-3">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            <span id="editAdjCapacityWarningText"></span>
                        </div>
                    </div>

                    <!-- Dokumen Pendukung -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_document" class="font-weight-bold">Dokumen Pendukung</label>
                            @if(!empty($adjustment->document_path))
                                <div class="mb-2">
                                    <a href="{{ route('cns-inbound-adjustment.document', ['id' => $adjustment->id], false) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-file-text-o mr-1"></i> Lihat Dokumen ({{ $adjustment->document_name ?: 'Dokumen Pendukung' }})
                                    </a>
                                </div>
                            @endif
                            @if($adjustment->approval_status !== 'APPROVED')
                                <input type="file" class="text-black form-control-file" id="edit_adj_document" name="document" accept=".pdf,.jpg,.jpeg,.png" />
                                <small class="form-text text-muted">Upload file baru untuk mengganti dokumen yang ada.</small>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_adj_notes" class="font-weight-bold">Alasan / Catatan Penyesuaian</label>
                            <textarea class="text-black form-control" id="edit_adj_notes" name="notes" rows="2" {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }}>{{ $adjustment->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        @if($adjustment->approval_status !== 'APPROVED')
                            <button type="submit" class="btn btn-primary" id="btnUpdateAdjustment">Simpan Perubahan</button>
                            <button type="button" class="btn btn-danger ml-1" onclick="deleteInboundAdjustment({{ $adjustment->id }})">Hapus</button>
                        @endif
                        <button type="button" class="btn btn-secondary ml-1" onclick="$('#InboundAdjustmentDet').empty();">Tutup</button>
                    </div>

                    <div>
                        @if($adjustment->approval_status !== 'APPROVED')
                            <button type="button" class="btn btn-success" onclick="approveInboundAdjustment({{ $adjustment->id }})">
                                <i class="fa fa-check mr-1"></i> Approve Adjustment
                            </button>
                            <button type="button" class="btn btn-outline-danger ml-1" onclick="rejectInboundAdjustment({{ $adjustment->id }})">
                                <i class="fa fa-times mr-1"></i> Reject Adjustment
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var editTankRemaining = 0;
    var editTankCapacity = 0;
    var editTankStock = 0;

    function onEditAdjTankChange() {
        var tankId = $('#edit_adj_tank_id').val();
        if (!tankId) return;

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                editTankCapacity = Number(res.capacity) || 0;
                editTankStock = Number(res.balance) || 0;
                editTankRemaining = Math.max(0, editTankCapacity - editTankStock);

                var infoHtml = '<strong>' + res.name + '</strong> | Kapasitas: <strong>' + cnsFormatNumber(editTankCapacity, 2) + ' KG</strong> | ' +
                    'Stok Saat Ini: <strong>' + cnsFormatNumber(editTankStock, 2) + ' KG</strong> | ' +
                    'Sisa Ruang: <strong class="text-success">' + cnsFormatNumber(editTankRemaining, 2) + ' KG</strong>';

                $('#editAdjTankCapacityInfo').html(infoHtml);
                validateEditAdjCapacity();
            }
        });
    }

    function calculateEditAdjSelisih() {
        var orig = Number(String($('#edit_adj_original_qty').val()).replace(/,/g, '')) || 0;
        var adj = Number(String($('#edit_adj_adjusted_qty').val()).replace(/,/g, '')) || 0;
        var selisih = Math.max(0, orig - adj);
        $('#edit_adj_selisih_qty').val(cnsFormatNumber(selisih, 2));
        validateEditAdjCapacity();
    }

    function validateEditAdjCapacity() {
        var adj = Number(String($('#edit_adj_adjusted_qty').val()).replace(/,/g, '')) || 0;
        if (editTankCapacity > 0 && adj > editTankRemaining) {
            var over = adj - editTankRemaining;
            $('#editAdjCapacityWarningText').html('Qty Adjustment (' + cnsFormatNumber(adj, 2) + ' KG) melebihi sisa ruang tangki yang tersedia (' + cnsFormatNumber(editTankRemaining, 2) + ' KG) sebanyak ' + cnsFormatNumber(over, 2) + ' KG.');
            $('#editAdjCapacityWarning').show();
            $('#btnUpdateAdjustment').prop('disabled', true);
            return false;
        } else {
            $('#editAdjCapacityWarning').hide();
            $('#btnUpdateAdjustment').prop('disabled', false);
            return true;
        }
    }

    function submitUpdateAdjustment(e, id) {
        e.preventDefault();

        if (!validateEditAdjCapacity()) {
            alert('Qty adjustment melebihi sisa kapasitas tangki.');
            return;
        }

        var form = document.getElementById('formInboundAdjustmentDetail');
        var formData = new FormData(form);

        var orig = String($('#edit_adj_original_qty').val()).replace(/,/g, '');
        var adj = String($('#edit_adj_adjusted_qty').val()).replace(/,/g, '');
        var selisih = String($('#edit_adj_selisih_qty').val()).replace(/,/g, '');

        formData.set('original_qty', orig);
        formData.set('adjusted_qty', adj);
        formData.set('selisih_qty', selisih);

        $('#btnUpdateAdjustment').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'cns-inbound-adjustment-update/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Data berhasil diperbarui.');
                $('#InboundAdjustmentDet').empty();
                refreshAdjData();
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan saat memperbarui data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                $('#btnUpdateAdjustment').prop('disabled', false).html('Simpan Perubahan');
            }
        });
    }

    function deleteInboundAdjustment(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus data Inbound Adjustment ini?')) return;

        $.ajax({
            url: 'cns-inbound-adjustment-delete/' + id,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Data berhasil dihapus.');
                $('#InboundAdjustmentDet').empty();
                refreshAdjData();
            },
            error: function(xhr) {
                var msg = 'Gagal menghapus data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    function approveInboundAdjustment(id) {
        if (!confirm('Approve Inbound Adjustment ini? Setelah di-approve, Qty Inbound Movement akan otomatis menggunakan nilai Qty Adjustment.')) return;

        $.ajax({
            url: 'cns-inbound-adjustment-approve/' + id,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Inbound Adjustment berhasil di-approve.');
                LoadM('InboundAdjustmentDet', 'cns-inbound-adjustment-detail?ID=' + id);
                refreshAdjData();
            },
            error: function(xhr) {
                var msg = 'Gagal approve data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    function rejectInboundAdjustment(id) {
        var reason = prompt('Masukkan alasan penolakan Inbound Adjustment:');
        if (reason === null) return;
        reason = $.trim(reason);
        if (!reason) {
            alert('Alasan penolakan wajib diisi.');
            return;
        }

        $.ajax({
            url: 'cns-inbound-adjustment-reject/' + id,
            type: 'POST',
            data: { reason: reason },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                alert(res.message || 'Inbound Adjustment ditolak.');
                LoadM('InboundAdjustmentDet', 'cns-inbound-adjustment-detail?ID=' + id);
                refreshAdjData();
            },
            error: function(xhr) {
                var msg = 'Gagal reject data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    onEditAdjTankChange();
    $('#DetailAdjustmentBtn').prop('disabled', false).html('Detail / Edit');
    if ($('#InboundAdjustmentDet').length) {
        $('html, body').animate({ scrollTop: $('#InboundAdjustmentDet').offset().top - 70 }, 250);
    }
</script>
