@php
    $isEdit = $isEdit ?? (isset($company) && $company);

    $emails = [];
    if ($isEdit && !empty($company->Emails)) {
        $decoded = json_decode($company->Emails, true);
        if (is_array($decoded)) {
            $emails = array_values(array_filter($decoded));
        }
    }

    if (count($emails) === 0 && $isEdit && !empty($company->Email)) {
        $emails = [$company->Email];
    }

    if (count($emails) === 0) {
        $emails = [''];
    }
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">{{ $isEdit ? 'DETAIL COMPANY' : 'NEW COMPANY' }}</div>
    <div class="card">
        <div class="card-body pt-2">
            <p class="text-muted mb-3">{{ $isEdit ? 'Perbarui data company di bawah ini.' : 'Isi form berikut untuk membuat company baru.' }}</p>

            <div id="ContentPages">
                @if($isEdit)
                    <input type="hidden" id="ID" value="{{ $company->ID }}" />
                @endif

                <h6 class="font-weight-bold border-bottom pb-1 mt-2" style="color:#1a56a0;">Company</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Company_Name">Company Name</label>
                            <input type="text" id="Company_Name" class="form-control shadow-sm" value="{{ $company->Company_Name ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Contact_Person">Contact Person</label>
                            <input type="text" id="Contact_Person" class="form-control shadow-sm" value="{{ $company->Contact_Person ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="NPWP">NPWP</label>
                            <input type="text" id="NPWP" class="form-control shadow-sm" value="{{ $company->NPWP ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="City">City</label>
                            <input type="text" id="City" class="form-control shadow-sm" value="{{ $company->City ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Street">Street</label>
                            <input type="text" id="Street" class="form-control shadow-sm" value="{{ $company->Street ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="Address">Address</label>
                            <textarea id="Address" rows="2" class="form-control shadow-sm">{{ $company->Address ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Contact</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Phone">Phone</label>
                            <input type="text" id="Phone" class="form-control shadow-sm" value="{{ $company->Phone ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Fax">Fax</label>
                            <input type="text" id="Fax" class="form-control shadow-sm" value="{{ $company->Fax ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="HP">HP</label>
                            <input type="text" id="HP" class="form-control shadow-sm" value="{{ $company->HP ?? '' }}" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email (maksimal 10)</label>
                    <div id="CompanyEmailRows">
                        @foreach($emails as $email)
                            <div class="input-group mb-2 company-email-row">
                                <input type="email" name="Emails[]" value="{{ $email }}" class="form-control shadow-sm company-email-input" required />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger remove-company-email">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="AddCompanyEmailBtn">+ Tambah Email</button>
                    <small class="text-muted d-block">Minimal satu email, maksimal 10 alamat.</small>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Sales &amp; Payment</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Sales_Name">Sales Name</label>
                            <input type="text" id="Sales_Name" class="form-control shadow-sm" value="{{ $company->Sales_Name ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Sales_Phone">Sales No</label>
                            <input type="text" id="Sales_Phone" class="form-control shadow-sm" value="{{ $company->Sales_Phone ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Payment">Payment Method</label>
                            <input type="text" id="Payment" class="form-control shadow-sm" value="{{ $company->Payment ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="Note">Note</label>
                            <textarea id="Note" rows="2" class="form-control shadow-sm">{{ $company->Note ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Bank</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Rupiah">Bank Name (Rp)</label>
                            <input type="text" id="Bank_Rupiah" class="form-control shadow-sm" value="{{ $company->Bank_Rupiah ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Rupiah_Account">Bank Account (Rp)</label>
                            <input type="text" id="Bank_Rupiah_Account" class="form-control shadow-sm" value="{{ $company->Bank_Rupiah_Account ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Rupiah_Name">Bank Account Name (Rp)</label>
                            <input type="text" id="Bank_Rupiah_Name" class="form-control shadow-sm" value="{{ $company->Bank_Rupiah_Name ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Foreign">Bank Foreign</label>
                            <input type="text" id="Bank_Foreign" class="form-control shadow-sm" value="{{ $company->Bank_Foreign ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Foreign_Account">Bank Foreign Account</label>
                            <input type="text" id="Bank_Foreign_Account" class="form-control shadow-sm" value="{{ $company->Bank_Foreign_Account ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Foreign_Name">Bank Foreign Name</label>
                            <input type="text" id="Bank_Foreign_Name" class="form-control shadow-sm" value="{{ $company->Bank_Foreign_Name ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Bank_Foreign_Currency">Bank Foreign Currency</label>
                            <input type="text" id="Bank_Foreign_Currency" class="form-control shadow-sm" value="{{ $company->Bank_Foreign_Currency ?? '' }}" />
                        </div>
                    </div>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Branding</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="LetterHead">Upload Header</label>
                            <input type="file" id="LetterHead" class="form-control-file" accept=".jpg,.jpeg,.png" />
                            @if($isEdit && !empty($company->LetterHead))
                                <a href="{{ asset($company->LetterHead) }}" target="_blank" class="d-inline-block mt-1">Lihat header saat ini</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Footer">Footer</label>
                            <input type="file" id="Footer" class="form-control-file" accept=".jpg,.jpeg,.png" />
                            @if($isEdit && !empty($company->Footer))
                                <a href="{{ asset($company->Footer) }}" target="_blank" class="d-inline-block mt-1">Lihat footer saat ini</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Logo">Logo</label>
                            <input type="file" id="Logo" class="form-control-file" accept=".jpg,.jpeg,.png" />
                            @if($isEdit && !empty($company->Logo))
                                <a href="{{ asset($company->Logo) }}" target="_blank" class="d-inline-block mt-1">Lihat logo saat ini</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Logo_Inverse">Logo Inverse</label>
                            <input type="file" id="Logo_Inverse" class="form-control-file" accept=".jpg,.jpeg,.png" />
                            @if($isEdit && !empty($company->Logo_Inverse))
                                <a href="{{ asset($company->Logo_Inverse) }}" target="_blank" class="d-inline-block mt-1">Lihat logo inverse saat ini</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Stamps">Stamps</label>
                            <input type="file" id="Stamps" class="form-control-file" accept=".jpg,.jpeg,.png" />
                            @if($isEdit && !empty($company->Stamps))
                                <a href="{{ asset($company->Stamps) }}" target="_blank" class="d-inline-block mt-1">Lihat stamps saat ini</a>
                            @endif
                        </div>
                    </div>
                </div>

                <h6 class="font-weight-bold border-bottom pb-1 mt-3" style="color:#1a56a0;">Type</h6>
                <div class="form-check mb-1">
                    <input type="checkbox" class="form-check-input" id="Is_Consignee" {{ $isEdit && $company->Is_Consignee ? 'checked' : '' }} />
                    <label class="form-check-label" for="Is_Consignee">Consignee / Buyer</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="Is_Supplier" {{ $isEdit && $company->Is_Supplier ? 'checked' : '' }} />
                    <label class="form-check-label" for="Is_Supplier">Vendor / Supplier</label>
                </div>

                <div class="mt-2">
                    <input type="button" class="btn btn-success mr-2" id="UpdateData" name="UpdateData" onclick="Submitkan()" value="Submit Data" />
                    <button type="button" class="btn btn-secondary" onclick="$('#MasterCompanyDet').html('');">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
