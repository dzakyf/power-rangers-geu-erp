<div class="personal-information">
    <div class="section-title">
        <i class="fas fa-user"></i> Personal Information
    </div>
    
    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Information:</strong> Please fill in your personal details accurately. This information will be used for verification purposes.
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="Name">
                    Full Name <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" id="Name" name="Name" class="form-control" 
                           placeholder="Enter your full name" required maxlength="255">
                </div>
                <small class="form-text text-muted">Your legal full name as per ID card</small>
                <div class="invalid-feedback">Please enter your full name</div>
            </div>
        </div>
        
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
                <div class="invalid-feedback">Please select your gender</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Date">
                    Birth Date <span class="required">*</span>
                </label>
                <input type="date" id="Birth_Date" name="Birth_Date" class="form-control" 
                       required max="{{ date('Y-m-d', strtotime('-17 years')) }}">
                <small class="form-text text-muted">Date of birth (minimum age 17 years)</small>
                <div class="invalid-feedback">Please enter your birth date</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Place">
                    Birth Place <span class="required">*</span>
                </label>
                <input type="text" id="Birth_Place" name="Birth_Place" class="form-control" 
                       placeholder="e.g. Jakarta" required maxlength="255">
                <small class="form-text text-muted">City/District of birth</small>
                <div class="invalid-feedback">Please enter your birth place</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="kewarganegaraan">
                    Citizenship <span class="required">*</span>
                </label>
                <input type="text" id="kewarganegaraan" name="kewarganegaraan" class="form-control" 
                       placeholder="Warga Negara Indonesia" required maxlength="100">
                <div class="invalid-feedback">Please enter your citizenship</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="KTP">
                    ID Card Number (KTP) <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                    </div>
                    <input type="text" id="KTP" name="KTP" class="form-control" 
                           placeholder="e.g. 32.12.XXXXXX.X.XXXXXX" required maxlength="20">
                </div>
                <small class="form-text text-muted">Your Indonesian ID card number</small>
                <div class="invalid-feedback">Please enter your KTP number</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="Religion">
                    Religion <span class="required">*</span>
                </label>
                <select id="Religion" name="Religion" class="form-control" required>
                    <option value="">-- Select Religion --</option>
                    <option value="ISLAM">Islam</option>
                    <option value="KRISTEN">Christian</option>
                    <option value="KATOLIK">Catholic</option>
                    <option value="HINDU">Hindu</option>
                    <option value="BUDDHA">Buddha</option>
                    <option value="KHIN">Confucius</option>
                </select>
                <div class="invalid-feedback">Please select your religion</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="Marital_Status">
                    Marital Status <span class="required">*</span>
                </label>
                <select id="Marital_Status" name="Marital_Status" class="form-control" required>
                    <option value="">-- Select Status --</option>
                    <option value="SINGLE">Single</option>
                    <option value="MARRIED">Married</option>
                    <option value="WIDOWER">Widower</option>
                    <option value="WIDOW">Widow</option>
                </select>
                <div class="invalid-feedback">Please select your marital status</div>
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="form-group">
                <label for="Address">
                    Current Address <span class="required">*</span>
                </label>
                <textarea id="Address" name="Address" class="form-control" rows="3" 
                          placeholder="Enter your complete address including RT/RW if applicable" 
                          required maxlength="500"></textarea>
                <small class="form-text text-muted">Your current residential address</small>
                <div class="invalid-feedback">Please enter your address</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="City">
                    City <span class="required">*</span>
                </label>
                <input type="text" id="City" name="City" class="form-control" 
                       placeholder="e.g. Jakarta Selatan" required maxlength="255">
                <div class="invalid-feedback">Please enter your city</div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="Province">
                    Province
                </label>
                <input type="text" id="Province" name="Province" class="form-control" 
                       placeholder="e.g. DKI Jakarta" readonly>
                <small class="form-text text-muted">Automatically filled based on city selection</small>
            </div>
        </div>
    </div>
    
    <div class="alert alert-success mt-4" role="alert">
        <i class="fas fa-check-circle"></i>
        <strong>Tips:</strong> Make sure all personal information matches your official ID documents.
    </div>
</div>
