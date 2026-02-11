<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$NIK =  Auth::user()->Emp_ID;

$dpt =  Auth::user()->Department;
$date = date('Y-m');
$query = DB::select(DB::raw("SELECT COUNT(ID) FROM purchase_request WHERE Location = '$LSP' and PR_Date LIKE '$date%'"));
$NoR = $query[0] + 1;
if ($NoR < 10) $NoR = "0$NoR";
$dateR = date('Ym');
$PR_No = strtoupper("PR/" . $LSP . "/$dateR-$NoR-" . substr(round(1) . md5(date('c')), -5));

?>
@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>PROCUREMENT</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">PURCHASING REQUEST</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Request</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				PR No

			</div>
			<div class="FormInput_right">

				<input type="text" id="PR_No" size="30" readonly="readonly" value="<?php echo $PR_No; ?>" />

			</div>
			<div class="FormInput_left">

				Date Request

			</div>
			<div class="FormInput_right">

				<input type="text" id="PR_Date" readonly="readonly" size="15" value="<?php echo date('Y-m-d'); ?>" />

			</div>
			<div class="FormInput_left">

				User Request

			</div>
			<div class="FormInput_right">

				<input type="text" id="User_Request" size="15" value="<?php echo $NIK ?>" readonly="readonly" /> &nbsp;&nbsp;
				<input type="text" id="User_Request_Name" size="40" value="<?php echo $Saya ?>" readonly="readonly" />

			</div>

			<!---->

			<div class="FormInput_left">

				PIC to Receive

			</div>
			<div class="FormInput_right">

				<input type="text" id="PIC" size="40" value="" required/> <i>(Penerima Barang)</i>

			</div>


			<div class="FormInput_left" style="height:60px;">

				PIC Address

			</div>
			<div class="FormInput_right" style="height:60px;">

				<textarea id="PIC_Address" rows="2" cols="55" required></textarea>


			</div>
			<div class="FormInput_left">

				PIC Phone

			</div>
			<div class="FormInput_right">

				<input type="text" id="PIC_Phone" size="30" required/>


			</div>



			<div class="FormInput_left">

				Dept. (Budgeting)

			</div>
			<div class="FormInput_right">


				<select id="Department" class="" style="width:250px;">
					<?php
					$Query = DB::table("master_department")->get();
					foreach ($Query as $Data) {
						echo "<option value='$Data->Initial'>$Data->Department - $Data->Initial_Code - $Data->Initial</option>";
					}
					?>
				</select>

			</div>


			<div class="FormInput_left">

				Category

			</div>
			<div class="FormInput_right">

				<select id="Category" onchange="loadSubCat(this.value);">
					<option value="">Select</option>
					<option>ASSET</option>
					<option>SERVICES</option>
					<option>CONSUMABLE</option>
				</select>

			</div>
			<div class="FormInput_left">

				Group Category

			</div>
			<div class="FormInput_right">
				<div id="SubCat">
					&nbsp; None

				</div>

			</div>
			<div class="FormInput_left">

				Sub Category

			</div>
			<div class="FormInput_right">
				<div id="SubCat2">
					&nbsp; None

				</div>

			</div>
			<div class="FormInput_left">

				References/FPB (Manual)

			</div>
			<div class="FormInput_right">

				<input type="text" id="Reference" size="40" />

			</div>
			<div class="FormInput_left">

				Estimated Budget

			</div>
			<div class="FormInput_right">

				<input type="text" id="Estimated_Budget" size="10" value="0" required/>

			</div>
			<div class="FormInput_left">

				Note

			</div>
			<div class="FormInput_right" style="height:80px;">
				<textarea id="Note" cols="100" rows="2" required></textarea>

			</div>
			<div id="gridPRNewDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<div id="divtom">
				<input type="button" id="AddPRNewDetail" class="btn btn-info mt-2 mr-2" NAME="AddPRNewDetail" value="Add New" />
				<input type="button" id="DelPRNewDetail" class="btn btn-danger mt-2 mr-2" NAME="DelPRNewDetail" value="Delete" />
				<input type="submit" id="SubmitPRNewDetail" class="btn btn-success mt-2 mr-2" NAME="SubmitPRNewDetail" value="Submit" />
			</div>
			<div id="WinCOA"></div>


		</div>
	</div>


	<script>
		var WinPR;
		var WPR;

		function loadPR_Product(text, url, width, height) {
			WinPR = new dhtmlXWindows();
			WinPR.attachViewportTo("ContentPages");
			WPR = WinPR.createWindow("WPR", 0, 0, width, height);
			WPR.keepInViewport(false);
			WPR.setText(text);
			WPR.attachURL(url, true);
			WPR.button("minmax1").hide();
			WPR.button("park").hide();
			WPR.centerOnScreen();
			WPR.allowMove();
		}


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


		PRNewDetail = new dhtmlXGridObject('gridPRNewDetail');
		PRNewDetail.setImagePath("dhtmlx/imgs/");
		PRNewDetail.setHeader("No,Purchase Type,Product Code,Product Desc,Qty,Specification,Measurement,Note,Category,SubCat,Prices");
		PRNewDetail.setInitWidths("50,100,100,100,200,200,100,200,100,100,100");
		PRNewDetail.setColTypes("ro,ro,ro,ro,edn,ed,ed,ed,ro,ro,edn");
		PRNewDetail.setColAlign("center,left,center,left,right,left,center,center,center,center");
		PRNewDetail.setColSorting("int,str,str,str,str,str,str,str");
		PRNewDetail.setEditable(true);
		PRNewDetail.setNumberFormat("0,000.00", 4, ".", ",");
		PRNewDetail.enableKeyboardSupport(true);
		PRNewDetail.setSkin("dhx_skyblue");
		PRNewDetail.enableMultiline(true);
		PPRNewDetail = new dataProcessor("pr-request-new-update");
		PPRNewDetail.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"PR_No": "<?php echo $PR_No;  ?>"
			}


		})
		PPRNewDetail.setUpdateMode("Off");
		PPRNewDetail.init(PRNewDetail);
		PRNewDetail.init();

		$("#AddPRNewDetail").click(function() {
			loadPR_Product('Product Lists', 'purchasing-product-list', 1024, 450);

		})
		$("#DelPRNewDetail").click(function() {

			PRNewDetail.deleteSelectedRows();

		})


		$("#SubmitPRNewDetail").click(function() {
			var count = PRNewDetail.getRowsNum();
			if (count == 0) {
				alert('Masukkan Item Request Terlebih Dahulu! ');
				return false;
			} else {
				Reference = $("#Reference").val();
				Note = $("#Note").val();
				Department = $("#Department").val();
				User_Request = $("#User_Request").val();
				Date_Request = $("#Date_Request").val();
				PR_No = $("#PR_No").val();
				Category = $("#Category").val();
				Sub_Category = $("#Sub_Category").val();
				Sub_Category2 = $("#Purchase_Type").val();
				PIC = $("#PIC").val();
				PIC_Phone = $("#PIC_Phone").val();
				PIC_Address = $("#PIC_Address").val();
				Estimated_Budget = $("#Estimated_Budget").val();
				
				if (PIC == '') {
					alert("Kolom Penerima Barang (PIC to Receive) Harus Diisi!");
					$("#PIC").focus();
					return false;
				} else if (PIC_Address == '') {
					alert("Kolom alamat PIC (PIC Address) Harus Diisi!");
					$("#PIC_Address").focus();
					return false;
				} else if (PIC_Address.length < 3) {
					alert("Kolom alamat PIC (PIC Address) Minimal 3 Karakter!");
					$("#PIC_Address").focus();
					return false;
				} else if (PIC_Phone == '') {
					alert("Kolom nomor telepon (PIC Phone) Harus Diisi!");
					$("#PIC_Phone").focus();
					return false;
				} else if (Estimated_Budget == '') {
					alert("Kolom Estimated_Budget Harus Diisi!");
					$("#Estimated_Budget").focus();
					return false;
				} else if (Note == '') {
					alert("Kolom catatan (Note) Harus Diisi!");
					$("#Note").focus();
					return false;
				} else if (Note.length < 5) {
					alert("Kolom catatan (Note) minimal 5 karakter!");
					$("#Note").focus();
					return false;
				} else {
					PPRNewDetail.sendData();

					var sip = 1;
					for (var i = 0; i < PRNewDetail.getRowsNum(); i++) {
						PurchaseType = PRNewDetail.cells2(i, 1).getValue();
						if (PurchaseType == Sub_Category2) {
							sip *= 1;
						} else {
							sip *= 0;
						}
					}
					$.blockUI();

					dapat = getAllValues();
					$.ajax({
						headers: {
							'X-CSRF-TOKEN': "{{ csrf_token() }}"
						},

						url: "pr-request-new-submit?_token={{ csrf_token() }}",
						type: "POST",
						data: dapat,
						success: function(data, textStatus, jqXHR) {
							if (data == 'success') {
								alert('Purchase Request Submitted with no  <?php echo $PR_No ?>!');

								setTimeout(function() {


									$.unblockUI();
									location.href = 'po-outstanding';
								}, 1000);

							} else {

							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							alert("error");

						}
					});


				}
			}
		});

		function selesai() {
			$.unblockUI();
			return true;
		}

		function loadSubCat(valCat) {
			LoadM('SubCat', 'pr-load-subcat?Category=' + encodeURI(valCat));
		}

		function gantiSub(valCat, vals) {
			LoadM('SubCat2', 'pr-load-sub?Main_Category=' + encodeURI(valCat) + '&subval=' + encodeURI(vals));
		}
	</script>


</div>
<div id="PRSupplierDet"></div>

@endsection