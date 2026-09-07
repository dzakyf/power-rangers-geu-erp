<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

$Fetch = DB::table("opportunity")->selectRaw("opportunity.*, (select op2.package_option from opportunity_partner op2 where op2.lead_id = opportunity.Task_ID order by op2.id desc limit 1) as package_option")->where("ID", request()->get('ID'))->first();

if ($Fetch->Capital_Estimation == 1) {
	$estimation = "<50 Juta";
} elseif ($Fetch->Capital_Estimation == 2) {
	$estimation = "100 Juta - 200 Juta";
} elseif ($Fetch->Capital_Estimation == 3) {
	$estimation = "200 Juta - 300 Juta";
} elseif ($Fetch->Capital_Estimation == 4) {
	$estimation = "300 Juta";
} else {
	$estimation = "50 Juta - 100 Juta";
}

?>
<div class="my-4 card">
	<div class="card-body">
		<h5>Partner Outstanding Leads Detail</h5>
		<div class="row" id="ContentPages">
			<div class="col-md-6">
				<input type="hidden" name="ID" id="ID" value="{{ $Fetch->ID }}" />
				<label for="Task_ID">Lead ID</label>
				<input type="text" id="Task_ID" name="Task_ID" class="form-control" value="{{ $Fetch->Task_ID }}" readonly />
			</div>
			<div class="col-md-6">
				<label for="Full_Name">Full Name</label>
				<input type="text" id="Full_Name" name="Full_Name" class="form-control" value="{{ $Fetch->Full_Name }}" readonly />
			</div>
			<div class="col-md-6 mt-3">
				<label for="Phone_Number">Contact</label>
				<input type="text" id="Phone_Number" name="Phone_Number" class="form-control" value="{{ $Fetch->Phone_Number }}" readonly />
			</div>
			<div class="col-md-6 mt-3">
				<label for="Partnership_Interest">Partnership Interest</label>
				<input type="text" id="Partnership_Interest" name="Partnership_Interest" class="form-control" value="{{ leads_interest_label($Fetch->package_option ?? null, $Fetch->Hub_Type ?? null, $Fetch->Partnership_Interest ?? null) }}" readonly />
			</div>
			<div class="col-md-6 mt-3">
				<label for="Capital_Estimation">Capital Estimation</label>
				<input type="text" id="Capital_Estimation" name="Capital_Estimation" class="form-control" value="{{ $estimation }}" readonly />
			</div>
			<div class="col-md-6 mt-3">
				<label for="Response">Response</label>
				<select disabled class="form-control" name="Response" id="Response">
					<option value="" selected disabled>-- Pilih Response --</option>
					<option value="Response" {{ $Fetch->Response == 'Response' ? 'selected' : '' }}>Response</option>
					<option value="No Response" {{ $Fetch->Response == 'No Response' ? 'selected' : '' }}>No Response</option>
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Qualification">Kualifikasi</label>
				<select disabled class="form-control" name="Qualification" id="Qualification">
					<option value="" selected disabled>-- Pilih Kualifikasi --</option>
					<option value="Qualified" {{ $Fetch->Qualification == 'Qualified' ? 'selected' : '' }}>Qualified</option>
					<option value="Unqualified" {{ $Fetch->Qualification == 'Unqualified' ? 'selected' : '' }}>Unqualified</option>
					<option value="Unidentified" {{ $Fetch->Qualification == 'Unidentified' ? 'selected' : '' }}>Unidentified</option>
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Classification">Klasifikasi</label>
				<select disabled class="form-control" name="Classification" id="Classification">
					<option value="" selected disabled>-- Pilih Klasifikasi --</option>
					<option value="Fresh" {{ $Fetch->Classification == 'Fresh' ? 'selected' : '' }}>Fresh</option>
					<option value="FU 1" {{ $Fetch->Classification == 'FU 1' ? 'selected' : '' }}>FU 1</option>
					<option value="FU 2" {{ $Fetch->Classification == 'FU 2' ? 'selected' : '' }}>FU 2</option>
					<option value="Meeting 1" {{ $Fetch->Classification == 'Meeting 1' ? 'selected' : '' }}>Meeting 1</option>
					<option value="Meeting 2" {{ $Fetch->Classification == 'Meeting 2' ? 'selected' : '' }}>Meeting 2</option>
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Status">Status</label>
				<select disabled class="form-control" name="Status" id="Status" onchange="changeStatus()">
					<option value="" selected disabled>-- Pilih Status --</option>
					<option value="Cold" {{ $Fetch->Status == 'Cold' ? 'selected' : '' }}>Cold</option>
					<option value="Warm" {{ $Fetch->Status == 'Warm' ? 'selected' : '' }}>Warm</option>
					<option value="Deal" {{ $Fetch->Status == 'Deal' ? 'selected' : '' }}>Deal</option>
					<option value="Cancel" {{ $Fetch->Status == 'Cancel' ? 'selected' : '' }}>Cancel</option>
				</select>
			</div>
			<div class="col-md-6 mt-3" style="display:{{ $Fetch->Status == 'Cancel' ? 'block' : 'none' }}" id="divReason">
				<label for="Reason">Cancel Reason</label>
				<select disabled class="form-control" name="Reason" id="Reason">
					<option value="" selected disabled>-- Pilih Cancel Reason --</option>
					<option value="Tidak Tertarik" {{ $Fetch->Reason == 'Tidak Tertarik' ? 'selected' : '' }}>Tidak Tertarik</option>
					<option value="Tidak Ada Modal" {{ $Fetch->Reason == 'Tidak Ada Modal' ? 'selected' : '' }}>Tidak Ada Modal</option>
					<option value="Modal Tidak Sesuai" {{ $Fetch->Reason == 'Modal Tidak Sesuai' ? 'selected' : '' }}>Modal Tidak Sesuai</option>
					<option value="Prospek Di Masa Depan" {{ $Fetch->Reason == 'Prospek Di Masa Depan' ? 'selected' : '' }}>Prospek Di Masa Depan</option>
					<option value="Lokasi Tidak Sesuai" {{ $Fetch->Reason == 'Lokasi Tidak Sesuai' ? 'selected' : '' }}>Lokasi Tidak Sesuai</option>
					<option value="Skema Bisnis  Tidak Sesuai" {{ $Fetch->Reason == 'Skema Bisnis  Tidak Sesuai' ? 'selected' : '' }}>Skema Bisnis Tidak Sesuai</option>
				</select>
			</div>
		</div>
	</div>
</div>

<script>
</script>