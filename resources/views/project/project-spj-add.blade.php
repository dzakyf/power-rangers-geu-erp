<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$NIK =  Auth::user()->Emp_ID;
$sayax = md5($Saya);
$sayax = substr($sayax, -5);
$SR_No = "SR/" . $LSP . "/$sayax/" . date("mdhis");

$source = filter_var($_GET['source'], FILTER_SANITIZE_STRING);
$ref = filter_var($_GET['ref'], FILTER_SANITIZE_STRING);
$id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);

?>
<div id="ContentPagex">
	<div class="FormInput_Form_Content">

		<div class="JudulContent bg-primary text-white">Pengajuan SPJ</div>
		<div class="FormInput_left">

			Subject

		</div>
		<div class="FormInput_right">


			<input type="text" id="subject" class="col-sm-6" />

		</div>


		<div class="FormInput_left">

			Waktu SPJ

		</div>
		<div class="FormInput_right">
			<input type="text" id="start" class="col-sm-2" value="<?php echo date('Y-m-d'); ?>" /> -<input type="text" id="end" class="col-sm-2" value="<?php echo date('Y-m-d'); ?>" />

		</div>

		<div class="FormInput_left">

			Kota Tujuan

		</div>
		<div class="FormInput_right">


			<input type="text" id="kota" class="col-sm-6" />

		</div>


		<div class="FormInput_left">

			Reference

		</div>
		<div class="FormInput_right">
			<input type="text" id="reference" class="col-sm-6" value="<?= $ref; ?>" disabled /> <input type="hidden" id="source" size="15" value="<?= $source; ?>" disabled />
			<input type="hidden" id="note_submit" size="15" disabled />
			<input type="hidden" id="file_submit" size="15" disabled />
			<input type="hidden" id="project_id" size="15" disabled value="<?= $id; ?>" />

		</div>

		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">
			<input type="text" id="total" class="col-sm-6" value="0" onblur="sanitizeDot()" />
			<input type="hidden" id="total_submit" />
		</div>





		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NOTE</div>


		<div id="note" style="width:100%; height:150px; border:#909090 1px solid;"></div>



		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD FILE CONTRACT</div>

		<div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>

		<div id="divtom">
			<input type="button" id="SubmitSpj" NAME="SubmitSpj" value="Submit" />
		</div>



	</div>
</div>

<script>
	$(function() {
		$('#total').mask('#.##0', {
			reverse: true
		});
	});

	function sanitizeDot() {
		var x = document.getElementById('total').value.replaceAll(".", "");
		document.getElementById('total_submit').value = parseFloat(x);
	}
	var myEditor = new dhtmlXEditor("note");

	var vault = new dhx.Vault("File_Documents", {
		uploader: {
			target: "upload-spj.php"
		},
		filesLimit: 1

	});

	vault.events.on("uploadComplete", function(obj) {
		$("#file_submit").val(obj[0].name);
	});
	vault.events.on("uploadBegin", function(obj) {
		$("#file_submit").val("");

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

	myCalendar = new dhtmlXCalendarObject(["start", "end"]);
	myCalendar.setDateFormat("%Y-%m-%d");





	$('#SubmitSpj').click(function() {

		document.getElementById("note_submit").value = myEditor.getContent();


		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "project-spj-add-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos = 'success') {

					alert('Data SPJ Request Submitted!');
					setTimeout(function() {
						location.href = "project-spj";
					}, 1000);
				} else {
					alert('Data SPJ Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPagex input,select,textarea').each(function() {
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