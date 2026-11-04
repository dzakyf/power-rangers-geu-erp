<div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
    <div
        class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white p-2">
                <i class="fa fa-calendar-plus fs-14"></i>
            </span>
            <div>
                <h6 class="mb-0 fw-bold text-dark">Tambah Master Work Calendar Baru</h6>
                <small class="text-muted">Tentukan nama kalender, jenis karyawan, dan alokasi total jam kerja
                    mingguan.</small>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#WorkCalendarDiv').html('');">
                <i class="fa fa-xmark me-1"></i> Batal / Tutup
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <form id="formWorkCalendarNew" onsubmit="return false;">
            <div class="row g-3">
                <div class="col-md-6 col-12">
                    <label for="Name" class="form-label fw-semibold text-dark fs-13 mb-1">
                        Nama Calendar <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa fa-font"></i></span>
                        <input type="text" id="Name" class="form-control" name="Name"
                            placeholder="Contoh: General Office 40H / Shift Security" required />
                    </div>
                </div>
                <div class="col-md-3 col-12">
                    <label for="Employee_Type" class="form-label fw-semibold text-dark fs-13 mb-1">
                        Employee Type <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa fa-briefcase"></i></span>
                        <select name="Employee_Type" id="Employee_Type" class="form-select" required>
                            <option value="OFFICE">OFFICE (Normal Days)</option>
                            <option value="SHIFT">SHIFT (Roster / Rotating)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-12">
                    <label for="Weekly_Hours" class="form-label fw-semibold text-dark fs-13 mb-1">
                        Weekly Hours <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa fa-clock"></i></span>
                        <input type="number" step="0.5" min="0" max="168" id="Weekly_Hours"
                            class="form-control" name="Weekly_Hours" placeholder="Contoh: 40" required />
                    </div>
                    <small class="text-muted fs-11">Total jam kerja dalam satu minggu.</small>
                </div>
                <div class="col-12 border-top pt-3 mt-4 d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="$('#WorkCalendarDiv').html('');">
                        <i class="fa fa-arrow-left me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="submitBtn">
                        <i class="fa fa-floppy-disk me-1"></i> Simpan Calendar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('#submitBtn').click(function() {
        var name = $('#Name').val();
        var empType = $('#Employee_Type').val();
        var weeklyHours = $('#Weekly_Hours').val();

        if (!name || name.trim() === '') {
            alert('Silakan isi Nama Calendar.');
            $('#Name').focus();
            return;
        }
        if (!weeklyHours || weeklyHours < 0) {
            alert('Silakan isi Weekly Hours dengan benar.');
            $('#Weekly_Hours').focus();
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: 'hrd-master-work-calendar-new-submit',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                Name: name,
                Employee_Type: empType,
                Weekly_Hours: weeklyHours
            },
            success: function(data) {
                $btn.prop('disabled', false).html(
                    '<i class="fa fa-floppy-disk me-1"></i> Simpan Calendar');
                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Data work calendar berhasil disimpan.',
                            confirmButtonColor: '#0d6efd'
                        }).then(function() {
                            reloadgroup();
                            $('#WorkCalendarDiv').html('');
                        });
                    } else {
                        alert(data.message || 'Data berhasil disimpan');
                        reloadgroup();
                        $('#WorkCalendarDiv').html('');
                    }
                } else {
                    alert(data.message || 'Terjadi kesalahan saat menyimpan data');
                }
            },
            error: function(jqXHR) {
                $btn.prop('disabled', false).html(
                    '<i class="fa fa-floppy-disk me-1"></i> Simpan Calendar');
                var err = 'Terjadi kesalahan saat menghubungi server.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    err = jqXHR.responseJSON.message;
                }
                alert(err);
            }
        });
    });
</script>
