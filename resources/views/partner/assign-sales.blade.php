<?php if ((auth()->user()->Priv_ID ?? '') === 'MITRA') { abort(403); } ?>
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Assignment</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="AssignSales" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselAssignSalesDet" name="ekselAssignSalesDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignSales.toExcel('./export/generate.php');" />
		<input type="button" id="detailassign" name="detailassign" value="Detail Assignment Leads" class="btn btn-info mt-2 mr-2" onclick="detailassign()" />
		@if(can_menu('SI00226', 'approve'))
		<button class="btn btn-success mr-2 mt-2" onclick="closeAssignment()">Close Assignment</button>
		@endif
	</div>
	<div class="mt-3" id="divlistAssign"></div>
</div>


<script>
	function closeAssignment() {
		AssignSalesID = AssignSales.getSelectedId();
		if (AssignSalesID == null) {
			dhtmlx.alert("Pilih assignment yang ingin di close");
			return;
		}

		dhtmlx.confirm('Close Assignment?', 'Assignment Leads', function(r) {

			if (r == true) {
				AssignSales.cells(AssignSalesID, 2).setValue("CLOSED");
				PAssignSales.setUpdated(AssignSalesID, "updated");
				PAssignSales.sendData();
				setTimeout(function() {
					alert("Berhasil menutup assignment")
					refreshdata();
					$('#divlistAssign').html('');
				}, 500);

			}
		});
	}

	function AssignSalesKlik() {
		AssignSalesID = AssignSales.getSelectedId();
		$("#divlistAssign").show();
		if (AssignSalesID != null) {
			$("#divlistProjectList").hide();
			LoadM('divlistAssign', 'partner-assign-sales-detail?ID=' + AssignSales.cells(AssignSalesID, 1).getValue());
		}
		return true;
	}

	AssignSales = new dhtmlXGridObject('AssignSales');
	AssignSales.setImagePath("./dhtmlx/imgs/");
	AssignSales.setHeader("No, Assignment ID, Status Assignment,Propose Date, Proposed By, Leads Name");
	AssignSales.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AssignSales.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignSales.setInitWidths("60,180,100,150,150,200");
	AssignSales.setColAlign("center,center,center,center,center,left");
	AssignSales.setColSorting("int,str,str,str,str,str");
	AssignSales.setColTypes("ro,ro,ro,ro,ro,ro");
	AssignSales.enableSmartRendering(true, 150);
	// AssignSales.setNumberFormat("0,000", 7, ".", ",");
	PAssignSales = new dataProcessor("partner-assign-sales-update");
	PAssignSales.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PAssignSales.setTransactionMode("POST", true);
	PAssignSales.setUpdateMode("Off");
	PAssignSales.init(AssignSales);
	AssignSales.attachEvent("onRowDblClicked", AssignSalesKlik);
	AssignSales.setSkin("dhx_skyblue");
	AssignSales.init();
	refreshdata();

	function refreshdata() {
		AssignSales.clearAndLoad('xml-partner-assign-sales');

	}

	function detailassign() {
		AssignSalesKlik();
	}
</script>
@endsection