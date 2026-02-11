<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


function getRomawi($bln)
{
	switch ($bln) {
		case 1:
			return "I";
			break;
		case 2:
			return "II";
			break;
		case 3:
			return "III";
			break;
		case 4:
			return "IV";
			break;
		case 5:
			return "V";
			break;
		case 6:
			return "VI";
			break;
		case 7:
			return "VII";
			break;
		case 8:
			return "VIII";
			break;
		case 9:
			return "IX";
			break;
		case 10:
			return "X";
			break;
		case 11:
			return "XI";
			break;
		case 12:
			return "XII";
			break;
	}
}

function diffDate($start, $end)
{
	$diff = round(abs(strtotime($end) - strtotime($start)) / 86400 / 30);

	return  $diff;
}


function  nomorBA($inisial)
{
	$th = date("Y");
	$romawi = getRomawi(date("m"));

	if ($th == 2021) {
		$nomorba = DB::table("proforma")->where("BA_Date", "LIKE", "$th%")->where("BA_No", "LIKE", "%599/BA-KSPS%")->orderBy('Dateinsert', 'DESC')->first();
		if ($nomorba) {
			$nomorba = DB::table("proforma")->where("BA_Date", "LIKE", "$th%")->where("BA_No", "LIKE", "%BA-KSPS%")->orderBy('Dateinsert', 'DESC')->first();
			$nomor =  explode("/", $nomorba->BA_No);
			$lanjutan = intval($nomor[0]) + 1;
		} else {
			$lanjutan = 599;
		}
	} else {
		$nomorba = DB::table("proforma")->where("BA_Date", "LIKE", "$th%")->where("BA_No", "LIKE", "%BA-KSPS%")->orderBy('Dateinsert', 'DESC')->first();
		if ($nomorba) {
			$nomor =  explode("/", $nomorba->BA_No);
			$lanjutan = intval($nomor[0]) + 1;
		} else {
			$lanjutan = 1;
		}
	}

	$bano = sprintf("%'.04d", $lanjutan) . "/BA-KSPS/$inisial/$romawi/$th";

	return $bano;
}

function  nomorSurat()
{
	$th = date("Y");
	$romawi = getRomawi(date("m"));
	$nomorba = DB::table("proforma")->where("BA_Date", "LIKE", "$th%")->where("BA_No", "LIKE", "%/KSPS/%")->orderBy('ID', 'DESC')->first();
	if ($nomorba) {
		$nomor =  explode("/", $nomorba->BA_No);
		$lanjutan = intval($nomor[0]) + 1;
	} else {
		$lanjutan = 1;
	}

	$bano = sprintf("%'.04d", $lanjutan) . "/KSPS/1100/$th";

	return $bano;
}


$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$dfp = DB::table("proforma_detail")->where("Proforma_No", $_GET['ID'])->first();
$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
$datahasil2 = DB::table("opportunity_detail")->where("Task_ID", $dfp->Task_ID)->first();


$result2 = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity_detail'");
$jumlah2 = count($result2);
if ($jumlah2 > 0 && $datahasil2->ID != '') {
	echo "<script>";
	foreach ($result2 as $row2) {

		$kolom2 = $row2->column_name;

		if ($kolom2 != 'BA_No' && $kolom2 != 'Total') {

			if ($datahasil2->$kolom2 == '0000-00-00') {
				echo "$('#$kolom').val('');\n";
			} else {

				$dataForm2 = $datahasil2->$kolom2;
				preg_replace("/\r|\n/", "", $dataForm2);
				$dataForm2 = str_replace(array("\r", "\n"), '', $dataForm2);


				echo "
				
				$('#$kolom2').val('$dataForm2');
;\n";
			}
		}
	}
	echo "</script>";
}





if ($datahasil2->Termin == 'MONTHLY') {
	$bagi = diffDate($datahasil2->Start_Date, $datahasil2->End_Date);
	$totaltagihan = round($datahasil2->Total / $bagi, 0);
} else {
	$totaltagihan = round($datahasil2->Total, 0);
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

			Using No BA/Surat

		</div>
		<div class="FormInput_right">

			<select name="penomoran" id="penomoran" onchange="penomoran(event)">
				<option disabled selected>Pilih Tipe Penomoran</option>
				<option value=0>Nomor BA</option>
				<option value=1>Nomor Surat</option>
			</select>
		</div>

		<div class="FormInput_left">

			BA No

		</div>
		<div class="FormInput_right">

			<input type="text" id="BA_No" size="50" />

		</div>
		<div class="FormInput_left">

			Jenis Pekerjaan

		</div>
		<div class="FormInput_right">

			<input type="hidden" value="<?php echo $proforma->ID ?>" id="ID_Proforma" size="80" readonly='readonly' />
			<input type="text" id="Subject" size="80" />

		</div>
		<div class="FormInput_left">

			PO No

		</div>
		<div class="FormInput_right">

			<input type="text" id="PO_No" size="50" readonly='readonly' />

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

			<input type="text" id="BA_Date" size="15" value="<?php echo date("Y-m-d"); ?>" readonly='readonly' />

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

			<input type="text" id="Termin" size="10" readonly="readonly" />

		</div>


		<div class="FormInput_left">

			Invoice To

		</div>
		<div class="FormInput_right">

			<input type="text" id="Invoice_To" size="40" />

		</div>
		<div class="FormInput_left">

			Signed By (USER)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Signed_By" size="35" />

		</div>
		<div class="FormInput_left">

			Signed by Position (USER)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Signed_By_Position" size="40" />

		</div>
		<div class="FormInput_left">

			Signed By (KSPS)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Signed_By_KSPS" size="35" value="SUCI ANDRINI" />

		</div>
		<div class="FormInput_left">

			Signed by Position (KSPS)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Signed_By_Position_KSPS" size="40" value="SENIOR VICE PRESIDENT" />

		</div>

	</div>
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
		var myCalendar;
		myCalendar = new dhtmlXCalendarObject(["BA_Date"]);
		myCalendar.setDateFormat("%Y-%m-%d");

		var myCalendar1;
		myCalendar1 = new dhtmlXCalendarObject(["Periode_Tagihan"]);
		myCalendar1.setDateFormat("%Y%m");


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
						var x = $(this).val();
						if (x.search("&") >= 0) {
							x = x.replace('&', 'gamp;');
						}
						inputValues.push($(this).attr("id") + "=" + x);
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

				url: "sales-proforma-ba-detail-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {

						setTimeout(function() {
							alert('Data BA  Submitted!');
							window.open("badownload?ID=<?php echo $_GET['ID'] ?>");
							location.href = 'sales-proforma-ba';
						}, 1000);

					} else {
						alert(data);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("error");

				}
			});

		});

		function penomoran(e) {


			if (e.target.value == 1) {
				document.getElementById("BA_No").value = '<?= nomorSurat()  ?>';
			} else {
				document.getElementById("BA_No").value = '<?= nomorBA($datahasil2->PO_Initial)  ?>';
			}
		}
	</script>