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
                <span class="active">Data Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="ProyeksiHistory" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ProyeksiHistory.toExcel('./export/generate.php');"  />
			<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			</div>

		<div id="ProyeksiHistoryDiv"></div>


	
	</div>
<script>


function ApprovalInvKlik() {

CustomerListsID = ProyeksiHistory.getSelectedId();

if (CustomerListsID !=''){
	LoadM('ProyeksiManagementDiv','proyeksi-cashflow-history-detail?ID=' + CustomerListsID);
}
return true;
}


ProyeksiHistory = new dhtmlXGridObject('ProyeksiHistory');
ProyeksiHistory.setImagePath("./dhtmlx/imgs/");
ProyeksiHistory.setHeader("No,Year,Department,Ratio From Last(%),January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Total Used,Note Used");
ProyeksiHistory.setInitWidths("80,70,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,300,100,200");
ProyeksiHistory.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,right,left");
ProyeksiHistory.setColTypes("ro,ed,combo,ed,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro,ro");
ProyeksiHistory.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,int,str");
ProyeksiHistory.setEditable(false);
ProyeksiHistory.setNumberFormat("0,000",4,".",",");
ProyeksiHistory.setNumberFormat("0,000",5,".",",");
ProyeksiHistory.setNumberFormat("0,000",6,".",",");
ProyeksiHistory.setNumberFormat("0,000",7,".",",");
ProyeksiHistory.setNumberFormat("0,000",8,".",",");
ProyeksiHistory.setNumberFormat("0,000",9,".",",");
ProyeksiHistory.setNumberFormat("0,000",10,".",",");
ProyeksiHistory.setNumberFormat("0,000",11,".",",");
ProyeksiHistory.setNumberFormat("0,000",12,".",",");
ProyeksiHistory.setNumberFormat("0,000",13,".",",");
ProyeksiHistory.setNumberFormat("0,000",14,".",",");
ProyeksiHistory.setNumberFormat("0,000",15,".",",");
ProyeksiHistory.setNumberFormat("0,000",16,".",",");
ProyeksiHistory.setNumberFormat("0,000",17,".",",");
ProyeksiHistory.setNumberFormat("0,000",18,".",",");
ProyeksiHistory.setNumberFormat("0,000",19,".",",");
ProyeksiHistory.setNumberFormat("0,000",20,".",",");
ProyeksiHistory.setNumberFormat("0,000",21,".",",");
ProyeksiHistory.setNumberFormat("0,000",23,".",",");
ProyeksiHistory.attachEvent("onRowDblClicked", ApprovalInvKlik);

ProyeksiHistory.setSkin("dhx_skyblue");
ProyeksiHistory.init();
reloadgroup();


function reloadgroup(){
	ProyeksiHistory.clearAndLoad("xml-finance-proyeksi-cashflow-history");	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
