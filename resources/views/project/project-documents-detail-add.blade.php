<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD NEW DOCUMENTS</div>

<div id="ContentPages">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Document Date
		</div>
		<div class="FormInput_right">
			<input type="hidden" id="Task_ID" size="20" value="<?php echo $_GET['Task_ID'] ?>" />
			<input type="text" id="Document_Date" size="20" value="<?php echo date('Y-m-d'); ?>" />


		</div>
		<div class="FormInput_left">

			Document No
		</div>
		<div class="FormInput_right">

			<input type="text" id="Document_No" size="40" />


		</div>
		<div class="FormInput_left">

			Reference No

		</div>
		<div class="FormInput_right">
			<input type="text" id="Reference_No" size="40" />
		</div>

		<div class="FormInput_left">

			Category Document

		</div>
		<div class="FormInput_right">

			<select id='File_Category'>
				<option>PO</option>
				<option>PKS</option>
				<option>SP1</option>
				<option>SP2</option>
				<option>SP3</option>
				<option>SPH</option>
				<option>SPPH</option>
				<option>NOKES</option>
				<option>MOM</option>
				<option>SPK</option>
				<option>UAT_DOCUMENTS</option>
				<option>MANUAL_BOOK</option>
				<option>PROPOSAL</option>
				<option value='TOR'>Term of Reference/Scope of Work</option>
				<option>QUOTATION</option>
				<option>MEMO</option>
				<option>OTHER</option>
			</select>
			<input type="hidden" id="Path_File" size="30" />

		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FILE TO UPLOAD</div>

		<div id="vault" class="with-header" style='height:200px;margin:0px;width:500px;'></div>

	</div>
</div>
<div id="divtom">
	<input type="button" id="DelCustDetail" NAME="DelCustDetail" value="Cancel" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
</div>
<script>
	var vault = new dhx.Vault("vault", {
		uploader: {
			target: "upload-project-document.php"
		},
		filesLimit: 1

	});

	vault.events.on("uploadComplete", function(obj) {
		$("#Path_File").val(obj[0].name);
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#Path_File").val("");

	});
	vault.events.on("beforeAdd", function(fileItem) {
		if (vault.data.getLength() >= 1) {
			alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
			return false;
		}
	});

	vault.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop();

		var predicate = extension === "pdf" || extension === "doc" || extension === "docx" || extension === "xls" || extension === "xlsx";
		if (!predicate) {
			dhtmlx.alert("Hanya Boleh mengupload tipe file PDF atau Word atau Excel");
		}
		return predicate;
	});
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Document_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#DelCustDetail').click(function() {
		refreshdataDocumentdetail();
		$("#DivDocumentListsDetail").html('');

	});

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "project-documents-detail-add-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					alert('Document Created!');
					refreshdataDocumentdetail();
					$("#DivDocumentListsDetail").html('');

				} else {
					alert('Document Data Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>