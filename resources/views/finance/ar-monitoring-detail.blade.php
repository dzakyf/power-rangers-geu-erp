<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$fadata = DB::table("customerz")->where("Customer_No", $_GET['CID'])->first();
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
				<option onclick="$('#Payment_Val').attr('disabled',true);ChangeTab('Communication',10);lepasevent();">Communication</option>
				<option onclick="$('#Payment_Val').attr('disabled',false).val('<?php echo $_GET['remain'] ?>');bukapayment();ChangeTab('Payment',10);">Payment</option>
				<option onclick="$('#Payment_Val').attr('disabled',true);ChangeTab('CM',10);pasangevent();">Credit Memorandum (CM)</option>
				<option onclick="$('#Payment_Val').attr('disabled',true);ChangeTab('REJECT_INVDELIVERY',10);lepasevent();">Reject to Invoice Delivery</option>
			</select>

		</div>
		<div class="FormInput_left">

			Sub Action

		</div>
		<div class="FormInput_right">

			<select id="Sub_Action">
				<option value="">Select</option>
				<option onclick="ChangeTab('Phone',11);">Phone</option>
				<option onclick="ChangeTab('Send Email',11);">Send Email</option>
				<option onclick="ChangeTab('Letter',11);">Letter</option>
				<option onclick="ChangeTab('Visit',11);">Visit</option>
				<option onclick="ChangeTab('APVENDOR',11);BANKDetail('211.1');" value='211.1'>AP VENDOR</option>
				<option onclick="ChangeTab('BI',11);BANKDetail('111.2.3');" value='111.2.3'>BANK INTRANSIT</option>
				<option onclick="ChangeTab('CM',11);BANKDetail('CM');" value='CM'>CREDIT MEMORANDUM (CM)</option>
				<option onclick="ChangeTab('CMTOTAL',11);BANKDetail('CMTOTAL');" value='CMTOTAL'>CREDIT MEMORANDUM (CM) TOTAL</option>
				<option onclick="ChangeTab('TITIPAN',11);BANKDetail('213.4');" value='213.4'>TITIPAN LAIN - LAIN</option>
				<?php
				$mx = DB::table("branch_coa")->where("Group_Report", "BANK")->where("Grouping_ID", "!=", "")->get();

				foreach ($mx as $xy) {
					echo "<option value='" . $xy->Acc_No . "' onclick=\"ChangeTab('" . $xy->Acc_No . "',11);ChangeTab2('" . $xy->Acc_No . "',7);BANKDetail('$xy->Acc_No');\">" . $xy->Description . "</option>";
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

			<input type="text" id="Payment_Val" size="25" value="0" onchange="ChangeTab(this.value,12);ChangeTab2(this.value,9);" disabled="disabled" />

		</div>

		<div class="FormInput_left">

			Debt Coll

		</div>
		<div class="FormInput_right">

			<input type="text" id="DebtColl" size="30" onchange="ChangeTab(this.value,13);ChangeTab2(this.value,10);" />

		</div>
		<div class="FormInput_left">

			P I C

		</div>
		<div class="FormInput_right">

			<input type="text" id="PIC" size="30" onchange="ChangeTab(this.value,14);ChangeTab2(this.value,11);" value="<?php echo $fadata->PIC_Billing_Name; ?>" />

		</div>

		<div class="FormInput_left">

			P I C No.

		</div>
		<div class="FormInput_right">

			<input type="text" id="PIC_No" size="25" onchange="ChangeTab(this.value,15);ChangeTab2(this.value,12);" value="<?php echo $fadata->PIC_Billing_Phone; ?>" />

		</div>
		<div class="FormInput_left">

			<!--Promise To Pay-->
			Promise Kept

		</div>
		<div class="FormInput_right">

			<input type="text" id="PTP" size="15" onchange="ChangeTab(this.value,16);ChangeTab2(this.value,2);" readonly="readonly" value="<?php echo date("Y-m-d") ?>" />

		</div>
		<div class="FormInput_left" style="height:80px">

			Note Action

		</div>
		<div class="FormInput_right" style="height:80px">

			<input type="text" id="Note" size="50" onchange="ChangeTab(this.value,17);ChangeTab2(this.value,13);" />
			<input type="button" id="addlistmain" name="addlistmain" value="Add to Payment List" class="btn btn-info mt-2 mr-2" />
		</div>

	</div>
	<div id="ARDetail" style=" width: 100%; height: 300px;"></div>
	<div>
		<input type="button" id="ExcelDetailAR" name="ExcelDetailAR" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="ARDetail.toExcel('./export/generate.php');" />
		<input type="button" id="addlistmain2" name="addlistmain2" value="Add Invoice to Payment" class="btn btn-success mt-2 mr-2" />

	</div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PAYMENT LISTS</div>
	<div id="ARPaymentList" style=" width: auto; height: 200px;"></div>
	<input type="button" value="Add Lists" id="addlists" class="btn btn-info mt-2 mr-2" /> <input type="button" value="Remove Lists" id="removelists" class="btn btn-danger mt-2 mr-2" /> :: <input type="button" value="Submit" id="SubmitAR" class="btn btn-success mt-2 mr-2" />

	<div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AR HISTORY</div>
		<div id="ARDetailHistory" style=" width: auto; height: 350px;"></div>
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

			var references = $("#References option:selected").val();
			var paymentval = $("#Payment_Val").val();
			var debtcol = $("#DebtColl").val();
			var pic = $("#PIC").val();
			var picno = $("#PIC_No").val();
			var ptp = $("#PTP").val();
			var note = $("#Note").val();
			if (ptp != '') {

				ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, 'CID_<?php echo $_GET["CID"] ?>', ptp, subaction, subactiontext, paymentval, '0', subaction, references, paymentval, debtcol, pic, picno, note,action]);

			} else {
				alert('Promise Kept is not defined!');
			}



		});
		$("#addlistmain2").click(function() {

			var nomorinv = ARDetail.cells(ARDetail.getSelectedId(), 1).getValue();
			if (!nomorinv) {
				return false;
			}
			var action = $("#ActionP").val();
			var subaction = ARDetail.cells(ARDetail.getSelectedId(), 7).getValue();
			var subactiontext = ARDetail.cells(ARDetail.getSelectedId(), 8).getValue();

			var references = ARDetail.cells(ARDetail.getSelectedId(), 3).getValue();
			var paymentval = ARDetail.cells(ARDetail.getSelectedId(), 6).getValue();
			var refcek = ARDetail.cells(ARDetail.getSelectedId(), 10).getValue();
			var debtcol = $("#DebtColl").val();
			var pic = $("#PIC").val();
			var picno = $("#PIC_No").val();
			var ptp = $("#PTP").val();
			var note = $("#Note").val();
			if (ptp != '') {
				if (nomorinv != '') {
					ARPaymentList.addRow(ARPaymentList.uid(), [ARPaymentList.getRowsNum() + 1, 'INV_' + nomorinv, ptp, subaction, subactiontext, '0', paymentval, subaction, references, paymentval, debtcol, pic, picno, note,refcek]);
				} else {
					alert('Please select the invoice first');
				}
			} else {
				alert('Promise Kept is not defined!');
			}



		});
		$("#removelists").click(function() {

			ARPaymentList.deleteSelectedRows();




		});

		$("#addlists").click(function() {
			var nomorinv = ARDetail.cells(ARDetail.getSelectedId(), 1).getValue();
			if (!nomorinv) {
				return false;
			}
			var action = $("#ActionP").val();
			var subaction = $("#Sub_Action").val();
			var references = $("#References").val();
			var paymentval = $("#Payment_Val").val();
			var debtcol = $("#DebtColl").val();
			var pic = $("#PIC").val();
			var picno = $("#PIC_No").val();
			var ptp = $("#PTP").val();
			var note = $("#Note").val();
			if (ptp != '') {
				if (nomorinv != '') {
					var nomorlist = ARPaymentList.uid();
					ARPaymentList.addRow(nomorlist, [ARPaymentList.getRowsNum() + 1, nomorinv, ptp, '', '', '0','0', subaction, references, paymentval, debtcol, pic, picno, note]);
					ARPaymentList.selectRowById(nomorlist);
					WindoWCOA("Chart of Accounts", "ar-monitoring-detail-coa", 650, 350);
				} else {
					alert('Please select the invoice first');
				}
			} else {
				alert('Promise Kept is not defined!');
			}


		});
		$("#SubmitAR").click(function() {
			
			var debetin = ($("#debetin").html());
			var kreditin = ($("#kreditin").html());
			if (debetin != kreditin){
				alert("Data tidak balance!");
				return false;
			}
			$("#SubmitAR").hide();
			var dibayar = $("#Payment_Val").val();
			var tagihan = sumColumn(7);
			dhtmlx.confirm('Processing Now?', 'AR Care', function(r) {

				if (r == true) {
					$.blockUI();
					PARDetail.sendData();
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
			for (var i = 0; i < ARDetail.getRowsNum(); i++) {
				rowIdMov = ARDetail.getRowId(i);
				PARDetail.setUpdated(rowIdMov, true);
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

		function ChangeTab(Isi, Cols) {
			for (i = 0; i < ARDetail.getRowsNum(); i++) {

				idAR = ARDetail.getRowId(i);

				ARDetail.cells(idAR, Cols).setValue(Isi);
				ARDetail.cells(idAR, 16).setValue($("#PTP").val());


			}
		}

		function BANKDetail(IDData) {
			LoadM('divRef', 'ar-reference?Acc=' + IDData);

		}


		function sumColumn(ind) {
			var out = 0;
			for (var i = 0; i < ARDetail.getRowsNum(); i++) {
				out += parseFloat(ARDetail.cells2(i, ind).getValue());
			}
			return out;
		}
		ARDetail = new dhtmlXGridObject('ARDetail');
		ARDetail.setImagePath("./dhtmlx/imgs/");
		ARDetail.setHeader("No.,Invoice No,Invoice Date,No INV KSPS,Aging(days),Total AR,Remain,Account No,Account Name,After PPH,Action,SubAction,PaymentVal,DebtColl,PIC,PIC No,PTP,Note,Claim,Claim Type,Claim Note,Collector,Rek No,Customer Name,Remain,PIC Receive,Delivered to Cust");
		ARDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		ARDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,,,,,,,,,,,,,,");
		ARDetail.setInitWidths("70,100,100,250,150,100,100,100,120,100,100,100,100,100,100,100,100,100,100,120,100,100,100,100,100,100,100");
		ARDetail.setColAlign("center,center,center,center,center,right,right,right,center,right,center,center,left,left,center");
		ARDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,edn,ro,txt,ro,ro,ro,ro,ron,ro,ro");
		ARDetail.setColSorting("int,str,str,str,int,int,int,int,str,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int,str,str");
		ARDetail.setEditable(true);
		ARDetail.setSkin("dhx_skyblue");
		ARDetail.setNumberFormat("0,000.00", 6, ".", ",");
		ARDetail.setNumberFormat("0,000.00", 5, ".", ",");
		PARDetail = new dataProcessor("ar-monitoring-detail-update");
		PARDetail.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			},
			payload: {
				"ID": "<?php echo $_GET['CID'] ?>"
			}

		})
		PARDetail.setUpdateMode("Off");
		PARDetail.init(ARDetail);
		ARDetail.init();
		ARDetail.loadXML('xml-ar-monitoring-detail?CID=<?php echo $CID ?>');

		function DBLKLIKAR(rId, cInd) {

			if (cInd == 3 || cInd == 4) {
				WindoWCOA("Chart of Accounts", "ar-monitoring-detail-coa", 650, 350);
			}


			return true;
		}

		ARPaymentList = new dhtmlXGridObject('ARPaymentList');
		ARPaymentList.setImagePath("./dhtmlx/imgs/");
		ARPaymentList.setHeader("No.,Invoice No,Date PTP,Payment Type,Description,Amount Dr,Amount Cr,Action,Reference No,Payment Val,Debt Coll,PIC,PIC No,Note,Actions");
		ARPaymentList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
		ARPaymentList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,<div id='debetin'>{#stat_total}</div>,<div id='kreditin'>{#stat_total}</div>,,,,,,,,");
		ARPaymentList.setInitWidths("70,100,100,100,150,100,100,100,100,100,100,100,100,100,60");
		ARPaymentList.setColAlign("center,center,center,center,right,right,center,center,center,center,center,center,center,center");
		ARPaymentList.setColTypes("ro,ro,ro,ro,ro,edn,edn,ro,ro,edn,ed,ed,ed,ed,ro");
		ARPaymentList.setColSorting("int,str,str,str,int,int,str,str,str,str,str,str,str,str");
		ARPaymentList.setEditable(true);
		ARPaymentList.setSkin("dhx_skyblue");
		ARPaymentList.setNumberFormat("0,000.00", 5, ".", ",");
		ARPaymentList.setNumberFormat("0,000.00", 6, ".", ",");
		ARPaymentList.setNumberFormat("0,000.00", 9, ".", ",");
		ARPaymentList.attachEvent("onRowDblClicked", DBLKLIKAR);
		PARPaymentList = new dataProcessor("ar-monitoring-detail-list-update");
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



		ARHistory = new dhtmlXGridObject('ARDetailHistory');
		ARHistory.setImagePath("./dhtmlx/imgs/");
		ARHistory.setHeader("No.,Customer,Name,Invoice No,Method,Debt Coll,Processed By,Processed Date,PTP,Action,Action Result,Result Output,PIC, PIC No,AR Value Before,Payment Value,Note,Created By, Created Date,Invoice No");
		ARHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		ARHistory.setInitWidths("70,100,170,0,0,0,0,100,100,100,100,100,100,100,100,100,300,100,100,100");
		ARHistory.setColAlign("center,center,left,center,center,left,left,center,center,left,left,left,left,left,right,right,left,left,center,center");
		ARHistory.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro");
		ARHistory.setEditable(true);
		ARHistory.setSkin("dhx_skyblue");
		ARHistory.setNumberFormat("0,000", 14, ".", ",");
		ARHistory.setNumberFormat("0,000", 15, ".", ",");
		ARHistory.init();
		ARHistory.loadXML('xml-ar-monitoring-detail-history?CID=<?php echo $CID ?>');




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