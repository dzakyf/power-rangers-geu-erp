<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

// $LSP = "GEU-HO";
// $Saya = "admin";

// $leadID = "L/BP/".date('Y')."/".sprintf('%02d',date('m'))."/".strtoupper(substr(uniqid(), 0, 4));

$leadID = 'TT/'.$LSP.'/'.date('Ymdhis').'/'.substr(md5($Saya),0,3);

$masterCategory = DB::table('master_ticket_category')->get();
?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Ticket</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Create New</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TICKET SUBJECT</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-12">
							<div class="form-group">
								<input type="hidden" name="Ticket_No" id="Ticket_No" value="{{ $leadID }}">
								<label for="Proposed_Date">Tanggal Pengajuan Ticket</label>
								<input type="text" name="Proposed_Date" id="Proposed_Date" value="{{ date('Y-m-d') }}" readonly class="form-control" />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Ticket_Type">Jenis Permasalahan</label>
								<input type="text" name="Ticket_Type" id="Ticket_Type" value="" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Ticket_Category">Kategori Ticket</label>
								<select name="Ticket_Category" id="Ticket_Category" class="form-control" required>
									<option value="">-- Pilih kategori --</option>
									@foreach ($masterCategory as $mc)
									<option value="{{ $mc->status }}">{{$mc->status}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Ticket_Subject">Judul Ticket</label>
								<input type="text" name="Ticket_Subject" id="Ticket_Subject" value="" class="form-control" />
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
								<label for="File_Documents">Lampiran Pendukung (Jika Ada)</label>
								<div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
								<input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>
<script src="{{ asset('jquery.coolfieldset.js') }}"></script>
<script>
	$("#GEU_Express_ID").coolautosuggest({
		url:"partner-warehouse-audit-get-warehouse?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#GEU_Express_ID"),
		width:500,
		minChars:1,
		onSelected:function(result){
		if(result!=null){
			$("#GEU_Express_ID").val(result.id); 

		}
		}
	});
	var vault = new dhx.Vault("File_Documents", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vault.events.on("uploadComplete", function(obj) {
		$("#File_Document").val(obj[0].name);
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#File_Document").val("");

	});
	vault.events.on("beforeAdd", function(fileItem) {
		if (vault.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint.');
			return false;
		}
	});


	vault.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop();

		var predicate = extension === "pdf";
		if (!predicate) {
			dhtmlx.alert("Hanya Boleh mengupload tipe file PDF");
		}
		return predicate;
	});

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject("Date");
	myCalendar.setDateFormat("%Y-%m-%d");


	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-ticket-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Ticket Submitted!');

					location.href = "partner-ticket-new";
				} else {
					alert('Ticket Failed to Submit!');
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
@endsection