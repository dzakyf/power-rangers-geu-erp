<div class="ml-2">
    <div class="JudulContent" style="color:#699;">NEW TANGKI</div>
    <div class="card">
        <div class="card-body pt-2">
            <p class="text-muted mb-3">Isi form berikut untuk menambahkan master tangki baru.</p>

            <form id="formTankNew" onsubmit="submitNewTank(event)">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_tank_name" class="font-weight-bold">Nama / Kode Tangki <span class="text-danger">*</span></label>
                            <input type="text" class="text-black form-control" id="new_tank_name" name="name" placeholder="Contoh: Tangki-F, TK-01" required maxlength="100" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_tank_capacity" class="font-weight-bold">Kapasitas Maksimal (KG) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="text-black form-control" id="new_tank_capacity" name="capacity" placeholder="Contoh: 1500000" required />
                            <small class="form-text text-muted">Kapasitas total tangki dalam satuan Kilogram (KG).</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_tank_status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                            <select class="text-black form-control" id="new_tank_status" name="status" required>
                                <option value="ACTIVE" selected>ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_tank_notes" class="font-weight-bold">Keterangan / Lokasi</label>
                            <textarea class="text-black form-control" id="new_tank_notes" name="notes" rows="2" placeholder="Contoh: Pelindo Dermaga UCO, Tangki timbun utama"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSubmitNewTank">Submit</button>
                    <button type="button" class="btn btn-secondary ml-1" onclick="cancelTankForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function submitNewTank(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitNewTank');
        $btn.prop('disabled', true).text('Menyimpan...');

        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

        $.ajax({
            url: 'cns-master-tank-store',
            type: 'POST',
            data: {
                _token: token,
                name: $('#new_tank_name').val().trim(),
                capacity: $('#new_tank_capacity').val(),
                status: $('#new_tank_status').val(),
                notes: $('#new_tank_notes').val()
            },
            success: function(res) {
                $btn.prop('disabled', false).text('Submit');
                if (res.success) {
                    alert('New Tank Created!');
                    $('#MasterTankDet').empty();
                    refreshdata();
                } else {
                    alert(res.message || 'Gagal menyimpan data.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Submit');
                var msg = 'Gagal menyimpan data tangki.';
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
