<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TICKET SUBJECT</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-12">
							<div class="form-group">
								<input type="hidden" name="ID" id="ID" value="{{ $announcement->ID }}" class="form-control" required readonly />
								<label for="Title">Judul Pengumuman</label>
								<input type="text" name="Title" id="Title" value="{{ $announcement->Title }}" class="form-control" required />
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">
								<label for="Content">Isi Pengumuman</label>
								<textarea name="Content" id="Content" rows="5" class="form-control" required>{{ $announcement->Content }}</textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetailDraft" NAME="SubmitCustDetailDraft" value="Save as Draft" class="btn btn-secondary mt-2 mr-2" />
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Publish" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
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


	$('#SubmitCustDetailDraft').click(function() {

		gettingVars = getAllValues();
        gettingVars += "&Status=Draft";

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-announcement-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Announcement Submitted!');

					// location.href = "partner-announcement";
				} else {
					alert('Announcement Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-announcement-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Announcement Submitted!');

					// location.href = "partner-announcement";
                    refreshdata();
                    $('#divlistOpportunity').html('')
				} else {
					alert('Announcement Failed to Submit!');
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