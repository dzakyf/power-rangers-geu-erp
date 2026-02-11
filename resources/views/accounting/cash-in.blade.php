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
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Cash IN</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashIN" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashINDel" NAME="CashINDel" value="Ignore to Post" class="btn btn-danger mt-2 mr-2"  />
                   
                </div>
<script>

$('#CashINDel').click(function(){
	dhtmlx.confirm('Abaikan Posting?', 'Cash IN Posting', function(r) {
			
		if (r==true){
			
				CashIN.deleteRow(CashIN.getSelectedId());
				PCashIN.sendData();
			 	alert('Tasklog Diabaikan!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	CashIN.clearAndLoad("xml-accounting-cash-in");

}
$("#CashINSubmit").click(function(){
	
	PCashIN.sendData();
	alert('Data Succesfully created');
	setTimein(function(){
		refreshdata();
	},1500);

});
function CashINDoubleClick() {
	
	CashINID = CashIN.getSelectedId();
	kurs = CashIN.cells(CashINID,11).getValue();
	tglx = CashIN.cells(CashIN.getSelectedId(),2).getValue();
		if (CashINID !=''){
			LoadM('DivCashDetail','accounting-cash-in-detail?ID=' + CashINID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


CashIN = new dhtmlXGridObject('CashIN');
CashIN.setImagePath("./dhtmlx/imgs/");
CashIN.setHeader("No.,Cash No,Date,Account No,Voucher No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CashIN.setInitWidths("70,100,100,100,100,100,100,100,100,110,100,100");
CashIN.setColTypes("ro,ro,dhxCalendar,ro,ed,ro,ron,ron,ro,ro,ro,ro,ro");
CashIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashIN.setColSorting("int,str,str,str,str,ro");
CashIN.setDateFormat("%Y-%m-%d"); 

CashIN.setEditable(false);
CashIN.setNumberFormat("0,000",6,".",",");
CashIN.setNumberFormat("0,000",7,".",",");

CashIN.enableKeyboardSupport(true);
CashIN.setSkin("dhx_skyblue");
CashIN.enableEditEvents(true,false,true);

CashIN.attachEvent("onRowDblClicked", CashINDoubleClick);
PCashIN = new dataProcessor("accounting-cash-in-update");
PCashIN.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashIN.setUpdateMode("Off");
PCashIN.init(CashIN);
CashIN.init();
refreshdata();

</script>
<div id="DivCashDetail"></div>
</div>
@endsection