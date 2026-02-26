<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

$Fetch = DB::table("opportunity")->where("ID", request()->get('ID'))->first();

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
<div class="mt-4 card">
	<div class="card-body">
		<h4>Detail Pengajuan Ticket</h4>
		<div id="ContentPages1">
			<div class="FormInput_Form_Content">
				<div class="row">
					<div class="col-12">
						<div class="form-group">
							<label for="Date">Tanggal Pengajuan Ticket</label>
							<input type="text" name="Date" id="Date" value="{{ date('Y-m-d') }}" readonly class="form-control" />
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Type">Jenis Permasalahan</label>
							<input type="text" name="Type" id="Type" value="" class="form-control" required />
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Type">Kategori Ticket</label>
							<select name="Category" id="Category" class="form-control" required>
								<option value="">-- Pilih kategori --</option>
								@foreach ($masterCategory as $mc)
								<option value="{{ $mc->status }}">{{$mc->status}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Subject">Judul Ticket</label>
							<input type="text" name="Subject" id="Subject" value="" class="form-control" />
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="GEU_Express_ID">HUB GEU Express</label>
							<input type="text" name="GEU_Express_ID" id="GEU_Express_ID" value="" class="form-control" />
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Description">Deskripsi Ticket</label>
							<textarea name="Description" id="Description" rows="5" class="form-control"></textarea>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Attachment_Files">Lampiran Pendukung (Jika Ada)</label>
							<div id="Attachment_Files" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
							<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="Attachment_File" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="mt-4 card">
	<div class="card-body">
		<h4>Response Ticket</h4>
		<div id="ContentPages">
			<div class="FormInput_Form_Content">
				<div class="row">
					<div class="col-12">
						<div class="form-group">
							<label for="Response">Response dari Tim Support</label>
							<textarea name="Response" id="Response" rows="5" class="form-control"></textarea>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label for="Status">Status Penyelesaian</label>
							<select name="Status" id="Status" class="form-control" onchange="changeStatus()">
								<option value="In Progress">In Progress</option>
								<option value="Closed">Closed</option>
							</select>
						</div>
					</div>
					<div class="col-12" id="divReason" style="display:none">
						<div class="form-group">
							<label for="Note">Catatan Penyelesaian</label>
							<textarea name="Note" id="Note" rows="3" class="form-control"></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function changeStatus() {
		var Status = $("#Status").find('option:selected').val();
		if (Status == 'Closed') {
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