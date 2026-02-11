<?php


use Illuminate\Http\Request;
use App\Controller\BudgetListing;
use Illuminate\Http\BudgetController;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
$Saya = Auth::user()->username;
?>

@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>FINANCE</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">BUDGET</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Transaction</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="BudgetTransaction" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BudgetTransaction.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
		</div>

		<div id="BudgetTransactionDiv"></div>


	
	</div>
<script>





BudgetTransaction = new dhtmlXGridObject('BudgetTransaction');
BudgetTransaction.setImagePath("./dhtmlx/imgs/");
BudgetTransaction.setHeader("No,Year,Department,Ratio From Last(%),January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Status");
BudgetTransaction.setInitWidths("80,70,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,300,100");
BudgetTransaction.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,center");
BudgetTransaction.setColTypes("ro,ed,combo,ed,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro");
BudgetTransaction.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,str");
BudgetTransaction.setEditable(true);
BudgetTransaction.setNumberFormat("0,000",4,".",",");
BudgetTransaction.setNumberFormat("0,000",5,".",",");
BudgetTransaction.setNumberFormat("0,000",6,".",",");
BudgetTransaction.setNumberFormat("0,000",7,".",",");
BudgetTransaction.setNumberFormat("0,000",8,".",",");
BudgetTransaction.setNumberFormat("0,000",9,".",",");
BudgetTransaction.setNumberFormat("0,000",10,".",",");
BudgetTransaction.setNumberFormat("0,000",11,".",",");
BudgetTransaction.setNumberFormat("0,000",12,".",",");
BudgetTransaction.setNumberFormat("0,000",13,".",",");
BudgetTransaction.setNumberFormat("0,000",14,".",",");
BudgetTransaction.setNumberFormat("0,000",15,".",",");
BudgetTransaction.setNumberFormat("0,000",16,".",",");
BudgetTransaction.setNumberFormat("0,000",17,".",",");
BudgetTransaction.setNumberFormat("0,000",18,".",",");
BudgetTransaction.setNumberFormat("0,000",19,".",",");
BudgetTransaction.setNumberFormat("0,000",20,".",",");
BudgetTransaction.setNumberFormat("0,000",21,".",",");

BudgetTransaction.init();
	reloadgroup();


function reloadgroup(){
	BudgetTransaction.clearAndLoad("xml-finance-budget-transaction");	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
