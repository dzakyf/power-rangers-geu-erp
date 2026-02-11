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
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Recurring</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
				<input type="button" id="cancelrec" name="cancelrec"  class="btn btn-danger mt-2 mr-2" value="Cancel" onclick="canceldata();"  />
              <input type="button" id="manualrec" name="manualrec"  class="btn btn-warning mt-2 mr-2" value="Manual Exec" onclick="manualexec();"  />
			  <input type="button" id="postingbatch" name="postingbatch"  class="btn btn-success mt-2 mr-2" value="New Data" onclick="newdata();"  />
								

				   
                </div>
<script>



function canceldata(){
	dhtmlx.confirm('Are you sure want to cancel/delete this data?', 'Recurring Management', function(r) {
						
					if (r==true){
						CashOUT.deleteSelectedRows();
						PCashOUT.sendData();
						setTimeout(function(){
							refreshdata();
							$("#DivCashDetail").html('');
						},2000);
						
					}
				})

}

function manualexec(){
	dhtmlx.confirm('Are you sure want exec this data?', 'Recurring Manual Exec', function(r) {
						
					if (r==true){
						LoadM('DivCashDetail','accounting-recurring-manual-exec?ID=' + CashOUT.getSelectedId());

						
						
					}
				})
				
}

function newdata(){
				LoadM('DivCashDetail','accounting-recurring-data-create-new');

}
function refreshdata(){
	
	CashOUT.clearAndLoad("xml-accounting-recurring-data");

}

function CashOUTDoubleClick() {
	
	CashOUTID = CashOUT.getSelectedId();
		if (CashOUTID !=''){
			LoadM('DivCashDetail','accounting-recurring-data-detail?ID=' + CashOUTID);
		}
	return true;
}


CashOUT = new dhtmlXGridObject('CashOUT');
CashOUT.setImagePath("./dhtmlx/imgs/");
CashOUT.setHeader("No.,Recurring ID,Transaction Type,Task No,Description,Start Date,Times,Executed Recuring Date,Next Will Exec,Method,Created Date,Created By,Processed,Last Processed");
CashOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CashOUT.setInitWidths("70,100,100,100,100,100,100,100,100,100,100,110,100,100");
CashOUT.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
CashOUT.setColAlign("center,left,center,left,center,center,right,right,center,center,center,center,center,center");
CashOUT.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
CashOUT.setDateFormat("%Y-%m-%d"); 
PCashOUT = new dataProcessor("accounting-recurring-data-update");
PCashOUT.setTransactionMode({
 mode: "POST",
  total:true,
  headers:{
	'X-CSRF-TOKEN': "{{ csrf_token() }}"
  }	,

})

PCashOUT.setUpdateMode("Off");
PCashOUT.init(CashOUT);
CashOUT.setEditable(false);
CashOUT.setSkin("dhx_skyblue");

CashOUT.attachEvent("onRowDblClicked", CashOUTDoubleClick);
CashOUT.init();
refreshdata();

</script>
<div id="DivCashDetail"></div>
</div>
@endsection