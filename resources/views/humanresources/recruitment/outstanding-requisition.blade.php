<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Requisition</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Outstanding Request</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		 <div id="SelectPO" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<div class="FormInput_right" style="height:175px;vertical-align:bottom;">

			<input type="button" value="Approved" id="UpdateReq" onclick="UpdateReq();" class="btn btn-info mt-2 mr-2"  />        	

		</div>		


	</div>	
		<div id="divlistRecruitment"></div>
<script>
SelectPO = new dhtmlXGridObject('SelectPO');
		SelectPO.setImagePath("./dhtmlx/imgs/");
		SelectPO.setHeader("Approve,ID,Department,Position,Qty Person,Detail Requirement,Detail Position,Total Amount(IDR),Site,Salary Offering,Benefit Information,Subject,Open Date,Close Date");
		SelectPO.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#cspan","#cspan","#stat_total",,,,,,]);
		SelectPO.setInitWidths("70,100,120,150,100,250,250,200,100,120,250,300,100,100");
		SelectPO.setColAlign("center,center,left,center,left,left,left,left,center,right,right,center,left,left,center,center");
		SelectPO.setColTypes("ch,ro,ed,ed,ed,txt,txt,edn,ed,edn,txt,txt,dhxCalendar,dhxCalendar");
		SelectPO.setColSorting("int,str,str,str,str,str,int,str,int,str,str,str,str");
		PSelectPO = new dataProcessor("outstanding-requisition-update");
		PSelectPO.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  
			})
			
		PSelectPO.setTransactionMode("POST", true);
		PSelectPO.setUpdateMode("Off");
		PSelectPO.init(SelectPO);
		SelectPO.setDateFormat("%Y-%m-%d"); 
		SelectPO.enableMultiline(true);
		SelectPO.setEditable(true);
		SelectPO.setNumberFormat("0,000",7,".",",");
		SelectPO.setNumberFormat("0,000",8,".",",");
		SelectPO.enableEditEvents(true,false,true);
		SelectPO.enableKeyboardSupport(true);
		SelectPO.setSkin("dhx_skyblue");
		SelectPO.init();
		SelectPO.clearAndLoad("xml-outstanding-requisition");
		
function UpdateReq(){

PSelectPO.sendData();
alert("Outstanding Approved!");
	setTimeout(function(){
		location.href='recruitment-outstanding-requisition';
	},1000);
}	
	


</script>
@endsection