@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3" id="ContentPages">
    <!-- Advanced Questionnaire -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-question-circle me-2"></i>Advanced Candidate Questionnaire</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Self">Describe Yourself *</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Self" placeholder="Ceritakan tentang diri Anda..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Left_Job">Why did you leave your last job? *</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Left_Job" placeholder="Alasan meninggalkan pekerjaan sebelumnya..."></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Previous_Boss">What do you truly think about your previous boss?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Previous_Boss"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Know">What do you know about our company?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Know"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Improve">What did you do to improve your knowledge lately?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Improve"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Looking">What are you looking for in this position of work?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Looking"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Interest">Why are you interested in working at this company?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Interest"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Long">How long would you expect to work for us if hired?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Long"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Target">What is your career goal in 5 years?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Target"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Free_Time">How do you spend your leisure time?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Free_Time"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Strong">What are your strengths?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Strong"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Describe_Weak">Can you describe your greatest weakness?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Describe_Weak"></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Computer">What computer skills do you have?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Computer"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Questions_Us">Do you have any questions for us?</label>
                    <textarea class="form-control form-control-sm" rows="3" id="Questions_Us"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferences & Work Conditions -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-sliders me-2"></i>Preferences & Work Conditions</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Salary_Expectation">Salary Expectations</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp.</span>
                        <input type="text" class="form-control" id="Salary_Expectation" placeholder="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Facility_Expectation">Other Expected Facilities</label>
                    <input type="text" class="form-control form-control-sm" id="Facility_Expectation" placeholder="Contoh: BPJS, Mess, dll">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Environment">Work Environment Preference</label>
                    <select id="Environment" class="form-select form-select-sm">
                        <option>OFFICE</option>
                        <option>FIELD</option>
                        <option>FACTORY</option>
                        <option>WORKSHOP</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Overtime">Agree to Overtime When Needed?</label>
                    <select id="Overtime" class="form-select form-select-sm">
                        <option>YES</option>
                        <option>NO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Holiday">Work on Holiday If Needed?</label>
                    <select id="Holiday" class="form-select form-select-sm">
                        <option>YES</option>
                        <option>NO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Shift_Work">Comfortable with Shift Work?</label>
                    <select id="Shift_Work" class="form-select form-select-sm">
                        <option>YES</option>
                        <option>NO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Out_Town">Willing to Travel for Work?</label>
                    <select id="Out_Town" class="form-select form-select-sm">
                        <option>YES</option>
                        <option>NO</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview Decision -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-gavel me-2"></i>Interview Decision</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Interview_Date">Interview Date</label>
                    <input type="date" id="Interview_Date" class="form-control form-control-sm" value="<?php echo date('Y-m-d');?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Status">Interview Status Decision</label>
                    <select id="Status" class="form-select form-select-sm">
                        <option value="">-- Select Status --</option>
                        <option>ACCEPT</option>
                        <option>REJECT</option>
                        <option>PENDING</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Interview_Result">Interview Result Score/Desc</label>
                    <input type="text" id="Interview_Result" class="form-control form-control-sm">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-muted" for="Interview_Note">Interview Assessment Note</label>
                    <textarea id="Interview_Note" class="form-control form-control-sm" rows="3" placeholder="Catatan penilaian wawancara..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
