<div class="education-information">
    <div class="section-title">
        <i class="fas fa-graduation-cap"></i> Education Background
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Information:</strong> Provide your highest educational background and relevant qualifications
    </div>

    <div class="row">
        {{-- Last Education Level --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Last_Education">
                    Last Education Level <span class="required">*</span>
                </label>
                <select id="Last_Education" name="Last_Education" class="form-control" required>
                    <option value="">-- Select Education Level --</option>
                    <option value="SD">Elementary School (SD)</option>
                    <option value="SMP">Junior High School (SMP)</option>
                    <option value="SMA/SMK">Senior High School (SMA/SMK)</option>
                    <option value="D1">Diploma 1 (D1)</option>
                    <option value="D2">Diploma 2 (D2)</option>
                    <option value="D3">Diploma 3 (D3)</option>
                    <option value="D4">Diploma 4 (D4)</option>
                    <option value="S1">Bachelor's Degree (S1)</option>
                    <option value="S2">Master's Degree (S2)</option>
                    <option value="S3">Doctoral Degree (S3)</option>
                </select>
                <div class="invalid-feedback">Please select education level</div>
            </div>
        </div>

        {{-- Institution Name --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Last_Education_From">
                    Institution/University Name <span class="required">*</span>
                </label>
                <input type="text" id="Last_Education_From" name="Last_Education_From" 
                       class="form-control" placeholder="e.g. University of Indonesia" 
                       required maxlength="100">
                <div class="invalid-feedback">Please enter institution name</div>
            </div>
        </div>

        {{-- Major/Field of Study --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Education_Major">
                    Major/Field of Study
                </label>
                <input type="text" id="Education_Major" name="Education_Major" 
                       class="form-control" placeholder="e.g. Computer Science" 
                       maxlength="100">
                <small class="form-text text-muted">Leave empty if not applicable</small>
            </div>
        </div>

        {{-- Graduation Year --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Graduation_Year">
                    Graduation Year
                </label>
                <input type="number" id="Graduation_Year" name="Graduation_Year" 
                       class="form-control" placeholder="e.g. 2020" 
                       min="1970" max="2026">
                <small class="form-text text-muted">Year of graduation</small>
            </div>
        </div>

        {{-- GPA/Final Score --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="GPA">
                    GPA / Final Score
                </label>
                <input type="text" id="GPA" name="GPA" class="form-control" 
                       placeholder="e.g. 3.50 or 85.5" maxlength="10">
                <small class="form-text text-muted">GPA (scale 4.00) or percentage</small>
            </div>
        </div>

        {{-- Certificate Number (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Certificate_Number">
                    Certificate/Diploma Number
                </label>
                <input type="text" id="Certificate_Number" name="Certificate_Number" 
                       class="form-control" placeholder="Certificate number" maxlength="50">
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Professional Certifications Section --}}
    <div class="section-title">
        <i class="fas fa-certificate"></i> Professional Certifications (Optional)
    </div>

    <div id="certifications-container">
        <div class="certification-item mb-3 p-3 border rounded">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Certification Name</label>
                        <input type="text" name="certification_name[]" class="form-control" 
                               placeholder="e.g. PMP, Six Sigma" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Issuing Organization</label>
                        <input type="text" name="certification_org[]" class="form-control" 
                               placeholder="e.g. PMI" maxlength="100">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Year Obtained</label>
                        <input type="number" name="certification_year[]" class="form-control" 
                               placeholder="2020" min="1990" max="2026">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm btn-block remove-certification" 
                                style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-outline-success btn-sm" id="addCertification">
        <i class="fas fa-plus"></i> Add More Certification
    </button>

    <hr class="my-4">

    {{-- Language Skills Section --}}
    <div class="section-title">
        <i class="fas fa-language"></i> Language Skills
    </div>

    <div class="row">
        {{-- English Proficiency --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="English_Level">English Proficiency</label>
                <select id="English_Level" name="English_Level" class="form-control">
                    <option value="">-- Select Level --</option>
                    <option value="BEGINNER">Beginner</option>
                    <option value="INTERMEDIATE">Intermediate</option>
                    <option value="ADVANCED">Advanced</option>
                    <option value="FLUENT">Fluent</option>
                    <option value="NATIVE">Native</option>
                </select>
            </div>
        </div>

        {{-- Other Language --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Other_Language">Other Languages</label>
                <input type="text" id="Other_Language" name="Other_Language" 
                       class="form-control" placeholder="e.g. Mandarin, Japanese" 
                       maxlength="200">
                <small class="form-text text-muted">Separate multiple languages with comma</small>
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- Skills Section --}}
    <div class="section-title">
        <i class="fas fa-tools"></i> Skills & Competencies
    </div>

    <div class="row">
        {{-- Technical Skills --}}
        <div class="col-md-12">
            <div class="form-group">
                <label for="Technical_Skills">Technical Skills</label>
                <textarea id="Technical_Skills" name="Technical_Skills" class="form-control" 
                          rows="3" placeholder="e.g. Microsoft Office, Adobe Photoshop, Python Programming, etc." 
                          maxlength="500"></textarea>
                <small class="form-text text-muted">List your technical skills and software proficiency</small>
            </div>
        </div>

        {{-- Soft Skills --}}
        <div class="col-md-12">
            <div class="form-group">
                <label for="Soft_Skills">Soft Skills</label>
                <textarea id="Soft_Skills" name="Soft_Skills" class="form-control" 
                          rows="3" placeholder="e.g. Leadership, Communication, Problem Solving, Teamwork, etc." 
                          maxlength="500"></textarea>
                <small class="form-text text-muted">List your soft skills and personal competencies</small>
            </div>
        </div>
    </div>

    <div class="alert alert-success mt-3" role="alert">
        <i class="fas fa-lightbulb"></i>
        <strong>Tip:</strong> Highlighting relevant skills and certifications can improve your application evaluation.
    </div>
</div>

<script>
    // Add more certification fields
    $(document).ready(function() {
        $('#addCertification').click(function() {
            var certItem = $('.certification-item:first').clone();
            certItem.find('input').val('');
            certItem.find('.remove-certification').show();
            $('#certifications-container').append(certItem);
        });

        $(document).on('click', '.remove-certification', function() {
            $(this).closest('.certification-item').remove();
        });
    });
</script>
