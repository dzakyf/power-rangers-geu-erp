@php
    $isEdit = isset($kyc);
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terdapat kesalahan pengisian data:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Bagian Header / Pengisi Form -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
            <i class="fa fa-user-edit mr-2"></i>Filler Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="filler_name" class="text-black form-label font-weight-bold">
                    Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="text-black form-control @error('filler_name') is-invalid @enderror" 
                       id="filler_name" name="filler_name" 
                       value="{{ old('filler_name', $isEdit ? $kyc->filler_name : '') }}" 
                       placeholder="Nama lengkap pengisi form" required>
                <div class="invalid-feedback" id="err_filler_name">@error('filler_name') {{ $message }} @else Nama pengisi form wajib diisi. @enderror</div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="form_date" class="text-black form-label font-weight-bold">
                    Date <span class="text-danger">*</span>
                </label>
                <input type="date" class="text-black form-control @error('form_date') is-invalid @enderror" 
                       id="form_date" name="form_date" 
                       value="{{ old('form_date', $isEdit ? ($kyc->form_date ? $kyc->form_date->format('Y-m-d') : '') : date('Y-m-d')) }}" 
                       required>
                <div class="invalid-feedback" id="err_form_date">@error('form_date') {{ $message }} @else Tanggal form wajib diisi. @enderror</div>
            </div>
            <!-- <div class="col-md-4 mb-3">
                <label for="supplier_id" class="text-black form-label font-weight-bold">
                    Hubungkan ke Master Partner / Buyer <span class="text-muted font-weight-normal">(Opsional)</span>
                </label>
                <select class="text-black form-control select2 @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                    <option value="">-- Pilih Partner / Standalone --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->ID }}" 
                                data-name="{{ $supplier->Company_Name }}"
                                data-address="{{ $supplier->Address ?? '' }}"
                                data-phone="{{ $supplier->Phone ?? $supplier->HP ?? '' }}"
                                data-email="{{ $supplier->Email ?? '' }}"
                                {{ (string) old('supplier_id', $isEdit ? $kyc->supplier_id : '') === (string) $supplier->ID ? 'selected' : '' }}>
                            {{ $supplier->Company_Name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Jika dipilih, nama dan alamat otomatis terisi ke form di bawah.</small>
            </div> -->
        </div>
    </div>
</div>

<!-- Bagian General Company Information -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
            <i class="fa fa-building mr-2"></i>General Company Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="company_name" class="text-black form-label font-weight-bold">
                    Company name <span class="text-danger">*</span>
                </label>
                <input type="text" class="text-black form-control @error('company_name') is-invalid @enderror" 
                       id="company_name" name="company_name" 
                       value="{{ old('company_name', $isEdit ? $kyc->company_name : '') }}" 
                       placeholder="Contoh: PT. ABC Global Trading" required>
                <div class="invalid-feedback" id="err_company_name">@error('company_name') {{ $message }} @else Nama perusahaan wajib diisi. @enderror</div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="company_reg_number" class="text-black form-label font-weight-bold">
                    Company registration number
                </label>
                <input type="text" class="text-black form-control @error('company_reg_number') is-invalid @enderror" 
                       id="company_reg_number" name="company_reg_number" 
                       value="{{ old('company_reg_number', $isEdit ? $kyc->company_reg_number : '') }}" 
                       placeholder="Contoh: AHU-0012345.AH.01.01">
            </div>
            <div class="col-md-3 mb-3">
                <label for="incorporation_date" class="text-black form-label font-weight-bold">
                    Date of incorporation
                </label>
                <input type="date" class="text-black form-control @error('incorporation_date') is-invalid @enderror" 
                       id="incorporation_date" name="incorporation_date" 
                       value="{{ old('incorporation_date', $isEdit ? ($kyc->incorporation_date ? $kyc->incorporation_date->format('Y-m-d') : '') : '') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="registered_address" class="text-black form-label font-weight-bold">
                    Registered address
                </label>
                <textarea class="text-black form-control @error('registered_address') is-invalid @enderror" 
                          id="registered_address" name="registered_address" rows="3" 
                          placeholder="Alamat domisili terdaftar">{{ old('registered_address', $isEdit ? $kyc->registered_address : '') }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="admin_accounting_address" class="text-black form-label font-weight-bold">
                    Admin & accounting address
                </label>
                <textarea class="text-black form-control @error('admin_accounting_address') is-invalid @enderror" 
                          id="admin_accounting_address" name="admin_accounting_address" rows="3" 
                          placeholder="Alamat operasional / administrasi & keuangan">{{ old('admin_accounting_address', $isEdit ? $kyc->admin_accounting_address : '') }}</textarea>
                <button type="button" class="btn btn-sm btn-link p-0 mt-1" id="btnCopyAddress">
                    <i class="fa fa-copy"></i> Samakan dengan Registered Address
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="legal_entity_type" class="text-black form-label font-weight-bold">
                    Type of legal entity
                </label>
                <input type="text" class="text-black form-control @error('legal_entity_type') is-invalid @enderror" 
                       id="legal_entity_type" name="legal_entity_type" 
                       value="{{ old('legal_entity_type', $isEdit ? $kyc->legal_entity_type : '') }}" 
                       placeholder="Contoh: PT, Ltd, LLC, Corp, BV, Pte Ltd">
            </div>
            <div class="col-md-4 mb-3">
                <label for="vat_number" class="text-black form-label font-weight-bold">
                    VAT number
                </label>
                <input type="text" class="text-black form-control @error('vat_number') is-invalid @enderror" 
                       id="vat_number" name="vat_number" 
                       value="{{ old('vat_number', $isEdit ? $kyc->vat_number : '') }}" 
                       placeholder="Nomor Pokok Wajib Pajak (NPWP) / Tax ID / VAT">
            </div>
            <div class="col-md-4 mb-3">
                <label for="website" class="text-black form-label font-weight-bold">
                    Website address
                </label>
                <input type="text" class="text-black form-control @error('website') is-invalid @enderror" 
                       id="website" name="website" 
                       value="{{ old('website', $isEdit ? $kyc->website : '') }}" 
                       placeholder="https://www.example.com">
            </div>
        </div>

        <hr class="my-3">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="trading_contact_person" class="text-black form-label font-weight-bold">
                    Contact person for trading
                </label>
                <input type="text" class="text-black form-control @error('trading_contact_person') is-invalid @enderror" 
                       id="trading_contact_person" name="trading_contact_person" 
                       value="{{ old('trading_contact_person', $isEdit ? $kyc->trading_contact_person : '') }}" 
                       placeholder="Nama lengkap PIC bagian Trading / Komersial">
            </div>
            <div class="col-md-6 mb-3">
                <label for="trading_contact_details" class="text-black form-label font-weight-bold">
                    Contact details for (Trading PIC Email & Phone)
                </label>
                <input type="text" class="text-black form-control @error('trading_contact_details') is-invalid @enderror" 
                       id="trading_contact_details" name="trading_contact_details" 
                       value="{{ old('trading_contact_details', $isEdit ? $kyc->trading_contact_details : '') }}" 
                       placeholder="Contoh: Email: trader@company.com / Telp: +62 812-3456-7890">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="finance_contact_person" class="text-black form-label font-weight-bold">
                    Contact person for finance
                </label>
                <input type="text" class="text-black form-control @error('finance_contact_person') is-invalid @enderror" 
                       id="finance_contact_person" name="finance_contact_person" 
                       value="{{ old('finance_contact_person', $isEdit ? $kyc->finance_contact_person : '') }}" 
                       placeholder="Nama lengkap PIC bagian Keuangan / Finance">
            </div>
            <div class="col-md-6 mb-3">
                <label for="finance_contact_details" class="text-black form-label font-weight-bold">
                    Contact details for (Finance PIC Email & Phone)
                </label>
                <input type="text" class="text-black form-control @error('finance_contact_details') is-invalid @enderror" 
                       id="finance_contact_details" name="finance_contact_details" 
                       value="{{ old('finance_contact_details', $isEdit ? $kyc->finance_contact_details : '') }}" 
                       placeholder="Contoh: Email: finance@company.com / Telp: +62 811-2233-4455">
            </div>
        </div>
    </div>
</div>

<!-- Bagian Ownership & Management -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
            <i class="fa fa-users mr-2"></i>Ownership & Management
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="business_activities" class="text-black form-label font-weight-bold">
                    Business activities
                </label>
                <textarea class="text-black form-control @error('business_activities') is-invalid @enderror" 
                          id="business_activities" name="business_activities" rows="3" 
                          placeholder="Jelaskan bidang usaha utama perusahaan (misal: Trading UCO, Biofuel, Renewable Energy)">{{ old('business_activities', $isEdit ? $kyc->business_activities : '') }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="countries_of_activities" class="text-black form-label font-weight-bold">
                    Countries of activities
                </label>
                <textarea class="text-black form-control @error('countries_of_activities') is-invalid @enderror" 
                          id="countries_of_activities" name="countries_of_activities" rows="3" 
                          placeholder="Daftar negara operasional / negara rekanan bisnis">{{ old('countries_of_activities', $isEdit ? $kyc->countries_of_activities : '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<!-- Bagian Supporting Documents -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold gap-2">
            <i class="fa fa-file-upload"></i>Supporting Documents
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="doc_register_extract" class="text-black form-label font-weight-bold">
                    Commercial / Company register extract
                </label>
                <input type="file" class="text-black form-control-file @error('doc_register_extract') is-invalid @enderror" 
                       id="doc_register_extract" name="doc_register_extract" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small class="form-text text-muted">Format: PDF, JPG, PNG, DOCX (Maks. 10MB)</small>
                @if($isEdit && $kyc->doc_register_extract)
                    <div class="mt-2 p-2 bg-light border rounded">
                        <i class="fa fa-file-alt text-success mr-1"></i>
                        <strong>Dokumen saat ini:</strong> 
                        <a href="{{ route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => 'register']) }}" target="_blank">
                            {{ $kyc->doc_register_extract_name ?: 'Download Register Extract' }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label for="doc_certificate" class="text-black form-label font-weight-bold">
                    Sustainability certificate (e.g. ISCC) or trading certificate
                </label>
                <input type="file" class="text-black form-control-file @error('doc_certificate') is-invalid @enderror" 
                       id="doc_certificate" name="doc_certificate" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                <small class="form-text text-muted">Format: PDF, JPG, PNG, DOCX (Maks. 10MB)</small>
                @if($isEdit && $kyc->doc_certificate)
                    <div class="mt-2 p-2 bg-light border rounded">
                        <i class="fa fa-certificate text-success mr-1"></i>
                        <strong>Dokumen saat ini:</strong> 
                        <a href="{{ route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => 'certificate']) }}" target="_blank">
                            {{ $kyc->doc_certificate_name ?: 'Download Certificate' }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-fill form from selected supplier
        var supplierSelect = document.getElementById('supplier_id');
        if (supplierSelect) {
            supplierSelect.addEventListener('change', function () {
                var selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    var name = selectedOption.getAttribute('data-name');
                    var address = selectedOption.getAttribute('data-address');
                    var phone = selectedOption.getAttribute('data-phone');
                    var email = selectedOption.getAttribute('data-email');

                    var compNameInput = document.getElementById('company_name');
                    if (compNameInput && (!compNameInput.value || confirm('Isi Nama Perusahaan dari Partner terpilih?'))) {
                        compNameInput.value = name || '';
                    }

                    var regAddrInput = document.getElementById('registered_address');
                    if (regAddrInput && address && (!regAddrInput.value || confirm('Isi Registered Address dari alamat Partner?'))) {
                        regAddrInput.value = address || '';
                    }

                    var tradingContactDetails = document.getElementById('trading_contact_details');
                    if (tradingContactDetails && !tradingContactDetails.value && (email || phone)) {
                        tradingContactDetails.value = (email ? 'Email: ' + email : '') + (phone ? (email ? ' / ' : '') + 'Telp: ' + phone : '');
                    }
                }
            });
        }

        // Copy registered address to admin address
        var btnCopyAddress = document.getElementById('btnCopyAddress');
        if (btnCopyAddress) {
            btnCopyAddress.addEventListener('click', function () {
                var regAddr = document.getElementById('registered_address').value;
                document.getElementById('admin_accounting_address').value = regAddr;
            });
        }

        // Validasi client-side instan tanpa reload halaman
        var form = document.querySelector('form[action*="cns-customer-compliance-kyc"]');
        if (form) {
            form.setAttribute('novalidate', 'novalidate');

            var requiredInputs = form.querySelectorAll('#filler_name, #form_date, #company_name');
            requiredInputs.forEach(function (input) {
                ['input', 'change'].forEach(function (evtName) {
                    input.addEventListener(evtName, function () {
                        if (input.value && input.value.trim()) {
                            input.classList.remove('is-invalid');
                            var feedback = input.closest('.mb-3, .form-group') ? input.closest('.mb-3, .form-group').querySelector('.invalid-feedback') : null;
                            if (feedback) feedback.style.display = 'none';
                            if (!form.querySelector('.is-invalid')) {
                                var alertBox = document.getElementById('clientValidationAlert');
                                if (alertBox) alertBox.remove();
                            }
                        }
                    });
                });
            });

            form.addEventListener('submit', function (e) {
                var isValid = true;
                var firstInvalid = null;

                var validations = [
                    { id: '#filler_name', errId: '#err_filler_name', msg: 'Nama pengisi form wajib diisi.' },
                    { id: '#form_date', errId: '#err_form_date', msg: 'Tanggal form wajib diisi.' },
                    { id: '#company_name', errId: '#err_company_name', msg: 'Nama perusahaan wajib diisi.' }
                ];

                validations.forEach(function (item) {
                    var el = form.querySelector(item.id);
                    if (!el) return;
                    var val = el.value;
                    var errEl = form.querySelector(item.errId);
                    if (!val || !val.trim()) {
                        isValid = false;
                        el.classList.add('is-invalid');
                        if (errEl) {
                            errEl.textContent = item.msg;
                            errEl.style.display = 'block';
                        }
                        if (!firstInvalid) {
                            firstInvalid = el;
                        }
                    } else {
                        el.classList.remove('is-invalid');
                        if (errEl) {
                            errEl.style.display = 'none';
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();

                    var existingAlert = document.getElementById('clientValidationAlert');
                    if (existingAlert) existingAlert.remove();

                    var alertDiv = document.createElement('div');
                    alertDiv.id = 'clientValidationAlert';
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show mb-3';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.innerHTML = '<strong>Validasi Gagal:</strong> Mohon lengkapi seluruh field wajib yang ditandai merah.' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                    form.prepend(alertDiv);

                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }

                var activeAlert = document.getElementById('clientValidationAlert');
                if (activeAlert) activeAlert.remove();
                return true;
            });
        }
    });
</script>
