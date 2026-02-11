@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Accounting</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Chart Of Account</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Banks</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
	<div id="BranchCoA" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
	<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BranchCoA.toExcel('./export/generate.php');" />
	<input type="button" id="RefreshBCoA" NAME="RefreshBCoA" value="Refresh Data" class="btn btn-info mt-2 mr-2" />
	<input type="button" id="DeleteBCoA" NAME="DeleteBCoA" value="Delete Data" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="AddBCoA" NAME="AddBCoA" value="Add Data" class="btn btn-success mt-2 mr-2" />
	<input type="button" id="SaveBCoA" NAME="SaveBCoA" value="Save Data" class="btn btn-success mt-2 mr-2" />
</div>

<div id="BranchCoADiv"></div>



</div>
<script>
	function BranchCoACounting() {
		$.unblockUI();
		return true;
	}
	BranchCoA = new dhtmlXGridObject('BranchCoA');
	BranchCoA.setImagePath("./dhtmlx/imgs/");
	BranchCoA.setHeader("&nbsp;,Acc No,Description,Group,Remark,Normal,Class,As Data,Sorts,Status");
	BranchCoA.attachHeader("&nbsp;,#text_filter,#text_filter,&nbsp;,#rspan,#rspan,#text_filter,#text_filter,#rspan,#combo_filter");
	BranchCoA.setInitWidths("60,100,300,200,250,150,100,80,80,120");
	BranchCoA.setColAlign("center,center,left,center,left,center,center,center,center,center");
	BranchCoA.setColSorting("str,str,str,str,str,str,str,str,str,str");
	BranchCoA.setColTypes("ro,edtxt,edtxt,edtxt,edtxt,coro,ed,coro,edn,coro");
	BranchCoA.setEditable(true);
	BranchCoA.enableSmartRendering(true, 100);
	//BranchCoA.enableMultiline(true);
	//BranchCoA.enableEditEvents(true, true, true);
	var comboboxNormal = BranchCoA.getCombo(5);
	comboboxNormal.put("Kredit", "Kredit");
	comboboxNormal.put("Debet", "Debet");
	var combobox = BranchCoA.getCombo(7);
	combobox.put("1", "Bukan Header");
	combobox.put("0", "Header");
	var comboboxX = BranchCoA.getCombo(9);
	comboboxX.put("ACTIVE", "ACTIVE");
	comboboxX.put("INACTIVE", "INACTIVE");
	
	BranchCoA.setSkin("dhx_skyblue");
	PBranchCoA = new dataProcessor("accounting-master-coa-update");
	PBranchCoA.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	PBranchCoA.setUpdateMode("Off");
	PBranchCoA.init(BranchCoA);
	BranchCoA.enableEditEvents(true, true, true);
	BranchCoA.init();

	reloadcoa();


	function reloadcoa() {
		//	$.blockUI();
		BranchCoA.clearAndLoad("xml-accounting-master-coa", finishload);

	}

	function finishload() {
		//$.unblockUI();
	}
	$("#SaveBCoA").click(function() {
		PBranchCoA.sendData();
		alert("CoA was Saved!");

	});
	$("#DeleteBCoA").click(function() {
		BranchCoA.deleteSelectedRows();
		PBranchCoA.sendData();
		alert("Data were deleted!");
		reloadcoa();


	});
	$("#AddBCoA").click(function() {
		var idbi = BranchCoA.uid();
		BranchCoA.addRow(idbi, [BranchCoA.getRowsNum() + 1, ''], 0);
		BranchCoA.selectRow(0);
	});


	$("#RefreshBCoA").click(function() {
		reloadcoa();
	});
</script>

@endsection