<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$opportunitySales = DB::table('opportunity_sales')->where('Sales_ID', $_GET['ID'])->first();
$pic = DB::table('users')->where('Location', $LSP)->where('username', '!=', $opportunitySales->Sales)->where('Priv_ID', 'SALES')->get();

?>
<div class="card">
	<div class="card-body">
		<div id="AssignSalesDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAssignSalesDetailDet" name="ekselAssignSalesDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignSalesDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailLeads" name="detailLeads" value="Detail Leads" class="btn btn-info mt-2 mr-2" onclick="showLeadsDetail()" />
			<input type="button" id="assignToSalesDetail" name="assignToSalesDetail" value="Re-Assign to Sales" class="btn btn-primary mt-2 mr-2" onclick="assignToSalesDetail()" />
			<div id="divAssignToSalesDetail" style="display: none;">
				<select name="Sales_Detail" id="Sales_Detail" class="mr-2">
					<option value="">-- Pilih Sales --</option>
					@foreach($pic as $p)
					<option value="{{ $p->username }}">{{ $p->Name }}</option>
					@endforeach
				</select>
				<input type="button" id="assignDetail" name="assignDetail" value="Re-Assign Sales" class="btn btn-success mt-2 mr-2" onclick="assignDetail()" />
			</div>

			<input type="file" id="csvFileInput" accept=".csv" style="display: none;">
		</div>
	</div>
</div>
<!-- <div class="mt-3" id="divlistAssignDetail"></div> -->
<div class="mt-3" id="divLeadsDetail" style="display:none;">
	@include('partner.leads-detail')
</div>

<script>
	function assignToSalesDetail() {
		if ($('#divAssignToSalesDetail').css('display') == 'none') {
			$('#divAssignToSalesDetail').css('display', 'inline-block');
		} else {
			$('#divAssignToSalesDetail').css('display', 'none');
			$('#Sales_Detail').val('');
		}
	}

	function assignDetail() {
		let sales = $('#Sales_Detail').val()
		let ids = AssignSalesDetail.getChangedRows().split(',').filter(id => id != '');
		console.log(ids);

		let checker = 0;
		for (let i = 0; i < ids.length; i++) {
			const ele = ids[i];
			console.log(AssignSalesDetail.cells(ele, 1).getValue());
			if (AssignSalesDetail.cells(ele, 1).getValue() == 1) {
				checker++;
			}
		}

		if (checker == 0 || ids.length == 0) {
			alert('Tidak dapat assign ke sales, pastikan checklist ada dicentang!');
			return;
		}

		if (sales == '') {
			alert("Pilih sales terlebih dahulu!")
			return
		}
		PAssignSalesDetail.sendData();
	}

	function AssignSalesDetailKlik() {
		AssignSalesDetailID = AssignSalesDetail.getSelectedId();
		$("#divlistAssignDetail").show();
		if (AssignSalesDetailID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistAssignDetail', 'partner-reassign-sales-detail-leads?ID=' + AssignSalesDetailID);
		}
		return true;
	}
	AssignSalesDetail = new dhtmlXGridObject('AssignSalesDetail');
	AssignSalesDetail.setImagePath("./dhtmlx/imgs/");
	AssignSalesDetail.setHeader("No,check list, Task ID,Created Person,Full Name, Date, Active Contact, Partnership Interest, Capital Estimation (Modal/RP), Created Date, Source, Response, Kualifikasi, Klasifikasi, Status, Note Cancellation");
	AssignSalesDetail.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AssignSalesDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignSalesDetail.setInitWidths("60,80,150,100,200,100,120,120,200,100,100,100,100,100,100,100");
	AssignSalesDetail.setColAlign("center,center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	AssignSalesDetail.setColSorting("int,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	AssignSalesDetail.setColTypes("ron,ch,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	AssignSalesDetail.enableSmartRendering(true, 150);
	// AssignSalesDetail.setNumberFormat("0,000", 7, ".", ",");
	PAssignSalesDetail = new dataProcessor("partner-reassign-sales-detail-update");
	PAssignSalesDetail.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PAssignSalesDetail.setTransactionMode("POST", true);
	PAssignSalesDetail.setUpdateMode("Off");
	PAssignSalesDetail.init(AssignSalesDetail);
	PAssignSalesDetail.attachEvent("onBeforeDataSending", function(id, state, data) {
		// Modify or add payload data here
		let sales = $('#Sales_Detail').val()
		// console.log(data)
		data.customPayload = {
			// Add your payload properties dynamically
			sales: sales,
			// ... and so on
		};
		// console.log(id)
		return true; // Return true to continue processing
	});
	PAssignSalesDetail.attachEvent("onAfterUpdateFinish", function(id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshdataDetail();
			assignToSalesDetail();
			refreshdata();
		}, 500);
	});
	AssignSalesDetail.attachEvent("onRowDblClicked", showLeadsDetail);
	AssignSalesDetail.setSkin("dhx_skyblue");
	AssignSalesDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		AssignSalesDetail.clearAndLoad('xml-partner-reassign-sales-detail?ID={{ $_GET["ID"] }}');

	}

	function detailLeads() {
		AssignSalesDetailKlik();
	}

	function showLeadsDetail() {
		var id = AssignSalesDetail.getSelectedId();
		if (!id) {
			alert('Pilih data terlebih dulu');
			return;
		}
		$('#ID').val(id);
		$('#divLeadsDetail').show();
		getLeads();
	}
</script>