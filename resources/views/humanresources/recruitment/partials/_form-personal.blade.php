<div class="personal-information">
    <div class="section-title">
        <i class="fas fa-id-card mr-2 me-2"></i> Data Pribadi & Posisi Lamaran
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle mr-1 me-1"></i>
        <strong>Informasi:</strong> Silakan lengkapi data pribadi dan posisi yang dilamar. Bidang bertanda <span class="text-danger font-weight-bold fw-bold">*</span> wajib diisi.
    </div>

    <div class="row">
        {{-- 7. Posisi yang Dilamar --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Position">
                    Posisi yang Dilamar <span class="required">*</span>
                </label>
                <select id="Position" name="Position" class="form-control" required onchange="handlePositionSelection(this)">
                    <option value="">-- Pilih Posisi Jabatan --</option>
                    @php
                        $location = optional(Auth::user())->Location ?? ($publicLocation ?? 'GEU-HO');

                        // 1. Ambil seluruh posisi aktif dari master_position
                        $masterPositions = DB::table('master_position')
                            ->where('Status', 'ACTIVE')
                            ->orderBy('Position', 'ASC')
                            ->get();

                        // 2. Ambil requisition PTK APPROVED yang masih berlaku
                        $activeRequisitions = DB::table('requisition')
                            ->where('Status', 'APPROVED')
                            ->where(function($q) {
                                $q->whereNull('End_Date')
                                  ->orWhere('End_Date', '>=', date('Y-m-d'));
                            })
                            ->get()
                            ->keyBy('Job_Position');

                        foreach ($masterPositions as $pos) {
                            $hasActiveReq = isset($activeRequisitions[$pos->Position]);
                            $reqInfo = $hasActiveReq ? $activeRequisitions[$pos->Position] : null;
                            $reqTaskId = $reqInfo ? $reqInfo->Task_ID : '';
                            $badge = $hasActiveReq ? ' [✓ Lowongan Aktif PTK]' : '';
                            $dept = $pos->Department ? " ({$pos->Department})" : '';

                            echo "<option value='{$pos->Position}' data-req-id='{$reqTaskId}' data-has-req='" . ($hasActiveReq ? '1' : '0') . "'>{$pos->Position}{$dept}{$badge}</option>";
                        }
                    @endphp
                </select>
                <input type="hidden" id="Requisition_ID" name="Requisition_ID" value="">
                <div class="invalid-feedback">Harap pilih posisi jabatan yang dilamar</div>

                <div id="requisitionNotice" class="mt-2 small" style="display:none;">
                    <div class="alert alert-success py-1 px-2 mb-0 d-flex align-items-center">
                        <i class="fas fa-link mr-2 me-2 text-success"></i>
                        <span>Terhubung ke Permintaan Tenaga Kerja (PTK): <strong id="ptkTaskNotice"></strong></span>
                    </div>
                </div>
                <div id="noRequisitionNotice" class="mt-2 small" style="display:none;">
                    <div class="alert alert-light border py-1 px-2 mb-0 text-muted d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2 me-2 text-info"></i>
                        <span>Posisi disimpan ke <strong>Talent Pool</strong> rekrutmen perusahaan.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recruitment Type (Otomatis disembunyikan pada mode Public) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Recruitment_Type">
                    Jalur Penerimaan <span class="required">*</span>
                </label>
                <select id="Recruitment_Type" name="Recruitment_Type" class="form-control" required>
                    <option value="INTERNAL">Internal</option>
                    <option value="EXTERNAL" selected>External</option>
                </select>
                <small class="form-text text-muted">
                    Pilih jalur penerimaan pelamar (Internal / External)
                </small>
            </div>
        </div>
    </div>

    <hr class="my-3">

    <div class="row">
        {{-- 1. Nama Lengkap --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Name">
                    Nama Lengkap <span class="required">*</span>
                </label>
                <input type="text" id="Name" name="Name" class="form-control" 
                       placeholder="Masukkan nama lengkap sesuai KTP" required maxlength="150">
                <div class="invalid-feedback">Harap isi nama lengkap Anda</div>
            </div>
        </div>

        {{-- 4. Jenis Kelamin --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Gender">
                    Jenis Kelamin <span class="required">*</span>
                </label>
                <select id="Gender" name="Gender" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="MALE">Laki-laki (Male)</option>
                    <option value="FEMALE">Perempuan (Female)</option>
                </select>
                <div class="invalid-feedback">Harap pilih jenis kelamin</div>
            </div>
        </div>

        {{-- 2. Tanggal Lahir --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="Birth_Date">
                    Tanggal Lahir <span class="required">*</span>
                </label>
                <input type="date" id="Birth_Date" name="Birth_Date" class="form-control" 
                       required max="{{ date('Y-m-d') }}">
                <div class="invalid-feedback">Harap pilih tanggal lahir (minimal usia 17 tahun)</div>
            </div>
        </div>

        {{-- 3. Usia (Otomatis Dihitung) --}}
        <div class="col-md-2">
            <div class="form-group">
                <label for="Candidate_Age">
                    Usia <span class="required">*</span>
                </label>
                <div class="input-group">
                    <input type="text" id="Candidate_Age" name="Candidate_Age" class="form-control bg-light font-weight-bold fw-bold text-dark text-center" 
                           placeholder="0" readonly>
                    <div class="input-group-append">
                        <span class="input-group-text">Thn</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 9. Status Pernikahan --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Marital_Status">
                    Status Pernikahan <span class="required">*</span>
                </label>
                <select id="Marital_Status" name="Marital_Status" class="form-control" required>
                    <option value="">-- Pilih Status Pernikahan --</option>
                    <option value="SINGLE">Belum Menikah (Single)</option>
                    <option value="MARRIED">Menikah (Married)</option>
                    <option value="DIVORCED">Cerai Hidup (Divorced)</option>
                    <option value="WIDOWED">Cerai Mati (Widowed)</option>
                </select>
                <div class="invalid-feedback">Harap pilih status pernikahan</div>
            </div>
        </div>

        {{-- 10. Alamat Domisili --}}
        <div class="col-md-12">
            <div class="form-group mb-1">
                <label for="Address">
                    Alamat Domisili Saat Ini <span class="required">*</span>
                </label>
                <textarea id="Address" name="Address" class="form-control" rows="3" 
                          placeholder="Contoh: Jl. Merdeka No. 45 RT 002/RW 005, Kel. Gambir, Kec. Gambir, Jakarta Pusat" 
                          required maxlength="250"></textarea>
                <small class="form-text text-muted">Tuliskan alamat tempat tinggal / domisili Anda saat ini secara lengkap.</small>
                <div class="invalid-feedback">Harap isi alamat domisili lengkap</div>
            </div>
        </div>

        {{-- Hidden fields untuk kompatibilitas database legacy --}}
        <input type="hidden" id="Province" name="Province" value="">
        <input type="hidden" id="City" name="City" value="">
        <input type="hidden" id="District" name="District" value="">
        <input type="hidden" id="Sub_District" name="Sub_District" value="">
        <input type="hidden" id="ZipCode" name="ZipCode" value="">
        <input type="hidden" id="Birth_Place" name="Birth_Place" value="-">
        <input type="hidden" id="kewarganegaraan" name="kewarganegaraan" value="WNI">
        <input type="hidden" id="Religion" name="Religion" value="ISLAM">
        <input type="hidden" id="KTP" name="KTP" value="">
    </div>
</div>

<script>
function handlePositionSelection(selectElem) {
    var selectedOption = $(selectElem).find('option:selected');
    var reqId = selectedOption.data('req-id');
    var hasReq = selectedOption.data('has-req');
    var posName = selectedOption.val();

    if (!posName) {
        $('#Requisition_ID').val('');
        $('#requisitionNotice').hide();
        $('#noRequisitionNotice').hide();
        return;
    }

    if (hasReq == '1' && reqId) {
        $('#Requisition_ID').val(reqId);
        $('#ptkTaskNotice').text(reqId);
        $('#requisitionNotice').slideDown();
        $('#noRequisitionNotice').hide();
    } else {
        $('#Requisition_ID').val('');
        $('#requisitionNotice').hide();
        $('#noRequisitionNotice').slideDown();
    }
}

function calculateCandidateAge(birthDateString) {
    if (!birthDateString) return '';
    var birthDate = new Date(birthDateString);
    if (isNaN(birthDate.getTime())) return '';
    var today = new Date();
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age >= 0 ? age : 0;
}

$(document).ready(function() {
    $('#Birth_Date').on('change input', function() {
        var val = $(this).val();
        if (val) {
            var age = calculateCandidateAge(val);
            $('#Candidate_Age').val(age);
        } else {
            $('#Candidate_Age').val('');
        }
    });
});
</script>
