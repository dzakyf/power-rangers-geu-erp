<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$getUsers = DB::table('users')->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Sales & Marketing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Master Target</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER TARGET</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesTiering" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesTieringDet" name="ekselSalesTieringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesTiering.toExcel('./export/generate.php');" />
				<input type="button" id="addSalesTiering" name="addSalesTiering" class="btn btn-primary mt-2 mr-2" value="Add Row" onclick="addRow()" />
				<input type="button" id="submitSalesTiering" name="submitSalesTiering" class="btn btn-success mt-2 mr-2" value="Update" onclick="submitButton()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesTiering = new dhtmlXGridObject('SalesTiering');
	SalesTiering.setImagePath("./dhtmlx/imgs/");
	SalesTiering.setHeader("No,Sales, Year, Month<?php for($i=1;$i<=11;$i++) echo ',#cspan' ?>");
	SalesTiering.attachHeader("#rspan,#rspan,#rspan<?php for($i=1;$i<=12;$i++) {echo ',' . $i . ' - ' . date('F', mktime(0, 0, 0, $i, 1));} ?>");
	SalesTiering.attachHeader("#rspan,#text_filter,#text_filter<?php for($i=1;$i<=12;$i++) {echo ',' . '#text_filter';} ?>");
	// SalesTiering.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesTiering.setInitWidths("60,100,100<?php for($i=1;$i<=12;$i++) {echo ',100';} ?>");
	SalesTiering.setColAlign("center,left,center<?php for($i=1;$i<=12;$i++) {echo ',right';} ?>");
	SalesTiering.setColSorting("int,str,int<?php for($i=1;$i<=12;$i++) {echo ',int';} ?>");
	SalesTiering.setColTypes("ron,combo,edn<?php for($i=1;$i<=12;$i++) {echo ',edn';} ?>");
    PSalesTiering = new dataProcessor("partner-sales-master-target-update");
	PSalesTiering.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PSalesTiering.setTransactionMode("POST", true);
	PSalesTiering.setUpdateMode("Off");
    PSalesTiering.init(SalesTiering);
    @for ($i = 0; $i < 12; $i++)
        SalesTiering.setNumberFormat("0,000",{{$i+3}},",",".");
    @endfor
	SalesTiering.enableSmartRendering(true, 150);
	// SalesTiering.attachEvent("onRowDblClicked", SalesTieringLKManually);
	SalesTiering.setSkin("dhx_skyblue");
	SalesTiering.init();

	combotopo = SalesTiering.getColumnCombo(1);
	combotopo.enableFilteringMode(true);
    @foreach ($getUsers as $value)
        combotopo.addOption("{{ $value->username }}", "{{ $value->Name }}");
    @endforeach
	refreshdata();

	function refreshdata() {
		SalesTiering.clearAndLoad('xml-partner-sales-master-target');
	}

    function addRow() {
        var id = SalesTiering.uid();
        let nums = SalesTiering.getRowsNum() + 1;
        SalesTiering.addRow(id, [nums,"", "", "", "", "", "", "", "", "", "", "", ""]);
        SalesTiering.selectRowById(id);
    }

    function deleteRow() {
        var id = SalesTiering.getSelectedRowId();
        if (id) {
            SalesTiering.deleteRow(id);
        } else {
            alert("Please select a row to delete.");
        }
    }

    function submitButton()
    {
        PSalesTiering.sendData();
        alert('Data Successfully Submitted!');
        setTimeout(function() {
            refreshdata();
        }, 1000);
    }
</script>
@endsection