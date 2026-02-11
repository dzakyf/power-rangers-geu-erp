<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	function load_bac_kota(){
		$query = DB::table("branch_lists")->get();
			foreach ($query as $fa){
					$optx .="<option value='$fa->IATA_Code'>$fa->IATA_Code - $fa->City_Name</option>";
			}
		$optx .= "<option value=''>ALL</option>";
		return $optx;
	}
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Journal Transaction</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Tasks</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	
	 <div style="width:auto;height:50px; background-color:white;overflow:hidden">
    </select>
     Branch <select id="Origin">
     <?php if ($LSP == 'GEU-HO'){ ?>
     
     <option value="<?php echo $LSP ?>"><?php echo $LSP?></option>
     
     <?php
	 
	 }else{ 
	 	echo "<option>$LSP</option>";
	 }
	 ?>
     </select>
     <input type="button" id="AccRSubmit" value="Process"  class="btn btn-success mt-2 mr-2" /> 
     </div>
    <div id="BranchMGTJournalTask" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
    <div id="divtom">
        <input type="button" id="BranchMGTJournalTaskAdd" NAME="BranchMGTJournalTaskAdd" class="btn btn-success mt-2 mr-2" value="Add New" />
        :: Cancel Reason <input type="text" size="30" id="CancelReason" onchange="isiReason(this.value);"/>
        <input type="button" id="BranchMGTJournalTaskDel" NAME="BranchMGTJournalTaskDel" class="btn btn-danger mt-2 mr-2" value="Cancel Selected Task" />
        :: <input type="button" id="BranchMGTJournalTaskAddUpload" NAME="BranchMGTJournalTaskAddUpload" class="btn btn-info mt-2 mr-2" value="Upload via Excel" /> 
				&nbsp;&nbsp;<a href="./contoh_format_branch_journal_entry.xls" target="_blank" style="cursor:pointer;text-decoration:none;color:blue;font-size:10px;">Download Xls Format</a> :: 

	</div>
	<div id="DivCashDetail"></div>

	</div>
<script>

var winNewJournal;
var WNewJournal;
function WindoWNewJournal(text,url,width,height) {
    winNewJournal = new dhtmlXWindows();

	winNewJournal.attachViewportTo('PagesContent');
    WNewJournal = winNewJournal.createWindow("WNewJournal", 0, 0, width, height);
    WNewJournal.keepInViewport(false);
	WNewJournal.setText(text);
    WNewJournal.attachURL(url, true);
	WNewJournal.button("minmax1").hide();
    WNewJournal.button("park").hide();
	WNewJournal.centerOnScreen();
	WNewJournal.allowMove();	
}

$('#BranchMGTJournalTaskAddUpload').click(function(){
	WindoWNewJournal("Upload New Data Journal","accounting-journal-transaction-upload",518,290);
});

$('#BranchMGTJournalTaskAdd').click(function(){
	WindoWNewJournal("New Data Journal","accounting-journal-transaction-manual?City=" + $("#Origin").val(), 1024, 456);
});

$('#BranchMGTJournalTaskDel').click(function(){
	isipesans = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),7).getValue();
	if (isipesans == ''){
		alert('Please fill the reason for cancel/return!');
		return false;	
	}
		dhtmlx.confirm('Are you sure want  to cancel or return this tasklog?', 'Journal', function(r) {
			
		if (r==true){
			BranchMGTJournalTaskID = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),1).getValue();
			Status = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),4).getValue();
			
				PBranchMGTJournalTask.setUpdated(BranchMGTJournalTask.getSelectedId(),true);
				BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),7).setValue($("#Reason").val());
				PBranchMGTJournalTask.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					
					reloadJournal();
					
				},2000);
				
				
			
		}
	})
	
});
function reloadJournal(){
		BranchMGTJournalTask.clearAndLoad("xml-accounting-journal-transaction?City=" + $("#Origin").val());

}


function ConfirmNextProc(){
	//$.blockUI(); 
	setTimeout(function(){
		$("#DivCashDetail").html('');
		$("#CancelReason").val('');
		//$.unblockUI();
	},2000);
	
}

$("#AccRSubmit").click(function(){
		reloadJournal();	
})




var Saya = '<?php echo $Saya; ?>';
var winUploadExcelBAccounting;
var WUploadExcelBAccounting;


function isiReason(isipesan){
	BranchMGTJournalTaskMID = BranchMGTJournalTask.getSelectedId();
	if (BranchMGTJournalTaskMID == ''){
		alert('Mohon tentukan tasklog yang akan di cancel/return!');
	} else {
		BranchMGTJournalTaskID = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),7).setValue(isipesan);
	}
	
}
function BranchMGTJournalTaskDoubleClick() {

	BranchMGTJournalTaskMID = BranchMGTJournalTask.getSelectedId();
	BranchMGTJournalTaskID = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),1).getValue();
	Status = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),4).getValue();
	lokasiID = BranchMGTJournalTask.cells(BranchMGTJournalTask.getSelectedId(),3).getValue();
	if (Status == 'CREATED'){
		LoadM('DivCashDetail','accounting-journal-transaction-detail?ID=' + BranchMGTJournalTaskID + "&CID=" + BranchMGTJournalTaskMID + '&City=' + lokasiID);
	}else if (Status == 'SUBMITTED'){
		LoadM('DivCashDetail','accounting-journal-transaction-detail?ID=' + BranchMGTJournalTaskID + "&CID=" + BranchMGTJournalTaskMID + '&City=' + lokasiID);
	}else if (Status == 'PROPOSED'){
		LoadM('DivCashDetail','accounting-journal-transaction-detail?ID=' + BranchMGTJournalTaskID + "&CID=" + BranchMGTJournalTaskMID + '&City=' + lokasiID);
	}else if (Status == 'CHECKED'){
		LoadM('DivCashDetail','accounting-journal-transaction-detail_Last?ID=' + BranchMGTJournalTaskID + "&CID=" + BranchMGTJournalTaskMID + '&City=' + lokasiID);
	}else{
		LoadM('DivCashDetail','accounting-journal-transaction-detail?Mode=CHECK&ID=' + BranchMGTJournalTaskID + "&CID=" + BranchMGTJournalTaskMID + '&City=' + lokasiID);
	}
}

        BranchMGTJournalTask = new dhtmlXGridObject('BranchMGTJournalTask');
        BranchMGTJournalTask.setImagePath("./dhtmlx/imgs/");
        BranchMGTJournalTask.setHeader("No.,Task ID,Date,Location,Status,Created Date,Created By,reasons");
        BranchMGTJournalTask.attachHeader("#rspan,#rspan,#rspan,#text_filter,#rspan,#rspan,#text_filter,#rspan");
        BranchMGTJournalTask.setInitWidths("70,150,120,100,100,100,120,0");
        BranchMGTJournalTask.setColAlign("center,center,center,center,center,center,center,center,center");
        BranchMGTJournalTask.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        BranchMGTJournalTask.setColSorting("int,str,str,str,str,str,str");
        BranchMGTJournalTask.setEditable(true);
        BranchMGTJournalTask.setNumberFormat("0,000.00",5,".",",");
        BranchMGTJournalTask.enableKeyboardSupport(true);
		PBranchMGTJournalTask = new dataProcessor("journal-transaction-update");
		PBranchMGTJournalTask.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  
			})
        PBranchMGTJournalTask.init(BranchMGTJournalTask);
        BranchMGTJournalTask.setSkin("dhx_skyblue");
        BranchMGTJournalTask.attachEvent("onRowDblClicked", BranchMGTJournalTaskDoubleClick);
        BranchMGTJournalTask.init();
		reloadJournal();
</script>

@endsection
