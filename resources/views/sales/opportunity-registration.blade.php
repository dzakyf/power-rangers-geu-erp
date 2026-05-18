<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location ?? 'GEU-HO';
$Saya =  Auth::user()->username ?? 'admin';

$leadID = 'OPPT/' . $LSP . '/' . date('Ymdhis') . '/' . substr(md5($Saya), 0, 3);
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
			<span class="active">Partner Registration</span>
		</li>
	</ol>
</div>
<div class="card card-body">

	<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">PARTNER INFORMATION</div>

	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">Register As</div>
			<div class="FormInput_right">
				<input type="radio" name="Register_As" id="Register_As_Individual" value="individual" checked /> Individual
				<input type="radio" name="Register_As" id="Register_As_Company" value="company" /> Company
			</div>

			<!-- Individual Section -->
			<div id="IndividualDiv">
				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">1. Personal Information</div>
				<div class="FormInput_left">Full Name</div>
				<div class="FormInput_right"><input type="text" name="Full_Name" id="Full_Name" /></div>

				<div class="FormInput_left">Phone Number</div>
				<div class="FormInput_right"><input type="text" name="Contact" id="Contact" /></div>

				<div class="FormInput_left">Email</div>
				<div class="FormInput_right"><input type="text" name="Email" id="Email" /></div>

				<div class="FormInput_left">Home Address</div>
				<div class="FormInput_right"><input type="text" name="Address" id="Address" /></div>

				<div class="FormInput_left">Gender</div>
				<div class="FormInput_right">
					<input type="radio" name="Gender" id="Gender_1" value="M" /> Male
					<input type="radio" name="Gender" id="Gender_2" value="F" /> Female
				</div>

				<div class="FormInput_left">Highest Education</div>
				<div class="FormInput_right">
					<input type="radio" name="Last_Education" id="Last_Education_1" value="1" />
					< High School
						<input type="radio" name="Last_Education" id="Last_Education_2" value="2" /> High School
					<input type="radio" name="Last_Education" id="Last_Education_3" value="3" /> Diploma
					<input type="radio" name="Last_Education" id="Last_Education_4" value="4" /> Bachelor
					<input type="radio" name="Last_Education" id="Last_Education_5" value="5" /> Postgraduate
				</div>

				<div class="FormInput_left">How did you learn about the partnership program?</div>
				<div class="FormInput_right">
					<input type="radio" name="Source_Instagram" id="Source_Instagram" value="Instagram" /> Instagram
					<input type="radio" name="Source_Facebook" id="Source_Facebook" value="Facebook" /> Facebook
					<input type="radio" name="Source_TikTok" id="Source_TikTok" value="TikTok" /> TikTok
					<input type="radio" name="Source_Website" id="Source_Website" value="Website" /> Website
					<input type="radio" name="Source_Event" id="Source_Event" value="Event" /> Exhibition/Event
				</div>

				<div class="FormInput_left">Sales PIC</div>
				<div class="FormInput_right">
					<select name="PIC" id="PIC">
						<option value="">-- Select Sales PIC --</option>
						@foreach($pic as $p)
						<option value="{{ $p->username }}">{{ $p->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">2. Employment Information</div>
				<div class="FormInput_left">Employment Status</div>
				<div class="FormInput_right">
					<input type="radio" name="Employment_Status" id="Employment_Status_1" value="1" /> Private (Employee)
					<input type="radio" name="Employment_Status" id="Employment_Status_2" value="2" /> Civil Servant
					<input type="radio" name="Employment_Status" id="Employment_Status_3" value="3" /> Entrepreneur
				</div>

				<div class="FormInput_left">Company Name</div>
				<div class="FormInput_right"><input type="text" name="Company_Name" id="Company_Name" /></div>

				<div class="FormInput_left">Business Field</div>
				<div class="FormInput_right"><input type="text" name="Business_Field" id="Business_Field" /></div>

				<div class="FormInput_left">Length of Work</div>
				<div class="FormInput_right">
					<input type="text" name="Length_of_Work" id="Length_of_Work" />
					<input type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_1" value="1" /> Years
					<input type="radio" name="Length_of_Work_Unit" id="Length_of_Work_Unit_2" value="2" /> Months
				</div>

				<div class="FormInput_left">Position</div>
				<div class="FormInput_right"><input type="text" name="Position" id="Position" /></div>

				<div class="FormInput_left">Company Address</div>
				<div class="FormInput_right"><input type="text" name="Company_Address" id="Company_Address" /></div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">3. Financial Information</div>
				<div class="FormInput_left">Total gross income (monthly)</div>
				<div class="FormInput_right"><input type="text" name="Gross_Monthly" id="Gross_Monthly" /></div>

				<div class="FormInput_left">Total gross income (annual)</div>
				<div class="FormInput_right"><input type="text" name="Gross_Annual" id="Gross_Annual" /></div>

				<div class="FormInput_left">Source of funds for partnership</div>
				<div class="FormInput_right">
					<input type="checkbox" name="Source_Personal_Savings" id="Source_Personal_Savings" value="savings" /> Personal Savings
					<input type="checkbox" name="Source_Inheritance" id="Source_Inheritance" value="inheritance" /> Inheritance
					<input type="checkbox" name="Source_Business_Income" id="Source_Business_Income" value="business" /> Business Income
					<input type="checkbox" name="Source_Loan" id="Source_Loan" value="loan" /> Loan
					<input type="checkbox" name="Source_Pension" id="Source_Pension" value="pension" /> Pension
				</div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">4. Partnership Information</div>
				<div class="FormInput_left">Do you currently run a partnership / business with warehouses? How many?</div>
				<div class="FormInput_right">
					<input type="radio" name="Warehouse_Count" id="Warehouse_Count_0" value="0" /> 0
					<input type="radio" name="Warehouse_Count" id="Warehouse_Count_1" value="1" /> 1
					<input type="radio" name="Warehouse_Count" id="Warehouse_Count_2" value="2" /> 2
					<input type="radio" name="Warehouse_Count" id="Warehouse_Count_3" value=">2" /> >2
				</div>

				<div class="FormInput_left">Motivation to join the partnership</div>
				<div class="FormInput_right"><textarea name="Motivation" id="Motivation" rows="3"></textarea></div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">5. Location Information</div>
				<div class="FormInput_left">Province</div>
				<div class="FormInput_right"><select name="Province" id="Province">
						<option value="">-- Select --</option>
					</select></div>

				<div class="FormInput_left">City / District</div>
				<div class="FormInput_right"><select name="City" id="City">
						<option value="">-- Select --</option>
					</select></div>

				<div class="FormInput_left">Location Status</div>
				<div class="FormInput_right">
					<input type="radio" name="Location_Status" id="Location_Own" value="own" /> Own
					<input type="radio" name="Location_Status" id="Location_Rent" value="rent" /> Rent
					<input type="radio" name="Location_Status" id="Location_NoTarget" value="notarget" /> No target location
				</div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">Partnership Questions</div>
				<div class="FormInput_left">1. Willing to comply with business standardization?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q1" id="Q1_Yes" value="Yes" /> Yes
					<input type="radio" name="Q1" id="Q1_No" value="No" /> No
				</div>

				<div class="FormInput_left">2. Willing to support growth targets for Hub GEU Express?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q2" id="Q2_Yes" value="Yes" /> Yes
					<input type="radio" name="Q2" id="Q2_No" value="No" /> No
				</div>

				<div class="FormInput_left">3. Aware that partnership carries business risk?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q3" id="Q3_Yes" value="Yes" /> Yes
					<input type="radio" name="Q3" id="Q3_No" value="No" /> No
				</div>

				<div class="FormInput_left">4. Willing to follow investment rules?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q4" id="Q4_Yes" value="Yes" /> Yes
					<input type="radio" name="Q4" id="Q4_No" value="No" /> No
				</div>

				<div class="FormInput_left">5. Willing not to run similar competing business?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q5" id="Q5_Yes" value="Yes" /> Yes
					<input type="radio" name="Q5" id="Q5_No" value="No" /> No
				</div>

				<div class="FormInput_left">6. Willing to sign partnership contract?</div>
				<div class="FormInput_right">
					<input type="radio" name="Q6" id="Q6_Yes" value="Yes" /> Yes
					<input type="radio" name="Q6" id="Q6_No" value="No" /> No
				</div>

				<div class="FormInput_left">Package Options</div>
				<div class="FormInput_right">
					<input type="radio" name="Package" id="Package_1" value="option1" /> Option 1
					<input type="radio" name="Package" id="Package_2" value="option2" /> Option 2
					<div style="color:red; font-size:12px;">Note: Option contains details such as partnership down payment and account number.</div>
				</div>

				<div class="FormInput_left">Supporting Documents (attach)</div>
				<div class="FormInput_right">
					<input type="checkbox" name="Doc_KTP" id="Doc_KTP" value="KTP" /> KTP
					<input type="checkbox" name="Doc_NPWP" id="Doc_NPWP" value="NPWP" /> NPWP
					<input type="checkbox" name="Doc_Others" id="Doc_Others" value="Others" /> Others
				</div>
			</div>

			<!-- Company Section -->
			<div id="CompanyDiv" style="display:none;">
				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">1. Company Information</div>
				<div class="FormInput_left">Company Name</div>
				<div class="FormInput_right"><input type="text" name="Company_Official_Name" id="Company_Official_Name" /></div>

				<div class="FormInput_left">Legal Form</div>
				<div class="FormInput_right">
					<input type="checkbox" name="Legal_PT" id="Legal_PT" value="PT" /> PT
					<input type="checkbox" name="Legal_CV" id="Legal_CV" value="CV" /> CV
					<input type="checkbox" name="Legal_Coop" id="Legal_Coop" value="Cooperative" /> Cooperative
					<input type="checkbox" name="Legal_Foundation" id="Legal_Foundation" value="Foundation" /> Foundation
					<input type="checkbox" name="Legal_Other" id="Legal_Other" value="Other" /> Other
				</div>

				<div class="FormInput_left">Primary Business</div>
				<div class="FormInput_right"><input type="text" name="Primary_Business" id="Primary_Business" /></div>

				<div class="FormInput_left">Year Established</div>
				<div class="FormInput_right"><input type="text" name="Year_Established" id="Year_Established" /></div>

				<div class="FormInput_left">Head Office Address</div>
				<div class="FormInput_right"><input type="text" name="Head_Office_Address" id="Head_Office_Address" /></div>

				<div class="FormInput_left">Company Website</div>
				<div class="FormInput_right"><input type="text" name="Company_Website" id="Company_Website" /></div>

				<div class="FormInput_left">Company NPWP</div>
				<div class="FormInput_right"><input type="text" name="Company_Tax_ID" id="Company_Tax_ID" /></div>

				<div class="FormInput_left">NIB</div>
				<div class="FormInput_right"><input type="text" name="Company_NIB" id="Company_NIB" /></div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">2. PIC (Person In Charge)</div>
				<div class="FormInput_left">PIC Name</div>
				<div class="FormInput_right"><input type="text" name="PIC_Name" id="PIC_Name" /></div>

				<div class="FormInput_left">PIC Position</div>
				<div class="FormInput_right"><input type="text" name="PIC_Position" id="PIC_Position" /></div>

				<div class="FormInput_left">PIC Phone</div>
				<div class="FormInput_right"><input type="text" name="PIC_Phone" id="PIC_Phone" /></div>

				<div class="FormInput_left">PIC Email</div>
				<div class="FormInput_right"><input type="text" name="PIC_Email" id="PIC_Email" /></div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">3. Company Financials</div>
				<div class="FormInput_left">Company Scale</div>
				<div class="FormInput_right">
					<input type="radio" name="Company_Scale" id="Company_Scale_1" value="micro" /> Micro
					<input type="radio" name="Company_Scale" id="Company_Scale_2" value="small" /> Small
					<input type="radio" name="Company_Scale" id="Company_Scale_3" value="medium" /> Medium
					<input type="radio" name="Company_Scale" id="Company_Scale_4" value="large" /> Large
				</div>

				<div class="FormInput_left">Estimated annual revenue</div>
				<div class="FormInput_right"><input type="text" name="Estimated_Revenue" id="Estimated_Revenue" /></div>

				<div class="FormInput_left">Source of funds</div>
				<div class="FormInput_right">
					<input type="checkbox" name="Company_Fund_Business" id="Company_Fund_Business" value="business" /> Business
					<input type="checkbox" name="Company_Fund_Investor" id="Company_Fund_Investor" value="investor" /> Investor
					<input type="checkbox" name="Company_Fund_Loan" id="Company_Fund_Loan" value="loan" /> Loan
					<input type="checkbox" name="Company_Fund_CSR" id="Company_Fund_CSR" value="csr" /> CSR
					<input type="checkbox" name="Company_Fund_Private" id="Company_Fund_Private" value="private" /> Private
				</div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">4. Partnership Information</div>
				<div class="FormInput_left">Number of warehouses / operational outlets planned</div>
				<div class="FormInput_right">
					<input type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_1" value="1" /> 1
					<input type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_2" value="2" /> 2
					<input type="radio" name="Company_Warehouse_Count" id="Company_Warehouse_Count_3" value=">2" /> >2
				</div>

				<div class="FormInput_left">Has experience in partnership business?</div>
				<div class="FormInput_right"><textarea name="Company_Experience" id="Company_Experience" rows="3"></textarea></div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">5. Operational Location</div>
				<div class="FormInput_left">Does the company have operational locations?</div>
				<div class="FormInput_right">
					<input type="radio" name="Company_Has_Location" id="Company_Has_Location_Yes" value="yes" /> Yes
					<input type="radio" name="Company_Has_Location" id="Company_Has_Location_No" value="no" /> No
				</div>

				<div class="FormInput_left">Location type</div>
				<div class="FormInput_right">
					<input type="checkbox" name="Loc_Warehouse" id="Loc_Warehouse" value="warehouse" /> Warehouse
					<input type="checkbox" name="Loc_Store" id="Loc_Store" value="store" /> Kiosk / Store
					<input type="checkbox" name="Loc_Own" id="Loc_Own" value="own" /> Own
					<input type="checkbox" name="Loc_Rent" id="Loc_Rent" value="rent" /> Rent
				</div>

				<div class="SectionTitle" style="color:#699;background-image:url(hdr_back.gif);">Partnership Questions</div>
				<div class="FormInput_left">1. Will the company follow operational standards and policies?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q1" id="C_Q1_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q1" id="C_Q1_No" value="No" /> No
				</div>

				<div class="FormInput_left">2. Will the company commit to support growth targets?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q2" id="C_Q2_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q2" id="C_Q2_No" value="No" /> No
				</div>

				<div class="FormInput_left">3. Does the company understand partnership risks?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q3" id="C_Q3_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q3" id="C_Q3_No" value="No" /> No
				</div>

				<div class="FormInput_left">4. Will the company meet investment obligations?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q4" id="C_Q4_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q4" id="C_Q4_No" value="No" /> No
				</div>

				<div class="FormInput_left">5. Will the company avoid running similar competing businesses?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q5" id="C_Q5_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q5" id="C_Q5_No" value="No" /> No
				</div>

				<div class="FormInput_left">6. Will the company sign the cooperation agreement (MOU)?</div>
				<div class="FormInput_right">
					<input type="radio" name="C_Q6" id="C_Q6_Yes" value="Yes" /> Yes
					<input type="radio" name="C_Q6" id="C_Q6_No" value="No" /> No
				</div>

				<div class="FormInput_left">Package Options</div>
				<div class="FormInput_right">
					<input type="radio" name="Company_Package" id="Company_Package_1" value="option1" /> Option 1
					<input type="radio" name="Company_Package" id="Company_Package_2" value="option2" /> Option 2
					<div style="color:red; font-size:12px;">Note: Option contains details such as partnership down payment and account number.</div>
				</div>

				<div class="FormInput_left">Supporting Documents (attach)</div>
				<div class="FormInput_right">
					<input type="checkbox" name="C_Doc_Akta" id="C_Doc_Akta" value="akta" /> Deed of Establishment / Amendments
					<input type="checkbox" name="C_Doc_NIB" id="C_Doc_NIB" value="nib" /> NIB
					<input type="checkbox" name="C_Doc_NPWP" id="C_Doc_NPWP" value="npwp" /> NPWP
					<input type="checkbox" name="C_Doc_Others" id="C_Doc_Others" value="others" /> Others
				</div>
			</div>

			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

	<script>
		// toggle sections based on Register As
		$(function() {
			$('input[name="Register_As"]').change(function() {
				var val = $(this).val();
				if (val === 'company') {
					$('#IndividualDiv').hide();
					$('#CompanyDiv').show();
				} else {
					$('#CompanyDiv').hide();
					$('#IndividualDiv').show();
				}
			});

			// initialize mask if field exists
			if ($('#Estimated_Revenue').length) {
				$('#Estimated_Revenue').mask('#,##0.00', {
					reverse: true
				});
			}
		});

		function isValidKey(e) {
			var charCode = e.keyCode || e.which;
			if (charCode == 46) return false;
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
						alert('Opportunity data submitted successfully!');
						location.href = "sales-opportunity-new";
					} else {
						alert('Failed to submit opportunity!');
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("Internal error. Please try again.");

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
					if ($(this).attr('id')) {
						inputValues.push($(this).attr("id") + "=" + $(this).val());
					}
				} else if ($(this).is("textarea")) {
					inputValues.push($(this).attr("id") + "=" + $(this).val());
				}
			})
			return inputValues.join('&');
		}
	</script>
	@endsection