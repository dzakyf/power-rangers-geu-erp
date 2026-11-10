<div class="education-information">
    <div class="section-title">
        <i class="fas fa-graduation-cap mr-2 me-2"></i> Pendidikan Terakhir
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle mr-1 me-1"></i>
        <strong>Informasi:</strong> Cantumkan informasi pendidikan terakhir Anda (jenjang pendidikan, nama instansi, dan tahun lulus).
    </div>

    <div class="row">
        {{-- 6a. Jenjang Pendidikan Terakhir --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="Last_Education">
                    Jenjang Pendidikan <span class="required">*</span>
                </label>
                <select id="Last_Education" name="Last_Education" class="form-control" required>
                    <option value="">-- Pilih Jenjang --</option>
                    <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                    <option value="D1">Diploma 1 (D1)</option>
                    <option value="D2">Diploma 2 (D2)</option>
                    <option value="D3">Diploma 3 (D3)</option>
                    <option value="D4">Diploma 4 (D4)</option>
                    <option value="S1">Sarjana (S1)</option>
                    <option value="S2">Magister (S2)</option>
                    <option value="S3">Doktor (S3)</option>
                    <option value="SMP">SMP / Sederajat</option>
                    <option value="SD">SD / Sederajat</option>
                </select>
                <div class="invalid-feedback">Harap pilih jenjang pendidikan terakhir</div>
            </div>
        </div>

        {{-- 6b. Nama Instansi Pendidikan --}}
        <div class="col-md-5">
            <div class="form-group">
                <label for="Last_Education_From">
                    Nama Instansi / Sekolah / Universitas <span class="required">*</span>
                </label>
                <input type="text" id="Last_Education_From" name="Last_Education_From" 
                       class="form-control" placeholder="Contoh: Universitas Indonesia / SMKN 1 Jakarta" 
                       required maxlength="150">
                <div class="invalid-feedback">Harap isi nama instansi pendidikan</div>
            </div>
        </div>

        {{-- 6c. Tahun Lulus --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="Graduation_Year">
                    Tahun Lulus <span class="required">*</span>
                </label>
                <input type="number" id="Graduation_Year" name="Graduation_Year" 
                       class="form-control" placeholder="Contoh: 2022" 
                       required min="1970" max="{{ date('Y') + 1 }}">
                <div class="invalid-feedback">Harap isi tahun kelulusan (4 digit)</div>
            </div>
        </div>

        {{-- Hidden optional fields for backend compatibility --}}
        <input type="hidden" id="Education_Major" name="Education_Major" value="">
        <input type="hidden" id="GPA" name="GPA" value="">
    </div>
</div>
