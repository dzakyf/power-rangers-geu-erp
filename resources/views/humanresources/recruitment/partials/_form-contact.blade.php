<div class="contact-information">
    <div class="section-title">
        <i class="fas fa-address-book mr-2 me-2"></i> Kontak & Referensi Kandidat
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle mr-1 me-1"></i>
        <strong>Informasi:</strong> Masukkan nomor WhatsApp dan email aktif yang dapat dihubungi, serta data kontak referensi profesional Anda.
    </div>

    <div class="row">
        {{-- 5. Nomor WhatsApp --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Handphone">
                    Nomor WhatsApp Aktif <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                    </div>
                    <input type="tel" id="Handphone" name="Handphone" class="form-control" 
                           placeholder="Contoh: 081234567890" required maxlength="15" 
                           pattern="[0-9]{10,15}">
                </div>
                <small class="form-text text-muted">Pastikan nomor terhubung dengan WhatsApp aktif (10-15 digit)</small>
                <div class="invalid-feedback">Harap isi nomor WhatsApp yang valid (10-15 digit angka)</div>
            </div>
        </div>

        {{-- 8. Email --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Email">
                    Alamat Email Aktif <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope text-secondary"></i></span>
                    </div>
                    <input type="email" id="Email" name="Email" class="form-control" 
                           placeholder="Contoh: kandidat@email.com" required maxlength="100">
                </div>
                <small class="form-text text-muted">Informasi undangan seleksi akan dikirimkan ke email ini</small>
                <div class="invalid-feedback">Harap isi alamat email yang valid</div>
            </div>
        </div>

        {{-- Hidden legacy phone --}}
        <input type="hidden" id="Phone" name="Phone" value="">
    </div>

    <hr class="my-4">

    {{-- 11. Kontak Referensi --}}
    <div class="section-title">
        <i class="fas fa-user-check mr-2 me-2"></i> Kontak Referensi
    </div>

    <div class="alert alert-light border py-2 px-3 small text-muted mb-3">
        <i class="fas fa-info-circle text-success mr-1 me-1"></i>
        Cantumkan kontak referensi (atasan sebelumnya, rekan kerja, dosen/pembimbing, atau kerabat profesional) yang dapat dikonfirmasi.
    </div>

    <div class="row">
        {{-- Nama Kontak Referensi --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Contact_Name">
                    Nama Kontak Referensi <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user text-secondary"></i></span>
                    </div>
                    <input type="text" id="Emergency_Contact_Name" name="Emergency_Contact_Name" 
                           class="form-control" placeholder="Contoh: Bpk. Budi Santoso" required maxlength="100">
                </div>
                <div class="invalid-feedback">Harap isi nama kontak referensi</div>
            </div>
        </div>

        {{-- Hubungan dengan Kandidat --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Relation">
                    Hubungan dengan Kandidat <span class="required">*</span>
                </label>
                <select id="Emergency_Relation" name="Emergency_Relation" class="form-control" required>
                    <option value="">-- Pilih Hubungan --</option>
                    <option value="Mantan Atasan / Supervisor">Mantan Atasan / Supervisor</option>
                    <option value="Rekan Kerja">Rekan Kerja (Colleague)</option>
                    <option value="Dosen / Pembimbing">Dosen / Guru / Pembimbing</option>
                    <option value="Kerabat / Keluarga">Kerabat / Keluarga</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                <div class="invalid-feedback">Harap pilih hubungan dengan kandidat</div>
            </div>
        </div>

        {{-- Nomor Kontak Referensi --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Contact_No">
                    Nomor Kontak / HP Referensi <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone-alt text-secondary"></i></span>
                    </div>
                    <input type="tel" id="Emergency_Contact_No" name="Emergency_Contact_No" 
                           class="form-control" placeholder="Contoh: 081298765432" required maxlength="15"
                           pattern="[0-9]{10,15}">
                </div>
                <div class="invalid-feedback">Harap isi nomor telepon referensi yang valid (10-15 digit)</div>
            </div>
        </div>

        {{-- Perusahaan Referensi --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Address">
                    Perusahaan / Instansi Referensi <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-building text-secondary"></i></span>
                    </div>
                    <input type="text" id="Emergency_Address" name="Emergency_Address" 
                           class="form-control" placeholder="Contoh: PT. Graha Energi Usahatama" 
                           required maxlength="150">
                </div>
                <div class="invalid-feedback">Harap isi nama perusahaan atau instansi referensi</div>
            </div>
        </div>
    </div>
</div>
