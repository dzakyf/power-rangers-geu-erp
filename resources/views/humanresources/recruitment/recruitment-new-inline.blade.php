<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$positions = DB::table('master_position')->orderBy('Position', 'asc')->get();
?>

<div class="container-fluid py-3" id="ContentPages">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fa fa-user-plus me-2"></i>New Recruitment Candidate
            </h5>
        </div>
        <div class="card-body p-4">
            <form id="recruitmentInlineForm">
                <!-- Section 1: Position & Personal Info -->
                <div class="border-bottom pb-3 mb-4">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fa fa-briefcase me-2"></i>Job Position & Identification
                    </h6>
                    <div class="row g-3">
                        <!-- Position from master_position -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted" for="Position">
                                Position <span class="text-danger">*</span>
                            </label>
                            <select id="Position" name="Position" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Position --</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->Position }}">
                                        {{ $pos->Position }} @if(!empty($pos->Department)) ({{ $pos->Department }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="fa fa-info-circle me-1"></i>Jika terdapat Requisition aktif dengan posisi ini, Requisition ID akan terhubung otomatis.
                            </small>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted" for="Name">
                                Name of Candidate <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="Name" name="Name" class="form-control form-control-sm" placeholder="Nama lengkap kandidat" required>
                        </div>

                        <!-- Gender -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Gender">
                                Gender <span class="text-danger">*</span>
                            </label>
                            <select id="Gender" name="Gender" class="form-select form-select-sm" required>
                                <option value="MALE">MALE</option>
                                <option value="FEMALE">FEMALE</option>
                            </select>
                        </div>

                        <!-- Birth Date -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Birth_Date">
                                Birth Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="Birth_Date" name="Birth_Date" class="form-control form-control-sm" value="1995-01-01" required>
                        </div>

                        <!-- Birth Place -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Birth_Place">Birth Place</label>
                            <input type="text" id="Birth_Place" name="Birth_Place" class="form-control form-control-sm" placeholder="Tempat lahir">
                        </div>

                        <!-- KTP -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="KTP">Citizen ID Card / KTP</label>
                            <input type="text" id="KTP" name="KTP" class="form-control form-control-sm" placeholder="16 digit NIK">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact & Address Info -->
                <div class="border-bottom pb-3 mb-4">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fa fa-address-book me-2"></i>Contact & Address
                    </h6>
                    <div class="row g-3">
                        <!-- Address -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted" for="Address">Address</label>
                            <textarea rows="2" id="Address" name="Address" class="form-control form-control-sm" placeholder="Alamat sesuai KTP"></textarea>
                        </div>

                        <!-- City -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted" for="City">City</label>
                            <input type="text" id="City" name="City" class="form-control form-control-sm" placeholder="Kota/Kabupaten">
                        </div>

                        <!-- Phone -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted" for="Phone">Phone</label>
                            <input type="text" id="Phone" name="Phone" class="form-control form-control-sm" placeholder="Nomor telepon rumah/kantor">
                        </div>

                        <!-- Handphone / WhatsApp -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted" for="Handphone">WhatsApp No</label>
                            <input type="text" id="Handphone" name="Handphone" class="form-control form-control-sm" placeholder="08xxxxxxxxxx">
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted" for="Email">Email Address</label>
                            <input type="email" id="Email" name="Email" class="form-control form-control-sm" placeholder="email@domain.com">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Background & Other Details -->
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fa fa-graduation-cap me-2"></i>Education & Background
                    </h6>
                    <div class="row g-3">
                        <!-- Marital Status -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Marital_Status">Marital Status</label>
                            <select id="Marital_Status" name="Marital_Status" class="form-select form-select-sm">
                                <option value="SINGLE">SINGLE</option>
                                <option value="MARRIED">MARRIED</option>
                                <option value="WIDOWER">WIDOWER</option>
                                <option value="WIDOW">WIDOW</option>
                            </select>
                        </div>

                        <!-- Last Education -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Last_Education">Last Education</label>
                            <select id="Last_Education" name="Last_Education" class="form-select form-select-sm">
                                <option>SMA/SMK</option>
                                <option>D1</option>
                                <option>D2</option>
                                <option>D3</option>
                                <option>D4</option>
                                <option selected>S1</option>
                                <option>S2</option>
                                <option>S3</option>
                            </select>
                        </div>

                        <!-- Education Institution -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted" for="Last_Education_From">Education Institution</label>
                            <input type="text" id="Last_Education_From" name="Last_Education_From" class="form-control form-control-sm" placeholder="Nama Universitas / Sekolah">
                        </div>

                        <!-- Religion -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="Religion">Religion</label>
                            <select id="Religion" name="Religion" class="form-select form-select-sm">
                                <option>ISLAM</option>
                                <option>PROTESTAN</option>
                                <option>KATHOLIK</option>
                                <option>BUDHA</option>
                                <option>HINDU</option>
                                <option>KONGHUCU</option>
                            </select>
                        </div>

                        <!-- Citizenship -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted" for="kewarganegaraan">Citizenship</label>
                            <input type="text" id="kewarganegaraan" name="kewarganegaraan" class="form-control form-control-sm" value="WNI">
                        </div>
                    </div>
                </div>

                <input type="hidden" id="source" name="source" value="internal">

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="button" id="SubmitCustDetail" class="btn btn-primary px-4">
                        <i class="fa fa-paper-plane me-1"></i> Submit Candidate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="DivProcess"></div>

<script>
$('#SubmitCustDetail').click(function(){
    var name = $('#Name').val();
    var pos = $('#Position').val();
    if (!pos) {
        alert('Silakan pilih Position terlebih dahulu!');
        $('#Position').focus();
        return;
    }
    if (!name) {
        alert('Silakan isi Name of Candidate!');
        $('#Name').focus();
        return;
    }

    if (typeof $.blockUI === 'function') $.blockUI();
    var gettingVars = getAllValues();
    
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "recruitment-new-submit?_token={{ csrf_token() }}",
        type: "POST",
        data: gettingVars,
        complete: function() {
            if (typeof $.unblockUI === 'function') $.unblockUI();
        },
        success: function(data, textStatus, jqXHR) {
            var isSuccess = false;
            if (typeof data === 'object' && data.status === 'success') {
                isSuccess = true;
            } else if (typeof data === 'string' && data.indexOf("success") !== -1) {
                isSuccess = true;
            }

            if (isSuccess) {
                alert('Data New Recruitment Submitted Successfully!');
                if (typeof refreshdata === 'function') refreshdata();
                if ($('#divlistRecruitment').length) $('#divlistRecruitment').html('');
            } else {
                var msg = (typeof data === 'object' && data.message) ? data.message : 'Recruitment Failed to Submit!';
                alert(msg);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var msg = 'Error Internal! Please Try again!';
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                msg = jqXHR.responseJSON.message;
            }
            alert(msg);
        }
    });
});

function getAllValues() {
    var inputValues = [];
    $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
        var el = $(this);
        var id = el.attr("id") || el.attr("name");
        if (!id) return;
        var type = el.attr("type");
        if ((type === "checkbox" || type === "radio") && this.checked) {
            inputValues.push(encodeURIComponent(id) + "=" + encodeURIComponent(el.val()));
        } else if (type !== "button" && type !== "submit") {
            inputValues.push(encodeURIComponent(id) + "=" + encodeURIComponent(el.val()));
        } else if (el.is("textarea")) {
            inputValues.push(encodeURIComponent(id) + "=" + encodeURIComponent(el.val()));
        }
    });
    return inputValues.join('&');
}
</script>
