<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FOTO</div>

<div id="vault" class="with-header" style='height:330px;margin:0px;width:500px;'></div>
<input type="hidden" id="Path_File">
<div id="divtom">
	<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Upload" class="btn btn-success mt-2 mr-2" />
</div>
<script>
	var vault = new dhx.Vault("vault", {
		uploader: {
			target: "upload-foto.php"
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
			alert('Only can upload 1 file photo!');
			return false;
		}
	});

	vault.events.on("beforeAdd", function(item) {
		var extension = item.file.name.split(".").pop();

		var predicate = extension === "jpg" || extension === "png" || extension === "wmf" || extension === "tiff" || extension === "gif";
		if (!predicate) {
			dhtmlx.alert("Hanya Boleh mengupload tipe file PDF atau Word atau Excel");
		}
		return predicate;
	});


	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "update-foto?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					alert('Photo Updated!');
					$("#DivDocumentListsDetail").html('');

				} else {
					alert('Failed to Update');
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