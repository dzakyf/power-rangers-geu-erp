@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Payroll Benefit and Costs</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="Payroll_Lists" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Payroll_Lists.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="Payroll_ListsDiv"></div>


	
	</div>
<script>





Payroll_Lists = new dhtmlXGridObject('Payroll_Lists');
Payroll_Lists.setImagePath("./dhtmlx/imgs/");
Payroll_Lists.setHeader("No,No Urut,Name,Fix Allowance,WHT/PPH,Category,BOD,Overhead,OS,#cspan,#cspan,#cspan,OT,#cspan,#cspan,#cspan,Project,#cspan,#cspan,#cspan,AP Salary Group");
Payroll_Lists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#rspan,#rspan,Lintasarta,LMD,Artajasa,Kopkarla,Lintasarta,LMD,Artajasa,Kopkarla,Lintasarta,LMD,Artajasa,Kopkarla,#text_filter");

Payroll_Lists.setInitWidths("80,100,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
Payroll_Lists.setColAlign("center,center,left,center,left,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center,center");
Payroll_Lists.setColTypes("ro,ro,ro,ro,ro,ro,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,ed,combo");
Payroll_Lists.setColSorting("int,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Payroll_Lists.setEditable(true);
Payroll_Lists.setSkin("dhx_skyblue");
PPayroll_Lists = new dataProcessor("accounting-master-benefit-update");
	PPayroll_Lists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PPayroll_Lists.setUpdateMode("Off");
PPayroll_Lists.init(Payroll_Lists);

Payroll_Lists.init();
combocurr = Payroll_Lists.getColumnCombo(20);
combocurr.enableFilteringMode(true);
combocurr.load("xml-payroll-ap-salary");

	Reload_Payroll_Lists();

function Reload_Payroll_Lists(){
	Payroll_Lists.clearAndLoad("xml-accounting-master-benefit");	

}

$("#SaveBM").click(function(){
		PPayroll_Lists.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_Payroll_Lists();
		},2000);
});


$("#RefreshBM").click(function(){
	Reload_Payroll_Lists();		
});



</script>

@endsection
