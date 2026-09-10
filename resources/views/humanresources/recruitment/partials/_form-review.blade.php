<div class="review-information">
    <div class="section-title">
        <i class="fas fa-clipboard-check"></i> Review & Submit
    </div>

    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        <strong>Almost Done!</strong> Please review all information before submitting your application.
    </div>

    {{-- Personal Information Review --}}
    <div class="review-section mb-4">
        <h6 class="review-section-title">
            <i class="fas fa-user"></i> Personal Information
            <button type="button" class="btn btn-sm btn-link float-right" onclick="switchToTab('personal-tab')">
                <i class="fas fa-edit"></i> Edit
            </button>
        </h6>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Full Name:</dt>
                            <dd class="col-sm-7" id="review-Name">-</dd>
                            
                            <dt class="col-sm-5">Gender:</dt>
                            <dd class="col-sm-7" id="review-Gender">-</dd>
                            
                            <dt class="col-sm-5">Birth Place:</dt>
                            <dd class="col-sm-7" id="review-Birth_Place">-</dd>
                            
                            <dt class="col-sm-5">Birth Date:</dt>
                            <dd class="col-sm-7" id="review-Birth_Date">-</dd>
                            
                            <dt class="col-sm-5">ID Card (KTP):</dt>
                            <dd class="col-sm-7" id="review-KTP">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Citizenship:</dt>
                            <dd class="col-sm-7" id="review-kewarganegaraan">-</dd>
                            
                            <dt class="col-sm-5">Religion:</dt>
                            <dd class="col-sm-7" id="review-Religion">-</dd>
                            
                            <dt class="col-sm-5">Marital Status:</dt>
                            <dd class="col-sm-7" id="review-Marital_Status">-</dd>
                            
                            <dt class="col-sm-5">Address:</dt>
                            <dd class="col-sm-7" id="review-Address">-</dd>
                            
                            <dt class="col-sm-5">City:</dt>
                            <dd class="col-sm-7" id="review-City">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Information Review --}}
    <div class="review-section mb-4">
        <h6 class="review-section-title">
            <i class="fas fa-address-book"></i> Contact Information
            <button type="button" class="btn btn-sm btn-link float-right" onclick="switchToTab('contact-tab')">
                <i class="fas fa-edit"></i> Edit
            </button>
        </h6>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Phone:</dt>
                            <dd class="col-sm-7" id="review-Phone">-</dd>
                            
                            <dt class="col-sm-5">Mobile/WhatsApp:</dt>
                            <dd class="col-sm-7" id="review-Handphone">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Email:</dt>
                            <dd class="col-sm-7" id="review-Email">-</dd>
                            
                            <dt class="col-sm-5">Emergency Contact:</dt>
                            <dd class="col-sm-7" id="review-Emergency_Contact_Name">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Education Review --}}
    <div class="review-section mb-4">
        <h6 class="review-section-title">
            <i class="fas fa-graduation-cap"></i> Education Background
            <button type="button" class="btn btn-sm btn-link float-right" onclick="switchToTab('education-tab')">
                <i class="fas fa-edit"></i> Edit
            </button>
        </h6>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Education Level:</dt>
                            <dd class="col-sm-7" id="review-Last_Education">-</dd>
                            
                            <dt class="col-sm-5">Institution:</dt>
                            <dd class="col-sm-7" id="review-Last_Education_From">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Major:</dt>
                            <dd class="col-sm-7" id="review-Education_Major">-</dd>
                            
                            <dt class="col-sm-5">GPA:</dt>
                            <dd class="col-sm-7" id="review-GPA">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Review --}}
    <div class="review-section mb-4">
        <h6 class="review-section-title">
            <i class="fas fa-file-upload"></i> Uploaded Documents
            <button type="button" class="btn btn-sm btn-link float-right" onclick="switchToTab('documents-tab')">
                <i class="fas fa-edit"></i> Edit
            </button>
        </h6>
        <div class="card">
            <div class="card-body">
                <ul class="list-unstyled mb-0" id="review-documents">
                    <li class="mb-2">
                        <i class="fas fa-times-circle text-danger"></i>
                        <span class="text-muted">No documents uploaded yet</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Terms & Conditions --}}
    <div class="terms-section mb-4">
        <h6 class="review-section-title">
            <i class="fas fa-file-contract"></i> Terms & Conditions
        </h6>
        <div class="card">
            <div class="card-body">
                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" class="custom-control-input" id="agree_terms" name="agree_terms" required>
                    <label class="custom-control-label" for="agree_terms">
                        <strong>I hereby declare that:</strong>
                    </label>
                </div>
                <ul class="small">
                    <li>All information provided in this application is true, accurate, and complete to the best of my knowledge.</li>
                    <li>I understand that any false information or misrepresentation may result in disqualification or termination.</li>
                    <li>I authorize the company to verify the information provided and contact my references.</li>
                    <li>I agree to comply with company policies and procedures if selected for employment.</li>
                    <li>I understand that this application does not guarantee employment.</li>
                </ul>
                
                <div class="custom-control custom-checkbox mt-3">
                    <input type="checkbox" class="custom-control-input" id="agree_privacy" name="agree_privacy" required>
                    <label class="custom-control-label" for="agree_privacy">
                        I have read and agree to the <a href="#" data-toggle="modal" data-target="#privacyModal">Privacy Policy</a> and consent to the processing of my personal data for recruitment purposes.
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Declaration --}}
    <div class="declaration-section mb-4">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="declaration_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="declaration_name" name="declaration_name" 
                                   class="form-control" placeholder="Type your full name" required>
                            <small class="form-text text-muted">Type your name as electronic signature</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="declaration_date">Date <span class="required">*</span></label>
                            <input type="text" id="declaration_date" name="declaration_date" 
                                   class="form-control" readonly required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Important:</strong> After submission, you will receive a confirmation email. Please check your inbox and spam folder.
    </div>
</div>

{{-- Privacy Policy Modal --}}
<div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="privacyModalLabel">
                    <i class="fas fa-shield-alt"></i> Privacy Policy
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Data Collection and Usage</h6>
                <p>We collect personal information for recruitment and employment purposes only. Your data will be:</p>
                <ul>
                    <li>Used solely for recruitment evaluation and selection process</li>
                    <li>Stored securely in our HR database</li>
                    <li>Accessible only to authorized HR personnel</li>
                    <li>Retained for [specified period] as per company policy</li>
                    <li>Not shared with third parties without your consent</li>
                </ul>
                
                <h6 class="mt-3">Your Rights</h6>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Request corrections to your data</li>
                    <li>Withdraw your application at any time</li>
                    <li>Request deletion of your data (subject to legal requirements)</li>
                </ul>
                
                <h6 class="mt-3">Contact</h6>
                <p>For questions about data privacy, contact our HR department at: <strong>hr@company.com</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .review-section-title {
        font-weight: 600;
        color: #28a745;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 15px;
    }
    
    .review-section .card {
        border: 1px solid #e9ecef;
        box-shadow: none;
    }
    
    .review-section dl dt {
        font-weight: 600;
        color: #6c757d;
    }
    
    .review-section dl dd {
        color: #212529;
    }
</style>

<script>
$(document).ready(function() {
    // Set current date
    $('#declaration_date').val(new Date().toLocaleDateString('id-ID'));
    
    // Populate review section when tab is shown
    $('a[href="#review"]').on('shown.bs.tab', function() {
        populateReviewData();
    });
});

function populateReviewData() {
    // Personal Information
    $('#review-Name').text($('#Name').val() || '-');
    $('#review-Gender').text($('#Gender').val() || '-');
    $('#review-Birth_Place').text($('#Birth_Place').val() || '-');
    $('#review-Birth_Date').text($('#Birth_Date').val() || '-');
    $('#review-KTP').text($('#KTP').val() || '-');
    $('#review-kewarganegaraan').text($('#kewarganegaraan').val() || '-');
    $('#review-Religion').text($('#Religion').val() || '-');
    $('#review-Marital_Status').text($('#Marital_Status').val() || '-');
    $('#review-Address').text($('#Address').val() || '-');
    $('#review-City').text($('#City').val() || '-');
    
    // Contact Information
    $('#review-Phone').text($('#Phone').val() || '-');
    $('#review-Handphone').text($('#Handphone').val() || '-');
    $('#review-Email').text($('#Email').val() || '-');
    $('#review-Emergency_Contact_Name').text($('#Emergency_Contact_Name').val() || '-');
    
    // Education
    $('#review-Last_Education').text($('#Last_Education').val() || '-');
    $('#review-Last_Education_From').text($('#Last_Education_From').val() || '-');
    $('#review-Education_Major').text($('#Education_Major').val() || '-');
    $('#review-GPA').text($('#GPA').val() || '-');
    
    // Documents
    var docList = '';
    var hasDocuments = false;
    
    $('input[type="file"]').each(function() {
        var fileInput = $(this);
        if (fileInput[0].files.length > 0) {
            hasDocuments = true;
            var fileName = fileInput[0].files[0].name;
            var label = fileInput.closest('.form-group').find('label').first().text().replace('*', '').trim();
            docList += '<li class="mb-2"><i class="fas fa-check-circle text-success"></i> ' + label + ': <strong>' + fileName + '</strong></li>';
        }
    });
    
    if (hasDocuments) {
        $('#review-documents').html(docList);
    } else {
        $('#review-documents').html('<li class="mb-2"><i class="fas fa-times-circle text-danger"></i> <span class="text-muted">No documents uploaded yet</span></li>');
    }
}

function switchToTab(tabId) {
    $('#' + tabId).tab('show');
}
</script>
