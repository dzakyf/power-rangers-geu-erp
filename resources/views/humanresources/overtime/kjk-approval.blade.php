@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">KJK</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="KJK" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-warning mt-2 mr-2"/>
			<input type="button" id="SubmitApproval" NAME="SubmitApproval" value="Approve" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="KJKDiv"></div>


	
	</div>
<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

?>	
<script>

function editKJK(stage,rId,cInd,nValue,oValue) {
	
	if (stage == 2){
		var rates = KJK.cells(rId,5).getValue();
		var kjkval = KJK.cells(rId,6).getValue();	
		var rumus = rates * kjkval * 2;
		KJK.cells(rId,7).setValue(rumus);
	}				
		return true
}




KJK = new dhtmlXGridObject('KJK');
KJK.setImagePath("./dhtmlx/imgs/");
KJK.setHeader("Select,Task ID,NIK,Name,Allowance,Rate,KJK Value,Total,KJK Period,Note,Salary Group");
KJK.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter");
KJK.setInitWidths("80,100,120,250,100,100,100,100,100,300,100");
KJK.setColAlign("center,center,center,left,left,center,left,center,center");
KJK.setColTypes("ch,ro,ro,ro,ron,ron,edn,ron,dhxCalendar,ed,ro");
KJK.setColSorting("int,str,str,str,str,str,str,str,str");
KJK.setEditable(true);
KJK.attachEvent("onEditCell", editKJK);
KJK.setDateFormat("%Y-%m"); 
KJK.setNumberFormat("0,000",4,".",",");
KJK.setNumberFormat("0,000.00",5,".",",");
KJK.setNumberFormat("0,000.00",6,".",",");
KJK.setNumberFormat("0,000.00",7,".",",");
KJK.setSkin("dhx_skyblue");
PKJK = new dataProcessor("kjk-approval-update");
	PKJK.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,

    })
PKJK.setUpdateMode("Off");
PKJK.init(KJK);
KJK.init();
Reload_KJK();

function Reload_KJK(){
	KJK.clearAndLoad("xml-kjk-approval");
}


$("#refreshdata").click(function(){
		Reload_KJK();
});

$("#SubmitApproval").click(function(){
	dhtmlx.confirm('Are you sure want to approve?', 'KJK Submission', function(r) {
		
		if (r==true){
			var out = 0;
			for (var i = 0; i < KJK.getRowsNum(); i++) {
				rowIdMov=KJK.getRowId(i);
				PKJK.setUpdated(rowIdMov,true);
				KJK.cells(rowIdMov,0).setValue('SB');
			}
			
			
			PKJK.sendData();
			setTimeout(function(){
				alert("Data was Approved!");
				location.href='kjk-approval';
			},2000);
			
		}
	});	
});


</script>

@endsection
