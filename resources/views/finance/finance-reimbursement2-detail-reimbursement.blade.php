<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$q = DB::table("reimbursement")->where("Reimbursement_No", $_GET['ID'])->first();

$kursval = 1;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT DETAIL</div>
<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="AddGridCash" NAME="AddGridCash" value="Add Detail" class="btn btn-success mt-2 mr-2" />
	<input type="button" id="DelGridCash" NAME="DelGridCash" value="Delete" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="SaveGridCash" NAME="SaveGridCash" value="S A V E" class="btn btn-success mt-2 mr-2" />

	<input type="button" id="exportGridCash" name="exportGridCash" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="GridCash.toExcel('./export/generate.php');" />
</div>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PAYMENT PROCESS</div>
<div id="CashTransferDiv">
	<input type="hidden" disabled="disabled" value="<?php echo $q->Reimbursement_No ?>" id="Reimbursement_No" />
	<input type="hidden" disabled="disabled" value="<?php echo $q->ID ?>" id="ID" />

	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Payment Mode

		</div>
		<div class="FormInput_right">

			<select id="Payment_Mode">
				<?php
				$Query = DB::table("bank_acc")->where("Status", 'ACTIVE')->get();
				foreach ($Query as $Data) {
					echo "<option value='BANK|$Data->Acc_No|$Data->Bank_Acc'>$Data->Acc_No - $Data->Bank_Name / $Data->Bank_Acc</option>";
				}
				$Query = DB::table('cash_handler')->where("Status", 'ACTIVE')->get();
				foreach ($Query as $Data) {
					echo "<option value='CASH|$Data->Account_No|$Data->NIK_Handler|$Data->Name'>$Data->Name - $Data->Account_No - $Data->Location</option>";
				}

				?>

			</select>

		</div>

		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">
			<input type="hidden" disabled="disabled" value="<?php echo $q->Total ?>" id="Total" />
			Rp. <?php echo number_format($q->Total, 0, ".", ",") ?>
		</div>
		<div class="FormInput_left">

			Paid

		</div>

		<div class="FormInput_right">
			Rp. <?php echo number_format($q->Paid, 0, ".", ",") ?>
		</div>
		<div class="FormInput_left">

			Remain

		</div>

		<div class="FormInput_right">
			Rp. <?php echo number_format($q->Remain, 0, ".", ",") ?>
		</div>
		<div class="FormInput_left">

			Payment Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Payment_Date" size="25" value="<?php echo date("Y-m-d") ?>" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			Amount to Pay

		</div>
		<div class="FormInput_right">
			<input type="text" onkeydown="return isValidKey(event)" value="<?php echo $q->Remain ?>" id="Payment_Value" />
		</div>
		<div class="FormInput_left" style="height:90px;">

			Note

		</div>
		<div class="FormInput_right" style="height:100px;">
			<textarea id="Payment_Note" cols="80" rows="3"></textarea>

		</div>

		<div class="FormInput_right" style="height:100px;">

			<input type="button" value="Process" id="SubmitTransfer" onclick="PaymentProcess();" class="btn btn-success mt-2 mr-2" />

		</div>


	</div>
</div>





<script>
	$('#SaveGridCash').click(function() {

		PGridCash.sendData();

		alert("Data Successfully Saved!");


	});


	$('#AddGridCash').click(function() {
		if (GridCash.getRowsNum() == 0) {
			nums_reqrow = 1;
			nomorrowsreq = 0;
		} else {
			nums_reqrow = GridCash.getRowsNum() + 1;
			nomorrowsreq = nums_reqrow - 1;
		}

		var note = Reimbursement.cells(Reimbursement.getSelectedId(), 12).getValue();
		var amount = Reimbursement.cells(Reimbursement.getSelectedId(), 6).getValue();
		var department = Reimbursement.cells(Reimbursement.getSelectedId(), 5).getValue();
		var ref = Reimbursement.cells(Reimbursement.getSelectedId(), 4).getValue();
		GridCash.addRow(GridCash.uid(), [nums_reqrow, '', '<?php echo date("Y-m-d") ?>', ref, '', note, amount, department], GridCash.getRowsNum());
		GridCash.selectRow(nomorrowsreq);
	});

	$('#DelGridCash').click(function() {

		dhtmlx.confirm('Are you sure want to delete this data?', 'Cash Management', function(r) {

			if (r == true) {
				GridCash.deleteSelectedRows();
			}
		})

	});

	$(function() {
		$('#Payment_Value').mask('#.##0', {
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
	myCalendar = new dhtmlXCalendarObject(["Payment_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	var winCOA;
	var WCOA;

	function WindoWCOA(text, url, width, height) {
		winCOA = new dhtmlXWindows();

		winCOA.attachViewportTo('PagesContent');
		WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
		WCOA.keepInViewport(false);
		WCOA.setText(text);
		WCOA.attachURL(url, true);
		WCOA.button("minmax1").hide();
		WCOA.button("park").hide();
		WCOA.centerOnScreen();
		WCOA.allowMove();
	}

	function editcekcashout(rId, cInd, stage, nValue, oValue) {

		if (cInd == 1) {
			WindoWCOA("Chart of Accounts", "finance-cash-out-detail-coa", 650, 350);
		}


		return true;
	}


	GridCash = new dhtmlXGridObject('grid_kbterima');
	GridCash.setImagePath("./dhtmlx/imgs/");
	GridCash.setHeader("No.,Account No,Date,Reference,External Ref,Remark,Value,Departmen");
	GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', "<div id='totalamount'>{#stat_total}</div>", '#cspan']);
	GridCash.setInitWidths("70,100,100,150,100,200,100,100");
	GridCash.setColAlign("center,left,center,left,left,left,right,left");
	GridCash.setColTypes("ro,ro,dhxCalendar,ed,ed,ed,edn,combo");
	GridCash.setColSorting("int,str,str,str,str,str,str,str");
	GridCash.setDateFormat("%Y-%m-%d");
	PGridCash = new dataProcessor("finance-reimbursement2-detail-reimbursement-update");

	PGridCash.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		payload: {
			"ID": "<?php echo $_GET['ID'] ?>"
		}

	})
	PGridCash.setUpdateMode("Off");
	PGridCash.init(GridCash);
	GridCash.setEditable(true);
	GridCash.setNumberFormat("0,000", 6, ".", ",");
	GridCash.attachEvent("onRowDblClicked", editcekcashout);

	GridCash.enableKeyboardSupport(true);
	GridCash.setSkin("dhx_skyblue");
	GridCash.init();
	refreshdatadetail();

	combocurr = GridCash.getColumnCombo(7);
	combocurr.enableFilteringMode(true);
	combocurr.load("xml-finance-department");

	function refreshdatadetail() {
		GridCash.clearAndLoad("xml-finance-reimbursement2-detail-reimbursement?ID=<?php echo $_GET['ID']; ?>");

	}

	function PaymentProcess() {

		dhtmlx.confirm('Are you sure want to process?', 'Reimbursement Management', function(r) {

			if (r == true) {
				SubmitTransferan();



			}
		})
	}


	function getAllValues() {
		var inputValues = [];
		$('#CashTransferDiv input,select,textarea').each(function() {
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

	function SubmitTransferan() {
		dapat = getAllValues();
		PGridCash.sendData();
		setTimeout(function() {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},

				url: "finance-reimbursement2-detail-reimbursement-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: dapat,
				success: function(data, textStatus, jqXHR) {
					if (data == 'success') {
						alert('Reimbursement Payment Done!');

						setTimeout(function() {

							$("#divlistProjectReimbursement").html('');

							refreshdata();
						}, 1000);

					} else {

					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("Error" + jqXHR.responseText);

				}
			});
		}, 2000);
	}
</script>