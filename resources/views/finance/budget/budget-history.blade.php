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
	<div>
	Year
	<input type='text' id='tahun' maxlength='4' value="<?php echo date("Y")?>"> 	<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />

	</div>
		 <div id="BudgetHistory" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BudgetHistory.toExcel('./export/generate.php');"  />
			</div>

		<div id="BudgetHistoryDiv"></div>


	
	</div>
<script>


function ApprovalInvKlik() {

CustomerListsID = BudgetHistory.getSelectedId();

if (CustomerListsID !=''){
	LoadM('BudgetManagementDiv','budget-history-detail?ID=' + CustomerListsID + "&tahun=" + $("#tahun").val());
}
return true;
}


BudgetHistory = new dhtmlXGridObject('BudgetHistory');
BudgetHistory.setImagePath("./dhtmlx/imgs/");
BudgetHistory.setHeader("No,Year,Department,Ratio From Last(%),January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Total Used,Note Used");
BudgetHistory.setInitWidths("80,70,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,300,100,200");
BudgetHistory.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,right,left");
BudgetHistory.setColTypes("ro,ed,combo,ed,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro,ro");
BudgetHistory.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,int,str");
BudgetHistory.setEditable(false);
BudgetHistory.setNumberFormat("0,000",4,".",",");
BudgetHistory.setNumberFormat("0,000",5,".",",");
BudgetHistory.setNumberFormat("0,000",6,".",",");
BudgetHistory.setNumberFormat("0,000",7,".",",");
BudgetHistory.setNumberFormat("0,000",8,".",",");
BudgetHistory.setNumberFormat("0,000",9,".",",");
BudgetHistory.setNumberFormat("0,000",10,".",",");
BudgetHistory.setNumberFormat("0,000",11,".",",");
BudgetHistory.setNumberFormat("0,000",12,".",",");
BudgetHistory.setNumberFormat("0,000",13,".",",");
BudgetHistory.setNumberFormat("0,000",14,".",",");
BudgetHistory.setNumberFormat("0,000",15,".",",");
BudgetHistory.setNumberFormat("0,000",16,".",",");
BudgetHistory.setNumberFormat("0,000",17,".",",");
BudgetHistory.setNumberFormat("0,000",18,".",",");
BudgetHistory.setNumberFormat("0,000",19,".",",");
BudgetHistory.setNumberFormat("0,000",20,".",",");
BudgetHistory.setNumberFormat("0,000",21,".",",");
BudgetHistory.setNumberFormat("0,000",23,".",",");
BudgetHistory.attachEvent("onRowDblClicked", ApprovalInvKlik);

BudgetHistory.setSkin("dhx_skyblue");
BudgetHistory.init();
reloadgroup();


function reloadgroup(){
	BudgetHistory.clearAndLoad("xml-finance-budget-history?tahun=" + $("#tahun").val());	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
