<form id="storageForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="module_type" value="{{ $moduleType }}">
    @if($action === 'edit' && $doc)
        <input type="hidden" name="id" value="{{ $doc->id }}">
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Kategori / Tipe <span class="text-danger">*</span></label>
            <select name="sub_type" class="form-select" required>
                @foreach($subTypes as $code => $label)
                    <option value="{{ $code }}" {{ (isset($doc) && $doc->sub_type === $code) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Nomor Dokumen</label>
            <input type="text" name="document_number" class="form-control"
                placeholder="{{ $moduleType === 'REGULATION' ? 'Contoh: SK-DIR/001/2026' : 'Contoh: INFO/HRD/001/2026' }}"
                value="{{ $doc->document_number ?? '' }}">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Departemen Pemilik</label>
            <select name="owner_department" class="form-select">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ ((isset($doc) ? $doc->owner_department : (Auth::user()->Department ?? '')) === $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Judul Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="Tuliskan judul regulasi atau informasi..." value="{{ $version->title ?? ($doc->title ?? '') }}" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Ringkasan Singkat (Summary)</label>
            <input type="text" name="summary" class="form-control" maxlength="500" placeholder="Ringkasan singkat untuk preview di Mobile Apps..." value="{{ $version->summary ?? '' }}">
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Isi / Ketentuan Lengkap <span class="text-danger">*</span></label>
            <textarea name="content" class="form-control" rows="6" placeholder="Tuliskan isi atau deskripsi lengkap dokumen..." required>{{ $version->content ?? '' }}</textarea>
        </div>

        <!-- Ketetapan Masa Berlaku -->
        <div class="col-12">
            <div class="p-3 border rounded bg-light">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-calendar-check text-primary me-1"></i> Ketetapan Masa Berlaku</h6>
                @php
                    $vMode = $version ? $version->validity_mode : 'PERPETUAL';
                    $effFrom = $version && $version->effective_from ? $version->effective_from : date('Y-m-d');
                    $effUntil = $version && $version->effective_until ? $version->effective_until : '';
                @endphp
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Mode Masa Berlaku</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="validity_mode" id="valPerpetual" value="PERPETUAL"
                                    {{ $vMode === 'PERPETUAL' ? 'checked' : '' }} onchange="toggleValidityUntil()">
                                <label class="form-check-label small fw-semibold" for="valPerpetual">Selalu Aktif (Tanpa Batas)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="validity_mode" id="valFixed" value="FIXED_RANGE"
                                    {{ $vMode === 'FIXED_RANGE' ? 'checked' : '' }} onchange="toggleValidityUntil()">
                                <label class="form-check-label small fw-semibold" for="valFixed">Rentang Tanggal</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Berlaku Dari Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $effFrom }}" required>
                    </div>
                    <div class="col-md-4" id="effectiveUntilWrap" style="{{ $vMode === 'PERPETUAL' ? 'display:none;' : '' }}">
                        <label class="form-label small fw-semibold">Berlaku Sampai Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="effective_until" id="effectiveUntilInput" class="form-control form-control-sm" value="{{ $effUntil }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Target Audience -->
        <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-users text-primary me-1"></i> Target Audience</h6>
                @php
                    $aud = $version ? $version->target_audience : 'ALL';
                    $selectedDepts = ($version && is_array($version->target_departments)) ? $version->target_departments : [];
                @endphp
                <div class="d-flex gap-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_audience" id="audAll" value="ALL"
                            {{ $aud !== 'DEPARTMENT' ? 'checked' : '' }} onchange="toggleDeptSelect()">
                        <label class="form-check-label small fw-semibold" for="audAll">Semua Karyawan (ALL)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_audience" id="audDept" value="DEPARTMENT"
                            {{ $aud === 'DEPARTMENT' ? 'checked' : '' }} onchange="toggleDeptSelect()">
                        <label class="form-check-label small fw-semibold" for="audDept">Departemen Tertentu</label>
                    </div>
                </div>
                <div id="deptMultiWrap" style="{{ $aud === 'DEPARTMENT' ? '' : 'display:none;' }}">
                    <div class="border rounded bg-white p-2" style="max-height: 130px; overflow-y: auto;">
                        <div class="row g-1">
                            @foreach($departments as $dept)
                                <div class="col-6">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="target_departments[]"
                                            value="{{ $dept }}" id="deptChk_{{ $loop->index }}"
                                            {{ in_array($dept, $selectedDepts) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="deptChk_{{ $loop->index }}">{{ $dept }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrasi Pengumuman Mobile -->
        <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-mobile-alt text-primary me-1"></i> Pengaturan Mobile Apps</h6>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="publish_to_mobile" value="0">
                    <input class="form-check-input" type="checkbox" name="publish_to_mobile" value="1" id="swPublishMobile"
                        {{ (!isset($version) || $version->publish_to_mobile) ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="swPublishMobile">Publikasikan ke Pengumuman Mobile Apps</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="swPin"
                        {{ (isset($version) && $version->is_pinned) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="swPin">Sematkan di Atas Feed (Pinned)</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="require_read_receipt" value="1" id="swReadReceipt"
                        {{ (isset($version) && $version->require_read_receipt) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="swReadReceipt">Wajib Konfirmasi Baca (Read Receipt)</label>
                </div>
            </div>
        </div>

        <!-- Lampiran & Thumbnail -->
        <div class="col-md-6">
            <label class="form-label fw-bold text-dark">File Dokumen Lampiran (PDF/Office)</label>
            <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
            @if(isset($version) && $version->attachment_file)
                <div class="mt-2 d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                    <span class="small text-truncate"><i class="fa fa-paperclip text-primary me-1"></i>{{ $version->attachment_name ?: basename($version->attachment_file) }}</span>
                    <div class="form-check mb-0 ms-2">
                        <input class="form-check-input" type="checkbox" name="remove_attachment" value="1" id="rmAtt">
                        <label class="form-check-label small text-danger" for="rmAtt">Hapus</label>
                    </div>
                </div>
            @endif
            <div class="form-text">Maksimal 25MB. File PDF dapat langsung di-preview di detail CMS & Mobile.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Thumbnail / Banner (Opsional)</label>
            <input type="file" name="thumbnail" class="form-control" accept="image/*">
            @if(isset($version) && $version->thumbnail)
                <div class="mt-2 d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                    <img src="{{ asset($version->thumbnail) }}" class="rounded border" style="width:40px;height:40px;object-fit:cover;">
                    <div class="form-check mb-0 ms-2">
                        <input class="form-check-input" type="checkbox" name="remove_thumbnail" value="1" id="rmThumb">
                        <label class="form-check-label small text-danger" for="rmThumb">Hapus</label>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <hr class="my-4">

    <input type="hidden" name="submit_action" id="submitActionField" value="PUBLISH">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            <i class="fa fa-shield-alt text-info me-1"></i>
            Mode Approval {{ $moduleType === 'REGULATION' ? 'Regulasi' : 'Informasi' }}:
            <strong>{{ $approvalMode }}</strong>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Batal</button>
            @if($action === 'create' || (isset($version) && in_array($version->version_status, ['DRAFT', 'REJECTED'])))
                <button type="button" class="btn btn-outline-secondary px-3" onclick="submitStorageForm('DRAFT')">
                    <i class="fa fa-save me-1"></i> Simpan Draft
                </button>
                @if($approvalMode === 'REQUIRED')
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4" onclick="submitStorageForm('SUBMIT_APPROVAL')">
                        <i class="fa fa-paper-plane me-1"></i> Ajukan Approval (Manager HCGA)
                    </button>
                @elseif($approvalMode === 'OPTIONAL')
                    <button type="button" class="btn btn-outline-warning text-dark fw-semibold px-3" onclick="submitStorageForm('SUBMIT_APPROVAL')">
                        <i class="fa fa-user-check me-1"></i> Ajukan Approval
                    </button>
                    <button type="button" class="btn btn-primary fw-bold px-4" onclick="submitStorageForm('PUBLISH')">
                        <i class="fa fa-check-circle me-1"></i> Langsung Publikasikan
                    </button>
                @else
                    <button type="button" class="btn btn-primary fw-bold px-4" onclick="submitStorageForm('PUBLISH')">
                        <i class="fa fa-check-circle me-1"></i> Publikasikan
                    </button>
                @endif
            @else
                <button type="button" class="btn btn-primary fw-bold px-4" onclick="submitStorageForm('PUBLISH')">
                    <i class="fa fa-save me-1"></i> Simpan Perubahan Metadata
                </button>
            @endif
        </div>
    </div>
</form>

<script>
    function toggleValidityUntil() {
        var isFixed = document.getElementById('valFixed').checked;
        document.getElementById('effectiveUntilWrap').style.display = isFixed ? 'block' : 'none';
        document.getElementById('effectiveUntilInput').required = isFixed;
    }

    function toggleDeptSelect() {
        var isDept = document.getElementById('audDept').checked;
        document.getElementById('deptMultiWrap').style.display = isDept ? 'block' : 'none';
    }

    function submitStorageForm(action) {
        var form = document.getElementById('storageForm');
        if (!form.reportValidity()) {
            return;
        }
        document.getElementById('submitActionField').value = action;

        var formData = new FormData(form);
        var url = "{{ $action === 'edit' ? route('hrd.storage.update') : route('hrd.storage.store') }}";

        $('#storageForm button').prop('disabled', true);

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                alert(res.message || 'Berhasil disimpan!');
                window.location.reload();
            },
            error: function(xhr) {
                $('#storageForm button').prop('disabled', false);
                var msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.';
                if (xhr.responseJSON?.errors) {
                    msg += "\n" + Object.values(xhr.responseJSON.errors).flat().join("\n");
                }
                alert(msg);
            }
        });
    }
</script>
