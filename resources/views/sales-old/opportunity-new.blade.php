<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Sales</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Data</span>
		</li>
	</ol>
</div>

<div class="row">

	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OPPORTUNITY SUBJECT</div>

	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				Source Information

			</div>
			<div class="FormInput_right">
				<select id='Source_Information'>
					<option>WEBSITE</option>
					<option>AGENT</option>
					<option>COURIER</option>
					<option>CUSTOMER SERVICES</option>
					<option>BLITZ</option>
					<option>OTHER</option>
				</select>

			</div>
			<div class="FormInput_left">

				Source Reference

			</div>
			<div class="FormInput_right">

				<input type="text" id="Source_Reference" size="40" />

			</div>
			<div class="FormInput_left">

				Subject

			</div>
			<div class="FormInput_right">

				<input type="text" id="Oppor_Subject" size="50" onchange="this.value.toUpper()" />

			</div>
			<div class="FormInput_left">

				Company

			</div>
			<div class="FormInput_right">

				<input type="text" id="Account_No" size="20" /> :: <input type="text" disabled="disabled" id="Company_Name" size="45" />

			</div>
			<div class="FormInput_left" style="height:80px;">

				Address

			</div>
			<div class="FormInput_right" style="height:80px;">

				<textarea cols="100" rows="4" id="Address"></textarea>

			</div>
			<div class="FormInput_left">

				City

			</div>
			<div class="FormInput_right">

				<input type="text" id="City" size="35" />

			</div>
			<div class="FormInput_left">

				Phone

			</div>
			<div class="FormInput_right">

				<input type="text" id="Phone" size="35" />

			</div>
			<div class="FormInput_left">

				Ext

			</div>
			<div class="FormInput_right">

				<input type="text" id="Ext" size="35" />

			</div>



			<div class="FormInput_left">

				Email

			</div>
			<div class="FormInput_right">

				<input type="text" id="Email" size="35" />

			</div>

			<div class="FormInput_left">

				PIC

			</div>
			<div class="FormInput_right">
				<input type="text" id="PIC" size="30" />
			</div>
			<div class="FormInput_left">

				PIC Phone

			</div>
			<div class="FormInput_right">

				<input type="text" id="PIC_Phone" size="30" />


			</div>
			<div class="FormInput_left">

				PIC Function

			</div>
			<div class="FormInput_right">

				<input type="text" id="PIC_Function" size="40" />


			</div>

			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);"> OPPORTUNITY DESCRIPTION </div>
			<div class="FormInput_left">

				Reference

			</div>
			<div class="FormInput_right">

				<input type="text" id="Reference" size="50" />


			</div>

			<div class="FormInput_left">

				Priority

			</div>
			<div class="FormInput_right">
				<select id='Priority'>
					<option>NORMAL</option>
					<option>HIGH</option>
					<option>HIGHEST</option>
					<option>LOW</option>
					<option>LOWEST</option>
				</select>

			</div>


			<div class="FormInput_left">

				Espectation Revenue

			</div>
			<div class="FormInput_right">

				<input type="text" id="Estimated_Rev" onkeydown="return isValidKey(event)" value="0" size="30" />


			</div>
			<div class="FormInput_left">

				Estimated Closing Date

			</div>
			<div class="FormInput_right">

				<input type="text" id="Expected_Closing_Date" size="10" />


			</div>

			<div class="FormInput_left">

				Category

			</div>
			<div class="FormInput_right">

				<select id='Category'>
					<option value=""></option>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM industry_type ORDER BY Industry_Type"));
					foreach ($q as $fa) {

						echo "<option>$fa->Industry_Type</option>";
					}
					?>
				</select>

			</div>

			<div class="FormInput_left">

				Note

			</div>
			<div class="FormInput_right" style="height:80px;">
				<textarea id="Note" cols="100" rows="2"></textarea>
			</div>
			<div class="FormInput_left">

				Sales

			</div>
			<div class="FormInput_right">

				<select id='Created_By'>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM sales_person ORDER BY Sales_Name"));
					foreach ($q as $fax) {
						$fa = DB::table("users")->where("Emp_ID", $fax->NIK)->first();
						if ($fa) {
							echo "<option value='$fa->username'>$fa->Name :: $fa->Emp_ID</option>";
						}
					}
					?>
				</select>

			</div>
		</div>
		<div id="divtom">
			<input type="button" id="DelCustDetail" NAME="DelCustDetail" value="Reset" class="btn btn-danger mt-2 mr-2" />
			<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
	$(function() {
		$('#Estimated_Rev').mask('#,##0.00', {
			reverse: true
		});
	});

	function isValidKey(e) {
		var charCode = e.keyCode || e.which;
		if (charCode == 46)
			return false;

		return true;
	}
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject("Expected_Closing_Date");
	myCalendar.setDateFormat("%Y-%m-%d");


	$("#Account_No").coolautosuggest({
		url: "search-customer?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#Account_No"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {
				var x = result.thumbnail.split("|");

				$("#Account_No").val(result.id);
				$("#Company_Name").val(result.data);
				$("#Address").val(x[8]);
				$("#City").val(x[7]);
				$("#Email").val(x[2]);
				$("#PIC").val(x[3]);
				$("#PIC_Phone").val(x[4]);
				$("#PIC_Function").val(x[5]);
			}
		}
	});
	$('#DelCustDetail').click(function() {


		location.href = "sales-opportunity-new";

	})
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
@endsection