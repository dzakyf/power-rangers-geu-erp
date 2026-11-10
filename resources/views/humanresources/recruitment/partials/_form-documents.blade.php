<div class="documents-information">
    <div class="section-title">
        <i class="fas fa-file-upload mr-2 me-2"></i> Unggah Berkas & Dokumen
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle mr-1 me-1"></i>
        <strong>Ketentuan Berkas:</strong> Dokumen yang <strong>wajib diunggah hanya CV dan KTP</strong>. Dokumen pendukung lainnya bersifat opsional (tidak wajib). Format: PDF, JPG, PNG (Maks 5MB per file).
    </div>

    <div class="row">
        {{-- CV Upload (WAJIB) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="CV_File">
                    <i class="fas fa-file-alt text-success mr-1 me-1"></i> Curriculum Vitae (CV) <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="CV_File" name="CV_File" 
                           accept=".pdf,.doc,.docx" required>
                    <label class="custom-file-label" for="CV_File">Pilih file CV (PDF / Word)</label>
                </div>
                <small class="form-text text-muted">
                    Format PDF atau Word (Maks 5MB) — <strong class="text-danger">Wajib Diunggah</strong>
                </small>
                <div class="invalid-feedback">Harap unggah file CV Anda</div>
                <div class="file-info mt-2" id="info-CV_File"></div>
            </div>
        </div>

        {{-- ID Card (KTP) Upload (WAJIB) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="KTP_File">
                    <i class="fas fa-id-card text-success mr-1 me-1"></i> Kartu Tanda Penduduk (KTP) <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="KTP_File" name="KTP_File" 
                           accept="image/*,.pdf" required>
                    <label class="custom-file-label" for="KTP_File">Pilih scan / foto KTP</label>
                </div>
                <small class="form-text text-muted">
                    Scan atau foto KTP yang jelas (Maks 5MB) — <strong class="text-danger">Wajib Diunggah</strong>
                </small>
                <div class="invalid-feedback">Harap unggah file KTP Anda</div>
                <div class="preview-container mt-2" id="preview-KTP_File"></div>
                <div class="file-info mt-2" id="info-KTP_File"></div>
            </div>
        </div>

        {{-- Photo Upload (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Foto">
                    <i class="fas fa-camera text-secondary mr-1 me-1"></i> Pas Foto Formal <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Foto" name="Foto" 
                           accept="image/jpeg,image/jpg,image/png">
                    <label class="custom-file-label" for="Foto">Pilih pas foto (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Pas foto formal terbaru (JPG/PNG, Maks 2MB)
                </small>
                <div class="preview-container mt-2" id="preview-Foto"></div>
            </div>
        </div>

        {{-- Education Certificate Upload (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Education_Certificate_File">
                    <i class="fas fa-graduation-cap text-secondary mr-1 me-1"></i> Ijazah / SKL Terakhir <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Education_Certificate_File" 
                           name="Education_Certificate_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Education_Certificate_File">Pilih file ijazah (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Ijazah atau Surat Keterangan Lulus pendidikan terakhir
                </small>
                <div class="preview-container mt-2" id="preview-Education_Certificate_File"></div>
                <div class="file-info mt-2" id="info-Education_Certificate_File"></div>
            </div>
        </div>

        {{-- Transcript Upload (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Transcript_File">
                    <i class="fas fa-file-alt text-secondary mr-1 me-1"></i> Transkrip Nilai <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Transcript_File" 
                           name="Transcript_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Transcript_File">Pilih transkrip nilai (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Transkrip nilai akademik jika tersedia
                </small>
                <div class="preview-container mt-2" id="preview-Transcript_File"></div>
                <div class="file-info mt-2" id="info-Transcript_File"></div>
            </div>
        </div>

        {{-- NPWP Upload (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="NPWP_File">
                    <i class="fas fa-file-invoice text-secondary mr-1 me-1"></i> Kartu NPWP <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="NPWP_File" 
                           name="NPWP_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="NPWP_File">Pilih file NPWP (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Kartu NPWP jika sudah memiliki
                </small>
                <div class="preview-container mt-2" id="preview-NPWP_File"></div>
                <div class="file-info mt-2" id="info-NPWP_File"></div>
            </div>
        </div>

        {{-- Additional Certificates (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Certificate_File">
                    <i class="fas fa-certificate text-secondary mr-1 me-1"></i> Sertifikat Keahlian / Pelatihan <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Certificate_File" 
                           name="Certificate_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Certificate_File">Pilih sertifikat (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Sertifikat kursus / pelatihan pendukung jika ada
                </small>
                <div class="preview-container mt-2" id="preview-Certificate_File"></div>
                <div class="file-info mt-2" id="info-Certificate_File"></div>
            </div>
        </div>

        {{-- Reference Letter (OPSIONAL) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Reference_Letter_File">
                    <i class="fas fa-envelope-open-text text-secondary mr-1 me-1"></i> Surat Pengalaman Kerja / Paklaring <span class="text-muted font-weight-normal fw-normal">(Opsional)</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Reference_Letter_File" 
                           name="Reference_Letter_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Reference_Letter_File">Pilih surat referensi (opsional)</label>
                </div>
                <small class="form-text text-muted">
                    Surat pengalaman kerja dari perusahaan sebelumnya jika ada
                </small>
                <div class="preview-container mt-2" id="preview-Reference_Letter_File"></div>
                <div class="file-info mt-2" id="info-Reference_Letter_File"></div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Document Checklist --}}
    <div class="document-checklist p-3 bg-light rounded border">
        <h6 class="font-weight-bold fw-bold mb-3 text-dark">
            <i class="fas fa-clipboard-check text-success mr-1 me-1"></i> Indikator Kelengkapan Berkas
        </h6>
        <div class="row small">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-CV_File"></i>
                        <span>Curriculum Vitae (CV) <span class="badge bg-danger badge-danger text-white ml-1 ms-1">Wajib</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-KTP_File"></i>
                        <span>Kartu Tanda Penduduk (KTP) <span class="badge bg-danger badge-danger text-white ml-1 ms-1">Wajib</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-Foto"></i>
                        <span>Pas Foto Formal <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-Education_Certificate_File"></i>
                        <span>Ijazah / SKL <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-Transcript_File"></i>
                        <span>Transkrip Nilai <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-NPWP_File"></i>
                        <span>Kartu NPWP <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-Certificate_File"></i>
                        <span>Sertifikat Pelatihan <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fas fa-check-circle text-muted mr-2 me-2" id="check-Reference_Letter_File"></i>
                        <span>Surat Pengalaman Kerja <span class="text-muted ml-1 ms-1">(Opsional)</span></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .preview-container img {
        max-width: 180px;
        max-height: 150px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 4px;
        background: white;
    }
    
    .file-info {
        font-size: 0.85rem;
        color: #198754;
        font-weight: 600;
    }
    
    .document-checklist .fa-check-circle.text-success {
        color: #198754 !important;
    }
</style>
