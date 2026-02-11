@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">KJK</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">New Request</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="KJK" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Cancel KJK" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-warning mt-2 mr-2"/>
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/>
			<input type="button" id="SubmitApproval" NAME="SubmitApproval" value="Submit to Approval" class="btn btn-success mt-2 mr-2"/>
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

$taskid = "KJK/$LSP/".date('ymd')."/".date('his');
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
KJK.setHeader("No,Task ID,NIK,Name,Allowance,Rate,KJK Value,Total,KJK Period,Note,Salary Group");
KJK.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter");
KJK.setInitWidths("80,100,120,250,100,100,100,100,100,300,100");
KJK.setColAlign("center,center,center,left,left,center,left,center,center");
KJK.setColTypes("ro,ro,ro,ro,ron,ron,edn,ron,dhxCalendar,ed,ro");
KJK.setColSorting("int,str,str,str,str,str,str,str,str");
KJK.setEditable(true);
KJK.attachEvent("onEditCell", editKJK);
KJK.setDateFormat("%Y-%m"); 
KJK.setNumberFormat("0,000",4,".",",");
KJK.setNumberFormat("0,000.00",5,".",",");
KJK.setNumberFormat("0,000.00",6,".",",");
KJK.setNumberFormat("0,000.00",7,".",",");
KJK.setSkin("dhx_skyblue");
PKJK = new dataProcessor("kjk-new-update");
	PKJK.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  	    payload: {"ID":"<?php echo $taskid ?>"}

    })
PKJK.setUpdateMode("Off");
PKJK.init(KJK);
KJK.init();
Reload_KJK();

function Reload_KJK(){
	KJK.clearAndLoad("xml-kjk-new?taskid=<?php echo $taskid ?>");
}

var winCOA;
var WCOA;
function WindoWCOA(text,url,width,height) {
    winCOA = new dhtmlXWindows();

	winCOA.attachViewportTo('PagesContent');
    WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
    WCOA.keepInViewport(false);
	WCOA.setText(text);
    WCOA.attachURL(url, true);
	WCOA.button("minmax1").hide();
    WCOA.button("park").hide();
	WCOA.centerOnScreen();
	WCOA.allowMove();	
}

$("#refreshdata").click(function(){
		Reload_KJK();
});
$("#SaveBM").click(function(){
		PKJK.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_KJK();
		},2000);
});
$("#DeleteBM").click(function(){
		KJK.deleteSelectedRows();
		PKJK.sendData();
		alert("Data were Canceled!");
		Reload_KJK();

		
});
$("#AddBM").click(function(){
	var idbi =KJK.uid();
	var idbix = idbi + "<?php echo rand(10,1000);?>";
		KJK.addRow(idbi,[KJK.getRowsNum()+1,"<?php echo $taskid;?>",,,,0,0,0,'<?php echo date("Y-m")?>'],0);
		WindoWCOA("Select Employee","new-kjk-employee?id=" + idbi,650,350);
		KJK.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	Reload_KJK();		
});
$("#SubmitApproval").click(function(){
	dhtmlx.confirm('Are you sure want to submit?', 'KJK Submission', function(r) {
		
		if (r==true){
			var out = 0;
			for (var i = 0; i < KJK.getRowsNum(); i++) {
				rowIdMov=KJK.getRowId(i);
				PKJK.setUpdated(rowIdMov,true);
				KJK.cells(rowIdMov,0).setValue('SB');
			}
			
			
			PKJK.sendData();
			setTimeout(function(){
				alert("Data was Submitted!");
				location.href='kjk-new';
			},2000);
			
		}
	});	
});


</script>

@endsection
