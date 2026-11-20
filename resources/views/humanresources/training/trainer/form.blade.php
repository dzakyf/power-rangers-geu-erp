<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $trainer ? 'Edit Pemateri: ' . $trainer->Trainer_Name : 'Tambah Master Pemateri / Trainer' }}</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divTrainerContainer').html('')">Tutup</button>
    </div>
    <div class="card-body">
        <form id="formTrainer">
            @csrf
            <input type="hidden" name="id" value="{{ $trainer->ID ?? '' }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe Pemateri *</label>
                    <select name="Trainer_Type" id="Trainer_Type" class="form-control" onchange="toggleEmployeeHelper()" required>
                        <option value="INTERNAL" {{ ($trainer->Trainer_Type ?? 'INTERNAL') == 'INTERNAL' ? 'selected' : '' }}>INTERNAL</option>
                        <option value="EXTERNAL" {{ ($trainer->Trainer_Type ?? '') == 'EXTERNAL' ? 'selected' : '' }}>EXTERNAL</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3" id="divEmployeeHelper" style="{{ ($trainer->Trainer_Type ?? 'INTERNAL') == 'INTERNAL' ? '' : 'display:none;' }}">
                    <label class="form-label">Ambil dari Data Karyawan (Opsional)</label>
                    <select id="employee_picker" class="form-control" onchange="fillFromEmployee(this)">
                        <option value="">-- Pilih Karyawan untuk Auto-Fill --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->username }}"
                                data-name="{{ $emp->name }}"
                                data-email="{{ $emp->email ?? '' }}"
                                data-dept="{{ $emp->Department ?? '' }}">
                                {{ $emp->name }} ({{ $emp->username }}) - {{ $emp->Department ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pemateri *</label>
                    <input type="text" name="Trainer_Name" id="Trainer_Name" class="form-control" value="{{ $trainer->Trainer_Name ?? '' }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Instansi / Perusahaan / Departemen *</label>
                    <input type="text" name="Trainer_Company" id="Trainer_Company" class="form-control" value="{{ $trainer->Trainer_Company ?? '' }}" placeholder="Misal: Internal - HCGA / PT Lembaga Training" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="Email" id="Trainer_Email" class="form-control" value="{{ $trainer->Email ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No. HP / Telepon</label>
                    <input type="text" name="Phone" id="Trainer_Phone" class="form-control" value="{{ $trainer->Phone ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="Status" class="form-control" required>
                        <option value="ACTIVE" {{ ($trainer->Status ?? 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="INACTIVE" {{ ($trainer->Status ?? '') == 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sertifikasi / Keahlian</label>
                    <input type="text" name="Certificate" class="form-control" value="{{ $trainer->Certificate ?? '' }}" placeholder="Misal: BNSP Trainer, K3 Umum, ISO Lead Auditor">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Alamat / Catatan</label>
                    <textarea name="Address" class="form-control" rows="2">{{ $trainer->Address ?? '' }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Simpan Pemateri</button>
        </form>
    </div>
</div>

<script>
    function toggleEmployeeHelper() {
        var type = $('#Trainer_Type').val();
        if (type === 'INTERNAL') {
            $('#divEmployeeHelper').show();
        } else {
            $('#divEmployeeHelper').hide();
            $('#employee_picker').val('');
        }
    }

    function fillFromEmployee(sel) {
        var opt = $(sel).find('option:selected');
        if (!opt.val()) return;
        $('#Trainer_Name').val(opt.data('name') || '');
        if (opt.data('email')) {
            $('#Trainer_Email').val(opt.data('email'));
        }
        var dept = opt.data('dept');
        $('#Trainer_Company').val(dept ? ('Internal - ' + dept) : 'Internal');
    }

    $('#formTrainer').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-trainer-store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message || 'Master Pemateri tersimpan.');
                refreshTrainerGrid();
                $('#divTrainerContainer').html('');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data pemateri.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    if (firstKey) msg = xhr.responseJSON.errors[firstKey][0];
                }
                alert(msg);
            }
        });
    });
</script>
