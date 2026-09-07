<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

$masterQualification = DB::table('master_leads_qualification')->get();
$masterClassification = DB::table('master_leads_classification')->get();
$masterResponse = DB::table('master_leads_response')->get();
$masterReason = DB::table('master_leads_reason')->get();
$masterStatus = DB::table('master_leads_status')->get();

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
				<label class="col-form-label"><strong>Bagaimana Anda mengetahui program kemitraan ini?</strong></label>
				<div class="row">
					<div class="col-md-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="Source_Channel" disabled id="Source_Instagram" value="Instagram" {{ $Fetch->Source_Channel == 'Instagram' ? 'checked' : '' }} />
							<label class="form-check-label" for="Source_Instagram">Instagram</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="Source_Channel" disabled id="Source_Facebook" value="Facebook" {{ $Fetch->Source_Channel == 'Facebook' ? 'checked' : '' }} />
							<label class="form-check-label" for="Source_Facebook">Facebook</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="Source_Channel" disabled id="Source_TikTok" value="TikTok" {{ $Fetch->Source_Channel == 'TikTok' ? 'checked' : '' }} />
							<label class="form-check-label" for="Source_TikTok">TikTok</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="Source_Channel" disabled id="Source_Website" value="Website" {{ $Fetch->Source_Channel == 'Website' ? 'checked' : '' }} />
							<label class="form-check-label" for="Source_Website">Website</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="Source_Channel" disabled id="Source_Event" value="Event" {{ $Fetch->Source_Channel == 'Event' ? 'checked' : '' }} />
							<label class="form-check-label" for="Source_Event">Pameran/Acara</label>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Response">Response</label>
				<select readonly class="form-control" name="Response" id="Response">
					<option value="" selected disabled>-- Pilih Response --</option>
					@foreach($masterResponse as $mr)
					<option value="{{ $mr->status }}" {{ $Fetch->Response == $mr->status ? 'selected' : '' }}>{{ $mr->status }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Qualification">Kualifikasi</label>
				<select readonly class="form-control" name="Qualification" id="Qualification">
					<option value="" selected disabled>-- Pilih Kualifikasi --</option>
					@foreach ($masterQualification as $mq)
					<option value="{{ $mq->status }}" {{ $Fetch->Qualification == $mq->status ? 'selected' : '' }}>{{ $mq->status }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Classification">Klasifikasi</label>
				<select readonly class="form-control" name="Classification" id="Classification">
					<option value="" selected disabled>-- Pilih Klasifikasi --</option>
					@foreach ($masterClassification as $mc)
					<option value="{{ $mc->status }}" {{ $Fetch->Classification == $mc->status ? 'selected' : '' }}>{{ $mc->status }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3">
				<label for="Status">Status</label>
				<select readonly class="form-control" name="Status" id="Status" onchange="changeStatus()">
					<option value="" selected disabled>-- Pilih Status --</option>
					@foreach ($masterStatus as $ms)
					<option value="{{ $ms->status }}" {{ $Fetch->Status == $ms->status ? 'selected' : '' }}>{{ $ms->status }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-6 mt-3" style="display:none" id="divReason">
				<label for="Reason">Cancel Reason</label>
				<select readonly class="form-control" name="Reason" id="Reason">
					<option value="" selected disabled>-- Pilih Cancel Reason --</option>
					@foreach ($masterReason as $mr)
					<option value="{{ $mr->status }}" {{ $Fetch->Reason == $mr->status ? 'selected' : '' }}>{{$mr->status}}</option>
					@endforeach
				</select>
			</div>
		</div>
	</div>
</div>

<script>
	function changeStatus() {
		var Status = $("#Status").find('option:selected').val();
		if (Status == 'Cancel') {
			$('#divReason').show();
		} else {
			$('#divReason').hide();
		}
	}
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-outstanding-leads-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Opportunity Submitted!');

					location.href = "partner-outstanding-leads";
				} else {
					alert('Opportunity Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>