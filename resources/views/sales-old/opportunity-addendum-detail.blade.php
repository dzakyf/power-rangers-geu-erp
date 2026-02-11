<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$priv =  Auth::user()->Priv_ID;
$datahasil = DB::table("opportunity")->where("ID", $_GET['ID'])->first();
$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity'");
$jumlah = count($result);
if ($jumlah > 0) {
	echo "<script>";

	foreach ($result as $row) {

		$kolom = $row->column_name;


		if ($datahasil->$kolom == '0000-00-00') {
			echo "$('#$kolom').val('');\n";
		} else {
			if ($kolom != 'ID') {
				$dataForm = $datahasil->$kolom;

				echo "$('#$kolom').val('$dataForm');\n";
			}
		}
	}
	echo "</script>";
}

$datahasil2 = DB::table("opportunity_detail")->where("Task_ID", $datahasil->Task_ID)->first();
if (!$datahasil2) {
} else {

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

				echo "$('#$kolom2').val('$dataForm2');\n";
			}
		}
		echo "</script>";
	}
}


?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL PO OPPORTUNITY</div>

<div id="AddendumDetailDiv">
	<div class="FormInput_Form_Content">

		<div class="FormInput_left">

			PO Number

		</div>
		<div class="FormInput_right">

			<input type="hidden" id="Task_ID" size="30" value="<?php echo $datahasil->Task_ID ?>" />

			<input type="text" id="PO_No" size="25" />



		</div>
		<div class="FormInput_left">

			PO Initial

		</div>
		<div class="FormInput_right">

			<input type="text" id="PO_Initial" size="30" />




		</div>
		<div class="FormInput_left">

			PO Date

		</div>
		<div class="FormInput_right">
			<input type="hidden" id="ID" size="25" />

			<input type="text" id="PO_Date" size="15" value="<?php echo date("Y-m-d"); ?>" />

		</div>

		<div class="FormInput_left">

			Project Type

		</div>
		<div class="FormInput_right">

			<select id="Project_Type">
				<option>OUTSOURCING</option>
				<option>OUTTASK</option>
				<option>PROJECT</option>
			</select>


		</div>
		<div class="FormInput_left">

			Contract No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Contract_No" size="35" />

		</div>
		<div class="FormInput_left">

			Start Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Start_Date" size="15" value="<?php echo date("Y-m-d"); ?>" />

		</div>
		<div class="FormInput_left">

			End Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="End_Date" size="15" value="<?php echo date("Y-m-d"); ?>" />

		</div>
		<div class="FormInput_left">

			Reference No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Reference_No" size="35" />

		</div>
		<div class="FormInput_left">

			Customer Department

		</div>
		<div class="FormInput_right">

			<input type="text" id="Department" size="35" />

		</div>
		<div class="FormInput_left">

			Site/Location

		</div>
		<div class="FormInput_right">

			<input type="text" id="Alocation" size="55" />

		</div>
		<div class="FormInput_left">

			Subject

		</div>
		<div class="FormInput_right">

			<input type="text" id="Subject" size="50" />

		</div>
		<div class="FormInput_left">

			Description

		</div>
		<div class="FormInput_right">

			<input type="text" id="Description" size="80" />

		</div>
		<div class="FormInput_left">

			Due Days

		</div>
		<div class="FormInput_right">

			<input type="text" id="Due_Days" size="5" value="30" />

		</div>
		<div class="FormInput_left">

			Gross Amount

		</div>
		<div class="FormInput_right">

			Rp. <input type="text" id="Subtotal" onkeydown="return isValidKey(event)" size="20" value="0" />
			<input type="hidden" id="PPN" onkeydown="return isValidKey(event)" size="20" value="1" />
			<input type="hidden" id="PPH" onkeydown="return isValidKey(event)" size="20" value="1" />
		</div>
		<div class="FormInput_left">

			Net Amounts

		</div>
		<div class="FormInput_right">

			Rp. <input type="text" id="Total" onkeydown="return isValidKey(event)" size="25" />

		</div>
		<div class="FormInput_left">

			Uang Muka

		</div>
		<div class="FormInput_right">

			Rp. <input type="text" id="Uang_Muka" onkeydown="return isValidKey(event)" size="25" value="0" />

		</div>
		<div class="FormInput_left">

			Termin

		</div>
		<div class="FormInput_right">

			<select id="Termin">
				<option>ONE TIME CHARGE</option>
				<option>MONTHLY</option>
				<option>YEARLY</option>
				<option>WEEKLY</option>
				<option>QUARTERLY</option>
				<option>2 TIMES</option>
				<option>3 TIMES</option>
			</select>

		</div>

	</div>


</div>
<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

	<input type="button" value="PROCESS ADDENDUM" id="UpdatePO" onclick="UpdatePO();" class="btn btn-info mt-2 mr-2" />

</div>

<script src="{{ asset('jquery.mask.js') }}"></script>

<script>
	$(function() {
		$('#Estimated_Rev,#Uang_Muka,#Total,#Subtotal').mask('#.##0', {
			reverse: true
		});
	});

	function isValidKey(e) {
		var charCode = e.keyCode || e.which;
		if (charCode == 46)
			return false;

		return true;
	}




	function getAllValues2() {
		var inputValues2 = [];
		$('#AddendumDetailDiv input,select,textarea').each(function() {
			if ($(this).attr("id") != 'Source_Information') {
				var type = $(this).attr("type");
				if ((type == "checkbox" || type == "radio") && this.checked) {
					inputValues2.push($(this).attr("id") + "=" + $(this).val());
				} else {
					if (type != "button" && type != "submit") {

						inputValues2.push($(this).attr("id") + "=" + $(this).val());
					}
				}

			} else {

				return false;
			}
		})
		return inputValues2.join('&');

	}

	function UpdatePO() {
		dapat2 = getAllValues2();
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},

			url: "opportunity-addendum-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: dapat2,
			success: function(data, textStatus, jqXHR) {
				if (data == 'success') {
					alert('Data Addendum Updated!');

					setTimeout(function() {

						$("#divlistOpportunity").html('');

						refreshdata();
					}, 1000);

				} else {

				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error");

			}
		});

	}

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["PO_Date", "Start_Date", "End_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");
</script>