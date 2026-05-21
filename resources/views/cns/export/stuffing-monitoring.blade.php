<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Stuffing Monitoring</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Stuffing Monitoring List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STUFFING MONITORING LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdataDetail()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="StuffingMonitoring" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div class="mt-3">
				<button class="btn btn-primary mr-3" onclick="addRowStuffingMonitoring()">Add Container</button>
				<button class="btn btn-danger mr-3" onclick="deleteRowStuffingMonitoring()">Delete Container</button>
				<button class="btn btn-success mr-3" id="SubmitCustDetail">Update Data</button>
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mr-3" value="Export Excel" onclick="StuffingMonitoring.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	$('#SubmitCustDetail').click(function() {

		PStuffingMonitoring.sendData();
	})

    
	StuffingMonitoring = new dhtmlXGridObject('StuffingMonitoring');
	StuffingMonitoring.setImagePath("./dhtmlx/imgs/");
	StuffingMonitoring.setHeader("No, Order No, Jam Awal (Jam:Menit), Jam Akhir (Jam:Menit), Tanggal, Nopol, Kontainer, Seal, Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Note");
	StuffingMonitoring.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// StuffingMonitoring.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	StuffingMonitoring.setInitWidths("60,100,100,100,100,200,150,150,100,100,100,100,100,auto");
	StuffingMonitoring.setColAlign("center,center,center,center,center,center,center,center,right,right,right,right,right,right,left");
	StuffingMonitoring.setDateFormat("%d %M %Y");
	StuffingMonitoring.setColSorting("int,str,str,str,str,str,str,str,int,int,int,int,int,int,str");
	StuffingMonitoring.setColTypes("ro,ed,ed,ed,dhxCalendar,ed,ed,ed,edn,edn,edn,edn,edn,edn,ed");
	StuffingMonitoring.enableSmartRendering(true, 150);
	StuffingMonitoring.setNumberFormat("0,000", 8, ".", ",");
	StuffingMonitoring.setNumberFormat("0,000", 9, ".", ",");
	StuffingMonitoring.setNumberFormat("0,000.00", 10, ".", ",");
	StuffingMonitoring.setNumberFormat("0,000", 11, ".", ",");
	StuffingMonitoring.setNumberFormat("0,000.00", 12, ".", ",");
	PStuffingMonitoring = new dataProcessor("cns-export-stuffing-monitoring-update");
	PStuffingMonitoring.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	});

	PStuffingMonitoring.setTransactionMode("POST", true);
	PStuffingMonitoring.setUpdateMode("Off");
	PStuffingMonitoring.init(StuffingMonitoring);
    PStuffingMonitoring.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(data)
        return true; // Return true to continue processing
    });
    PStuffingMonitoring.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdata();
        }, 500);
    });
	StuffingMonitoring.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
		if (stage == 2) {
			if (cInd == 2 || cInd == 3) {
				if (!/^\d{2}:\d{2}$/.test(nValue)) {
					alert("Format waktu harus HH:MM");
					return false; // Cancel the edit
				}
			}
			if (cInd == 8 || cInd == 9) {
				let tare = StuffingMonitoring.cells(rId, 8).getValue();
				let netto = StuffingMonitoring.cells(rId, 9).getValue();
				if ((tare != 0 && netto != 0) && tare != "" && netto != "") {
					let nettoMT = parseFloat(netto) / 1000;
					let gross = parseFloat(tare) + parseFloat(netto);
					StuffingMonitoring.cells(rId, 10).setValue(nettoMT.toFixed(2));
					StuffingMonitoring.cells(rId, 11).setValue(gross);
					StuffingMonitoring.cells(rId, 12).setValue((gross / 1000).toFixed(2));
				}
			}
		}
		return true; // Allow the edit
	});
	StuffingMonitoring.setSkin("dhx_skyblue");
	StuffingMonitoring.init();
	refreshdataDetail();

	function refreshdataDetail() {
		StuffingMonitoring.clearAndLoad('xml-cns-export-stuffing-monitoring?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
	}

	function addRowStuffingMonitoring()
	{
		let newId = StuffingMonitoring.uid();
		let rowCount = StuffingMonitoring.getRowsNum();
		StuffingMonitoring.addRow(newId, [rowCount + 1, "", "", "", "", "", "", "", "", "", "", "", "", ""]);
		StuffingMonitoring.selectRowById(newId);
	}

	function deleteRowStuffingMonitoring() {
		let selectedId = StuffingMonitoring.getSelectedId();
		if (selectedId) {
			StuffingMonitoring.deleteRow(selectedId);
		} else {
			alert("Pilih baris yang ingin dihapus!");
		}
	}
</script>
@endsection