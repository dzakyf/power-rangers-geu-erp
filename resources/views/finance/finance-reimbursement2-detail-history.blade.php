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

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PAYMENT PROCESS</div>
<div id="CashTransferDiv">
	<input type="hidden" disabled="disabled" value="<?php echo $q->Reimbursement_No ?>" id="Reimbursement_No" />
	<input type="hidden" disabled="disabled" value="<?php echo $q->ID ?>" id="ID" />

	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Payment Mode

		</div>
		<div class="FormInput_right">

			<?php echo $q->Payment_Type; ?> :: <?php echo $q->Payment_Reference_No; ?>



		</div>

		<div class="FormInput_left">

			Total

		</div>
		<div class="FormInput_right">
			<input type="hidden" disabled="disabled" value="<?php echo $q->Total ?>" id="Total" disabled="disabled" />
			Rp. <?php echo number_format($q->Total, 0, ".", ",") ?>
		</div>

		<div class="FormInput_left">

			Payment Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="Payment_Date" size="25" disabled="disabled" value="<?php echo date("Y-m-d") ?>" readonly="readonly" />

		</div>
		<div class="FormInput_left">

			Amount to Pay

		</div>
		<div class="FormInput_right">
			<input type="text" onkeydown="return isValidKey(event)" disabled="disabled" value="<?php echo $q->Total ?>" id="Payment_Value" />
		</div>
		<div class="FormInput_left" style="height:90px;">

			Note

		</div>
		<div class="FormInput_right" style="height:100px;">
			<textarea id="Payment_Note" cols="80" rows="3" disabled="disabled"></textarea>

		</div>



	</div>
</div>





<script>
	GridCash = new dhtmlXGridObject('grid_kbterima');
	GridCash.setImagePath("./dhtmlx/imgs/");
	GridCash.setHeader("No.,Account No,Date,Reference,External Ref,Remark,Value,Department");
	GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', "", "", "<div id='totalamount'>{#stat_total}</div>", '#cspan']);
	GridCash.setInitWidths("70,100,100,150,100,200,100,100");
	GridCash.setColAlign("center,left,center,left,left,right,right,center,center");
	GridCash.setColTypes("ro,ro,dhxCalendar,ed,ed,ed,edn,ed");
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
	GridCash.setEditable(false);
	GridCash.setNumberFormat("0,000", 6, ".", ",");

	GridCash.enableKeyboardSupport(true);
	GridCash.setSkin("dhx_skyblue");
	GridCash.init();
	refreshdatadetail();

	function refreshdatadetail() {
		GridCash.clearAndLoad("xml-finance-reimbursement2-detail-reimbursement?ID=<?php echo $_GET['ID']; ?>");

	}
</script>