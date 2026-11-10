<div class="documents-information">
    <div class="section-title">
        <i class="fas fa-file-upload"></i> Required Documents
    </div>

    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Important:</strong> Please upload clear and legible documents. Accepted formats: PDF, JPG, PNG (Max 5MB per file)
    </div>

    <div class="row">
        {{-- Photo Upload --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Foto">
                    <i class="fas fa-camera"></i> Candidate Photo <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Foto" name="Foto" 
                           accept="image/jpeg,image/jpg,image/png" required>
                    <label class="custom-file-label" for="Foto">Choose photo (4x6 cm)</label>
                </div>
                <small class="form-text text-muted">
                    Recent photo with formal attire (Max 2MB)
                </small>
                <div class="invalid-feedback">Please upload your photo</div>
                <div class="preview-container mt-2" id="preview-Foto"></div>
            </div>
        </div>

        {{-- CV Upload --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="CV_File">
                    <i class="fas fa-file-alt"></i> Curriculum Vitae (CV) <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="CV_File" name="CV_File" 
                           accept=".pdf,.doc,.docx" required>
                    <label class="custom-file-label" for="CV_File">Choose CV file</label>
                </div>
                <small class="form-text text-muted">
                    PDF or Word format (Max 5MB)
                </small>
                <div class="invalid-feedback">Please upload your CV</div>
                <div class="file-info mt-2" id="info-CV_File"></div>
            </div>
        </div>

        {{-- ID Card (KTP) Upload --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="KTP_File">
                    <i class="fas fa-id-card"></i> ID Card (KTP) <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="KTP_File" name="KTP_File" 
                           accept="image/*,.pdf" required>
                    <label class="custom-file-label" for="KTP_File">Choose KTP file</label>
                </div>
                <small class="form-text text-muted">
                    Clear scan or photo of ID card
                </small>
                <div class="invalid-feedback">Please upload your ID card</div>
                <div class="preview-container mt-2" id="preview-KTP_File"></div>
            </div>
        </div>

        {{-- Education Certificate Upload --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Education_Certificate_File">
                    <i class="fas fa-graduation-cap"></i> Education Certificate <span class="required">*</span>
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Education_Certificate_File" 
                           name="Education_Certificate_File" accept="image/*,.pdf" required>
                    <label class="custom-file-label" for="Education_Certificate_File">Choose certificate</label>
                </div>
                <small class="form-text text-muted">
                    Diploma/Certificate from last education
                </small>
                <div class="invalid-feedback">Please upload your education certificate</div>
                <div class="file-info mt-2" id="info-Education_Certificate_File"></div>
            </div>
        </div>

        {{-- Transcript Upload (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Transcript_File">
                    <i class="fas fa-file-alt"></i> Academic Transcript
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Transcript_File" 
                           name="Transcript_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Transcript_File">Choose transcript (optional)</label>
                </div>
                <small class="form-text text-muted">
                    Academic transcript if available
                </small>
                <div class="file-info mt-2" id="info-Transcript_File"></div>
            </div>
        </div>

        {{-- NPWP Upload (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="NPWP_File">
                    <i class="fas fa-file-invoice"></i> Tax ID (NPWP)
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="NPWP_File" 
                           name="NPWP_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="NPWP_File">Choose NPWP (optional)</label>
                </div>
                <small class="form-text text-muted">
                    Tax ID card if available
                </small>
                <div class="preview-container mt-2" id="preview-NPWP_File"></div>
            </div>
        </div>

        {{-- Additional Certificates (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Certificate_File">
                    <i class="fas fa-certificate"></i> Professional Certificates
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Certificate_File" 
                           name="Certificate_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Certificate_File">Choose certificates (optional)</label>
                </div>
                <small class="form-text text-muted">
                    Professional certifications if any
                </small>
                <div class="file-info mt-2" id="info-Certificate_File"></div>
            </div>
        </div>

        {{-- Reference Letter (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Reference_Letter_File">
                    <i class="fas fa-envelope-open-text"></i> Reference Letter
                </label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="Reference_Letter_File" 
                           name="Reference_Letter_File" accept="image/*,.pdf">
                    <label class="custom-file-label" for="Reference_Letter_File">Choose reference letter (optional)</label>
                </div>
                <small class="form-text text-muted">
                    Letter of recommendation from previous employer
                </small>
                <div class="file-info mt-2" id="info-Reference_Letter_File"></div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Document Checklist --}}
    <div class="document-checklist">
        <h6 class="font-weight-bold mb-3">
            <i class="fas fa-clipboard-check"></i> Document Checklist
        </h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-Foto"></i>
                        <span class="ml-2">Candidate Photo <span class="text-danger">*</span></span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-CV_File"></i>
                        <span class="ml-2">Curriculum Vitae <span class="text-danger">*</span></span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-KTP_File"></i>
                        <span class="ml-2">ID Card (KTP) <span class="text-danger">*</span></span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-Education_Certificate_File"></i>
                        <span class="ml-2">Education Certificate <span class="text-danger">*</span></span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-Transcript_File"></i>
                        <span class="ml-2">Academic Transcript</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-NPWP_File"></i>
                        <span class="ml-2">Tax ID (NPWP)</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-Certificate_File"></i>
                        <span class="ml-2">Professional Certificates</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted" id="check-Reference_Letter_File"></i>
                        <span class="ml-2">Reference Letter</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-3" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Note:</strong> All uploaded documents will be verified during the recruitment process. Please ensure all documents are authentic and valid.
    </div>
</div>

<style>
    .preview-container img {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 5px;
        background: white;
    }
    
    .file-info {
        font-size: 0.875rem;
        color: #28a745;
    }
    
    .document-checklist .fa-check-circle.text-success {
        color: #28a745 !important;
    }
</style>

<script>
$(document).ready(function() {
    // Handle file input changes
    $('input[type="file"]').on('change', function() {
        var input = $(this);
        var fileName = input.val().split('\\').pop();
        var fileId = input.attr('id');
        
        // Update label
        input.next('.custom-file-label').html(fileName);
        
        // Update checklist
        $('#check-' + fileId).removeClass('text-muted').addClass('text-success');
        
        // Handle image preview
        if (input.attr('accept') && input.attr('accept').includes('image')) {
            var file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-' + fileId).html(
                        '<img src="' + e.target.result + '" alt="Preview" class="img-thumbnail">'
                    );
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Show file info for non-image files
        if (fileName && !input.attr('accept').includes('image')) {
            $('#info-' + fileId).html(
                '<i class="fas fa-check-circle"></i> ' + fileName + ' selected'
            );
        }
        
        // Validate file size (5MB)
        if (this.files[0].size > 5242880) {
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            input.val('');
            input.next('.custom-file-label').html('Choose file');
            $('#check-' + fileId).removeClass('text-success').addClass('text-muted');
            $('#preview-' + fileId).empty();
            $('#info-' + fileId).empty();
        }
    });
});
</script>
