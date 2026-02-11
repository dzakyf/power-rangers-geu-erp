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
                <a href="#">Project Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Posting</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="OpporActive" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselOpporActiveDet" name="ekselOpporActiveDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpporActive.toExcel('./export/generate.php');"  />
			<input type="button" id="showdetail" name="showdetail"  class="btn btn-info mt-2 mr-2" value="Detail" onclick="OpporActiveKlik();"  />
			<input type="button" id="showdetail2" name="showdetail2"  class="btn btn-info mt-2 mr-2" value="Refresh Data" onclick="refreshdata();"  />
			<input type="button" id="postingbatch" name="postingbatch"  class="btn btn-warning mt-2 mr-2" value="Posting Batch" onclick="postingbatch();"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>
function postingbatch(){
	POpporActive.sendData();
	setTimeout(function(){
		refreshdata();
		
	},2000);
}
function OpporActiveKlik() {

OpporActiveID = OpporActive.getSelectedId();

if (OpporActiveID !=''){
LoadM('divlistOpportunity','accounting-project-posting-detail?ID=' + OpporActive.cells(OpporActiveID,1).getValue());
}
return true;
}
OpporActive = new dhtmlXGridObject('OpporActive');
OpporActive.setImagePath("./dhtmlx/imgs/");
OpporActive.setHeader("Posting,Task No,PO/Posting Date,Periode,Company Name,Reference,Subject,Created Date,Estimated Rev,Category,Expired Date,Note,Status");
OpporActive.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
OpporActive.setInitWidths("60,120,100,100,200,120,200,120,100,100,100,200,100");
OpporActive.setColAlign("center,center,center,center,left,left,left,left,right,left,left,left,center");
OpporActive.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
OpporActive.setColTypes("ch,ro,dhxCalendar,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
OpporActive.enableSmartRendering(true, 150);
OpporActive.setNumberFormat("0,000",8,".",",");
OpporActive.setDateFormat("%Y-%m-%d"); 
POpporActive = new dataProcessor("accounting-project-posting-update");
POpporActive.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
POpporActive.setUpdateMode("Off");
POpporActive.init(OpporActive);
OpporActive.setSkin("dhx_skyblue");
OpporActive.init();
refreshdata();

function refreshdata(){
	OpporActive.clearAndLoad('xml-accounting-project-posting');

}
</script>
@endsection