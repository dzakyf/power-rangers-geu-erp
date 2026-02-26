<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

// $LSP = Auth::user()->Location;
// $Saya =  Auth::user()->username;

$LSP = "GEU-HO";
$Saya = "admin";

// $leadID = "L/BP/".date('Y')."/".sprintf('%02d',date('m'))."/".strtoupper(substr(uniqid(), 0, 4));

$leadID = 'BP/'.$LSP.'/'.date('Ymdhis').'/'.substr(md5($Saya),0,3);

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
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
	var vault = new dhx.Vault("Attachment_Files", {
		uploader: {
			target: "upload-receiveinvoice.php"
		},
		filesLimit: 1

	});

	vault.events.on("uploadComplete", function(obj) {
		$("#Attachment_File").val(obj[0].name);
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#Attachment_File").val("");

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
			url: "sales-opportunity-new-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Opportunity Submitted!');

					location.href = "sales-opportunity-new";
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
@endsection