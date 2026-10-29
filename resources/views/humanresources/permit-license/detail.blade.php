@php $isEdit = $permit !== null; @endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $isEdit ? 'Edit Permit & License #' . $permit->ID : 'New Permit & License' }}</strong>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="PermitLicenseCancel();">Tutup</button>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ url('hrd-permit-license-save') }}" enctype="multipart/form-data" id="PermitLicenseForm">
            @csrf
            <input type="hidden" name="ID" value="{{ $isEdit ? $permit->ID : 0 }}" />

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Kode *</label>
                    <select name="Category_ID" class="form-control" required>
                        <option value="">- Pilih Kode -</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->ID }}" {{ (string) old('Category_ID', $permit->Category_ID ?? '') === (string) $category->ID ? 'selected' : '' }}>
                            {{ $category->Code }} - {{ $category->Name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Kode 001 = Permit &amp; license, 002 = Sewa menyewa, 003 = Sewa menyewa - Office.</small>
                </div>
                <div class="form-group col-md-6">
                    <label>Nama Perizinan *</label>
                    <input type="text" name="Name" class="form-control" value="{{ old('Name', $permit->Name ?? '') }}" required maxlength="255" />
                </div>
                <div class="form-group col-md-3">
                    <label>Tanggal Terbit</label>
                    <input type="date" name="Issue_Date" class="form-control" value="{{ old('Issue_Date', $permit->Issue_Date ?? '') }}" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Instansi yang menerbitkan</label>
                    <input type="text" name="Issuer" class="form-control" value="{{ old('Issuer', $permit->Issuer ?? '') }}" maxlength="255" />
                </div>
                <div class="form-group col-md-3">
                    <label>Nomor Perizinan</label>
                    <input type="text" name="Permit_Number" class="form-control" value="{{ old('Permit_Number', $permit->Permit_Number ?? '') }}" maxlength="150" />
                </div>
                <div class="form-group col-md-3">
                    <label>Pengingat (hari sebelum berakhir)</label>
                    <input type="number" name="Reminder_Days" class="form-control" min="1" max="365" value="{{ old('Reminder_Days', $permit->Reminder_Days ?? $defaultReminderDays) }}" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Kontak PIC - Nama</label>
                    <input type="text" name="PIC_Name" class="form-control" value="{{ old('PIC_Name', $permit->PIC_Name ?? '') }}" maxlength="150" />
                </div>
                <div class="form-group col-md-6">
                    <label>Kontak PIC - Telepon / Email</label>
                    <input type="text" name="PIC_Contact" class="form-control" value="{{ old('PIC_Contact', $permit->PIC_Contact ?? '') }}" maxlength="150" />
                </div>
            </div>

            <hr>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tipe Masa Berlaku *</label>
                    <select name="Validity_Type" id="Validity_Type" class="form-control" required onchange="PermitLicenseToggleValidity();">
                        <option value="TEXT" {{ old('Validity_Type', $permit->Validity_Type ?? 'TEXT') === 'TEXT' ? 'selected' : '' }}>Berupa teks / sesuai ketentuan</option>
                        <option value="END_DATE" {{ old('Validity_Type', $permit->Validity_Type ?? 'TEXT') === 'END_DATE' ? 'selected' : '' }}>Hingga tanggal tertentu</option>
                        <option value="PERIOD" {{ old('Validity_Type', $permit->Validity_Type ?? 'TEXT') === 'PERIOD' ? 'selected' : '' }}>Periode (tanggal awal - tanggal akhir)</option>
                    </select>
                </div>

                <div class="form-group col-md-8" id="Validity_Text_Wrap">
                    <label>Masa Berlaku (teks) *</label>
                    <textarea name="Validity_Text" class="form-control" rows="2" maxlength="2000" placeholder="Contoh: Selama usaha berjalan, sepanjang tidak dicabut atau diubah sesuai ketentuan">{{ old('Validity_Text', $permit->Validity_Text ?? '') }}</textarea>
                </div>

                <div class="form-group col-md-4" id="Valid_From_Wrap">
                    <label>Tanggal Awal *</label>
                    <input type="date" name="Valid_From" class="form-control" value="{{ old('Valid_From', $permit->Valid_From ?? '') }}" />
                </div>

                <div class="form-group col-md-4" id="Valid_To_Wrap">
                    <label>Tanggal Akhir *</label>
                    <input type="date" name="Valid_To" class="form-control" value="{{ old('Valid_To', $permit->Valid_To ?? '') }}" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Upload Dokumen</label>
                    <input type="file" name="Document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" />
                    <small class="form-text text-muted">PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, atau XLSX. Maksimal 20 MB.</small>
                </div>

                @if($isEdit && !empty($permit->Meta_File_ID))
                <div class="form-group col-md-6">
                    <label>Dokumen Saat Ini</label>
                    <div>
                        <a href="{{ url('hrd-permit-license-document?ID=' . $permit->ID) }}" target="_blank" class="btn btn-sm btn-outline-info">Lihat</a>
                        <a href="{{ url('hrd-permit-license-download?ID=' . $permit->ID) }}" class="btn btn-sm btn-outline-primary">Download</a>
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="Remove_Document" value="1" class="form-check-input" id="Remove_Document" {{ old('Remove_Document') ? 'checked' : '' }} />
                        <label class="form-check-label" for="Remove_Document">Hapus dokumen saat menyimpan</label>
                    </div>
                </div>
                @endif
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" onclick="PermitLicenseCancel();">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function PermitLicenseToggleValidity() {
    var type = document.getElementById('Validity_Type').value;
    var textWrap = document.getElementById('Validity_Text_Wrap');
    var fromWrap = document.getElementById('Valid_From_Wrap');
    var toWrap = document.getElementById('Valid_To_Wrap');
    if (!textWrap || !fromWrap || !toWrap) return;

    textWrap.style.display = type === 'TEXT' ? '' : 'none';
    fromWrap.style.display = type === 'PERIOD' ? '' : 'none';
    toWrap.style.display = (type === 'END_DATE' || type === 'PERIOD') ? '' : 'none';

    var text = textWrap.querySelector('textarea');
    var from = fromWrap.querySelector('input');
    var to = toWrap.querySelector('input');
    if (text) text.required = type === 'TEXT';
    if (from) from.required = type === 'PERIOD';
    if (to) to.required = type === 'END_DATE' || type === 'PERIOD';
}

function PermitLicenseCancel() {
    var detail = document.getElementById('PermitLicenseDetail');
    if (detail) detail.innerHTML = '';
}

PermitLicenseToggleValidity();
</script>