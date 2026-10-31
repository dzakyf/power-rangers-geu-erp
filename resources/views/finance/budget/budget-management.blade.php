@extends('layout.mainlayout-finance')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>FINANCE</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">BUDGET</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="BudgetManagement" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BudgetManagement.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Budget" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="dteil" NAME="dteil" value="Detail Budget" onclick='ApprovalInvKlik()' class="btn btn-warning mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Budget" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Update" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="BudgetManagementDiv"></div>


	
	</div>
<script>



function ApprovalInvKlik() {

CustomerListsID = BudgetManagement.getSelectedId();
var stats = BudgetManagement.cells(BudgetManagement.getSelectedId(),18).getValue();

if (CustomerListsID !=''){
	LoadM('BudgetManagementDiv','budget-management-detail?ID=' + CustomerListsID + '&status=' + stats);
}
return true;
}


BudgetManagement = new dhtmlXGridObject('BudgetManagement');
BudgetManagement.setImagePath("./dhtmlx/imgs/");
BudgetManagement.setHeader("No,Year,Department,Growth(%),January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Status");
BudgetManagement.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,");
BudgetManagement.setInitWidths("30,50,140,75,115,115,115,115,115,115,115,115,115,115,115,115,115,300,100");
BudgetManagement.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,center");
BudgetManagement.setColTypes("ro,ed,combo,ed,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro");
BudgetManagement.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,str");
BudgetManagement.setEditable(true);
BudgetManagement.setNumberFormat("0,000.00",4,".",",");
BudgetManagement.setNumberFormat("0,000.00",5,".",",");
BudgetManagement.setNumberFormat("0,000.00",6,".",",");
BudgetManagement.setNumberFormat("0,000.00",7,".",",");
BudgetManagement.setNumberFormat("0,000.00",8,".",",");
BudgetManagement.setNumberFormat("0,000.00",9,".",",");
BudgetManagement.setNumberFormat("0,000.00",10,".",",");
BudgetManagement.setNumberFormat("0,000.00",11,".",",");
BudgetManagement.setNumberFormat("0,000.00",12,".",",");
BudgetManagement.setNumberFormat("0,000.00",13,".",",");
BudgetManagement.setNumberFormat("0,000.00",14,".",",");
BudgetManagement.setNumberFormat("0,000.00",15,".",",");
BudgetManagement.setNumberFormat("0,000.00",16,".",",");
BudgetManagement.setNumberFormat("0,000.00",17,".",",");
BudgetManagement.setNumberFormat("0,000.00",18,".",",");
BudgetManagement.setNumberFormat("0,000.00",19,".",",");
BudgetManagement.setNumberFormat("0,000.00",20,".",",");
BudgetManagement.setNumberFormat("0,000.00",21,".",",");

BudgetManagement.setSkin("dhx_skyblue");
PBudgetManagement = new dataProcessor("finance-budget-management-update");
	PBudgetManagement.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,

    })
PBudgetManagement.setUpdateMode("Off");
PBudgetManagement.init(BudgetManagement);
BudgetManagement.init();
combocurr = BudgetManagement.getColumnCombo(2);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-department");
	reloadbudgetmgt();


function reloadbudgetmgt(){
	BudgetManagement.clearAndLoad("xml-finance-budget-management");	

}

$("#SaveBM").click(function(){
	var stats = BudgetManagement.cells(BudgetManagement.getSelectedId(),18).getValue();
	if (stats =='CREATED' || stats =='' ){
		PBudgetManagement.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			reloadbudgetmgt();
		},1000);
	}
		
});
$("#DeleteBM").click(function(){
	var stats = BudgetManagement.cells(BudgetManagement.getSelectedId(),18).getValue();
	if (stats =='CREATED' || stats ==''){

		BudgetManagement.deleteSelectedRows();
		PBudgetManagement.sendData();
		alert("Data were deleted!");
		
		reloadbudgetmgt();
	}

		
});
$("#AddBM").click(function(){
	
	var idbi =BudgetManagement.uid();
		BudgetManagement.addRow(idbi,[BudgetManagement.getRowsNum()+1,'','','0','0','0','0','0','0','0','0','0','0','0','0','0','0'],0);
		BudgetManagement.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadbudgetmgt();		
});



</script>

@endsection
