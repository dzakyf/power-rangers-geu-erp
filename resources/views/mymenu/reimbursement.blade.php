<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('cash_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
		echo "You Don't have access to this menu!";
		exit;
	}
	
	$priv =  Auth::user()->Priv_ID;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>My Menu</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Reimbursement</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Reimbursement Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="Reimbursement" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="ReimbursementExport" NAME="ReimbursementExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="Reimbursement.toExcel('./export/generate.php');" />
                   	<input type="button" id="cashdetail2" NAME="cashdetail2" value="Detail" class="btn btn-info mt-2 mr-2" />

                </div>
<script>

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");




$('#SubmitReimbursement').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		Reimbursement.clearAll();
	
	},1500);
});




$('#ReimbursementDel').click(function(){
	dhtmlx.confirm('Are you sure want to cancel this tasklog?', 'Reimbursement Management', function(r) {
			
		if (r==true){
				$("#DivCashDetail").html('');
				Reimbursement.deleteSelectedRows();
				PReimbursement.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	Reimbursement.clearAndLoad("xml-my-reimbursement");

}

$('#cashdetail2').click(function(){
	
		ReimbursementID = Reimbursement.getSelectedId();
		
		if (ReimbursementID !=''){
			LoadM('DivCashDetail','my-reimbursement-detail?ID=' + ReimbursementID);
		}
	return true;
});



Reimbursement = new dhtmlXGridObject('Reimbursement');
Reimbursement.setImagePath("./dhtmlx/imgs/");
Reimbursement.setHeader("No.,Reimbursement No,Date,Account No,Reference No,Department,Total,Responsibility,Created Date,Created By,Status,Currency");
Reimbursement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
Reimbursement.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
Reimbursement.setColTypes("ro,ro,ro,ro,ed,ro,ron,ro,ro,ro,ro,ro");

Reimbursement.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
Reimbursement.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
Reimbursement.setDateFormat("%Y-%m-%d"); 

Reimbursement.setEditable(false);
Reimbursement.setNumberFormat("0,000",6,".",",");

Reimbursement.setSkin("dhx_skyblue");

PReimbursement = new dataProcessor("my-reimbursement-update");
PReimbursement.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PReimbursement.setUpdateMode("Off");
PReimbursement.init(Reimbursement);
Reimbursement.init();
refreshdata();

</script>
<div id="DivCashDetail"></div>
</div>
@endsection