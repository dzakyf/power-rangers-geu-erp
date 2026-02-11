<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

// $leadID = "L/BP/".date('Y')."/".sprintf('%02d',date('m'))."/".strtoupper(substr(uniqid(), 0, 4));

$leadID = 'OPPT/'.$LSP.'/'.date('Ymdhis').'/'.substr(md5($Saya),0,3);
$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();
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
			<span class="active">Registration Partner</span>
		</li>
	</ol>
</div>

<div class="row">

	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PARTNER SUBJECT</div>

	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				Daftar Sebagai

			</div>
			<div class="FormInput_right">
                <input type="radio" name="Daftar_Sebagai" id="Daftar_Sebagai_1" value="1" /> Perorangan
                <input type="radio" name="Daftar_Sebagai" id="Daftar_Sebagai_2" value="2" /> Perusahaan

			</div>
            <div id="IndividuDiv">
	            <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">1. Data Diri</div>
                <div class="FormInput_left">
                    Full Name
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Full_Name" id="Full_Name" />
                </div>
                <div class="FormInput_left">
                    No Hp
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Contact" id="Contact" />
                </div>
                <div class="FormInput_left">
                    Email
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Email" id="Email" />
                </div>
                <div class="FormInput_left">
                    Address
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Address" id="Address" />
                </div>
                <div class="FormInput_left">
                    Gender
                </div>
                <div class="FormInput_right">
                    <input type="radio" name="Gender" id="Gender_1" value="M" /> Male
                    <input type="radio" name="Gender" id="Gender_2" value="F" /> Female
                </div>
                <div class="FormInput_left">
                    Last Education
                </div>
                <div class="FormInput_right">
                    <input type="radio" name="Last_Education" id="Last_Education_1" value="1" /> <SMU
                    <input type="radio" name="Last_Education" id="Last_Education_2" value="2" /> SMU
                    <input type="radio" name="Last_Education" id="Last_Education_3" value="3" /> D3
                    <input type="radio" name="Last_Education" id="Last_Education_4" value="4" /> S1
                    <input type="radio" name="Last_Education" id="Last_Education_5" value="5" /> >S1
                </div>
                <div class="FormInput_left">
                    Sales PIC
                </div>
                <div class="FormInput_right">
                    <select name="PIC" id="PIC">
                        <option value="">-- Pilih Sales PIC --</option>
                        @foreach($pic as $p)
                            <option value="{{ $p->username }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
	            <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">2. Informasi Pekerjaan</div>
                <div class="FormInput_left">
                    Employment Status
                </div>
                <div class="FormInput_right">
                    <input type="radio" name="Employment_Status" id="Employment_Status_1" value="1" /> Private (Karyawan Swasta)
                    <input type="radio" name="Employment_Status" id="Employment_Status_2" value="2" /> Civil Servant (PNS/Aparat)
                    <input type="radio" name="Employment_Status" id="Employment_Status_3" value="3" /> Entrepreneur (Wiraswasta)
                </div>
                <div class="FormInput_left">
                    Company Name
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Company_Name" id="Company_Name" />
                </div>
                <div class="FormInput_left">
                    Business Field
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Business_Field" id="Business_Field" />
                </div>
                <div class="FormInput_left">
                    Length of Work
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Length_of_Work" id="Length_of_Work" />
                    <input type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_1" value="1" /> Years
                    <input type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_2" value="2" /> Months
                </div>
                <div class="FormInput_left">
                    Position
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Position" id="Position" />
                </div>
                <div class="FormInput_left">
                    Company Address
                </div>
                <div class="FormInput_right">
                    <input type="text" name="Company_Address" id="Company_Address" />
                </div>
	            <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">3. Informasi Keuangan</div>
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
			url: "sales-opportunity-registration-submit?_token={{ csrf_token() }}",
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