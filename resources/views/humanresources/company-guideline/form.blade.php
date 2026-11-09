<form id="formGuideline" enctype="multipart/form-data">
    @csrf
    @if($action === 'edit' && $item)
        <input type="hidden" name="id" value="{{ $item->id }}">
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-bold">Jenis / Kategori <span class="text-danger">*</span></label>
            <select name="type" class="form-select form-select-sm" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ (old('type', $item->type ?? '') === $key) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-bold">Kode Singkat / Identifier</label>
            <input type="text" name="code" class="form-control form-control-sm" placeholder="Contoh: VISION, AKHLAK, PP-01" 
                value="{{ old('code', $item->code ?? '') }}">
        </div>

        <div class="col-md-12">
            <label class="form-label small fw-bold">Judul / Nama Panduan <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-sm" placeholder="Masukkan judul..." 
                value="{{ old('title', $item->title ?? '') }}" required>
        </div>

        <div class="col-md-12">
            <label class="form-label small fw-bold">Ringkasan / Tagline (Singkat)</label>
            <textarea name="summary" class="form-control form-control-sm" rows="2" placeholder="Penjelasan singkat satu atau dua kalimat...">{{ old('summary', $item->summary ?? '') }}</textarea>
        </div>

        <div class="col-md-12">
            <label class="form-label small fw-bold">Isi Lengkap / Uraian Detail (Dapat berupa HTML / Poin-Poin)</label>
            <textarea name="content" class="form-control form-control-sm" rows="6" placeholder="Masukkan rincian teks lengkap...">{{ old('content', $item->content ?? '') }}</textarea>
            <small class="text-muted">Mendukung format HTML sederhana seperti <code>&lt;p&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;b&gt;</code>.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold">Icon FontAwesome</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i id="iconPreview" class="{{ $item->icon ?? 'fa fa-bookmark' }}"></i></span>
                <input type="text" name="icon" id="iconInput" class="form-control" placeholder="fa fa-award" 
                    value="{{ old('icon', $item->icon ?? 'fa fa-bookmark') }}" oninput="$('#iconPreview').attr('class', this.value)">
            </div>
            <small class="text-muted">Misal: <code>fa fa-eye</code>, <code>fa fa-bullseye</code>, <code>fa fa-award</code>, <code>fa fa-book</code></small>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold">Warna Tema / Badge</label>
            <select name="badge_color" class="form-select form-select-sm">
                @php
                    $colors = ['primary' => 'Biru (Primary)', 'success' => 'Hijau (Success)', 'info' => 'Cyan (Info)', 'warning' => 'Kuning (Warning)', 'danger' => 'Merah (Danger)', 'dark' => 'Gelap (Dark)'];
                @endphp
                @foreach($colors as $cKey => $cLabel)
                    <option value="{{ $cKey }}" {{ (old('badge_color', $item->badge_color ?? 'primary') === $cKey) ? 'selected' : '' }}>
                        {{ $cLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label small fw-bold">Urutan Tampil (Sort)</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0">
        </div>

        <div class="col-md-12">
            <label class="form-label small fw-bold">Dokumen / File Lampiran (Opsional, PDF/Doc)</label>
            @if(!empty($item->attachment_file))
                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded border">
                    <i class="fa fa-file-pdf text-danger me-2 fa-lg"></i>
                    <a href="{{ asset($item->attachment_file) }}" target="_blank" class="small fw-semibold text-truncate me-auto">
                        {{ $item->attachment_name ?? 'Download Dokumen' }}
                    </a>
                    <div class="form-check ms-3">
                        <input class="form-check-input" type="checkbox" name="remove_attachment" value="1" id="removeAttach">
                        <label class="form-check-label small text-danger" for="removeAttach">Hapus File</label>
                    </div>
                </div>
            @endif
            <input type="file" name="attachment" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
            <small class="text-muted">Maksimal 20MB (PDF sangat direkomendasikan untuk dibaca di mobile/desktop).</small>
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch mt-1">
                <input class="form-check-input" type="checkbox" id="isActiveCheck" name="is_active" value="1" 
                    {{ (!isset($item) || $item->is_active == 1) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold small" for="isActiveCheck">Status Aktif (Tampilkan di Mobile & Portal)</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="submit" id="btnSubmitGuideline" class="btn btn-primary btn-sm px-4">
            <i class="fa fa-save me-1"></i> Simpan Data
        </button>
    </div>
</form>

<script>
    $('#formGuideline').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);
        var submitUrl = "{{ $action === 'edit' ? route('hrd.company-guideline.update') : route('hrd.company-guideline.store') }}";

        $('#btnSubmitGuideline').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: submitUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(resp) {
                $('#btnSubmitGuideline').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data');
                if (resp.success) {
                    var modalEl = document.getElementById('guidelineModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    loadGridData();
                    alert(resp.message);
                } else {
                    alert('Gagal menyimpan data: ' + (resp.message || 'Terjadi kesalahan'));
                }
            },
            error: function(xhr) {
                $('#btnSubmitGuideline').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data');
                var msg = 'Terjadi kesalahan saat memproses data.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).map(function(e) { return e[0]; }).join('\n');
                    msg = errors;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
</script>

