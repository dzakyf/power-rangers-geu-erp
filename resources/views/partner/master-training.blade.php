<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Master</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">SOP Compliance</span>
		</li>
	</ol>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="MasterTraining" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                <div>
                    <input type="button" id="ekselMasterTrainingDet" name="ekselMasterTrainingDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterTraining.toExcel('./export/generate.php');" />
                    <input type="button" id="addRow" name="addRow" value="Add Row" class="btn btn-info mt-2 mr-2" onclick="addRow()" />
                    <input type="button" id="deleteRow" name="deleteRow" value="Delete Row" class="btn btn-danger mt-2 mr-2" onclick="deleteRow()" />
                    <input type="button" id="saveTraining" name="saveTraining" value="Save Training" class="btn btn-success mt-2 mr-2" onclick="saveTraining()" />
            
                    <input type="file" id="csvFileInput" accept=".csv" style="display: none;">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	MasterTraining = new dhtmlXGridObject('MasterTraining');
	MasterTraining.setImagePath("./dhtmlx/imgs/");
	MasterTraining.setHeader("No, Peruntukan Pelatihan, Nama Pelatihan, Frekuensi Pelatihan, Jadwal Pelatihan, Dibuat oleh");
	MasterTraining.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// MasterTraining.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterTraining.setInitWidths("60,100,200,100,100,100,100");
	MasterTraining.setColAlign("center,center,left,center,center,center");
	MasterTraining.setColSorting("int,str,int,str,str,str");
	MasterTraining.setColTypes("ron,ed,ed,ed,ed,ed");
	MasterTraining.enableSmartRendering(true, 150);
    MasterTraining.setDateFormat("%Y-%m-%d");
	PMasterTraining = new dataProcessor("partner-master-training-update");
	PMasterTraining.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PMasterTraining.setTransactionMode("POST", true);
	PMasterTraining.setUpdateMode("Off");
	PMasterTraining.init(MasterTraining);
    PMasterTraining.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PMasterTraining.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdata();
        }, 500);
    });
	MasterTraining.setSkin("dhx_skyblue");
	MasterTraining.init();
	refreshdata();

	function refreshdata() {
		MasterTraining.clearAndLoad('xml-partner-master-training');
	}

	function deleteRow() {
		dhtmlx.confirm('Delete this Row?', 'Opportunity', function(r) {

			if (r == true) {
				MasterTraining.deleteSelectedRows();
				PMasterTraining.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

    function addRow()
    {
        let uid = MasterTraining.uid();
        let num = MasterTraining.getRowsNum() + 1;
        MasterTraining.addRow(uid, [num,"{{ date('Y-m-d') }}",'0','','']);
        MasterTraining.selectRow(uid);
    }

	function saveTraining() {
		PMasterTraining.sendData();
		refreshdata();
	}

</script>
@endsection