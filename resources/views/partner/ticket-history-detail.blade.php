<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

$Fetch = DB::table("ticket")->where("Ticket_No", $_GET['Task_ID'])->first();
$masterCategory = DB::table('master_ticket_category')->get();

?>
<div class="mt-4 card">
	<div class="card-body">
		<h4>Detail Pengajuan Ticket</h4>
		<div class="row">
			<div class="col-12">
				<div class="form-group">
					<input type="hidden" name="Ticket_No" id="Ticket_No" value="{{ $Fetch->Ticket_No }}">
					<label for="Proposed_Date">Tanggal Pengajuan Ticket</label>
					<input type="text" name="Proposed_Date" id="Proposed_Date" value="{{ date('Y-m-d') }}" readonly class="form-control" />
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="Ticket_Type">Jenis Permasalahan</label>
					<input type="text" name="Ticket_Type" id="Ticket_Type" readonly value="{{ $Fetch->Ticket_Type }}" class="form-control" required />
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="Ticket_Category">Kategori Ticket</label>
					<select name="Ticket_Category" id="Ticket_Category" class="form-control" required disabled>
						<option value="">-- Pilih kategori --</option>
						@foreach ($masterCategory as $mc)
						<option value="{{ $mc->status }}"{{ $Fetch->Ticket_Category == $mc->status ? ' selected' : '' }}>{{$mc->status}}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="Ticket_Subject">Judul Ticket</label>
					<input type="text" name="Ticket_Subject" id="Ticket_Subject" value="{{ $Fetch->Ticket_Type }}" class="form-control" readonly />
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="GEU_Express_ID">HUB GEU Express</label>
					<input type="text" name="GEU_Express_ID" id="GEU_Express_ID" value="{{ $Fetch->Ticket_Type }}" class="form-control" readonly />
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="Description">Deskripsi Ticket</label>
					<textarea name="Description" id="Description" rows="5" class="form-control" readonly>{{ $Fetch->Description }}</textarea>
				</div>
			</div>
			<div class="col-12">
				<div class="form-group">
					<label for="File_Documents">Lampiran Pendukung (Jika Ada)</label>
					<div>
					    @if ($Fetch->File_Document)
					        <a href="{{ asset('filevendor/' . $Fetch->File_Document) }}" target="_blank" class="btn btn-info btn-sm">
					            <i class="fas fa-file-download"></i> Lihat File
					        </a>
					    @else
					        <span class="text-muted">Tidak ada lampiran</span>
					    @endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="mt-4 card">
	<div class="card-body">
		<h4>Response Ticket Log</h4>
		<div class="row">
			<div class="col-12">
				<div id="TicketHistoryDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
				<div>
					<input type="button" id="ekselTicketHistoryDetail" name="ekselTicketHistoryDetail" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="TicketHistoryDetail.toExcel('./export/generate.php');" />
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function changeStatus() {
		var Status = $("#Status_Progress").find('option:selected').val();
		if (Status == 'CLOSED') {
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
			url: "partner-ticket-history-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Ticket Response Submitted!');

					location.href = "partner-ticket-history";
				} else {
					alert('Ticket Response Failed to Submit!');
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

	TicketHistoryDetail = new dhtmlXGridObject('TicketHistoryDetail');
	TicketHistoryDetail.setImagePath("./dhtmlx/imgs/");
	TicketHistoryDetail.setHeader("No, Response, Status Progress, Completion Note");
	TicketHistoryDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
	// TicketHistoryDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TicketHistoryDetail.setInitWidths("60,250,100,250");
	TicketHistoryDetail.setColAlign("center,left,left,left");
	TicketHistoryDetail.setColSorting("int,str,str,str");
	TicketHistoryDetail.setColTypes("ro,ro,ro,ro");
	TicketHistoryDetail.enableSmartRendering(true, 150);
	// TicketHistoryDetail.setNumberFormat("0,000", 7, ".", ",");
	TicketHistoryDetail.setSkin("dhx_skyblue");
	TicketHistoryDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		TicketHistoryDetail.clearAndLoad('xml-partner-ticket-history-detail?Task_ID={{ $_GET["Task_ID"] }}');

	}
</script>