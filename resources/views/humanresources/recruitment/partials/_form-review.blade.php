<div class="review-information">
    <div class="section-title">
        <i class="fas fa-clipboard-check mr-2 me-2"></i> Tinjau & Kirim Lamaran
    </div>

    <div class="info-box">
        <i class="fas fa-check-circle mr-1 me-1"></i>
        <strong>Hampir Selesai!</strong> Silakan periksa kembali ringkasan data Anda di bawah ini sebelum mengirim lamaran.
    </div>

    {{-- 1. Posisi & Data Pribadi Review --}}
    <div class="review-section mb-3 p-3 border rounded bg-white">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h6 class="mb-0 font-weight-bold fw-bold text-success">
                <i class="fas fa-user mr-1 me-1"></i> 1. Data Pribadi & Posisi Lamaran
            </h6>
            <button type="button" class="btn btn-sm btn-outline-success py-1 px-3" onclick="switchToTab('personal-tab')">
                <i class="fas fa-edit mr-1 me-1"></i> Ubah
            </button>
        </div>
        <div class="row small">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-2">Posisi Dilamar:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-2" id="review-Position">-</dd>

                    <dt class="col-sm-5 text-muted mb-2">Nama Lengkap:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-2" id="review-Name">-</dd>

                    <dt class="col-sm-5 text-muted mb-2">Jenis Kelamin:</dt>
                    <dd class="col-sm-7 text-dark mb-2" id="review-Gender">-</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-2">Tanggal Lahir:</dt>
                    <dd class="col-sm-7 text-dark mb-2" id="review-Birth_Date">-</dd>

                    <dt class="col-sm-5 text-muted mb-2">Usia:</dt>
                    <dd class="col-sm-7 text-dark mb-2"><span id="review-Candidate_Age">-</span> Tahun</dd>

                    <dt class="col-sm-5 text-muted mb-2">Status Pernikahan:</dt>
                    <dd class="col-sm-7 text-dark mb-2" id="review-Marital_Status">-</dd>

                    <dt class="col-sm-5 text-muted mb-0">Alamat Domisili:</dt>
                    <dd class="col-sm-7 text-dark mb-0" id="review-Address">-</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- 2. Kontak & Referensi Review --}}
    <div class="review-section mb-3 p-3 border rounded bg-white">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h6 class="mb-0 font-weight-bold fw-bold text-success">
                <i class="fas fa-address-book mr-1 me-1"></i> 2. Kontak & Referensi Kandidat
            </h6>
            <button type="button" class="btn btn-sm btn-outline-success py-1 px-3" onclick="switchToTab('contact-tab')">
                <i class="fas fa-edit mr-1 me-1"></i> Ubah
            </button>
        </div>
        <div class="row small">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-2">Nomor WhatsApp:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-success mb-2" id="review-Handphone">-</dd>

                    <dt class="col-sm-5 text-muted mb-0">Alamat Email:</dt>
                    <dd class="col-sm-7 text-dark mb-0" id="review-Email">-</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-2">Nama Referensi:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-2" id="review-Emergency_Contact_Name">-</dd>

                    <dt class="col-sm-5 text-muted mb-2">Hubungan:</dt>
                    <dd class="col-sm-7 text-dark mb-2" id="review-Emergency_Relation">-</dd>

                    <dt class="col-sm-5 text-muted mb-2">No. Kontak Ref:</dt>
                    <dd class="col-sm-7 text-dark mb-2" id="review-Emergency_Contact_No">-</dd>

                    <dt class="col-sm-5 text-muted mb-0">Perusahaan Ref:</dt>
                    <dd class="col-sm-7 text-dark mb-0" id="review-Emergency_Address">-</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- 3. Pendidikan Terakhir Review --}}
    <div class="review-section mb-3 p-3 border rounded bg-white">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h6 class="mb-0 font-weight-bold fw-bold text-success">
                <i class="fas fa-graduation-cap mr-1 me-1"></i> 3. Pendidikan Terakhir
            </h6>
            <button type="button" class="btn btn-sm btn-outline-success py-1 px-3" onclick="switchToTab('education-tab')">
                <i class="fas fa-edit mr-1 me-1"></i> Ubah
            </button>
        </div>
        <div class="row small">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-2">Jenjang Pendidikan:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-2" id="review-Last_Education">-</dd>

                    <dt class="col-sm-5 text-muted mb-0">Nama Instansi:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-0" id="review-Last_Education_From">-</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted mb-0">Tahun Lulus:</dt>
                    <dd class="col-sm-7 font-weight-bold fw-bold text-dark mb-0" id="review-Graduation_Year">-</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- 4. Dokumen Review --}}
    <div class="review-section mb-3 p-3 border rounded bg-white">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h6 class="mb-0 font-weight-bold fw-bold text-success">
                <i class="fas fa-file-upload mr-1 me-1"></i> 4. Berkas Lampiran
            </h6>
            <button type="button" class="btn btn-sm btn-outline-success py-1 px-3" onclick="switchToTab('documents-tab')">
                <i class="fas fa-edit mr-1 me-1"></i> Ubah
            </button>
        </div>
        <ul class="list-unstyled mb-0 small" id="review-documents">
            <li class="mb-1 text-muted">
                <i class="fas fa-times-circle text-danger mr-2 me-2"></i> Belum ada berkas yang diunggah
            </li>
        </ul>
    </div>

    {{-- 5. Pernyataan & Persetujuan --}}
    <div class="review-section mb-3 p-3 border rounded bg-light">
        <h6 class="font-weight-bold fw-bold text-dark border-bottom pb-2 mb-3">
            <i class="fas fa-file-contract text-success mr-1 me-1"></i> Pernyataan & Persetujuan Kandidat
        </h6>
        <div class="mb-2 d-flex align-items-start">
            <input type="checkbox" id="agree_terms" name="agree_terms" class="mt-1 mr-2 me-2" style="width: 16px; height: 16px; cursor: pointer;" required>
            <label for="agree_terms" class="small mb-0" style="cursor: pointer;">
                <strong>Saya menyatakan bahwa</strong> seluruh informasi dan dokumen yang saya lampirkan adalah benar, akurat, dan dapat dipertanggungjawabkan.
            </label>
        </div>
        <div class="mb-3 d-flex align-items-start">
            <input type="checkbox" id="agree_privacy" name="agree_privacy" class="mt-1 mr-2 me-2" style="width: 16px; height: 16px; cursor: pointer;" required>
            <label for="agree_privacy" class="small mb-0 text-muted" style="cursor: pointer;">
                Saya memberikan persetujuan kepada perusahaan untuk memproses data pribadi dan menghubungi kontak referensi saya untuk keperluan seleksi rekrutmen.
            </label>
        </div>

        <div class="row pt-2 border-top">
            <div class="col-md-6">
                <div class="form-group mb-2 mb-md-0">
                    <label for="declaration_name" class="small font-weight-bold fw-bold">
                        Nama Lengkap (Tanda Tangan Elektronik) <span class="required">*</span>
                    </label>
                    <input type="text" id="declaration_name" name="declaration_name" 
                           class="form-control" placeholder="Ketik ulang nama lengkap Anda" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label for="declaration_date" class="small font-weight-bold fw-bold">
                        Tanggal Pengajuan <span class="required">*</span>
                    </label>
                    <input type="text" id="declaration_date" name="declaration_date" 
                           class="form-control bg-white" readonly required>
                </div>
            </div>
        </div>
    </div>
</div>
