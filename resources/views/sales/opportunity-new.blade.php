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
			<span class="active">New Data (Leads)</span>
		</li>
	</ol>
</div>

<div class="row">

	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OPPORTUNITY SUBJECT</div>

	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				Lead ID

			</div>
			<div class="FormInput_right">
                <input type="text" name="Task_ID" id="Task_ID" value="{{ $leadID }}" readonly />

			</div>
			<div class="FormInput_left">

				Date

			</div>
			<div class="FormInput_right">
                <input type="text" name="Date" id="Date" value="{{ date('Y-m-d') }}" readonly />

			</div>
			<div class="FormInput_left">

				Full Name

			</div>
			<div class="FormInput_right">
                <input type="text" name="Full_Name" id="Full_Name" />

			</div>
			<div class="FormInput_left">

				Active Phone Number

			</div>
			<div class="FormInput_right">
                <input type="text" name="Phone_Number" id="Phone_Number" />

			</div>
			<div class="FormInput_left">

				Partnership Interest

			</div>
			<div class="FormInput_right">
                <input type="radio" name="Partnership_Interest" id="Partnership_Interest_1" value="1" /> Opsi 1
                <input type="radio" name="Partnership_Interest" id="Partnership_Interest_2" value="2" /> Opsi 2

			</div>
			<div class="FormInput_left">

				Capital Estimation (Modal/RP)

			</div>
			<div class="FormInput_right">
                <input type="radio" name="Capital_Estimation" id="Capital_Estimation_1" value="1" /> <50 Million
                <input type="radio" name="Capital_Estimation" id="Capital_Estimation_2" value="2" /> 100 - 200 Million
                <input type="radio" name="Capital_Estimation" id="Capital_Estimation_3" value="3" /> 200 - 300 Million
                <input type="radio" name="Capital_Estimation" id="Capital_Estimation_4" value="4" /> >300 Million

			</div>
		</div>
		<div id="divtom">
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