<form id="announcementForm" enctype="multipart/form-data">
    @csrf
    @if($action === 'edit' && $item)
        <input type="hidden" name="ID" value="{{ $item->id }}">
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label fw-bold text-dark">Judul <span class="text-danger">*</span></label>
            <input type="text" name="Title" class="form-control" placeholder="Contoh: Kebijakan Jam Kerja Fleksibel 2026" value="{{ $item->title ?? '' }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Kategori <span class="text-danger">*</span></label>
            <select name="Category" class="form-select" required>
                <option value="ANNOUNCEMENT" {{ (isset($item) && $item->category === 'ANNOUNCEMENT') ? 'selected' : '' }}>Announcement</option>
                <option value="NEWS" {{ (isset($item) && $item->category === 'NEWS') ? 'selected' : '' }}>News</option>
                <option value="POLICY" {{ (isset($item) && $item->category === 'POLICY') ? 'selected' : '' }}>Policy & Guideline</option>
                <option value="EVENT" {{ (isset($item) && $item->category === 'EVENT') ? 'selected' : '' }}>Event / Gathering</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Ringkasan Singkat (Summary)</label>
            <input type="text" name="Summary" class="form-control" maxlength="500" placeholder="Ringkasan 1-2 kalimat untuk preview di aplikasi mobile..." value="{{ $item->summary ?? '' }}">
            <div class="form-text">Jika dikosongkan, ringkasan akan digenerate otomatis dari isi konten.</div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Isi Konten Lengkap <span class="text-danger">*</span></label>
            <textarea name="Content" id="announcementContent" class="form-control" rows="8" placeholder="Tuliskan isi pengumuman atau berita secara lengkap..." required>{{ $item->content ?? '' }}</textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Thumbnail / Gambar Banner</label>
            <input type="file" name="Thumbnail" class="form-control" accept="image/*">
            @if(isset($item) && $item->thumbnail)
                <div class="mt-2 d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset($item->thumbnail) }}" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                        <span class="small text-muted">Thumbnail saat ini</span>
                    </div>
                    <div class="form-check form-check-inline ms-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="Remove_Thumbnail" value="1" id="chkRemoveThumb">
                        <label class="form-check-label text-danger small" for="chkRemoveThumb">Hapus Thumbnail</label>
                    </div>
                </div>
            @endif
            <div class="form-text">Format: JPG, PNG, WEBP (Maksimal 5MB).</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-dark">File Attachment / Dokumen</label>
            <input type="file" name="Attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
            @if(isset($item) && $item->attachment_file)
                <div class="mt-2 d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <i class="fa fa-paperclip text-primary"></i>
                        <span class="small fw-semibold text-truncate">{{ $item->attachment_name ?: basename($item->attachment_file) }}</span>
                    </div>
                    <div class="form-check form-check-inline ms-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="Remove_Attachment" value="1" id="chkRemoveAttach">
                        <label class="form-check-label text-danger small" for="chkRemoveAttach">Hapus</label>
                    </div>
                </div>
            @endif
            <div class="form-text">Format: PDF, Word, Excel, ZIP (Maksimal 20MB).</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Target Audience</label>
            <select name="Target_Audience" class="form-select">
                <option value="ALL" {{ (isset($item) && $item->target_audience === 'ALL') ? 'selected' : '' }}>Semua Karyawan (ALL)</option>
                <option value="INTERNAL" {{ (isset($item) && $item->target_audience === 'INTERNAL') ? 'selected' : '' }}>Internal Perusahaan</option>
                <option value="HCGA" {{ (isset($item) && $item->target_audience === 'HCGA') ? 'selected' : '' }}>HCGA Departemen</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Pin ke Bagian Atas?</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="Is_Pinned" value="1" id="pinSwitch" {{ (isset($item) && $item->is_pinned) ? 'checked' : '' }}>
                <label class="form-check-label text-muted" for="pinSwitch">Sematkan sebagai Berita Utama (Pinned)</label>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Status Publikasi</label>
            <select name="Status" class="form-select">
                <option value="Final" {{ (!isset($item) || $item->is_final) ? 'selected' : '' }}>Published (Langsung Tayang)</option>
                <option value="Draft" {{ (isset($item) && !$item->is_final) ? 'selected' : '' }}>Draft (Simpan Sementara)</option>
            </select>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSubmitForm">
            <i class="fa fa-save me-1"></i> Simpan & Publikasikan
        </button>
    </div>
</form>

<script>
    $('#announcementForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var url = "{{ $action === 'edit' ? route('hrd.announcement.update') : route('hrd.announcement.store') }}";

        $('#btnSubmitForm').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                alert(res.message || 'Berhasil disimpan!');
                var myModalEl = document.getElementById('announcementModal');
                var modal = bootstrap.Modal.getInstance(myModalEl);
                if (modal) modal.hide();
                reloadGrid();
            },
            error: function(xhr) {
                $('#btnSubmitForm').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan');
                var msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON?.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).flat().join("\n");
                    msg += "\n" + errors;
                }
                alert(msg);
            }
        });
    });
</script>
