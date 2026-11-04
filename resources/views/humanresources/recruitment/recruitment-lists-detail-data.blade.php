@include('layout.plugins')
@include('layout.adminor.tab-head')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$priv =  Auth::user()->Priv_ID;
$datahasil = DB::table("recruitment")->where("ID", $_GET['ID'])->first();
$result = DB::select("SELECT COLUMN_NAME FROM information_schema.columns where TABLE_NAME = 'recruitment' AND TABLE_SCHEMA = 'erp_ksps'");
$jumlah = count($result);
if ($jumlah > 0) {
	echo "<script>";
	foreach ($result as $row) {
		$kolom = $row->column_name;
		if ($datahasil->$kolom == '0000-00-00') {
			echo "$('#$kolom').val('');\n";
		} else {
			if ($datahasil->$kolom != 'ID') {
				$dataForm = $datahasil->$kolom;
				// Mapping normalisasi nilai legacy ke format standar baru
				if ($kolom == 'Gender') {
					if ($dataForm == 'L') $dataForm = 'MALE';
					if ($dataForm == 'P') $dataForm = 'FEMALE';
				} elseif ($kolom == 'Marital_Status') {
					if ($dataForm == 'WIDOW' || $dataForm == 'WIDOWER') $dataForm = 'WIDOWED';
				} elseif ($kolom == 'Religion') {
					if ($dataForm == 'KRISTEN KATHOLIK') $dataForm = 'KATOLIK';
				}
				echo "$('#$kolom').val('$dataForm');\n";
			}
		}
	}
	echo "</script>";
}

$datapo = DB::table("opportunity_detail")->where("Task_ID", $datahasil->Registration_Status)->first();
$datahasil2 = DB::table("recruitment_detail")->where("Task_ID", $datahasil->Task_ID)->first();
$Query1 = DB::table("recruitment_akademic")->get();

foreach ($Query1 as $Fetch1) {
	$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
}
$rekusisi = "";
$pecahin = explode(",", $datahasil->Requisition_ID);

foreach ($pecahin as $pecahreq) {
	$Requisition = DB::table("requisition")->where("Task_ID", $pecahreq)->first();
	if ($Requisition && !empty($Requisition->Job_Position)) {
		$rekusisi .= $Requisition->Job_Position . ",";
	}
}
$rekusisi = rtrim($rekusisi, ',');
$ptkNo = $datahasil->Requisition_ID ?? '-';
?>

<div class="container-fluid py-3" id="ContentPages">
    <input type="hidden" value="<?php echo $datahasil->ID?>" id="ID" readonly="readonly"/>
    <input type="hidden" value="<?php echo $datahasil->Foto?>" id="Foto" readonly="readonly"/>

    <!-- Profile & Header Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="position-relative text-center">
                    <div class="rounded-3 overflow-hidden border shadow-sm" style="width: 120px; height: 140px; background-color: #f8f9fa;">
                        <img src="./doc_recruitment/photo/<?php echo $datahasil->Foto?>" id="gambar" onclick="if(typeof cekFoto === 'function') cekFoto('<?php echo $datahasil->Foto?>')" class="w-100 h-100 object-fit-cover" style="cursor:pointer;" alt="Candidate Photo" onerror="this.src='{{ asset('assets/images/user/big/1.png') }}'">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="if(typeof uploadGambar === 'function') uploadGambar('Foto');">
                        <i class="fa fa-camera me-1"></i> Change
                    </button>
                </div>
                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $datahasil->Name ?: 'Unnamed Candidate';?></h4>
                        @if($datahasil->Priority)
                            <span class="badge <?php echo $datahasil->Priority == 'URGENT' ? 'bg-danger text-white' : 'bg-secondary text-white'; ?> px-2 py-1">
                                <?php echo $datahasil->Priority;?>
                            </span>
                        @endif
                    </div>
                    <div class="row g-2 text-muted small">
                        <div class="col-sm-6 col-lg-2">
                            <span class="fw-semibold text-secondary">Task No:</span>
                            <div class="text-dark fw-bold"><?php echo $datahasil->Task_ID;?></div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <span class="fw-semibold text-secondary">PTK No:</span>
                            <div class="text-dark fw-bold"><?php echo $ptkNo;?></div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <span class="fw-semibold text-secondary">Interested Position:</span>
                            <div class="text-dark"><?php echo $rekusisi ?: '-';?></div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <span class="fw-semibold text-secondary">Date Created:</span>
                            <div class="text-dark"><?php echo $datahasil->Created_Date ?: '-';?></div>
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <span class="fw-semibold text-secondary">Date Join:</span>
                            <div class="text-dark"><?php echo !empty($datahasil->Date_in) && $datahasil->Date_in != '0000-00-00' ? date("d M Y", strtotime($datahasil->Date_in)) : '-';?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Personal Information -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-user me-2"></i>Personal Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Task ID</label>
                    <input type="text" class="form-control form-control-sm" value="<?php echo $datahasil->Task_ID;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PTK No (Requisition ID)</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold text-primary" value="<?php echo $ptkNo;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Candidate Name</label>
                    <input type="text" class="form-control form-control-sm" value="<?php echo $datahasil->Name;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Initial">Initial</label>
                    <input type="text" id="Initial" class="form-control form-control-sm" value="<?php echo $datahasil->Initial;?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Religion">Religion</label>
                    <select id="Religion" class="form-select form-select-sm">
                        <option value="ISLAM">Islam</option>
                        <option value="KRISTEN PROTESTAN">Kristen Protestan</option>
                        <option value="KATOLIK">Katolik</option>
                        <option value="HINDU">Hindu</option>
                        <option value="BUDHA">Budha</option>
                        <option value="KONGHUCU">Konghucu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Gender">Gender</label>
                    <select id="Gender" class="form-select form-select-sm">
                        <option value="MALE">MALE (Laki-laki)</option>
                        <option value="FEMALE">FEMALE (Perempuan)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Birth_Date">Birth Date</label>
                    <input type="text" id="Birth_Date" class="form-control form-control-sm" readonly value="<?php echo $datahasil->Birth_Date;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Birth_Place">Birth Place</label>
                    <input type="text" id="Birth_Place" class="form-control form-control-sm" value="<?php echo $datahasil->Birth_Place;?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="KTP">Citizen ID (KTP)</label>
                    <input type="text" id="KTP" class="form-control form-control-sm" value="<?php echo $datahasil->KTP;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="kewarganegaraan">Citizenship</label>
                    <select id="kewarganegaraan" class="form-select form-select-sm">
                        <option value="WNI">WNI (Indonesian)</option>
                        <option value="WNA">WNA (Foreign National)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Marital_Status">Marital Status</label>
                    <select id="Marital_Status" class="form-select form-select-sm">
                        <option value="SINGLE">Single (Lajang)</option>
                        <option value="MARRIED">Married (Menikah)</option>
                        <option value="DIVORCED">Divorced (Cerai)</option>
                        <option value="WIDOWED">Widowed (Janda/Duda)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Priority">Priority Recruitment</label>
                    <select id="Priority" class="form-select form-select-sm">
                        <option>URGENT</option>
                        <option>NORMAL</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Address">Address (ID Card)</label>
                    <input type="text" id="Address" class="form-control form-control-sm" value="<?php echo $datahasil->Address;?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="City">City (ID Card)</label>
                    <input type="text" id="City" class="form-control form-control-sm" value="<?php echo $datahasil->City;?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Address_Living">Current Address (Living)</label>
                    <input type="text" id="Address_Living" class="form-control form-control-sm" value="<?php echo $datahasil->Address_Living ?: $datahasil->Address;?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="Address_Living_City">Current City (Living)</label>
                    <input type="text" id="Address_Living_City" class="form-control form-control-sm" value="<?php echo $datahasil->City_Living ?: $datahasil->City;?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Phone">Phone Contact</label>
                    <input type="text" id="Phone" class="form-control form-control-sm" value="<?php echo $datahasil->Phone;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Handphone">WhatsApp No</label>
                    <input type="text" id="Handphone" class="form-control form-control-sm" value="<?php echo $datahasil->Handphone;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Email_Individual">Email Individual</label>
                    <input type="email" id="Email_Individual" class="form-control form-control-sm" value="<?php echo $datahasil->Email_Individual;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Blood Type / Rhesus</label>
                    <div class="input-group input-group-sm">
                        <select id="Blood" class="form-select">
                            <option>A</option>
                            <option>AB</option>
                            <option>B</option>
                            <option>O</option>
                        </select>
                        <select id="Blood_Rhesus" class="form-select" style="max-width: 80px;">
                            <option>+</option>
                            <option>-</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Education & Payroll Data -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-graduation-cap me-2"></i>Education & Tax Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Last_Education">Last Education</label>
                    <select id="Last_Education" class="form-select form-select-sm">
                        <option>SMA</option>
                        <option>SMK</option>
                        <option>D2</option>
                        <option>D3</option>
                        <option>S1</option>
                        <option>S2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Last_Education_From">Education From (Institution)</label>
                    <input type="text" id="Last_Education_From" class="form-control form-control-sm" value="<?php echo $datahasil->Last_Education_From;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Education_Major">Education Major</label>
                    <input type="text" id="Education_Major" class="form-control form-control-sm" value="<?php echo $datahasil->Education_Major;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Position">Interested Position</label>
                    <input type="text" id="Position" class="form-control form-control-sm" value="<?php echo $rekusisi;?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Date_in">Date Join</label>
                    <input type="text" id="Date_in" class="form-control form-control-sm" readonly value="<?php echo !empty($datahasil->Date_in) && $datahasil->Date_in != '0000-00-00' ? date('d M Y', strtotime($datahasil->Date_in)) : '';?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="PTKP_Status">PTKP Status</label>
                    <select id="PTKP_Status" class="form-select form-select-sm">
                        <option>TK/0</option>
                        <option>K/0</option>
                        <option>K/1</option>
                        <option>K/2</option>
                        <option>K/3</option>
                        <option>TK/1</option>
                        <option>TK/2</option>
                        <option>TK/3</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="NPWP">NPWP</label>
                    <input type="text" id="NPWP" class="form-control form-control-sm" value="<?php echo $datahasil->NPWP;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Payment Method</label>
                    <input type="text" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Method;?>" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference">Bank Name</label>
                    <input type="text" id="Payment_Reference" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference2">Bank Account No</label>
                    <input type="text" id="Payment_Reference2" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference2;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference3">Bank Account Name</label>
                    <input type="text" id="Payment_Reference3" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference3;?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference_Branch">Bank Branch</label>
                    <input type="text" id="Payment_Reference_Branch" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference_Branch;?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Employee Job Detail -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-briefcase me-2"></i>Employee Job Placement Detail</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Division / Directorate</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Directorat;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Allocation</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Alocation;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Department</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Department;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Location</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Location;?>" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Subsection</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Subsection;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Work Area</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Work_Area;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Employment Type</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Employment_Class;?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Employee Grade</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Employment_Grade;?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: HR & User Interview Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light py-2 border-bottom">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fa fa-comments me-2"></i>HR Interview Result</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Interview Note</label>
                        <div class="p-2 rounded bg-light border text-secondary small" style="min-height: 50px;">
                            <?php echo nl2br(e($datahasil->Interview_Note)) ?: '<em>No note provided</em>';?>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Date:</span>
                            <span class="small text-dark"><?php echo $datahasil->Interview_Date ?: '-';?></span>
                        </div>
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Result:</span>
                            <span class="badge bg-info text-white"><?php echo $datahasil->Interview_Result ?: '-';?></span>
                        </div>
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Interviewer:</span>
                            <span class="small text-dark"><?php echo $datahasil->Interview_By ?: '-';?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light py-2 border-bottom">
                    <h6 class="mb-0 fw-bold text-success"><i class="fa fa-user-check me-2"></i>User Interview Result</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">User Interview Note</label>
                        <div class="p-2 rounded bg-light border text-secondary small" style="min-height: 50px;">
                            <?php echo nl2br(e($datahasil->User_Interview_Note)) ?: '<em>No note provided</em>';?>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Date:</span>
                            <span class="small text-dark"><?php echo $datahasil->User_Interview_Date ?: '-';?></span>
                        </div>
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Result:</span>
                            <span class="badge bg-success text-white"><?php echo $datahasil->User_Interview_Result ?: '-';?></span>
                        </div>
                        <div class="col-4">
                            <span class="small text-muted d-block fw-semibold">Interviewer:</span>
                            <span class="small text-dark"><?php echo $datahasil->User_Interview_By ?: '-';?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-end gap-2">
            <button type="button" id="buttonSimpan" class="btn btn-primary px-4">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
var myCalendar;
if (typeof dhtmlXCalendarObject !== 'undefined') {
    myCalendar = new dhtmlXCalendarObject(["Start_Date","End_Date","Date_in","Birth_Date"]);
    myCalendar.setDateFormat("%Y-%m-%d");
}

$('#buttonSimpan').click(function(){
    if (typeof $.blockUI === 'function') $.blockUI();
    var gettingVars = getAllValues();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url : "recruitment-lists-detail-data-submit?_token={{ csrf_token() }}",
        type: "POST",
        data : gettingVars,
        complete: function() { 
            if (typeof $.unblockUI === 'function') $.unblockUI(); 
        },
        success: function(data) {
            if (data && data.status === 'success') {
                alert('Data updated successfully.');
                if (window.parent.refreshdata) window.parent.refreshdata();
                if (window.parent.$('#divlistRecruitment')) window.parent.$('#divlistRecruitment').empty();
            } else {
                alert('Failed to submit!');
            }
        },
        error: function () {
            alert("Error Internal! Please Try again!");
        }
    });
});

function getAllValues() {
    var inputValues = [];
    $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
        var el = $(this);
        var id = el.attr("id");
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
