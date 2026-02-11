@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>ACCOUNTING</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PROYEKSI CASHFLOW</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Transaction</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="ProyeksiTransaction" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ProyeksiTransaction.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
		</div>

		<div id="ProyeksiTransactionDiv"></div>


	
	</div>
<script>





ProyeksiTransaction = new dhtmlXGridObject('ProyeksiTransaction');
ProyeksiTransaction.setImagePath("./dhtmlx/imgs/");
ProyeksiTransaction.setHeader("No,Year,Department,Desciption,January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Status");
ProyeksiTransaction.setInitWidths("80,70,50,250,100,100,100,100,100,100,100,100,100,100,100,100,100,300,100,100");
ProyeksiTransaction.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,center");
ProyeksiTransaction.setColTypes("ro,ed,combo,ed,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,edn,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro");
ProyeksiTransaction.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,str");
ProyeksiTransaction.setEditable(true);
ProyeksiTransaction.setNumberFormat("0,000",4,".",",");
ProyeksiTransaction.setNumberFormat("0,000",5,".",",");
ProyeksiTransaction.setNumberFormat("0,000",6,".",",");
ProyeksiTransaction.setNumberFormat("0,000",7,".",",");
ProyeksiTransaction.setNumberFormat("0,000",8,".",",");
ProyeksiTransaction.setNumberFormat("0,000",9,".",",");
ProyeksiTransaction.setNumberFormat("0,000",10,".",",");
ProyeksiTransaction.setNumberFormat("0,000",11,".",",");
ProyeksiTransaction.setNumberFormat("0,000",12,".",",");
ProyeksiTransaction.setNumberFormat("0,000",13,".",",");
ProyeksiTransaction.setNumberFormat("0,000",14,".",",");
ProyeksiTransaction.setNumberFormat("0,000",15,".",",");
ProyeksiTransaction.setNumberFormat("0,000",16,".",",");
ProyeksiTransaction.setNumberFormat("0,000",17,".",",");
ProyeksiTransaction.setNumberFormat("0,000",18,".",",");
ProyeksiTransaction.setNumberFormat("0,000",19,".",",");
ProyeksiTransaction.setNumberFormat("0,000",20,".",",");
ProyeksiTransaction.setNumberFormat("0,000",21,".",",");

ProyeksiTransaction.init();
	reloadgroup();


function reloadgroup(){
	ProyeksiTransaction.clearAndLoad("xml-finance-proyeksi-cashflow-transaction");	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
