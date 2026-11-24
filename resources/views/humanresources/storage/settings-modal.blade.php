<form id="storageSettingsForm">
    @csrf
    <div class="alert alert-info small">
        <i class="fa fa-info-circle me-1"></i>
        Atur bagaimana publikasi dan revisi versi pada modul <strong>Storage Management</strong> diproses sebelum tayang ke karyawan.
        Jika menggunakan Approval Engine, pengajuan akan masuk ke <strong>Manager HCGA</strong>.
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold text-dark">Mode Pengajuan: Regulasi (SK-Dir, SOP, Internal Memo)</label>
        <select name="regulation_mode" class="form-select">
            <option value="DIRECT" {{ ($settings['REGULATION'] ?? '') === 'DIRECT' ? 'selected' : '' }}>
                DIRECT — Langsung Publish (Tanpa Approval)
            </option>
            <option value="OPTIONAL" {{ ($settings['REGULATION'] ?? 'OPTIONAL') === 'OPTIONAL' ? 'selected' : '' }}>
                OPTIONAL — Pembuat dapat memilih Langsung Publish atau Ajukan Approval Manager HCGA
            </option>
            <option value="REQUIRED" {{ ($settings['REGULATION'] ?? '') === 'REQUIRED' ? 'selected' : '' }}>
                REQUIRED — Wajib Melalui Approval Manager HCGA
            </option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold text-dark">Mode Pengajuan: Informasi</label>
        <select name="information_mode" class="form-select">
            <option value="DIRECT" {{ ($settings['INFORMATION'] ?? 'DIRECT') === 'DIRECT' ? 'selected' : '' }}>
                DIRECT — Langsung Publish (Tanpa Approval)
            </option>
            <option value="OPTIONAL" {{ ($settings['INFORMATION'] ?? '') === 'OPTIONAL' ? 'selected' : '' }}>
                OPTIONAL — Pembuat dapat memilih Langsung Publish atau Ajukan Approval Manager HCGA
            </option>
            <option value="REQUIRED" {{ ($settings['INFORMATION'] ?? '') === 'REQUIRED' ? 'selected' : '' }}>
                REQUIRED — Wajib Melalui Approval Manager HCGA
            </option>
        </select>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary fw-bold px-4">
            <i class="fa fa-save me-1"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<script>
    $('#storageSettingsForm').on('submit', function(e) {
        e.preventDefault();
        $.post("{{ route('hrd.storage.settings.update') }}", $(this).serialize())
            .done(function(res) {
                alert(res.message || 'Pengaturan disimpan!');
                window.location.reload();
            })
            .fail(function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menyimpan pengaturan.');
            });
    });
</script>
