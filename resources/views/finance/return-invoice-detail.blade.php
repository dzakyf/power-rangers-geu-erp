<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$invoice = DB::table("invoice")->where("Inv_No", $_GET['ID'])->first();

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

<div id="ProformaInitiate" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOADED DATA</div>
<div>
	<div id="OrderDoc" style="height:130px; background-color:white;overflow:hidden;width:85%;height:130px;float:left;"></div>
	<div>
	<a href="#" id="PicOrderDoc" onclick="window.open(this.href,'gbrx2','gbrx2',true);"> <img src="file.png" width="100" id="picN" />
		</a>
		<br> <span id="namafile" style="font-size:80%;"></span>
	</div>
</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INVOICE DATA</div>

<div id="ProformaBADiv">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Invoice Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Inv_Date" size="15" value="<?php echo date("Y-m-d"); ?>" readonly='readonly' />
			<input type="text" id="Inv_No" size="35" value="<?php echo $invoice->Inv_No ?>" readonly='readonly' />


		</div>
		<div class="FormInput_left">

			Gross Val

		</div>
		<div class="FormInput_right">

			<input type="text" id="Gross_Val" size="10" value="<?php echo $invoice->Total ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Discount %

		</div>
		<div class="FormInput_right">

			<input type="text" id="Discount" size="5" value="<?php echo $invoice->Discount ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Subtotal

		</div>
		<div class="FormInput_right">

			<input type="text" id="Subtotal" size="15" value="<?php echo $invoice->Subtotal ?>" readonly='readonly' />

		</div>

		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">

			<input type="text" id="PPN" size="10" value="<?php echo $invoice->PPN ?>" readonly='readonly' />

		</div>

		<div class="FormInput_left">

			Materai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Materai" size="5" value="<?php echo $invoice->Materai ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">

			<input type="text" id="Total" size="10" value="<?php echo $invoice->Total ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Faktur No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Faktur" size="30" value="<?php echo $invoice->Faktur_No ?>" />

		</div>
		<div class="FormInput_left">

			Signed By

		</div>
		<div class="FormInput_right">

			<input type="text" id="TTD_By" value="<?php echo $invoice->TTD_By ?>" size="35" readonly='readonly' />

		</div>

	</div>
	

	<script>
		ProformaInitiate = new dhtmlXGridObject('ProformaInitiate');
		ProformaInitiate.setImagePath("./dhtmlx/imgs/");
		ProformaInitiate.setHeader("No,PO No,Description,PO Date,Contract No,Amount,Customer No,Customer Name,PIC,PO End Date,Note");
		ProformaInitiate.setInitWidths("60,120,220,100,100,100,80,150,100,100,100,100");
		ProformaInitiate.setColAlign("center,center,left,center,center,right,center,center,center,center,left,right");
		ProformaInitiate.setColSorting("int,str,str,str,str,str,str,str,str,str,str,int");
		ProformaInitiate.setColTypes("ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
		ProformaInitiate.enableSmartRendering(true, 150);
		ProformaInitiate.setNumberFormat("0,000", 5, ".", ",");
		ProformaInitiate.setSkin("dhx_skyblue");
		ProformaInitiate.init();
		refreshdata();

		function refreshdata() {
			ProformaInitiate.clearAndLoad('xml-sales-proforma-posting-detail?Task_ID=<?php echo $invoice->Task_ID ?>');

		}

		function OrderDocDobelKlik(rowId, cellInd) {
		detgbr = OrderDoc.cells(OrderDoc.getSelectedId(), 4).getValue();
		var namafile = OrderDoc.cells(OrderDoc.getSelectedId(), 2).getValue();
		$("#PicOrderDoc").attr("href", 'proformadoc/' + detgbr);
		$("#picN").attr("src", 'download.png');
		document.getElementById("namafile").textContent = namafile;

		KodeDoc = OrderDoc.cells(rowId, 1).getValue();
		if (KodeDoc != '') {
			if (cellInd == 3) {

				UploadDetailGambar(KodeDoc);
			}

		}

		return true;


	}
	function rowseldetail(rowId, cellInd) {
		nmfile = OrderDoc.cells(rowId, 4).getValue();
		$("#PicOrderDoc").attr("src", 'proformadoc/' + nmfile);
		return true;
	}


		OrderDoc = new dhtmlXGridObject('OrderDoc');
		OrderDoc.setImagePath("./dhtmlx/imgs/");
		OrderDoc.setHeader("No,Category,Reference No,Upload,pathgbr");
		OrderDoc.setInitWidths("60,190,300,100,auto");
		OrderDoc.setColTypes("ro,ed,ed,img,ro");
		OrderDoc.setColAlign("center,left,left,center,center,center");
		OrderDoc.attachEvent("onRowSelect", rowseldetail);
		OrderDoc.attachEvent("onRowDblClicked", OrderDocDobelKlik);
		OrderDoc.setEditable(true);
		OrderDoc.setSkin("dhx_skyblue");
		OrderDoc.init();
		OrderDoc.clearAndLoad('xml-sales-proforma-posting-detail2?Task_ID=<?php echo $invoice->Task_ID ?>');



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
			window.open("print-invoice?ID=<?php echo $invoice->Inv_No ?>");

		});
	</script>