<div class="personal-information">
    <div class="section-title">
        <i class="fas fa-id-card"></i> Personal Information
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Information:</strong> Please fill in all required fields marked with <span class="text-danger">*</span>
    </div>

    <div class="row">
        {{-- Position/Requisition --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Requisition_ID">
                    Position Applied <span class="required">*</span>
                </label>
                <select id="Requisition_ID" name="Requisition_ID" class="form-control select2" required>
                    <option value="">-- Select Position --</option>
                    @php
                        $location = optional(Auth::user())->Location ?? ($publicLocation ?? 'GEU-HO');
                        $requisitions = $publicRequisitions ?? DB::table("requisition")
                            ->where("Status", "APPROVED")
                            ->where("Location", $location)
                            ->orderBy("ID", "DESC")
                            ->get();

                        foreach ($requisitions as $req) {
                            echo "<option value='{$req->Task_ID}'>{$req->Task_ID} - {$req->Job_Position} ({$req->Department})</option>";
                        }
                    @endphp
                </select>
                <small class="form-text text-muted">
                    <i class="fas fa-question-circle"></i> Select the position you are applying for
                </small>
            </div>
        </div>

        {{-- Recruitment Type --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Recruitment_Type">
                    Recruitment Type <span class="required">*</span>
                </label>
                <select id="Recruitment_Type" name="Recruitment_Type" class="form-control" required>
                    <option value="INTERNAL">Internal</option>
                    <option value="EXTERNAL">External</option>
                </select>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        {{-- Full Name --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Name">
                    Full Name <span class="required">*</span>
                </label>
                <input type="text" id="Name" name="Name" class="form-control" 
                       placeholder="Full name as per ID card" required maxlength="150">
                <div class="invalid-feedback">Please enter full name (min 3 characters)</div>
            </div>
        </div>

        {{-- Gender --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Gender">
                    Gender <span class="required">*</span>
                </label>
                <select id="Gender" name="Gender" class="form-control" required>
                    <option value="">-- Select Gender --</option>
                    <option value="MALE">Male</option>
                    <option value="FEMALE">Female</option>
                </select>
                <div class="invalid-feedback">Please select gender</div>
            </div>
        </div>

        {{-- Birth Place --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Place">
                    Birth Place <span class="required">*</span>
                </label>
                <input type="text" id="Birth_Place" name="Birth_Place" class="form-control" 
                       placeholder="e.g. Jakarta" required maxlength="150">
                <div class="invalid-feedback">Please enter birth place</div>
            </div>
        </div>

        {{-- Birth Date --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Date">
                    Birth Date <span class="required">*</span>
                </label>
                <input type="text" id="Birth_Date" name="Birth_Date" class="form-control datepicker" 
                       placeholder="DD-MM-YYYY" required readonly>
                <div class="invalid-feedback">Please select birth date</div>
            </div>
        </div>

        {{-- ID Card Number (KTP) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="KTP">
                    ID Card Number (KTP) <span class="required">*</span>
                </label>
                <input type="text" id="KTP" name="KTP" class="form-control" 
                       placeholder="16 digit ID number" required maxlength="20" 
                       pattern="[0-9]{16,20}">
                <div class="invalid-feedback">Please enter valid 16-digit ID number</div>
            </div>
        </div>

        {{-- Citizenship --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="kewarganegaraan">
                    Citizenship <span class="required">*</span>
                </label>
                <select id="kewarganegaraan" name="kewarganegaraan" class="form-control" required>
                    <option value="">-- Select Citizenship --</option>
                    <option value="WNI" selected>Indonesian (WNI)</option>
                    <option value="WNA">Foreign National (WNA)</option>
                </select>
                <div class="invalid-feedback">Please select citizenship</div>
            </div>
        </div>

        {{-- Religion --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Religion">
                    Religion <span class="required">*</span>
                </label>
                <select id="Religion" name="Religion" class="form-control" required>
                    <option value="">-- Select Religion --</option>
                    <option value="ISLAM">Islam</option>
                    <option value="KRISTEN PROTESTAN">Christian (Protestant)</option>
                    <option value="KATOLIK">Catholic</option>
                    <option value="HINDU">Hindu</option>
                    <option value="BUDHA">Buddhist</option>
                    <option value="KONGHUCU">Confucian</option>
                </select>
                <div class="invalid-feedback">Please select religion</div>
            </div>
        </div>

        {{-- Marital Status --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Marital_Status">
                    Marital Status <span class="required">*</span>
                </label>
                <select id="Marital_Status" name="Marital_Status" class="form-control" required>
                    <option value="">-- Select Marital Status --</option>
                    <option value="SINGLE">Single</option>
                    <option value="MARRIED">Married</option>
                    <option value="DIVORCED">Divorced</option>
                    <option value="WIDOWED">Widowed</option>
                </select>
                <div class="invalid-feedback">Please select marital status</div>
            </div>
        </div>

        {{-- Blood Type (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Blood">Blood Type</label>
                <select id="Blood" name="Blood" class="form-control">
                    <option value="">-- Select Blood Type --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                </select>
            </div>
        </div>

        {{-- Rhesus (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Blood_Rhesus">Rhesus</label>
                <select id="Blood_Rhesus" name="Blood_Rhesus" class="form-control">
                    <option value="">-- Select Rhesus --</option>
                    <option value="+">Positive (+)</option>
                    <option value="-">Negative (-)</option>
                </select>
            </div>
        </div>

        {{-- Full Address --}}
        <div class="col-md-12">
            <div class="form-group">
                <label for="Address">
                    Full Address <span class="required">*</span>
                </label>
                <textarea id="Address" name="Address" class="form-control" rows="3" 
                          placeholder="Complete address with street name, RT/RW, village" 
                          required maxlength="250"></textarea>
                <div class="invalid-feedback">Please enter complete address</div>
            </div>
        </div>

        {{-- City --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="City">
                    City <span class="required">*</span>
                </label>
                <input type="text" id="City" name="City" class="form-control" 
                       placeholder="e.g. Jakarta Selatan" required maxlength="50">
                <div class="invalid-feedback">Please enter city</div>
            </div>
        </div>

        {{-- Province (Optional but recommended) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Province">Province</label>
                <input type="text" id="Province" name="Province" class="form-control" 
                       placeholder="e.g. DKI Jakarta" maxlength="50">
            </div>
        </div>
    </div>
</div>
