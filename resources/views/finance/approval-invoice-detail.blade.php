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
<div id="SubDetailGrid" style="width:100%;height:220px; background-color:white;overflow:hidden"></div>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INVOICE DATA</div>

<div id="ProformaBADiv">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Invoice Type

		</div>
		<div class="FormInput_right">

			<input type="text" id="Invoice_Type" class='form-control-sm' size="40" value="<?php echo $invoice->Invoice_Type ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Invoice Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Inv_Date" class='form-control-sm' size="15" value="<?php echo date("Y-m-d", strtotime($invoice->Inv_Date)); ?>" readonly='readonly' />
			<input type="text" id="Inv_No" class='form-control-sm' size="35" value="<?php echo $invoice->Inv_No ?>" readonly='readonly' />


		</div>
		<div class="FormInput_left">

			Gross Val

		</div>
		<div class="FormInput_right">

			<input type="text" id="Gross_Val" class='form-control-sm' size="10" value="<?php echo $invoice->Total + 0 ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Discount %

		</div>
		<div class="FormInput_right">

			<input type="text" id="Discount" class='form-control-sm' size="5" value="<?php echo $invoice->Discount + 0 ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Subtotal

		</div>
		<div class="FormInput_right">

			<input type="text" id="Subtotal" class='form-control-sm' size="15" value="<?php echo $invoice->Subtotal + 0 ?>" readonly='readonly' />

		</div>

		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">

			<input type="text" id="PPN" class='form-control-sm' size="10" value="<?php echo $invoice->PPN + 0 ?>" readonly='readonly' />

		</div>

		<div class="FormInput_left">

			Materai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Materai" class='form-control-sm' size="5" value="<?php echo $invoice->Materai + 0 ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">

			<input type="text" id="Total" class='form-control-sm' size="10" value="<?php echo $invoice->Total + 0 ?>" readonly='readonly' />

		</div>
		<div class="FormInput_left">

			Signed By

		</div>
		<div class="FormInput_right">

			<input type="text" id="TTD_By" class='form-control-sm' value="<?php echo $invoice->TTD_By ?>" size="35" readonly='readonly' />

		</div>

	</div>
	<div style="float:none;padding-top:30px;" ;>

		<input type="button" id="ProSubmt" NAME="ProSubmt" value="Approve and Add Faktur" class="btn btn-success mt-2 mr-2" />

		<input type="button" id="PrintInv" NAME="PrintInv" value="Print Invoice" class="btn btn-info mt-2 mr-2" />

		<input type="button" id="PrintPajak" NAME="PrintPajak" value="Print Potongan Pajak" class="btn btn-warning mt-2 mr-2" />

	</div>


	<script>
		SubDetailGrid = new dhtmlXGridObject('SubDetailGrid');
		SubDetailGrid.setImagePath("./dhtmlx/imgs/");
		SubDetailGrid.setHeader("No,Deskripsi,@Harga,Qty,SubTotal,Harga Fee,Subtotal Fee");
		SubDetailGrid.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,,#stat_total");
		SubDetailGrid.setInitWidths("60,190,100,100,100,100,100");
		SubDetailGrid.setColTypes("ro,ed,edn,edn,ron[=c2*c3],edn,ron[=c3*c5]");
		SubDetailGrid.setColAlign("center,left,right,right,right,right,right");
		SubDetailGrid.setEditable(false);

		SubDetailGrid.setSkin("dhx_skyblue");
		SubDetailGrid.setNumberFormat("0,000", 2, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 3, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 4, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 5, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 6, ".", ",");


		SubDetailGrid.init();
		SubDetailGrid.clearAndLoad('xml-proforma-create-invoice-detail?Task_ID=<?php echo $invoice->Task_ID ?>');


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
		OrderDoc.setEditable(true);
		OrderDoc.attachEvent("onRowSelect", rowseldetail);
		OrderDoc.attachEvent("onRowDblClicked", OrderDocDobelKlik);
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
			dapat = getAllValues();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},

				url: "finance-approval-invoice-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {

						setTimeout(function() {
							alert('Invoice APPROVED!');
							location.href = 'finance-invoice-approval';
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

		$("#PrintInv").click(function() {
			window.open("print-invoice?ID=<?php echo $invoice->Inv_No ?>");
		});

		$("#PrintPajak").click(function() {
			window.open("print-pajak?ID=<?php echo $invoice->Inv_No ?>");
		});
	</script>