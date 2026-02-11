<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$dfp = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
$datahasil2 = DB::table("opportunity_detail")->where("PO_No", $dfp->PO_No)->first();


$result2 = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity_detail'");
$jumlah2 = count($result2);
if ($jumlah2 > 0 && $datahasil2->ID != '') {
	echo "<script>";
	foreach ($result2 as $row2) {

		$kolom2 = $row2->column_name;



		if ($datahasil2->$kolom2 == '0000-00-00') {
			echo "$('#$kolom').val('');\n";
		} else {

			$dataForm2 = $datahasil2->$kolom2;
			preg_replace("/\r|\n/", "", $dataForm2);
			$dataForm2 = str_replace(array("\r", "\n"), '', $dataForm2);

			echo "$('#$kolom2').val('$dataForm2');\n";
		}
	}
	echo "</script>";
}
$result2 = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'proforma'");
$jumlah2 = count($result2);
if ($jumlah2 > 0 && $proforma->ID != '') {
	echo "<script>";
	foreach ($result2 as $row2) {

		$kolom2 = $row2->column_name;



		if ($proforma->$kolom2 == '0000-00-00') {
			echo "$('#$kolom').val('');\n";
		} else {

			$dataForm2 = $proforma->$kolom2;

			echo "$('#$kolom2').val('$dataForm2');\n";
		}
	}
	echo "</script>";
}

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

<div id="ProformaInitiate" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselProformaInitiateDet" name="ekselProformaInitiateDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaInitiate.toExcel('./export/generate.php');" />

</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BA DATA</div>

<div id="ProformaBADiv">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Jenis Pekerjaan

		</div>
		<div class="FormInput_right">

			<input type="hidden" value="<?php echo $proforma->ID ?>" id="ID_Proforma" size="80" disabled="disabled" readonly='readonly' />
			<input type="text" id="Subject" size="80" />

		</div>
		<div class="FormInput_left">

			Proforma No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Proforma_No" size="15" />

		</div>
		<div class="FormInput_left">

			PO No

		</div>
		<div class="FormInput_right">

			<input type="text" id="PO_No" size="15" />

		</div>
		<div class="FormInput_left">

			Reference No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference_No" size="35" />

		</div>
		<div class="FormInput_left">

			BA Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="BA_Date" size="15" />

		</div>

		<div class="FormInput_left">

			Periode System

		</div>
		<div class="FormInput_right">

			<input type="text" id="Periode_Tagihan" size="10" value="<?php echo date("Ym"); ?>" /> yyyymm

		</div>

		<div class="FormInput_left">

			Periode Description

		</div>
		<div class="FormInput_right">

			<input type="text" id="Periode" size="50" />

		</div>
		<div class="FormInput_left">

			Subtotal

		</div>
		<div class="FormInput_right">

			<input type="text" id="Subtotal_PO" size="20" onchange="cekangka()" onkeydown="return isValidKey(event)" value="<?php echo $totaltagihan; ?>" />

		</div>
		<div class="FormInput_left">

			Management Fee

		</div>
		<div class="FormInput_right">

			<input type="text" id="Man_Fee" size="20" onchange="cekangka()" onkeydown="return isValidKey(event)" value="0" />

		</div>
		<div class="FormInput_left">

			Total Amount

		</div>
		<div class="FormInput_right">

			<input type="text" id="Total" readonly="readonly" size="20" onkeydown="return isValidKey(event)" value="<?php echo $totaltagihan; ?>" />

		</div>
		<div class="FormInput_left">

			Billing For

		</div>
		<div class="FormInput_right">

			<select id="Bill_Type">
				<option>RUTIN</option>
				<option>TIDAK RUTIN</option>
			</select>

		</div>

		<div class="FormInput_left">

			Billing Method

		</div>
		<div class="FormInput_right">

			<input type="text" id="Termin" size="10" />

		</div>
		<div class="FormInput_left">

			BA No

		</div>
		<div class="FormInput_right">

			<input type="text" id="BA_No" size="35" value="<?php echo $bano ?>" />
			<input type="hidden" id="file_lists">

		</div>

	</div>
	<div class="FormInput_left">

		Invoice To

	</div>
	<div class="FormInput_right">

		<input type="text" id="Invoice_To" size="70" />

	</div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD APPROVED BA</div>

	<div id="vault" class="with-header" style='height:200px;margin:0px;width:500px;'></div>

	<div style="float:none;padding-top:30px;" ;>

		<input type="button" id="ProSubmt" NAME="ProSubmt" value="Submit" class="btn btn-success mt-2 mr-2" />

	</div>
	<script src="{{ asset('jquery.coolautosuggest.js') }}"></script>

	<script>
		$("#Invoice_To").coolautosuggest({
			url: "sales-search-proforma-invoice-to?chars=",
			showThumbnail: false,
			showDescription: true,
			idField: $("#Invoice_To"),
			width: 500,
			minChars: 1,
			onSelected: function(result) {
				if (result != null) {
					var x = result.thumbnail.split("|");
					$("#Invoice_To").val(result.id);
				}
			}
		});

		$("#Signed_By").coolautosuggest({
			url: "sales-search-proforma-user-signed?chars=",
			showThumbnail: false,
			showDescription: true,
			idField: $("#Signed_By"),
			width: 500,
			minChars: 1,
			onSelected: function(result) {
				if (result != null) {
					//var x = result.thumbnail.split("|");
					$("#Signed_By").val(result.id);
					$("#Signed_By_Position").val(result.description);
				}
			}
		});

		$(function() {
			$('#Total,#Subtotal_PO,#Man_Fee').mask('#.##0', {
				reverse: true
			});
		});

		function cekangka() {

			var data1 = $('#Subtotal_PO').val().replace(".", "").replace(".", "").replace(".", "");
			var data2 = $('#Man_Fee').val().replace(".", "").replace(".", "").replace(".", "");


			$('#Total').val(new Intl.NumberFormat(['ban', 'id']).format(parseFloat(data1) + parseFloat(data2)));

		}


		function isValidKey(e) {
			var charCode = e.keyCode || e.which;
			if (charCode == 46)
				return false;

			return true;
		}

		var vault = new dhx.Vault("vault", {
			uploader: {
				target: "upload-ba.php"
			},
			filesLimit: 1

		});

		vault.events.on("uploadComplete", function(obj) {
			$("#file_lists").val(obj[0].name);
		});
		vault.events.on("uploadBegin", function(obj) {
			$("#file_lists").val("");

		});
		vault.events.on("beforeAdd", function(fileItem) {
			if (vault.data.getLength() >= 1) {
				alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
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

		var myCalendar2;
		myCalendar2 = new dhtmlXCalendarObject(["Periode_Tagihan"]);
		myCalendar2.setDateFormat("%Y%m");

		var myCalendar;
		myCalendar = new dhtmlXCalendarObject(["BA_Date"]);
		myCalendar.setDateFormat("%Y-%m-%d");


		ProformaInitiate = new dhtmlXGridObject('ProformaInitiate');
		ProformaInitiate.setImagePath("./dhtmlx/imgs/");
		ProformaInitiate.setHeader("No,PO No,Description,PO Date,Contract No,Amount,Customer No,Customer Name,PIC,PO End Date,Note");
		ProformaInitiate.setInitWidths("60,120,100,120,220,100,100,100,80,150,100,100,100,100");
		ProformaInitiate.setColAlign("center,center,center,center,left,center,center,right,center,center,center,center,left,right");
		ProformaInitiate.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int");
		ProformaInitiate.setColTypes("ro,ed,dhxCalendar,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
		ProformaInitiate.enableSmartRendering(true, 150);
		ProformaInitiate.setNumberFormat("0,000", 5, ".", ",");
		ProformaInitiate.setSkin("dhx_skyblue");
		ProformaInitiate.init();
		refreshdata();

		function refreshdata() {
			ProformaInitiate.clearAndLoad('xml-sales-proforma-attachment-detail?Task_ID=<?php echo $_GET['ID'] ?>');

		}

		function getAllValues() {
			var inputValues = [];
			$('#ProformaBADiv input,select,textarea').each(function() {
				var type = $(this).attr("type");
				if ((type == "checkbox" || type == "radio") && this.checked) {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				} else {
					if (type != "button" && type != "submit") {

						inputValues.push($(this).attr("id") + "=" + $(this).val());
					}
				}
			})

			return inputValues.join('&');
		}

		$("#ProSubmt").click(function() {
			dapat = getAllValues();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},

				url: "sales-proforma-ba-detail2-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {

						setTimeout(function() {
							alert('Data BA  Submitted!');
							location.href = 'sales-proforma-ba';
						}, 1000);

					} else {
						alert(data);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {

					var errorMessage = jqXHR.status + ': ' + jqXHR.statusText
        			 alert('Error - ' + errorMessage);

				}
			});

		});
	</script>