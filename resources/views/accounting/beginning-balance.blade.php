<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
	
	
	
?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Beginning Balance</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Tasks</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">

		<div style="width:auto;height:50px; background-color:white;overflow:hidden">
		Periode <input type="text" id="Tgl1" value="<?php echo date('Y');?>" size="5" readonly="readonly" />
		&nbsp;&nbsp;ACC No
			<select id="To_Acc">
				<?php
					echo "<option value=''>All</option>";
					$Query = DB::table("branch_coa")->orderBy("Acc_No","ASC")->get();
					foreach ($Query as $Data){
						echo "<option value='$Data->Acc_No'>$Data->Acc_No &nbsp;&nbsp;- $Data->Description</option>";
					}
				?>
				
			</select>
	
			
		 
			<input type="button" id="AccRSubmit" value="Submit" /> 
		 
		 </div>
		<div id="BEGBALANCE" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
	</div>				
<script>

var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["Tgl1"]);
	myCalendar.setDateFormat("%Y");

function BEGBALANCECounting() {
	$.unblockUI();
	return true;
}

	BEGBALANCE = new dhtmlXGridObject('BEGBALANCE');
	BEGBALANCE.setImagePath("./dhtmlx/imgs/");
	BEGBALANCE.setHeader("No,Account No,Description,Location,Group Balance,Group Report,Saldo,Ending Year,Reference,Created Date,Created By");
	BEGBALANCE.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,,,");
	BEGBALANCE.setInitWidths("80,100,200,100,150,100,150,100,150,100,100,100");
	BEGBALANCE.setColAlign("center,center,left,center,left,center,right,center,center,center,center");
	BEGBALANCE.setColTypes("ro,combo,ro,ro,ro,ro,edn,dhxCalendar,combo,ro,ro");
	BEGBALANCE.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	BEGBALANCE.setEditable(true);
	BEGBALANCE.setDateFormat("%Y"); 
	BEGBALANCE.setNumberFormat("0,000",6,".",",");
	BEGBALANCE.setSkin("dhx_skyblue");
	PBEGBALANCE = new dataProcessor("beginning-balance-update");
	PBEGBALANCE.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
			
		  }	
		   

		})
		
	PBEGBALANCE.init(BEGBALANCE);
		
	BEGBALANCE.init();
	


$("#AddJournal").click(function(){
		alert('b');
		BEGBALANCE.addRow(BEGBALANCE,[BEGBALANCE.getRowsNum()+1]);
		BEGBALANCE.selectRow(getid);
		
});

$("#SaveJournal").click(function(){
		PBEGBALANCE.sendData();
		alert("Adjustment was Saved!");
		
});
$("#RefreshBeginning").click(function(){
	dhtmlx.confirm('Are you sure refresh the beginning balance?', 'Journal Management', function(r) {
			
		if (r==true){
		$.blockUI();
		LoadM("BEGBALANCEDiv","beginning-balance-closing-year?Tgl1=" + $("#RefreshBeg").val());
		
		}
	})
});
$("#DeleteJournal").click(function(){
		BEGBALANCE.deleteSelectedRows();
		PBEGBALANCE.sendData();
		alert("Data were deleted!");
		
});



$("#AccRSubmit").click(function(){
	refreshing();	
		
});

function refreshing(){
	$.blockUI();
	BEGBALANCE.loadXML("xml-beginning-balance?Tgl1=" + $("#Tgl1").val() +"&City=" + $("#Origin").val()  + "&Acc=" + $("#To_Acc").val(),BEGBALANCECounting);
}
var winUploadExcelBAccounting;
var WUploadExcelBAccounting;

function WindoWNewJournal() {
    winUploadExcelBAccounting = new dhtmlXWindows();
	winUploadExcelBAccounting.attachViewportTo('PagesContent');
    WUploadExcelBAccounting = winUploadExcelBAccounting.createWindow("WUploadExcelBAccounting", 0, 0, 600, 400);
    WUploadExcelBAccounting.keepInViewport(false);
	WUploadExcelBAccounting.setText('Upload Beginning Balance');
    WUploadExcelBAccounting.attachURL('beginning-balance-upload', true);
	WUploadExcelBAccounting.button("minmax1").hide();
    WUploadExcelBAccounting.button("park").hide();
	WUploadExcelBAccounting.centerOnScreen();
	WUploadExcelBAccounting.allowMove();	
}



</script>
<div>

	<input type="button" id="UploadExcelCash" name="UploadExcelCash" value="Upload via Excel" onclick="WindoWNewJournal();" /> 
	&nbsp;&nbsp;<a href="./contoh_format_beginning.xls" target="_blank" style="cursor:pointer;text-decoration:none;color:blue;font-size:10px;">Download Xls Format</a> :: 
    <input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" onclick="BEGBALANCE.toExcel('./export/generate.php');"  />
    <input type="button" id="DeleteJournal" name="DeleteJournal" value="Delete Data" />                
    <input type="button" id="AddJournal" name="AddJournal" value="Add Data" />
    <input type="button" id="SaveJournal" name="SaveJournal" value="Save Data" /><br />
    <br /><b>Beginning Balance Reloading ::</b><br />
    For Year <select id="RefreshBeg">
		<?php
        for ($i = 2019; $i <= date ('Y'); $i++){
            
        echo "<option>$i</option>"	;
        }
        ?></select> ::	
    <input type="button" id="RefreshBeginning" name="RefreshBeginning" value="Refresh Beginning Balance" />
</div>

<div id="BEGBALANCEDiv"></div>
@endsection