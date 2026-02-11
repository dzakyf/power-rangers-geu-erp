@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Payroll</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Master Payroll Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="Payroll_Lists" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Payroll_Lists.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="Payroll_ListsDiv"></div>


	
	</div>
<script>





Payroll_Lists = new dhtmlXGridObject('Payroll_Lists');
Payroll_Lists.setImagePath("./dhtmlx/imgs/");
Payroll_Lists.setHeader("No,No Urut,Name,Fix Allowance,WHT/PPH,Category,Calculation Method,Cost Value,Max Value,Amount Limitation,Formula Method,TaxPayer,Created By,Created Date,Last Modified By,Last Modified Date,Last Seen By, Last Seen Date,AP Salary Group");
Payroll_Lists.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter");
Payroll_Lists.setInitWidths("80,100,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
Payroll_Lists.setColAlign("center,center,left,center,left,center,left,center,center,center,center,center,center,center,center,center");
Payroll_Lists.setColTypes("ro,ed,ed,coro,coro,coro,coro,edn,edn,edn,ed,coro,ro,ro,ro,ro,ro,ro,combo");
Payroll_Lists.setColSorting("int,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Payroll_Lists.setEditable(true);
Payroll_Lists.setNumberFormat("0,000.00",7,".",",");
Payroll_Lists.setNumberFormat("0,000.00",8,".",",");
Payroll_Lists.setNumberFormat("0,000",9,".",",");
Payroll_Lists.setSkin("dhx_skyblue");
PPayroll_Lists = new dataProcessor("payroll-lists-update");
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
combocurr = Payroll_Lists.getColumnCombo(18);
combocurr.enableFilteringMode(true);
combocurr.load("xml-payroll-ap-salary");

Payroll_Lists.getCombo(3).put('1','YES');
Payroll_Lists.getCombo(3).put('0','NO');
Payroll_Lists.getCombo(4).put('1','YES');
Payroll_Lists.getCombo(4).put('0','NO');
Payroll_Lists.getCombo(5).put('BASIC','BASIC');
Payroll_Lists.getCombo(5).put('ALLOWANCE','ALLOWANCE');
Payroll_Lists.getCombo(5).put('DEDUCTION','DEDUCTION');
Payroll_Lists.getCombo(6).put('VALUES','VALUES');
Payroll_Lists.getCombo(6).put('PERCENT','PERCENT');
Payroll_Lists.getCombo(11).put('EMPLOYEE','EMPLOYEE');
Payroll_Lists.getCombo(11).put('COMPANY','COMPANY');
	Reload_Payroll_Lists();

function Reload_Payroll_Lists(){
	Payroll_Lists.clearAndLoad("xml-payroll-lists");	

}

$("#SaveBM").click(function(){
		PPayroll_Lists.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_Payroll_Lists();
		},2000);
});
$("#DeleteBM").click(function(){
		Payroll_Lists.deleteSelectedRows();
		PPayroll_Lists.sendData();
		alert("Data were deleted!");
		Reload_Payroll_Lists();

		
});
$("#AddBM").click(function(){
	var idbi =Payroll_Lists.uid();
		Payroll_Lists.addRow(idbi,[Payroll_Lists.getRowsNum()+1,'','','','','','VALUES','0','0','0'],0);
		Payroll_Lists.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	Reload_Payroll_Lists();		
});



</script>

@endsection
