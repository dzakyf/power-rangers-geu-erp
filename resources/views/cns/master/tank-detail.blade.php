<div class="mt-3">
    <div class="JudulContent" style="color:#699;">DETAIL TANGKI: {{ $tank->name }}</div>
    <div class="card">
        <div class="card-body pt-2">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-3" id="tankDetailTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold" id="tab-data-tab" data-toggle="tab" href="#tab-tank-data" role="tab" aria-controls="tab-tank-data" aria-selected="true">
                        <i class="fa fa-info-circle mr-1"></i> Data Tangki
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link font-weight-bold" id="tab-log-tab" data-toggle="tab" href="#tab-tank-logs" role="tab" aria-controls="tab-tank-logs" aria-selected="false">
                        <i class="fa fa-history mr-1"></i> Riwayat Audit Log <span class="badge badge-secondary ml-1">{{ count($tank->logs) }}</span>
                    </a>
                </li> -->
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="tankDetailTabContent">
                <!-- Tab 1: Form Data Tangki -->
                <div class="tab-pane fade show active" id="tab-tank-data" role="tabpanel" aria-labelledby="tab-data-tab">
                    <form id="formTankDetail" onsubmit="submitUpdateTank(event)">
                        @csrf
                        <input type="hidden" id="edit_tank_id" value="{{ $tank->id }}" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tank_name" class="font-weight-bold">Nama / Kode Tangki <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_tank_name" name="name" value="{{ $tank->name }}" required maxlength="100" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tank_capacity" class="font-weight-bold">Kapasitas Maksimal (KG) <span class="text-danger">*</span></label>
                                    <input type="text" inputmode="decimal" class="form-control cns-decimal" id="edit_tank_capacity" name="capacity" value="{{ number_format((float) $tank->capacity, 2, '.', ',') }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tank_status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_tank_status" name="status" required>
                                        <option value="ACTIVE" {{ $tank->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                        <option value="INACTIVE" {{ $tank->status === 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tank_notes" class="font-weight-bold">Keterangan / Lokasi</label>
                                    <textarea class="form-control" id="edit_tank_notes" name="notes" rows="2">{{ $tank->notes }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group text-muted small">
                                    <span>Dibuat Oleh: <strong>{{ $tank->created_by ?: '-' }}</strong></span> &bull;
                                    <span>Tanggal: <strong>{{ $tank->created_at ? $tank->created_at->format('d/m/Y H:i') : '-' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="btnUpdateTank">Update</button>
                            <button type="button" class="btn btn-danger ml-1" id="btnDeleteTank" onclick="deleteDetailTank({{ $tank->id }})">Delete</button>
                            <button type="button" class="btn btn-secondary ml-1" onclick="cancelTankForm()">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Riwayat Audit Log -->
                <div class="tab-pane fade" id="tab-tank-logs" role="tabpanel" aria-labelledby="tab-log-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm text-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 150px;">Waktu</th>
                                    <th style="width: 110px;">Aksi</th>
                                    <th style="width: 140px;">Oleh</th>
                                    <th>Keterangan Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tank->logs as $idx => $log)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><small>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</small></td>
                                        <td>
                                            @if($log->action === 'CREATE')
                                                <span class="badge badge-success">CREATE</span>
                                            @elseif($log->action === 'UPDATE')
                                                <span class="badge badge-primary">UPDATE</span>
                                            @elseif($log->action === 'DELETE')
                                                <span class="badge badge-danger">DELETE</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $log->action }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->performed_by ?: '-' }}</td>
                                        <td class="text-left"><small>{{ $log->details ?: '-' }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada riwayat log untuk tangki ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" onclick="cancelTankForm()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitUpdateTank(e) {
        e.preventDefault();
        var id = $('#edit_tank_id').val();
        var $btn = $('#btnUpdateTank');
        $btn.prop('disabled', true).text('Updating...');

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        $.ajax({
            url: 'cns-master-tank-update/' + id,
            type: 'POST',
            data: {
                _token: token,
                name: $('#edit_tank_name').val().trim(),
                capacity: cnsParseNumber($('#edit_tank_capacity').val(), 0),
                status: $('#edit_tank_status').val(),
                notes: $('#edit_tank_notes').val()
            },
            success: function(res) {
                $btn.prop('disabled', false).text('Update');
                if (res.success) {
                    alert('Detail Tank Updated!');
                    $('#MasterTankDet').empty();
                    refreshdata();
                } else {
                    alert(res.message || 'Gagal memperbarui data.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Update');
                var msg = 'Gagal memperbarui data tangki.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    function deleteDetailTank(id) {
        var tankName = $('#edit_tank_name').val();
        if (!confirm('Apakah Anda yakin ingin menghapus tangki "' + tankName + '"? Data histori mutasi tetap tersimpan.')) {
            return;
        }

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        $.ajax({
            url: 'cns-master-tank-delete/' + id,
            type: 'POST',
            data: {
                _token: token
            },
            success: function(res) {
                if (res.success) {
                    alert('Tank Deleted!');
                    $('#MasterTankDet').empty();
                    refreshdata();
                } else {
                    alert(res.message || 'Gagal menghapus tangki.');
                }
            },
            error: function(xhr) {
                var msg = 'Gagal menghapus tangki.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    }

    function cancelTankForm() {
        $('#MasterTankDet').empty();
    }
</script>
