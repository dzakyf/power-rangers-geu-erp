@include('layout.plugins')
@include('layout.adminor.tab-head')
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? '';
$Saya =  Auth::user()->username ?? '';
$priv =  Auth::user()->Priv_ID ?? '';
$datahasil = DB::table("employment")->where("Task_ID", $_GET['ID'] ?? '')->first();
$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'employment' AND table_schema='erp_geu'");
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
				echo "$('#$kolom').val('$dataForm');\n";
			}
		}
	}
	echo "</script>";
}

$datapo = DB::table("opportunity_detail")->where("Task_ID", $datahasil->Registration_Status ?? '')->first();
$datahasil2 = DB::table("employment_detail")->where("Task_ID", $_GET['ID'] ?? '')->first();
$Query1 = DB::table("employment_akademic")->get();

foreach ($Query1 as $Fetch1) {
	$akademis[$Fetch1->Task_ID] = $Fetch1->Education_Major;
}
?>

<div class="container-fluid py-3" id="ContentPages">
    <input type="hidden" value="<?php echo $datahasil->ID ?? ''; ?>" id="ID" readonly="readonly" />
    <input type="hidden" value="<?php echo $datahasil->Foto ?? ''; ?>" id="Foto" readonly="readonly" />

    <!-- Profile & Header Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <div class="position-relative text-center">
                    <div class="rounded-3 overflow-hidden border shadow-sm" style="width: 120px; height: 140px; background-color: #f8f9fa;">
                        <img src="./doc_recruitment/photo/<?php echo $datahasil->Foto ?? ''; ?>" id="gambar" onclick="if(typeof cekFoto === 'function') cekFoto('<?php echo $datahasil->Foto ?? ''; ?>')" class="w-100 h-100 object-fit-cover" style="cursor:pointer;" alt="Employee Photo" onerror="this.src='{{ asset('assets/images/user/big/1.png') }}'">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="if(typeof uploadGambar === 'function') uploadGambar('Foto');">
                        <i class="fa fa-camera me-1"></i> Change
                    </button>
                </div>
                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <h4 class="mb-0 fw-bold text-dark"><?php echo $datahasil->Name ?? '-'; ?></h4>
                        <span class="badge bg-primary text-white"><?php echo $datahasil->Task_ID ?? '-'; ?></span>
                        <span class="badge bg-success text-white">NIK: <?php echo $datahasil->NIK_KSPS ?? $datahasil->NIK ?? '-'; ?></span>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fa fa-briefcase me-1 text-primary"></i> <strong><?php echo $datahasil->Position ?? '-'; ?></strong> &nbsp;|&nbsp; 
                        <i class="fa fa-sitemap me-1 text-info"></i> Department: <strong><?php echo $datahasil->Department ?? '-'; ?></strong> &nbsp;|&nbsp;
                        <i class="fa fa-map-marker me-1 text-danger"></i> Location: <strong><?php echo $datahasil->Location ?? '-'; ?></strong>
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        <button type="button" id="buttonSimpan" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="fa fa-save me-1"></i> Update Employee Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal & Identity Information -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-user me-2"></i>Personal & Identity Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Name">Full Name *</label>
                    <input type="text" id="Name" class="form-control form-control-sm" value="<?php echo $datahasil->Name ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Initial">Initial</label>
                    <input type="text" id="Initial" class="form-control form-control-sm" value="<?php echo $datahasil->Initial ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="NIK">NIK GEU</label>
                    <input type="text" id="NIK" class="form-control form-control-sm" value="<?php echo $datahasil->NIK_KSPS ?? $datahasil->NIK ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="NIK_Client">NIK (Client)</label>
                    <input type="text" id="NIK_Client" class="form-control form-control-sm" value="<?php echo $datahasil->NIK_Client ?? ''; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Attendant_No">Attendant No (Fingerprint ID)</label>
                    <input type="text" id="Attendant_No" class="form-control form-control-sm" value="<?php echo $datahasil->Attendant_No ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="KTP">Citizen ID Card (No KTP) *</label>
                    <input type="text" id="KTP" class="form-control form-control-sm" value="<?php echo $datahasil->KTP ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Gender">Gender</label>
                    <select id="Gender" class="form-select form-select-sm">
                        <option value="L">MALE</option>
                        <option value="P">FEMALE</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="kewarganegaraan">Citizenship</label>
                    <input type="text" id="kewarganegaraan" class="form-control form-control-sm" value="<?php echo $datahasil->kewarganegaraan ?? 'INDONESIA'; ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Birth_Place">Birth Place</label>
                    <input type="text" id="Birth_Place" class="form-control form-control-sm" value="<?php echo $datahasil->Birth_Place ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Birth_Date">Birth Date</label>
                    <input type="text" id="Birth_Date" class="form-control form-control-sm" value="<?php echo $datahasil->Birth_Date ?? ''; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Religion">Religion</label>
                    <select id="Religion" class="form-select form-select-sm">
                        <option>ISLAM</option>
                        <option>KRISTEN PROTESTAN</option>
                        <option>KRISTEN KATHOLIK</option>
                        <option>HINDU</option>
                        <option>BUDHA</option>
                        <option>KONGHUCU</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Marital_Status">Marital Status</label>
                    <select id="Marital_Status" class="form-select form-select-sm">
                        <option>TK/0</option>
                        <option>K/0</option>
                        <option>K/1</option>
                        <option>K/2</option>
                        <option>K/3</option>
                        <option>K/4</option>
                        <option>K/5</option>
                        <option>K/6</option>
                        <option>K/7</option>
                        <option>K/8</option>
                        <option>TK/1</option>
                        <option>TK/2</option>
                        <option>TK/3</option>
                        <option>TK/4</option>
                        <option>TK/5</option>
                        <option>TK/6</option>
                        <option>TK/7</option>
                        <option>TK/8</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Blood">Blood Type & Rhesus</label>
                    <div class="input-group input-group-sm">
                        <select id="Blood" class="form-select">
                            <option>A</option>
                            <option>AB</option>
                            <option>B</option>
                            <option>O</option>
                        </select>
                        <span class="input-group-text">Rh</span>
                        <select id="Blood_Rhesus" class="form-select">
                            <option>+</option>
                            <option>-</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Priority">Priority Recruitment</label>
                    <select id="Priority" class="form-select form-select-sm">
                        <option>NORMAL</option>
                        <option>URGENT</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Created Date</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Created_Date ?? '-'; ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact & Address Information -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-address-book me-2"></i>Contact & Address Information</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Phone">Phone Contact</label>
                    <input type="text" id="Phone" class="form-control form-control-sm" value="<?php echo $datahasil->Phone ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Handphone">WhatsApp / Mobile No</label>
                    <input type="text" id="Handphone" class="form-control form-control-sm" value="<?php echo $datahasil->Handphone ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Email">Email Company</label>
                    <input type="email" id="Email" class="form-control form-control-sm" value="<?php echo $datahasil->Email ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Email_Individual">Email Personal</label>
                    <input type="email" id="Email_Individual" class="form-control form-control-sm" value="<?php echo $datahasil->Email_Individual ?? ''; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted" for="Address">Address (KTP)</label>
                    <input type="text" id="Address" class="form-control form-control-sm" value="<?php echo $datahasil->Address ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="City">City (KTP)</label>
                    <input type="text" id="City" class="form-control form-control-sm" value="<?php echo $datahasil->City ?? ''; ?>">
                </div>

                <div class="col-md-9">
                    <label class="form-label small fw-bold text-muted" for="Address_Living">Current Living Address</label>
                    <input type="text" id="Address_Living" class="form-control form-control-sm" value="<?php echo $datahasil->Address_Living ?? $datahasil->Address ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Address_Living_City">Living City</label>
                    <input type="text" id="Address_Living_City" class="form-control form-control-sm" value="<?php echo $datahasil->Address_Living_City ?? $datahasil->City ?? ''; ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Education & Career Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-graduation-cap me-2"></i>Education & Employment Setup</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Last_Education_From">Education From / Campus</label>
                    <input type="text" id="Last_Education_From" class="form-control form-control-sm" value="<?php echo $datahasil->Last_Education_From ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Education_Major">Education Major</label>
                    <input type="text" id="Education_Major" class="form-control form-control-sm" value="<?php echo $datahasil->Education_Major ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Position">Position Fulfillment</label>
                    <input type="text" id="Position" class="form-control form-control-sm" value="<?php echo $datahasil->Position ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="overtime_compensation">
                        <i class="fa fa-clock-o text-primary me-1"></i>Kompensasi Lembur
                    </label>
                    <select id="overtime_compensation" class="form-select form-select-sm">
                        <option value="" <?php echo empty($datahasil->overtime_compensation) ? 'selected' : ''; ?>>-- Ikuti Aturan Posisi (Default) --</option>
                        <option value="PAID" <?php echo (($datahasil->overtime_compensation ?? '') === 'PAID') ? 'selected' : ''; ?>>PAID (Uang Lembur Sesuai Matriks)</option>
                        <option value="TIME_OFF" <?php echo (($datahasil->overtime_compensation ?? '') === 'TIME_OFF') ? 'selected' : ''; ?>>TIME_OFF (Tukar Cuti / Day Off)</option>
                    </select>
                    <small class="text-muted d-block" style="font-size: 11px;">
                        Pilih untuk memberikan override hak kompensasi khusus karyawan ini.
                    </small>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Location">Branch Office / Location</label>
                    <select id="Location" class="form-select form-select-sm" style="width:100%">
                        <option value="">-- Pilih Branch Office --</option>
                        <?php
                        $locList = DB::table('location')->orderBy('Name', 'ASC')->get();
                        foreach ($locList as $loc) {
                            $sel = (($datahasil->Location ?? '') == $loc->Location_ID) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($loc->Location_ID) . "' $sel>" . htmlspecialchars($loc->Name) . " (" . htmlspecialchars($loc->Location_ID) . ")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Contract_No">Contract No</label>
                    <input type="text" id="Contract_No" class="form-control form-control-sm" value="<?php echo $datahasil->Contract_No ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Date_in">Date Join</label>
                    <input type="text" id="Date_in" class="form-control form-control-sm" value="<?php echo !empty($datahasil->Date_in) && $datahasil->Date_in != '0000-00-00' ? date('d M Y', strtotime($datahasil->Date_in)) : ''; ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Start_Date">Start Contract Date</label>
                    <input type="text" id="Start_Date" class="form-control form-control-sm" value="<?php echo !empty($datahasil->Start_Date) && $datahasil->Start_Date != '0000-00-00' ? date('d M Y', strtotime($datahasil->Start_Date)) : ''; ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="End_Date">End Contract Date</label>
                    <input type="text" id="End_Date" class="form-control form-control-sm" value="<?php echo !empty($datahasil->End_Date) && $datahasil->End_Date != '0000-00-00' ? date('d M Y', strtotime($datahasil->End_Date)) : ''; ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll, Tax, Bank & BPJS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-money me-2"></i>Payroll, Tax, Bank & BPJS</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="NPWP">NPWP</label>
                    <input type="text" id="NPWP" class="form-control form-control-sm" value="<?php echo $datahasil->NPWP ?? ''; ?>" onchange="formatnpwp(this.value)">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="PTKP_Status">PTKP Status</label>
                    <select id="PTKP_Status" class="form-select form-select-sm">
                        <option>TK/0</option>
                        <option>K/0</option>
                        <option>K/1</option>
                        <option>K/2</option>
                        <option>K/3</option>
                        <option>K/4</option>
                        <option>K/5</option>
                        <option>K/6</option>
                        <option>K/7</option>
                        <option>K/8</option>
                        <option>TK/1</option>
                        <option>TK/2</option>
                        <option>TK/3</option>
                        <option>TK/4</option>
                        <option>TK/5</option>
                        <option>TK/6</option>
                        <option>TK/7</option>
                        <option>TK/8</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Tax Gross Up (Ditanggung Perusahaan)</label>
                    <select id="PPH_Allowance" class="form-select form-select-sm">
                        <option value="0" <?php echo (($datahasil->PPH_Allowance ?? 0) == 0) ? 'selected' : ''; ?>>NO (Gross - Potong Gaji Karyawan)</option>
                        <option value="1" <?php echo (($datahasil->PPH_Allowance ?? 0) == 1) ? 'selected' : ''; ?>>YES (Gross Up - Ditanggung Perusahaan)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference">Bank Name</label>
                    <input type="text" id="Payment_Reference" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference ?? ''; ?>" placeholder="BCA / Mandiri / BNI">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference2">Bank Account No</label>
                    <input type="text" id="Payment_Reference2" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference2 ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference3">Bank Account Name</label>
                    <input type="text" id="Payment_Reference3" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference3 ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Payment_Reference_Branch">Bank Branch</label>
                    <input type="text" id="Payment_Reference_Branch" class="form-control form-control-sm" value="<?php echo $datahasil->Payment_Reference_Branch ?? ''; ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="bpjs_kes">BPJS Kesehatan No</label>
                    <input type="text" id="bpjs_kes" class="form-control form-control-sm" value="<?php echo $datahasil->bpjs_kes ?? ''; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted" for="bpjs_tk">BPJS Ketenagakerjaan No</label>
                    <input type="text" id="bpjs_tk" class="form-control form-control-sm" value="<?php echo $datahasil->bpjs_tk ?? ''; ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Organization & Job Hierarchy Info -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-sitemap me-2"></i>Employee Job & Structural Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Directorat / Division</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Directorat ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Department</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Department ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Subsection</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Subsection ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Work Area</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Work_Area ?? '-'; ?>" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Employment Type</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Employment_Class ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Employee Grade</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Employment_Grade ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Alocation</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Alocation ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Payment Method</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datahasil->Payment_Method ?? '-'; ?>" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- External & Opportunity PO Info -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-file-text-o me-2"></i>External PO & Contract Details</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO No</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datapo->PO_No ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO Date</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo !empty($datapo->PO_Date) ? date('d M Y', strtotime($datapo->PO_Date)) : '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO Start Date</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo !empty($datapo->Start_Date) ? date('d M Y', strtotime($datapo->Start_Date)) : '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO End Date</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo !empty($datapo->End_Date) ? date('d M Y', strtotime($datapo->End_Date)) : '-'; ?>" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO Subject</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datapo->Subject ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO User / Dept</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datapo->Department ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">PO Initial</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datapo->PO_Initial ?? '-'; ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Reference No</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo $datapo->Reference_No ?? '-'; ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatnpwp(nilai) {
    if (!nilai) return;
    var nilaibaru = nilai.substr(0, 2) + "." + nilai.substr(2, 3) + "." + nilai.substr(5, 3) + "." + nilai.substr(8, 1) + "-" + nilai.substr(9, 3) + "." + nilai.substr(-3);
    $("#NPWP").val(nilaibaru);
}

var myCalendar = new dhtmlXCalendarObject(["Start_Date", "End_Date", "Date_in", "Birth_Date"]);
myCalendar.setDateFormat("%Y-%m-%d");

if (typeof $ !== 'undefined' && $.fn.select2) {
    $('#Location').select2({ width: '100%', placeholder: '-- Pilih Branch Office --', allowClear: true });
}

$('#buttonSimpan').click(function() {
    var gettingVars = getAllValues();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "employee-lists-detail-data-submit?_token={{ csrf_token() }}",
        type: "POST",
        data: gettingVars,
        success: function(data, textStatus, jqXHR) {
            if (data.indexOf("success") !== -1) {
                alert('Employee data updated successfully!');
            } else {
                alert('Contract Failed to Submit: ' + data);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error Internal! Please Try again!");
        }
    });
});

function getAllValues() {
    var inputValues = [];
    $('#ContentPages input, #ContentPages select, #ContentPages textarea').each(function() {
        var el = $(this);
        var type = el.attr("type");
        var id = el.attr("id");
        if (!id) return;
        if ((type === "checkbox" || type === "radio") && this.checked) {
            inputValues.push(id + "=" + encodeURIComponent(el.val()));
        } else if (type !== "button" && type !== "submit") {
            inputValues.push(id + "=" + encodeURIComponent(el.val()));
        }
    });
    return inputValues.join('&');
}

var winCOA, WCOA;
function uploadGambar(isi) {
    winCOA = new dhtmlXWindows();
    winCOA.attachViewportTo('ContentPages');
    WCOA = winCOA.createWindow("WCOA", 200, 100, 550, 350);
    WCOA.keepInViewport(false);
    WCOA.setText(isi);
    WCOA.attachURL('upload-foto?ID=<?php echo $_GET['ID'] ?? '' ?>', true);
    WCOA.button("minmax1").hide();
    WCOA.button("park").hide();
    WCOA.allowMove();
}
</script>
