<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Name =  Auth::user()->Name;
$NIK =  Auth::user()->Emp_ID;
$Priv = Auth::user()->Department;
$sayax = md5($Saya);
$sayax = substr($sayax, -5);
$SR_No = "SR/" . $LSP . "/$sayax/" . date("mdhis");
if ($Saya != 'admin'){
	$result = DB::table('pum_karyawan')->where("Cust_ID", $NIK)->first();
	$cust = $result->Cust_ID;
	$nm = $result->Name;
	$dept = $result->Department;
}else{
	$cust = $Saya;
	$nm = $Name;
	$dept = $Priv;
}
?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Assets And Inventory</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Inventory</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Stock Request</span>
		</li>
	</ol>
</div>

<div class="row">

	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">NEW STOCK REQUEST</div>
	<div id="ContentPagex">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				Date Request

			</div>
			<div class="FormInput_right">

				<input type="text" id="Date_Request" size="15" value="<?php echo date('Y-m-d'); ?>" />
				<input type="hidden" id="SR_No" size="15" value="<?php echo $SR_No; ?>" />

			</div>


			<div class="FormInput_left">

				User Request

			</div>
			<div class="FormInput_right">

				<input type="text" size="10" id="User_Responsibility" value="<?php echo $cust ?>" readonly="readonly" disabled="disabled" /> :: <input type="text" readonly="readonly" size="30" id="User_Request_Name" value="<?php echo $nm ?>" disabled="disabled" />

			</div>

			<div class="FormInput_left">

				Department

			</div>
			<div class="FormInput_right">
				<?php if ($Priv == "PURCHASING" || $Saya == "admin") : ?>
					<select id='Department'>
						<?php
						$q = DB::select(DB::raw("SELECT *  FROM master_department"));
						foreach ($q as $fa) {

							echo "<option value='$fa->Initial'>$fa->Department -  $fa->Initial</option>";
						}
						?>
					</select>
				<?php else : ?>
					<input type="text" id="Department" size="20" readonly="readonly" value="<?php echo $dept ?>" />
				<?php endif; ?>
				<input type="hidden" value="INVENTORY" id="Category">

			</div>

			<div class="FormInput_left">

				Request To

			</div>
			<div class="FormInput_right">
				<select id='WH_Dest'>
					<?php
					$qm = DB::table("gudang_list")->get();
					foreach ($qm as $fam) {

						echo "<option value='$fam->Gudang_ID'>$fam->Description</option>";
					}
					?>
				</select>


			</div>
			<div class="FormInput_left">

				FR No

			</div>
			<div class="FormInput_right">


				<input type="text" size="40" id="Reference_No">

			</div>

			<div class="FormInput_left">

				Category

			</div>
			<div class="FormInput_right">
				<select id='Sub_Inventory'>
					<option value=""></option>
					<?php
					$fpp = DB::select(DB::raw("SELECT * FROM master_product_sub_sub_group WHERE Product_Group='CONSUMABLE' ORDER BY Sub_Group"));
					foreach ($fpp as $fkm) {

						echo "<option value='$fkm->Sub_Sub_Group'>$fkm->Sub_Sub_Group # $fkm->Initial</option>";
					}
					?>
				</select>

			</div>

			<div class="FormInput_left">

				Date Pick Up

			</div>
			<div class="FormInput_right">

				<input type="text" id="Date_Pickup" readonly="readonly" size="15" value="<?php echo date('Y-m-d'); ?>" />

			</div>

			<div class="FormInput_left">

				Note

			</div>
			<div class="FormInput_right" style="height:80px;">
				<textarea id="Note" cols="100" rows="2"></textarea>

			</div>
			<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<div id="divtom">
				<input type="button" id="AddPRNewDetail" NAME="AddPRNewDetail" value="Add New" />
				<input type="button" id="DelPRNewDetail" NAME="DelPRNewDetail" value="Delete" />
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit" />
			</div>



		</div>
	</div>
</div>

<script>
	$("#User_Responsibility").coolautosuggest({
		url: "finance-bank-advance-search-user?chars=",
		showThumbnail: false,
		showDescription: true,
		idField: $("#User_Responsibility"),
		width: 500,
		minChars: 1,
		onSelected: function(result) {
			if (result != null) {

				var Description = result.description;
				Expr = Description.split('<br>');
				$("#User_Responsibility_ID").val(result.id);
				$("#User_Responsibility").val(result.thumbnail);
				$("#User_Request_Name").val(result.data);
				$("#Department").val(Expr[1]);

			}
		}
	});
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Date_Pickup"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	var winPR;
	var WPR;

	function WindoWPR(text, url, width, height) {
		winPR = new dhtmlXWindows();

		winPR.attachViewportTo('PagesContent');
		WPR = winPR.createWindow("WPR", 0, 0, width, height);
		WPR.keepInViewport(false);
		WPR.setText(text);
		WPR.attachURL(url, true);
		WPR.button("minmax1").hide();
		WPR.button("park").hide();
		WPR.centerOnScreen();
		WPR.allowMove();
	}


	PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
	PRNewDetail.setImagePath("./dhtmlx/imgs/");
	PRNewDetail.setHeader("No,Category,Product Code,Product Desc,Qty,Specification,Unit,Current Stock,Keterangan,Prices");
	PRNewDetail.setInitWidths("50,100,100,auto,200,200,100,0,200,0");
	PRNewDetail.setColTypes("ro,ro,ro,ro,edn,ro,ro,ro,txt,ro");
	PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,right");
	PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
	PRNewDetail.setEditable(true);
	PRNewDetail.setNumberFormat("0,000.00", 4, ".", ",");
	PRNewDetail.enableKeyboardSupport(true);
	PRNewDetail.setSkin("dhx_skyblue");
	PPRNewDetail = new dataProcessor("inventory-request-update?");
	PPRNewDetail.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		payload: {
			"SR_No": "<?php echo $SR_No ?>"
		}

	})

	PPRNewDetail.setTransactionMode("POST", true);
	PPRNewDetail.setUpdateMode("Off");
	PPRNewDetail.init(PRNewDetail);
	PRNewDetail.init();

	$("#AddPRNewDetail").click(function() {
		WindoWPR("Product Lists", "inventory-request-product-list", 1020, 420);


	})

	$("#DelPRNewDetail").click(function() {

		PRNewDetail.deleteSelectedRows();

	})




	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "inventory-request-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					PPRNewDetail.sendData();
					alert('Data Inventory Request Submitted with No <?php echo $SR_No ?>!');
					setTimeout(function() {
						location.href = "inventory-request";
					}, 2000);
				} else {
					alert('Inventory Request Failed to Submit!');
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
@endsection