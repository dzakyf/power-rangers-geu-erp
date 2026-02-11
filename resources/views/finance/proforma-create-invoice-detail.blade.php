<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
if (isset($_GET['batch'])) {
	$batch = explode(",", $_GET['batch']);
	$proforma = DB::table("proforma")
		->select(DB::raw('Account_No, Account_Name, sum(Subtotal_PO) as Subtotal_PO, sum(Man_Fee) as Man_Fee,  sum(Total) as Total'))
		->whereIn("Proforma_No", $batch)
		->groupBy('Account_No', 'Account_Name')
		->first();
	$cust = DB::table("customerz")->where("Customer_No", $proforma->Account_No)->first();
	$number_proforma = "PRF-UNION-GEU-HO-" . date("Ymd-His");
	$invoice_subject = "Tagihan Jasa";
	$invoice_periode = date("F Y");
} else {
	$proforma = DB::table("proforma")->where("Proforma_No", $_GET['ID'])->first();
	$cust = DB::table("customerz")->where("Customer_No", $proforma->Account_No)->first();
	$total = $proforma->Total;
	if ($proforma->Total > 5000000) {
		$materai = 10000;
	} else {
		$materai = 0;
	}
	$number_proforma = $proforma->Proforma_No;
	$invoice_subject = $proforma->Subject;
	$invoice_periode = $proforma->Periode;
}

$invd = strtotime("now");
$hari = $cust->Due_Days * 86400;
$tempo = date("Y-m-d", $invd + $hari);


?>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>

<div id="ProformaInitiate" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOADED DATA</div>
<div class="row">
	<div id="OrderDoc" style="height:130px; background-color:white;overflow:hidden;width:85%;height:130px;float:left;"></div>
	<div>
		<a href="#" id="PicOrderDoc" onclick="window.open(this.href,'gbrx2','gbrx2',true);"> <img src="file.png" width="100" id="picN" />
		</a>
	</div>
</div>


<div class="row">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SUB DETAIL DATA</div>

	Percentage? <input type="text" id="percentage" value="0" size="4" />
	<div id="SubDetailGrid" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
</div>
<div>

	<input type="button" id="Inv_Datex" size="60" value="Add Data" onclick="SubDetailGrid.addRow(SubDetailGrid.uid(),[SubDetailGrid.getRowsNum()+1,'','0',1,,'0'])" readonly='readonly' />
	<input type="button" id="Inv_Datexs" onclick="SubDetailGrid.deleteSelectedRows()" size="60" value="Delete Data" readonly='readonly' />


</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PRE INVOICE DATA</div>

<div id="ProformaBADiv">
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Invoice Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Inv_Date" size="60" value="<?php echo date("Y-m-d"); ?>" readonly='readonly' />
			<input type="text" id="Proforma_No" size="35" value="<?php echo $number_proforma ?>" />
			<input type="text" id="Account_No" size="35" value="<?php echo $proforma->Account_No ?>" />
			<input type="text" id="Account_Name" size="35" value="<?php echo $proforma->Account_Name ?>" />


		</div>

		<div class="FormInput_left">

			Jatuh Tempo

		</div>
		<div class="FormInput_right">

			<input type="text" id="jatuh_tempo" size="60" value="<?php echo $tempo; ?>" readonly='readonly' />



		</div>
		<div class="FormInput_left" style="height:100px">

			Invoice Subject/Note

		</div>
		<div class="FormInput_right" style="height:100px">

			<textarea id="Invoice_Subject" cols="50" rows="5">TAGIHAN BIAYA <?php echo $invoice_subject ?></textarea>
			<?php if (isset($_GET['batch'])) : ?>
				<textarea id="Note" cols="50" rows="5" disabled><?php echo $_GET['batch'] ?></textarea>
			<?php endif; ?>

		</div>

		<div class="FormInput_left">

			Periode

		</div>
		<div class="FormInput_right">

			<input type="text" id="periode_txt" size="60" value="<?php echo $invoice_periode ?>" />

		</div>
		<div class="FormInput_left">

			Gross Val

		</div>
		<div class="FormInput_right">

			<input type="text" id="Gross_Val" size="60" value="<?php echo $proforma->Subtotal_PO ?>" onchange='itung()' />

		</div>

		<div class="FormInput_left">

			Discount %

		</div>
		<div class="FormInput_right">

			<input type="text" id="Discount" size="5" value="<?php echo $cust->Discount ?>" onchange='itung()' /> ::
			<input type="text" id="Discount_Val" size="45" value="<?php echo $cust->Discount ?>" disabled="disabled" />

		</div>
		<div class="FormInput_left">

			Nominal(DPP)

		</div>
		<div class="FormInput_right">

			<input type="text" id="Subtotal" size="60" value="<?php echo $proforma->Subtotal_PO ?>" onchange='itung()' />

		</div>

		<div class="FormInput_left">

			Management fee (Tidak Dipisah)
		</div>
		<div class="FormInput_right">

			<input type="text" id="mgm_fee" size="60" value="<?php echo $proforma->Man_Fee; ?>" onchange='itung()' />

		</div>

		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">

			<input type="text" id="PPN_Val" size="5" value="0" onchange='itung()' /> ::
			<input type="text" id="PPN" size="45" value="0" onchange='noppn()' />


		</div>

		<div class="FormInput_left">

			Materai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Materai" size="60" value="0" onchange='nomaterai()' />

		</div>
		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">

			<input type="text" id="Total" size="60" value="<?php echo ($proforma->Subtotal_PO) ?>" />

		</div>

		<div class="FormInput_left">

			Faktur No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Faktur" size="60" />

		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MANAGEMENT FEE</div>

		<div class="FormInput_left">

			Management Fee

		</div>
		<div class="FormInput_right">

			<input type="text" id="Management_Fee" size="60" value="<?php echo $proforma->Man_Fee; ?>" onchange="itungfee()" />

		</div>
		<div class="FormInput_left">

			PPN

		</div>
		<div class="FormInput_right">

			<input type="text" id="PPN_Val_Fee" size="5" value="0" onchange='itungfee()' /> ::
			<input type="text" id="PPN_Fee" size="45" value="0" onchange='noppnfee()' />


		</div>

		<div class="FormInput_left">

			Materai

		</div>
		<div class="FormInput_right">

			<input type="text" id="Materai_Fee" size="60" value="0" onchange='nomateraifee()' />

		</div>
		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">

			<input type="text" id="Total_Fee" size="60" readonly="readonly" />

		</div>

		<div class="FormInput_left">

			Faktur No

		</div>
		<div class="FormInput_right">

			<input type="text" id="Faktur_Fee" size="60" />

		</div>

		<div class="FormInput_left">

			Signed By

		</div>
		<div class="FormInput_right">

			<input type="text" id="Signed_By" size="35" value="Suci Andrini" />

		</div>

	</div>
	<div style="float:none;padding-top:30px;" ;>

		<input type="button" id="ProSubmt" NAME="ProSubmt" value="Create Invoice" class="btn btn-success mt-2 mr-2" />

	</div>


	<script>
		function convertAmp(stage, rId, cInd, nValue, oValue) {
			var data = SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 1).getValue();
			var replace = data.replace("&", "g")
			SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 1).setValue(replace);
			return true;
		}

		function ItungCashAdvanceDetail(stage, rId, cInd, nValue, oValue) {
			var nilai = sumColumn(4);
			var grossval = parseFloat($("#Subtotal").val());
			if (nilai > grossval) {
				alert('Nilai Data melebihi dari Subtotal!');
				SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 2).setValue(grossval);
			}
			var srQ = document.getElementById("subnya");
			srQ.innerHTML = number_format(nilai);

			var perc = parseFloat($("#percentage").val());
			if (perc > 0) {
				var hrg = parseFloat(SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 2).getValue());
				var feedata = (perc / 100) * hrg;
				SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 5).setValue(feedata);

				var nilai2 = sumColumn(6);
				if (nilai2 > grossval) {
					alert('Nilai Data Fee melebihi gross val!');
					SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 5).setValue('0');
				}
				var srQx = document.getElementById("subnya2");
				srQx.innerHTML = number_format(nilai2);
				$("#Management_Fee").val(nilai2);
			}

			var nilai2 = sumColumn(6);
			if (nilai2 > grossval) {
				alert('Nilai Data Fee melebihi gross val!');
				SubDetailGrid.cells(SubDetailGrid.getSelectedId(), 5).setValue('0');
			}
			var srQx = document.getElementById("subnya2");
			srQx.innerHTML = number_format(nilai2);
			$("#Management_Fee").val(nilai2);

			return true;
		}

		function sumColumn(ind) {
			var out = 0;
			for (var i = 0; i < SubDetailGrid.getRowsNum(); i++) {
				out += parseFloat(SubDetailGrid.cells2(i, ind).getValue());
			}
			return out;
		}

		SubDetailGrid = new dhtmlXGridObject('SubDetailGrid');
		SubDetailGrid.setImagePath("./dhtmlx/imgs/");
		SubDetailGrid.setHeader("No,Deskripsi,@Harga,Qty,SubTotal,Harga Fee,Subtotal Fee");
		SubDetailGrid.attachFooter("Total,#cspan,#cspan,#cspan,<div id='subnya'>0</div>,,<div id='subnya2'>0</div>");
		SubDetailGrid.setInitWidths("60,190,100,100,100,100,100");
		SubDetailGrid.setColTypes("ro,ed,edn,edn,ron[=c2*c3],edn,ron[=c3*c5]");
		SubDetailGrid.setColAlign("center,left,right,right,right,right,right");
		SubDetailGrid.setEditable(true);
		//SubDetailGrid.attachEvent("onEditCell", ItungCashAdvanceDetail);
		SubDetailGrid.attachEvent("onEnter", convertAmp);
		SubDetailGrid.setSkin("dhx_skyblue");
		SubDetailGrid.setNumberFormat("0,000", 2, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 3, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 4, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 5, ".", ",");
		SubDetailGrid.setNumberFormat("0,000", 6, ".", ",");

		pSubDetailGrid = new dataProcessor("proforma-create-invoice-detail-update");
		pSubDetailGrid.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"Task_ID": "<?php echo $number_proforma ?>"
			}

		})
		pSubDetailGrid.setUpdateMode("Off");
		pSubDetailGrid.init(SubDetailGrid);
		SubDetailGrid.init();
		SubDetailGrid.clearAndLoad('proforma-create-invoice-detail?Task_ID=<?php echo $number_proforma; ?>');


		function itung() {

			var mgt = parseFloat($("#Subtotal").val()) + 0;
			var disk = parseFloat($("#Discount").val()) + 0;
			var materai = parseFloat($("#Materai").val()) + 0;
			var mgm_fee = parseFloat($("#mgm_fee").val()) + 0;
			var ppn_val = parseFloat($("#PPN_Val").val()) + 0;

			if (disk > 0) {
				var diskonval = (disk / 100) * mgt;
				var subtotal = mgt - diskonval;

			} else {
				var diskonval = 0;
				var subtotal = mgt;
			}

			if (ppn_val > 0) {
				if (mgm_fee > 0) {
					var ppn = ppn_val / 100 * (mgm_fee + mgt);
					ppn = Math.floor(ppn);
				} else {
					var ppn = ppn_val / 100 * mgt;
					ppn = Math.floor(ppn);
				}
			} else {
				var ppn = 0;
			}

			if (ppn > 0) {
				document.getElementById("PPN_Val_Fee").disabled = true;
				document.getElementById("PPN_Fee").disabled = true;
			} else {
				document.getElementById("PPN_Val_Fee").disabled = false;
				document.getElementById("PPN_Fee").disabled = false;
			}

			if ((subtotal + ppn) > 5000000) materai = 10000;
			else materai = 0;
			var total = subtotal + ppn + materai + mgm_fee;
			$("#Discount_Val").val(diskonval);
			$("#Subtotal").val(subtotal);
			$("#PPN").val(ppn);
			$("#Materai").val(materai);
			$("#Total").val(total);

		}

		function noppn() {
			var mgt = parseFloat($("#Subtotal").val()) + 0;
			var ppn = parseFloat($("#PPN").val()) + 0;
			var materai = parseFloat($("#Materai").val()) + 0;
			var mgm_fee = parseFloat($("#mgm_fee").val()) + 0;
			if ((mgt + ppn) > 5000000) materai = 10000;
			else materai = 0;
			var total = mgt + ppn + materai + mgm_fee;
			$("#Total").val(total);

		}

		function nomaterai() {
			var mgt = parseFloat($("#Subtotal").val()) + 0;
			var ppn = parseFloat($("#PPN").val()) + 0;
			var materai = parseFloat($("#Materai").val()) + 0;
			var total = mgt + ppn + materai;
			$("#Total").val(total);

		}


		function itungfee() {
			var mgtfee = parseFloat($("#Management_Fee").val()) + 0;

			var materai = parseFloat($("#Materai_Fee").val()) + 0;
			var ppnfeeval = parseFloat($("#PPN_Val_Fee").val()) + 0;

			if (mgtfee == 0) {

				$("#mgm_fee").val(<?= $proforma->Man_Fee ?>);
				$("#PPN_Val_Fee").val(0);
				$("#PPN_Val").val(11);
				document.getElementById("mgm_fee").dispatchEvent(new Event('change'));
				document.getElementById("PPN_Val_Fee").dispatchEvent(new Event('change'));
				document.getElementById("PPN_Val").dispatchEvent(new Event('change'));
			} else {
				$("#mgm_fee").val(<?= $proforma->Man_Fee ?>);
				$("#PPN_Val_Fee").val(0);
				$("#PPN_Val").val(11);
				document.getElementById("mgm_fee").dispatchEvent(new Event('change'));
				document.getElementById("PPN_Val_Fee").dispatchEvent(new Event('change'));
				document.getElementById("PPN_Val").dispatchEvent(new Event('change'));
			}
			if (ppnfeeval > 0) {
				var ppnfee = ppnfeeval / 100 * mgtfee;
				ppnfee = Math.floor(ppnfee);
			} else {
				var ppnfee = 0;
				if (mgtfee > 0) {
					document.getElementById("PPN_Val").value = 11;
					document.getElementById("PPN_Val").dispatchEvent(new Event('change'));
				}
			}

			if (ppnfee > 0) {
				document.getElementById("PPN_Val").disabled = true;
				document.getElementById("PPN").disabled = true;
			} else {
				document.getElementById("PPN_Val").disabled = false;
				document.getElementById("PPN").disabled = false;
			}
			if ((mgtfee + ppnfee) > 5000000) materai = 10000;
			else materai = 0;
			var totalfee = mgtfee + ppnfee + materai;
			$("#PPN_Fee").val(ppnfee);
			$("#Materai_Fee").val(materai);
			$("#Total_Fee").val(totalfee);

		}

		function noppnfee() {
			var mgtfee = parseFloat($("#Management_Fee").val()) + 0;
			var ppnfee = parseFloat($("#PPN_Fee").val()) + 0;
			var materai = parseFloat($("#Materai_Fee").val()) + 0;
			if ((mgtfee + ppnfee) > 5000000) materai = 10000;
			else materai = 0;
			var totalfee = mgtfee + ppnfee + materai;
			$("#Total_Fee").val(totalfee);

		}

		function nomateraifee() {
			var mgtfee = parseFloat($("#Management_Fee").val()) + 0;
			var ppnfee = parseFloat($("#PPN_Fee").val()) + 0;
			var materaifee = parseFloat($("#Materai_Fee").val()) + 0;
			var totalfee = mgtfee + ppnfee + materaifee;
			$("#Total_Fee").val(totalfee);

		}
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
			ProformaInitiate.clearAndLoad('xml-sales-proforma-posting-detail?Task_ID=<?php echo $number_proforma; ?>');

		}



		function OrderDocDobelKlik(rId, cInd) {

			detgbr = OrderDoc.cells(OrderDoc.getSelectedId(), 4).getValue();
			var namafile = OrderDoc.cells(OrderDoc.getSelectedId(), 2).getValue();
			$("#PicOrderDoc").attr("href", 'proformadoc/' + detgbr);
			$("#picN").attr("src", 'download.png');
			document.getElementById("namafile").textContent = namafile;





			return true;

		}
		OrderDoc = new dhtmlXGridObject('OrderDoc');
		OrderDoc.setImagePath("./dhtmlx/imgs/");
		OrderDoc.setHeader("No,Category,Reference No,Upload,pathgbr");
		OrderDoc.setInitWidths("60,190,300,100,auto");
		OrderDoc.setColTypes("ro,ed,ed,img,ro");
		OrderDoc.setColAlign("center,left,left,center,center,center");
		OrderDoc.setEditable(true);
		OrderDoc.setSkin("dhx_skyblue");
		OrderDoc.attachEvent("onRowSelect", OrderDocDobelKlik);
		OrderDoc.attachEvent("onRowDblClicked", OrderDocDobelKlik);

		pOrderDoc = new dataProcessor("finance-proforma-outstanding-detail-update");
		pOrderDoc.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"Task_ID": "<?php echo $number_proforma; ?>"
			}

		})
		pOrderDoc.setUpdateMode("Off");
		pOrderDoc.init(OrderDoc);
		OrderDoc.init();
		OrderDoc.clearAndLoad('xml-sales-proforma-posting-detail2?Task_ID=<?php echo  $number_proforma; ?>');



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

				url: "finance-proforma-create-invoice-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {
						pSubDetailGrid.sendData();
						setTimeout(function() {

							alert('Invoice Created!');
							location.href = 'finance-proforma-create-invoice';
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
		var myCalendar;
		myCalendar = new dhtmlXCalendarObject(["Inv_Date", "jatuh_tempo"]);
		myCalendar.setDateFormat("%Y-%m-%d");
		$("#Invoice_Subject").on("keypress", function(e) {
			var key = e.keyCode;

			// If the user has pressed enter
			if (key == 13) {
				document.getElementById("Invoice_Subject").value = document.getElementById("Invoice_Subject").value + "\n<br>";
				return false;
			} else {
				return true;
			}
		});


		Invoice_Subject = document.getElementById("Invoice_Subject").value;
		subtotal = document.getElementById("Subtotal").value;
		fee = document.getElementById("Management_Fee").value;
		SubDetailGrid.addRow(SubDetailGrid.uid(), [SubDetailGrid.getRowsNum() + 1, Invoice_Subject, subtotal, 1, , fee]);

		if (fee > 0) {

		}

		data = {
			rows: [{
					id: 1,
					data: [1, Invoice_Subject, subtotal, 1, subtotal, fee, fee]
				},

			]
		};
		SubDetailGrid.parse(data, "json"); // takes the name and format of the data source



		var subtotal = document.getElementById("Subtotal");
		var fee = document.getElementById("Management_Fee");
		if (fee.value > 0) {
			document.getElementById("PPN_Val_Fee").value = 0;
			document.getElementById("PPN_Val_Fee").dispatchEvent(new Event('change'));
		} else {
			document.getElementById("PPN_Val").value = 11;
			document.getElementById("PPN_Val").dispatchEvent(new Event('change'));
		}
	</script>