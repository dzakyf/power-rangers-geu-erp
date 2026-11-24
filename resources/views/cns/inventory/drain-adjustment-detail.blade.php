<div class="mt-3">
    <div class="cns-section-header">DETAIL DRAIN ADJUSTMENT: {{ $adjustment->adjustment_code }}</div>
    <div class="card cns-card">
        <div class="card-body pt-3">
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light border rounded">
                <div>
                    <strong>Status: </strong>
                    @if($adjustment->approval_status === 'APPROVED')
                        <span class="badge rounded-pill cns-status-badge bg-success">APPROVED</span>
                        <span class="text-muted ml-2 small">Oleh: {{ $adjustment->approved_by }} ({{ $adjustment->approved_at ? $adjustment->approved_at->format('d/m/Y H:i') : '-' }})</span>
                    @elseif($adjustment->approval_status === 'REJECTED')
                        <span class="badge badge-danger px-2 py-1" style="font-size:13px;">REJECTED</span>
                        <span class="text-muted ml-2 small">Oleh: {{ $adjustment->approved_by }} ({{ $adjustment->approved_at ? $adjustment->approved_at->format('d/m/Y H:i') : '-' }})</span>
                    @else
                        <span class="badge rounded-pill cns-status-badge bg-warning text-dark">PENDING APPROVAL</span>
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

            <form id="formDrainAdjustmentDetail" onsubmit="submitUpdateDrainAdjustment(event, {{ $adjustment->id }})" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_drain_adj_code" class="font-weight-bold">Kode Adjustment <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="edit_drain_adj_code" name="adjustment_code" value="{{ $adjustment->adjustment_code }}" required readonly />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_drain_adj_date" class="font-weight-bold">Tanggal Adjustment <span class="text-danger">*</span></label>
                            <input type="date" class="text-black form-control" id="edit_drain_adj_date" name="adjustment_date" value="{{ $adjustment->adjustment_date ? $adjustment->adjustment_date->format('Y-m-d') : date('Y-m-d') }}" required {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }} />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_drain_adj_tank_id" class="font-weight-bold">Tangki <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="edit_drain_adj_tank_id" name="tank_id" required onchange="onEditDrainAdjTankChange()" {{ $adjustment->approval_status === 'APPROVED' ? 'disabled' : '' }}>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}" data-name="{{ $tank->name }}" data-capacity="{{ (float) $tank->capacity }}" {{ $tank->id == $adjustment->tank_id ? 'selected' : '' }}>
                                        {{ $tank->name }} (Kapasitas: {{ number_format((float) $tank->capacity, 2, '.', ',') }} KG)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Info Tangki</label>
                            <div id="editDrainAdjTankInfo" class="alert alert-info py-2 px-3 mb-0" style="font-size:12px;">
                                <strong>{{ $adjustment->tank_name }}</strong> | Kapasitas: <strong>{{ number_format((float) $adjustment->tank_capacity, 2, '.', ',') }} KG</strong> | Saldo Sistem Saat Ini: <strong>{{ number_format((float) $currentStock, 2, '.', ',') }} KG</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_drain_adj_qty_sistem" class="font-weight-bold">Qty Sistem (KG)</label>
                            <input type="text" class="text-black form-control" id="edit_drain_adj_qty_sistem" value="{{ number_format((float) $adjustment->qty_sistem, 2, '.', ',') }}" readonly />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_drain_adj_qty_timbang" class="font-weight-bold">Qty Timbang Manual (KG) <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" class="text-black form-control cns-decimal" id="edit_drain_adj_qty_timbang" name="qty_timbang" value="{{ number_format((float) $adjustment->qty_timbang, 2, '.', ',') }}" required oninput="calculateEditDrainAdjSelisih()" {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }} />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_drain_adj_selisih_qty" class="font-weight-bold">Selisih (Timbang - Sistem)</label>
                            @php $selPrefix = $adjustment->selisih_qty > 0 ? '+' : ''; @endphp
                            <input type="text" class="text-black form-control" id="edit_drain_adj_selisih_qty" value="{{ $selPrefix . number_format((float) $adjustment->selisih_qty, 2, '.', ',') }}" readonly />
                        </div>
                    </div>

                    <div class="col-md-12" id="editDrainAdjValidationWarning" style="display:none;">
                        <div class="alert alert-danger py-2 px-3">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            <span id="editDrainAdjValidationWarningText"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_drain_adj_document" class="font-weight-bold">Dokumen / Bukti Timbang</label>
                            @if(!empty($adjustment->document_path))
                                <div class="mb-2">
                                    <a href="{{ route('cns-drain-adjustment.document', ['id' => $adjustment->id], false) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-file-text-o mr-1"></i> Lihat Dokumen ({{ $adjustment->document_name ?: 'Dokumen Pendukung' }})
                                    </a>
                                </div>
                            @endif
                            @if($adjustment->approval_status !== 'APPROVED')
                                <input type="file" class="text-black form-control-file" id="edit_drain_adj_document" name="document" accept=".pdf,.jpg,.jpeg,.png" />
                                <small class="form-text text-muted">Upload file baru untuk mengganti dokumen yang ada.</small>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_drain_adj_notes" class="font-weight-bold">Alasan / Catatan Penyesuaian</label>
                            <textarea class="text-black form-control" id="edit_drain_adj_notes" name="notes" rows="2" {{ $adjustment->approval_status === 'APPROVED' ? 'readonly' : '' }}>{{ $adjustment->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        @if($adjustment->approval_status !== 'APPROVED')
                            <button type="submit" class="btn btn-primary" id="btnUpdateDrainAdjustment">Simpan Perubahan</button>
                            <button type="button" class="btn btn-danger ml-1" onclick="deleteDrainAdjustment({{ $adjustment->id }})">Hapus</button>
                        @endif
                        <button type="button" class="btn btn-secondary ml-1" onclick="$('#DrainAdjustmentDet').empty();">Tutup</button>
                    </div>

                    <div>
                        @if($adjustment->approval_status !== 'APPROVED')
                            <button type="button" class="btn btn-success" onclick="approveDrainAdjustment({{ $adjustment->id }})">
                                <i class="fa fa-check mr-1"></i> Approve Adjustment
                            </button>
                            <button type="button" class="btn btn-outline-danger ml-1" onclick="rejectDrainAdjustment({{ $adjustment->id }})">
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
    var editDrainAdjCapacity = {{ (float) $adjustment->tank_capacity }};
    var editDrainAdjSistem = {{ (float) $adjustment->qty_sistem }};
    var isDrainAdjApproved = {{ $adjustment->approval_status === 'APPROVED' ? 'true' : 'false' }};

    function onEditDrainAdjTankChange() {
        var tankId = $('#edit_drain_adj_tank_id').val();
        if (!tankId) return;

        $.get('cns-tank-draining-tank-balance/' + tankId, function(res) {
            if (res && res.success) {
                editDrainAdjCapacity = Number(res.capacity) || 0;
                editDrainAdjSistem = Number(res.balance) || 0;
                $('#edit_drain_adj_qty_sistem').val(cnsFormatNumber(editDrainAdjSistem, 2));
                $('#editDrainAdjTankInfo').html(
                    '<strong>' + res.name + '</strong> | Kapasitas: <strong>' + cnsFormatNumber(editDrainAdjCapacity, 2) + ' KG</strong> | Saldo Sistem Saat Ini: <strong>' + cnsFormatNumber(editDrainAdjSistem, 2) + ' KG</strong>'
                );
                calculateEditDrainAdjSelisih();
            }
        });
    }

    function calculateEditDrainAdjSelisih() {
        var timbang = Number(String($('#edit_drain_adj_qty_timbang').val()).replace(/,/g, '')) || 0;
        var selisih = timbang - editDrainAdjSistem;
        var prefix = selisih > 0 ? '+' : '';
        $('#edit_drain_adj_selisih_qty').val(prefix + cnsFormatNumber(selisih, 2));
        validateEditDrainAdjInput();
    }

    function validateEditDrainAdjInput() {
        if (isDrainAdjApproved) return true;

        var rawTimbang = String($('#edit_drain_adj_qty_timbang').val()).replace(/,/g, '').trim();
        var timbang = Number(rawTimbang);

        if (rawTimbang === '' || isNaN(timbang)) {
            $('#editDrainAdjValidationWarning').hide();
            $('#btnUpdateDrainAdjustment').prop('disabled', false);
            return true;
        }

        if (timbang < 0) {
            $('#editDrainAdjValidationWarningText').text('Qty Timbang tidak boleh kurang dari 0 KG.');
            $('#editDrainAdjValidationWarning').show();
            $('#btnUpdateDrainAdjustment').prop('disabled', true);
            return false;
        }

        if (editDrainAdjCapacity > 0 && timbang > editDrainAdjCapacity) {
            $('#editDrainAdjValidationWarningText').text('Qty Timbang (' + cnsFormatNumber(timbang, 2) + ' KG) melebihi kapasitas tangki (' + cnsFormatNumber(editDrainAdjCapacity, 2) + ' KG).');
            $('#editDrainAdjValidationWarning').show();
            $('#btnUpdateDrainAdjustment').prop('disabled', true);
            return false;
        }

        if (Math.abs(timbang - editDrainAdjSistem) < 0.01) {
            $('#editDrainAdjValidationWarningText').text('Qty Timbang sama dengan Qty Sistem. Tidak diperlukan adjustment.');
            $('#editDrainAdjValidationWarning').show();
            $('#btnUpdateDrainAdjustment').prop('disabled', true);
            return false;
        }

        $('#editDrainAdjValidationWarning').hide();
        $('#btnUpdateDrainAdjustment').prop('disabled', false);
        return true;
    }

    function submitUpdateDrainAdjustment(e, id) {
        e.preventDefault();
        if (!validateEditDrainAdjInput()) return;

        var form = document.getElementById('formDrainAdjustmentDetail');
        var formData = new FormData(form);
        formData.set('qty_timbang', String($('#edit_drain_adj_qty_timbang').val()).replace(/,/g, ''));

        $('#btnUpdateDrainAdjustment').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'cns-drain-adjustment-update/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                swal('Berhasil!', res.message || 'Drain Adjustment berhasil diperbarui.', 'success').then(function () {
                    $('#DrainAdjustmentDet').empty();
                    refreshDrainAdjData();
                });
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui data.';
                swal('Error', msg, 'error');
                $('#btnUpdateDrainAdjustment').prop('disabled', false).html('Simpan Perubahan');
            }
        });
    }

    function deleteDrainAdjustment(id) {
        swal({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menghapus data Drain Adjustment ini?',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: true
        }).then(function (confirmed) {
            if (!confirmed) return;

            $.ajax({
                url: 'cns-drain-adjustment-delete/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    swal('Berhasil!', res.message || 'Data berhasil dihapus.', 'success').then(function () {
                        $('#DrainAdjustmentDet').empty();
                        refreshDrainAdjData();
                    });
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus data.';
                    swal('Error', msg, 'error');
                }
            });
        });
    }

    function approveDrainAdjustment(id) {
        swal({
            title: 'Konfirmasi',
            text: 'Approve Drain Adjustment ini? Setelah di-approve, saldo sistem tangki akan otomatis disesuaikan sesuai Qty Timbang.',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: false
        }).then(function (confirmed) {
            if (!confirmed) return;

            $.ajax({
                url: 'cns-drain-adjustment-approve/' + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    swal('Berhasil!', res.message || 'Drain Adjustment berhasil di-approve.', 'success').then(function () {
                        LoadM('DrainAdjustmentDet', 'cns-drain-adjustment-detail?ID=' + id);
                        refreshDrainAdjData();
                    });
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal approve Drain Adjustment.';
                    swal('Error', msg, 'error');
                }
            });
        });
    }

    function rejectDrainAdjustment(id) {
        var reason = prompt('Masukkan alasan penolakan Drain Adjustment:');
        if (reason === null) return;
        reason = $.trim(reason);
        if (!reason) {
            swal('Perhatian', 'Alasan penolakan wajib diisi.', 'warning');
            return;
        }

        $.ajax({
            url: 'cns-drain-adjustment-reject/' + id,
            type: 'POST',
            data: { reason: reason },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                swal('Berhasil!', res.message || 'Drain Adjustment ditolak.', 'success').then(function () {
                    LoadM('DrainAdjustmentDet', 'cns-drain-adjustment-detail?ID=' + id);
                    refreshDrainAdjData();
                });
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menolak Drain Adjustment.';
                swal('Error', msg, 'error');
            }
        });
    }

    $('#DetailDrainAdjBtn').prop('disabled', false).html('Detail / Edit');
    if ($('#DrainAdjustmentDet').length) {
        $('html, body').animate({ scrollTop: $('#DrainAdjustmentDet').offset().top - 70 }, 250);
    }
</script>
