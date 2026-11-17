<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $program ? 'Edit Program: ' . $program->program_code : 'Tambah Module & Program Training' }}</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#divProgramContainer').html('')">Tutup</button>
    </div>
    <div class="card-body">
        <form id="formProgram" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $program->id ?? '' }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Program / Modul *</label>
                    <input type="text" name="program_name" class="form-control" value="{{ $program->program_name ?? '' }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->ID }}" {{ ($program->category_id ?? '') == $cat->ID ? 'selected' : '' }}>{{ $cat->Category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Passing Grade (KKM) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="default_passing_grade" class="form-control" value="{{ $program->default_passing_grade ?? 75 }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe Training</label>
                    <select name="training_type" class="form-control">
                        @foreach(['INTERNAL', 'EXTERNAL', 'IN_HOUSE'] as $t)
                            <option value="{{ $t }}" {{ ($program->training_type ?? 'INTERNAL') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Metode</label>
                    <select name="training_method" class="form-control">
                        @foreach(['OFFLINE', 'ONLINE', 'HYBRID'] as $m)
                            <option value="{{ $m }}" {{ ($program->training_method ?? 'OFFLINE') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="ACTIVE" {{ ($program->status ?? 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="INACTIVE" {{ ($program->status ?? '') == 'INACTIVE' ? 'selected' : '' }}>INACTIVE</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan Training (Objective)</label>
                    <textarea name="objective" class="form-control" rows="2">{{ $program->objective ?? '' }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kompetensi Target</label>
                    <textarea name="competency" class="form-control" rows="2">{{ $program->competency ?? '' }}</textarea>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Deskripsi &amp; Ringkasan Silabus</label>
                    <textarea name="description" class="form-control" rows="2">{{ $program->description ?? '' }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">File Modul / Silabus (PDF/DOC/PPT)</label>
                    <input type="file" name="module_file" class="form-control">
                    @if($program && $program->module_file_path)
                        <small class="text-success">File tersimpan: {{ basename($program->module_file_path) }}</small>
                    @endif
                </div>
            </div>
            <button type="submit" class="btn btn-success">Simpan Program</button>
        </form>
    </div>
</div>

<script>
    $('#formProgram').on('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(this);
        $.ajax({
            url: '{{ url("hrd-training-program-store") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                alert(res.message || 'Program tersimpan.');
                refreshProgramGrid();
                $('#divProgramContainer').html('');
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan program.');
            }
        });
    });
</script>
