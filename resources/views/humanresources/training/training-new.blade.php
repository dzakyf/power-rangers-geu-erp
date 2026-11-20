<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Buat Jadwal &amp; Kurikulum Training ({{ $Task_No }})</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divlistTraining').html('')">Tutup</button>
    </div>
    <div class="card-body">
        <form id="formNewTraining">
            @csrf
            <input type="hidden" name="Task_No" value="{{ $Task_No }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Referensi TNA (Approved)</label>
                    <select name="tna_id" id="tna_id" class="form-control">
                        <option value="">-- Tanpa TNA / Ad-Hoc --</option>
                        @foreach($tnas as $t)
                            <option value="{{ $t->id }}" {{ ($selectedTnaId == $t->id) ? 'selected' : '' }}>
                                {{ $t->tna_no }} - {{ $t->title }} ({{ $t->department }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Module &amp; Program Training</label>
                    <select name="program_id" id="program_id" class="form-control" onchange="applyProgramDefaults(this)">
                        <option value="">-- Pilih Program (Opsional) --</option>
                        @foreach($programs as $prg)
                            <option value="{{ $prg->id }}"
                                data-name="{{ $prg->program_name }}"
                                data-type="{{ $prg->training_type }}"
                                data-method="{{ $prg->training_method }}"
                                data-kkm="{{ $prg->default_passing_grade }}"
                                data-obj="{{ $prg->objective }}"
                                data-comp="{{ $prg->competency }}"
                                data-desc="{{ $prg->description }}">
                                {{ $prg->program_code }} - {{ $prg->program_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Training *</label>
                    <input type="text" name="Training_Name" id="Training_Name" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="Training_Category" class="form-control">
                        <option value="">-- Pilih --</option>
                        @foreach($trCategory as $cat)
                            <option value="{{ $cat->ID }}">{{ $cat->Category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" min="0" max="100" name="passing_grade" id="passing_grade" class="form-control" value="75">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="Training_Type" id="Training_Type" class="form-control">
                        <option value="INTERNAL">INTERNAL</option>
                        <option value="EXTERNAL">EXTERNAL</option>
                        <option value="IN_HOUSE">IN_HOUSE</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Metode</label>
                    <select name="Training_Method" id="Training_Method" class="form-control">
                        <option value="OFFLINE">OFFLINE</option>
                        <option value="ONLINE">ONLINE</option>
                        <option value="HYBRID">HYBRID</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Mulai *</label>
                    <input type="date" name="Start_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Selesai *</label>
                    <input type="date" name="End_Date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pemateri / Trainer *
                        <a href="{{ url('hrd-training-trainer-index') }}" target="_blank" class="small ms-1">(Kelola Master)</a>
                    </label>
                    <select name="trainer_id" id="new_trainer_id" class="form-control" required>
                        <option value="">-- Pilih Pemateri dari Master --</option>
                        @foreach($trainers as $tr)
                            <option value="{{ $tr->ID }}">
                                {{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }} - {{ $tr->Trainer_Company }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi / Link Meeting</label>
                    <input type="text" name="Training_Location" class="form-control" placeholder="Ruang Meeting / Zoom Link">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan Training</label>
                    <input type="text" name="Training_Objective" id="Training_Objective" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kompetensi Target</label>
                    <input type="text" name="Training_Competence" id="Training_Competence" class="form-control">
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Form Kurikulum / Sesi Materi</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addCurriculumRow()">+ Tambah Sesi</button>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblCurriculums">
                    <thead class="table-light">
                        <tr>
                            <th>Topik / Sesi Materi</th>
                            <th>Pemateri</th>
                            <th style="width:110px;">Durasi (Menit)</th>
                            <th>Metode</th>
                            <th style="width:50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="curriculums[0][topic_title]" class="form-control form-control-sm" placeholder="Sesi 1: Pengenalan & Teori"></td>
                            <td>
                                <select name="curriculums[0][speaker_name]" class="form-control form-control-sm">
                                    <option value="">-- Ikuti Pemateri Utama --</option>
                                    @foreach($trainers as $tr)
                                        <option value="{{ $tr->Trainer_Name }}">{{ $tr->Trainer_Name }} ({{ $tr->Trainer_Type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="curriculums[0][duration_minutes]" class="form-control form-control-sm" value="60"></td>
                            <td><input type="text" name="curriculums[0][learning_method]" class="form-control form-control-sm" value="Presentasi & Diskusi"></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Peserta Training <small class="text-muted">(Kosongkan jika ingin auto-populate dari TNA terpilih)</small></h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addParticipantRow()">+ Tambah Peserta</button>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" id="tblParticipants">
                    <thead class="table-light">
                        <tr>
                            <th>Karyawan</th>
                            <th>Departemen</th>
                            <th style="width:50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="participants[0][employee_username]" class="form-control form-control-sm" onchange="syncPartInfo(this)">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->username }}" data-name="{{ $emp->name }}" data-dept="{{ $emp->Department }}">
                                            {{ $emp->name }} ({{ $emp->username }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="participants[0][employee_name]" class="part-name">
                            </td>
                            <td><input type="text" name="participants[0][department]" class="form-control form-control-sm part-dept" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">x</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success">Simpan Jadwal Training</button>
        </form>
    </div>
</div>

<script>
    var curIdx = 1;
    var partIdx = 1;

    function applyProgramDefaults(sel) {
        var opt = $(sel).find('option:selected');
        if (!opt.val()) return;
        $('#Training_Name').val(opt.data('name'));
        $('#Training_Type').val(opt.data('type'));
        $('#Training_Method').val(opt.data('method'));
        $('#passing_grade').val(opt.data('kkm'));
        $('#Training_Objective').val(opt.data('obj'));
        $('#Training_Competence').val(opt.data('comp'));
    }

    function addCurriculumRow() {
        var row = $('#tblCurriculums tbody tr:first').clone();
        row.find('input, select').each(function() {
            var n = $(this).attr('name');
            if (n) $(this).attr('name', n.replace(/\[\d+\]/, '[' + curIdx + ']'));
            if ($(this).is('select')) {
                $(this).val('');
            } else if ($(this).attr('type') === 'text') {
                $(this).val('');
            }
        });
        $('#tblCurriculums tbody').append(row);
        curIdx++;
    }

    function syncPartInfo(sel) {
        var opt = $(sel).find('option:selected');
        var tr = $(sel).closest('tr');
        tr.find('.part-name').val(opt.data('name') || '');
        tr.find('.part-dept').val(opt.data('dept') || '');
    }

    function addParticipantRow() {
        var row = $('#tblParticipants tbody tr:first').clone();
        row.find('input, select').each(function() {
            var n = $(this).attr('name');
            if (n) $(this).attr('name', n.replace(/\[\d+\]/, '[' + partIdx + ']'));
            if ($(this).is('input')) $(this).val('');
        });
        $('#tblParticipants tbody').append(row);
        partIdx++;
    }

    $('#formNewTraining').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("hrd-training-new-submit") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                refreshdata();
                LoadM('divlistTraining', 'hrd-training-detail?ID=' + res.data.ID);
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan jadwal.');
            }
        });
    });
</script>
