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
                <div id="MasterSatisfication" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                <div>
                    <input type="button" id="ekselMasterSatisficationDet" name="ekselMasterSatisficationDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterSatisfication.toExcel('./export/generate.php');" />
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
	MasterSatisfication = new dhtmlXGridObject('MasterSatisfication');
	MasterSatisfication.setImagePath("./dhtmlx/imgs/");
	MasterSatisfication.setHeader("No, Peruntukan, Pertanyaan");
	MasterSatisfication.attachHeader("#rspan,#text_filter,#text_filter");
	// MasterSatisfication.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterSatisfication.setInitWidths("60,100,auto");
	MasterSatisfication.setColAlign("center,center,left");
	MasterSatisfication.setColSorting("int,str,str");
	MasterSatisfication.setColTypes("ron,combo,ed");
	MasterSatisfication.enableSmartRendering(true, 150);
    MasterSatisfication.setDateFormat("%Y-%m-%d");
	PMasterSatisfication = new dataProcessor("partner-master-satisfication-update");
	PMasterSatisfication.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PMasterSatisfication.setTransactionMode("POST", true);
	PMasterSatisfication.setUpdateMode("Off");
	PMasterSatisfication.init(MasterSatisfication);
    PMasterSatisfication.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(id)
        return true; // Return true to continue processing
    });
    PMasterSatisfication.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdata();
        }, 500);
    });
	MasterSatisfication.setSkin("dhx_skyblue");
	MasterSatisfication.init();

	// Peruntukan is a dropdown (participant / manager).
	var peruntukanCombo = MasterSatisfication.getColumnCombo(1);
	peruntukanCombo.addOption([["participant", "participant"], ["manager", "manager"]]);

	refreshdata();

	function refreshdata() {
		MasterSatisfication.clearAndLoad('xml-partner-master-satisfication');
	}

	function deleteRow() {
		dhtmlx.confirm('Delete this Row?', 'Opportunity', function(r) {

			if (r == true) {
				MasterSatisfication.deleteSelectedRows();
				PMasterSatisfication.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

    function addRow()
    {
        let uid = MasterSatisfication.uid();
        let num = MasterSatisfication.getRowsNum() + 1;
        MasterSatisfication.addRow(uid, [num, '', '', '', '']);
        MasterSatisfication.selectRow(uid);
    }

	function saveTraining() {
		PMasterSatisfication.sendData();
		refreshdata();
	}

</script>
@endsection