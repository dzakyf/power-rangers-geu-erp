<div class="contact-information">
    <div class="section-title">
        <i class="fas fa-address-book"></i> Contact Information
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Information:</strong> Provide valid contact details for communication purposes
    </div>

    <div class="row">
        {{-- Phone Number --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Phone">
                    Phone Number <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    </div>
                    <input type="tel" id="Phone" name="Phone" class="form-control" 
                           placeholder="e.g. 02112345678" required maxlength="15">
                </div>
                <small class="form-text text-muted">Home or office phone number</small>
                <div class="invalid-feedback">Please enter valid phone number</div>
            </div>
        </div>

        {{-- Mobile/WhatsApp Number --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Handphone">
                    Mobile / WhatsApp Number <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                    </div>
                    <input type="tel" id="Handphone" name="Handphone" class="form-control" 
                           placeholder="e.g. 081234567890" required maxlength="15" 
                           pattern="[0-9]{10,15}">
                </div>
                <small class="form-text text-muted">Active WhatsApp number</small>
                <div class="invalid-feedback">Please enter valid mobile number (10-15 digits)</div>
            </div>
        </div>

        {{-- Email Address --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Email">
                    Email Address <span class="required">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" id="Email" name="Email" class="form-control" 
                           placeholder="e.g. candidate@email.com" required maxlength="100">
                </div>
                <small class="form-text text-muted">We will send important updates to this email</small>
                <div class="invalid-feedback">Please enter valid email address</div>
            </div>
        </div>

        {{-- Personal Email (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Email_Individual">
                    Personal Email (Optional)
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope-open"></i></span>
                    </div>
                    <input type="email" id="Email_Individual" name="Email_Individual" class="form-control" 
                           placeholder="Alternative email address" maxlength="100">
                </div>
                <small class="form-text text-muted">Alternative email if different from primary</small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Emergency Contact Section --}}
    <div class="section-title">
        <i class="fas fa-user-shield"></i> Emergency Contact
    </div>

    <div class="row">
        {{-- Emergency Contact Name --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Contact_Name">
                    Emergency Contact Name
                </label>
                <input type="text" id="Emergency_Contact_Name" name="Emergency_Contact_Name" 
                       class="form-control" placeholder="e.g. John Doe" maxlength="100">
                <small class="form-text text-muted">Person to contact in case of emergency</small>
            </div>
        </div>

        {{-- Emergency Contact Relation --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Relation">
                    Relationship
                </label>
                <select id="Emergency_Relation" name="Emergency_Relation" class="form-control">
                    <option value="">-- Select Relationship --</option>
                    <option value="PARENT">Parent</option>
                    <option value="SPOUSE">Spouse</option>
                    <option value="SIBLING">Sibling</option>
                    <option value="CHILD">Child</option>
                    <option value="FRIEND">Friend</option>
                    <option value="OTHER">Other</option>
                </select>
            </div>
        </div>

        {{-- Emergency Contact Number --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Contact_No">
                    Emergency Contact Number
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                    </div>
                    <input type="tel" id="Emergency_Contact_No" name="Emergency_Contact_No" 
                           class="form-control" placeholder="e.g. 081234567890" maxlength="15">
                </div>
            </div>
        </div>

        {{-- Emergency Contact Address --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Emergency_Address">
                    Emergency Contact Address
                </label>
                <textarea id="Emergency_Address" name="Emergency_Address" 
                          class="form-control" rows="2" placeholder="Complete address" 
                          maxlength="200"></textarea>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-3" role="alert">
        <i class="fas fa-shield-alt"></i>
        <strong>Privacy Notice:</strong> All contact information will be kept confidential and used only for recruitment and employment purposes.
    </div>
</div>
