<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$fadata = DB::table("supplier")->where("Supplier_ID", $_GET['CID'])->first();
$tgl = 1;
$CID = $_GET['CID'];
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA</div>
<div>
	<div class="FormInput_Form_Content">
		<div class="FormInput_left">

			Action

		</div>
		<div class="FormInput_right">

			<select id="ActionP">
				<option value="">Select</option>
				<option onclick="$('#Payment_Val').attr('disabled',false).val('<?php echo $_GET['remain'] ?>');bukapayment();ChangeTab2('Payment',10);">Payment</option>
				<option onclick="$('#Payment_Val').attr('disabled',true);lepasevent();" value="REJECT">Reject to Invoice Delivery</option>
			</select>

		</div>
		<div class="FormInput_left">

			Sub Action

		</div>
		<div class="FormInput_right">

			<select id="Sub_Action">
				<option value="">Select</option>
				<option onclick="BANKDetail('ARVENDOR');">AR VENDOR</option>
				<option onclick="BANKDetail('BANK_ADVANCE');" value='BANK_ADVANCE'>BANK ADVANCE</option>
				<option onclick="BANKDetail('CASH_ADVANCE');" value='CASH_ADVANCE'>CASH ADVANCE</option>
				<option onclick="BANKDetail('BI');">BANK INTRANSIT</option>
				<option onclick="BANKDetail('TITIPAN');" value='213.4'>TITIPAN LAIN LAIN</option>
				<option onclick="BANKDetail('CASH');" value='111.1'>Petty Cash</option>
				<?php
				$mx = DB::table("branch_coa")->where("Group_Report", "BANK")->where("Grouping_ID", "!=", "")->get();

				foreach ($mx as $xy) {
					echo "<option value='" . $xy->Acc_No . "' onclick=\"BANKDetail('$xy->Acc_No');\">" . $xy->Description . "</option>";
				}
				?>
			</select>

		</div>
		<div class="FormInput_left">

			References

		</div>

		<div class="FormInput_right">
			<div id="divRef" size='300'>
				<select id="References" size="300"> </select>
			</div>
		</div>


		<div class="FormInput_left">

			Payment Value

		</div>
		<div class="FormInput_right">

			<input type="text" id="Payment_Val" size="25" value="0" onchange="ChangeTab2(this.value,9);" disabled="disabled" />

		</div>



		<div class="FormInput_left">

			<!--Promise To Pay-->
			Payment Date

		</div>
		<div class="FormInput_right">

			<input type="text" id="PTP" size="15" onchange="ChangeTab2(this.value,2);" readonly="readonly" value="<?php echo date("Y-m-d") ?>" />

		</div>
		<div class="FormInput_left" style="height:80px">

			Note Action

		</div>
		<div class="FormInput_right" style="height:80px">

			<input type="text" id="Note" size="50" onchange="ChangeTab2(this.value,13);" />
			<input type="button" id="addlistmain" name="addlistmain" value="Add to Payment List" class="btn btn-info mt-2 mr-2" />
		</div>

	</div>
	<div id="APDetail" style=" width: 100%; height: 300px;"></div>
	<div>
		<input type="button" id="ExcelDetailAR" name="ExcelDetailAR" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="APDetail.toExcel('./export/generate.php');" />
		<input type="button" id="addlistmain2" name="addlistmain2" value="Add Invoice to Payment" class="btn btn-success mt-2 mr-2" />

	</div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PAYMENT LISTS</div>
	<div id="ARPaymentList" style=" width: auto; height: 200px;"></div>
	<input type="button" value="Add Lists" id="addlists" class="btn btn-info mt-2 mr-2" /> <input type="button" value="Remove Lists" id="removelists" class="btn btn-danger mt-2 mr-2" /> :: <input type="button" value="Submit" id="SubmitAR" class="btn btn-success mt-2 mr-2" />

	<div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AP HISTORY</div>
		<div id="GRIDDETAIL" style=" width: auto; height: 350px;"></div>
		<div>


			<input type="button" id="ExcelDetailAR2" name="ExcelDetailAR2" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="ARHistory.toExcel('./export/generate.php');" />

		</div>

	</div>


	<script>
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
		$("#addlistmain").click(function() {


			var action = $("#ActionP").val();
			var subaction = $("#Sub_Action").val();
			var subactiontext = $("#Sub_Action option:selected").text();

			var references = $("#References").val();
			var paymentval = $("#Payment_Val").val();
			var ptp = $("#PTP").val();
			var note = $("#Note").val();
			if (action == 'REJECT') {
				ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, subaction + '_<?php echo $_GET["CID"] ?>', ptp, 'REJECT', subactiontext, '0', paymentval, subaction, references, paymentval, note]);

			} else {
				if (ptp != '') {
					if (subaction == 'BANK_ADVANCE' || subaction == 'CASH_ADVANCE') {
						ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, references, ptp, subaction, subactiontext, '0', paymentval, subaction, references, paymentval, note]);

					} else {

						ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, subaction + '_<?php echo $_GET["CID"] ?>', ptp, subaction, subactiontext, '0', paymentval, subaction, references, paymentval, note]);
					}

				} else {
					alert('Promise Kept is not defined!');
				}
			}


		});
		$("#addlistmain2").click(function() {

			var nomorinv = APDetail.cells(APDetail.getSelectedId(), 1).getValue();
			if (!nomorinv) {
				return false;
			}
			var action = $("#ActionP").val();
			var subaction = APDetail.cells(APDetail.getSelectedId(), 7).getValue();
			var subactiontext = APDetail.cells(APDetail.getSelectedId(), 8).getValue();

			var references = APDetail.cells(APDetail.getSelectedId(), 1).getValue();
			var paymentval = APDetail.cells(APDetail.getSelectedId(), 6).getValue();
			var ptp = $("#PTP").val();
			var note = $("#Note").val();
			if (ptp != '') {
				if (nomorinv != '') {
					if (action == 'REJECT') {
						ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, 'AP_' + nomorinv, ptp, 'REJECT', subactiontext, paymentval, '0', 'REJECT', references, paymentval, note]);
					} else {
						ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, 'AP_' + nomorinv, ptp, 'AP', subactiontext, paymentval, '0', subaction, references, paymentval, note]);
					}
				} else {
					alert('Please select the invoice first');
				}
			} else {
				alert('Date Payment is not defined!');
			}



		});
		$("#removelists").click(function() {

			ARPaymentList.deleteSelectedRows();




		});

		$("#addlists").click(function() {
			var nomorinv = APDetail.cells(APDetail.getSelectedId(), 1).getValue();
			if (!nomorinv) {
				return false;
			}
			var action = $("#ActionP").val();
			var subaction = $("#Sub_Action").val();
			var references = $("#References").val();
			var paymentval = $("#Payment_Val").val();
			var note = $("#Note").val();
			var ptp = $("#PTP").val();

			if (ptp != '') {
				if (nomorinv != '') {
					var nomorlist = ARPaymentList.uid();
					ARPaymentList.addRow(nomorlist, [ARPaymentList.getRowsNum() + 1, nomorinv, ptp, '', '', 0, 0, subaction, references, paymentval, note]);
					ARPaymentList.selectRowById(nomorlist);
					WindoWCOA("Chart of Accounts", "finance-ap-management-detail-coa", 650, 350);
				} else {
					alert('Please select the invoice first');
				}
			} else {
				alert('Promise Kept is not defined!');
			}


		});
		$("#SubmitAR").click(function() {
			$("#SubmitAR").hide();
			var dibayar = $("#Payment_Val").val();
			var tagihan = sumColumn(7);
			dhtmlx.confirm('Processing Now?', 'AP Monitoring', function(r) {

				if (r == true) {
					$.blockUI();
					PARPaymentList.sendData();
					alert('Data berhasil disubmit!');

					$("#ARCareDetail").html('');
					setTimeout(function() {
						$.unblockUI();
						refreshdata();
					}, 4000);

				} else {
					return false;
				}
			});



		});


		function bukapayment() {
			var out = 0;
			for (var i = 0; i < APDetail.getRowsNum(); i++) {
				rowIdMov = APDetail.getRowId(i);
				PAPDetail.setUpdated(rowIdMov, true);
			}
			return out;
		}


		var myCalendar;
		myCalendar = new dhtmlXCalendarObject(["PTP"]);
		myCalendar.setDateFormat("%Y-%m-%d");
		myCalendar.attachEvent("onClick", function(d) {
			ChangeTab(myCalendar.getFormatedDate("%Y-%m-%d", d), 16);
		});

		function lepasevent() {

			myCalendar.setSensitiveRange(null, null);
		}

		function pasangevent() {

			return true;
		}

		function ChangeTab2(Isi, Cols) {
			for (i = 0; i < ARPaymentList.getRowsNum(); i++) {

				idAR = ARPaymentList.getRowId(i);

				ARPaymentList.cells(idAR, Cols).setValue(Isi);
				ARPaymentList.cells(idAR, 2).setValue($("#PTP").val());


			}
		}


		function BANKDetail(IDData) {
			LoadM('divRef', 'ap-reference?Acc=' + IDData);

		}


		function sumColumn(ind) {
			var out = 0;
			for (var i = 0; i < APDetail.getRowsNum(); i++) {
				out += parseFloat(APDetail.cells2(i, ind).getValue());
			}
			return out;
		}
		APDetail = new dhtmlXGridObject('APDetail');
		APDetail.setImagePath("./dhtmlx/imgs/");
		APDetail.setHeader("No.,Invoice No,Invoice Date,Invoice Type,Aging(days),Total,Remain,Account No,Account Name,Subject Payment");
		APDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,");
		APDetail.setInitWidths("70,100,100,100,150,100,100,100,120,200,100,100,100,100,100,100");
		APDetail.setColAlign("center,center,center,center,center,right,right,right,center,right,center,center,left,left,center");
		APDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		APDetail.setColSorting("int,str,str,str,int,int,int,int,str,int,str,str,str,str");
		APDetail.setEditable(true);
		APDetail.setSkin("dhx_skyblue");
		APDetail.setNumberFormat("0,000", 6, ".", ",");
		APDetail.setNumberFormat("0,000", 5, ".", ",");
		APDetail.init();
		APDetail.loadXML('xml-finance-ap-management-detail?CID=<?php echo $CID ?>');

		function DBLKLIKAR(rId, cInd) {

			if (cInd == 3 || cInd == 4) {

				var nomorlist = ARPaymentList.uid();
				ARPaymentList.selectRowById(nomorlist);
				WindoWCOA("Chart of Accounts", "finance-ap-management-detail-coa", 650, 350);
			}


			return true;
		}

		ARPaymentList = new dhtmlXGridObject('ARPaymentList');
		ARPaymentList.setImagePath("./dhtmlx/imgs/");
		ARPaymentList.setHeader("No.,Invoice No,Date PTP,Payment Type,Description,Amount Dr,Amount Cr,Action/Acc,Reference No,Payment Val,Note");
		ARPaymentList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,");
		ARPaymentList.setInitWidths("70,100,100,100,200,100,100,100,100,100,200,100,100,100");
		ARPaymentList.setColAlign("center,center,center,center,left,right,right,center,center,center,center,center,center");
		ARPaymentList.setColTypes("ro,ro,ro,ro,ro,edn,edn,ro,ed,edn,ed");
		ARPaymentList.setColSorting("int,str,str,str,int,int,str,str,str,str,str,str,str");
		ARPaymentList.setEditable(true);
		ARPaymentList.setSkin("dhx_skyblue");
		ARPaymentList.setNumberFormat("0,000", 5, ".", ",");
		ARPaymentList.setNumberFormat("0,000", 6, ".", ",");
		ARPaymentList.attachEvent("onRowDblClicked", DBLKLIKAR);
		PARPaymentList = new dataProcessor("finance-ap-management-detail-list-update");
		PARPaymentList.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"ID": "<?php echo $_GET['CID'] ?>"
			}

		})
		PARPaymentList.setUpdateMode("Off");
		PARPaymentList.init(ARPaymentList);
		ARPaymentList.init();



		detailpayment = new dhtmlXGridObject('GRIDDETAIL');
		detailpayment.setImagePath("./dhtmlx/imgs/");
		detailpayment.setHeader("No.,Payment Type,Payment Task ID,Reference,Payment Acc No,Value,Note,Project ID,Created By,Date Created");
		detailpayment.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "<div id='totalamountx'>{#stat_total}</div>", "", "", "", ""]);
		detailpayment.setInitWidths("70,100,100,150,100,100,300,100,100,100");
		detailpayment.setColAlign("center,left,center,left,left,right,right,center,center,center");
		detailpayment.setColTypes("ro,ro,ro,ed,ed,edn,ro,ro,ro,ro");
		detailpayment.setColSorting("int,str,str,str,str,str,str");
		detailpayment.setDateFormat("%Y-%m-%d");
		detailpayment.setEditable(false);
		detailpayment.setNumberFormat("0,000", 5, ".", ",");
		detailpayment.enableKeyboardSupport(true);
		detailpayment.setSkin("dhx_skyblue");
		detailpayment.init();
		refreshdatadetail2();

		function refreshdatadetail2() {
			detailpayment.clearAndLoad("xml-finance-ap-mangement-detail-history?ID=<?php echo $_GET['CID']; ?>");

		}



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
	</script>