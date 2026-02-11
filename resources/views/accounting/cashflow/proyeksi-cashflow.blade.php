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
		 <div id="ProyeksiManagement" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ProyeksiManagement.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Proyeksi" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="dteil" NAME="dteil" value="Detail Proyeksi" onclick='ApprovalInvKlik()' class="btn btn-warning mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Proyeksi" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Update" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="ProyeksiManagementDiv"></div>


	
	</div>
<script>



function ApprovalInvKlik() {

CustomerListsID = ProyeksiManagement.getSelectedId();
var stats = ProyeksiManagement.cells(ProyeksiManagement.getSelectedId(),18).getValue();

if (CustomerListsID !=''){
	LoadM('ProyeksiManagementDiv','proyeksi-cashflow-management-detail?ID=' + CustomerListsID + '&status=' + stats);
}
return true;
}


ProyeksiManagement = new dhtmlXGridObject('ProyeksiManagement');
ProyeksiManagement.setImagePath("./dhtmlx/imgs/");
ProyeksiManagement.setHeader("No,Year,Dept Code,Desciption,January,February,March,April,May,June,July,August,September,October,November,December,Total,Note,Status");
ProyeksiManagement.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,");
ProyeksiManagement.setInitWidths("30,50,70,200,175,115,115,115,115,115,115,115,115,115,115,115,115,115,300,100");
ProyeksiManagement.setColAlign("center,right,left,center,right,right,right,right,right,right,right,right,right,right,right,right,right,left,center");
ProyeksiManagement.setColTypes("ro,ed,combo,ed,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron[=c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15],ed,ro");
ProyeksiManagement.setColSorting("int,int,str,int,int,int,int,int,int,int,int,int,int,int,int,int,str,str");
ProyeksiManagement.setEditable(true);
ProyeksiManagement.setNumberFormat("0,000.00",4,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",5,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",6,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",7,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",8,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",9,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",10,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",11,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",12,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",13,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",14,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",15,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",16,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",17,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",18,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",19,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",20,".",",");
ProyeksiManagement.setNumberFormat("0,000.00",21,".",",");

ProyeksiManagement.setSkin("dhx_skyblue");
PProyeksiManagement = new dataProcessor("finance-proyeksi-cashflow-management-update");
	PProyeksiManagement.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,

    })
PProyeksiManagement.setUpdateMode("Off");
PProyeksiManagement.init(ProyeksiManagement);
ProyeksiManagement.init();
combocurr = ProyeksiManagement.getColumnCombo(2);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-department");
	reloadProyeksimgt();


function reloadProyeksimgt(){
	ProyeksiManagement.clearAndLoad("xml-finance-proyeksi-cashflow-management");	

}

$("#SaveBM").click(function(){
	var stats = ProyeksiManagement.cells(ProyeksiManagement.getSelectedId(),18).getValue();
	if (stats =='CREATED' || stats =='' ){
		PProyeksiManagement.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			reloadProyeksimgt();
		},1000);
	}
		
});
$("#DeleteBM").click(function(){
	var stats = ProyeksiManagement.cells(ProyeksiManagement.getSelectedId(),18).getValue();
	if (stats =='CREATED' || stats ==''){

		ProyeksiManagement.deleteSelectedRows();
		PProyeksiManagement.sendData();
		alert("Data were deleted!");
		
		reloadProyeksimgt();
	}

		
});
$("#AddBM").click(function(){
	
	var idbi =ProyeksiManagement.uid();
		ProyeksiManagement.addRow(idbi,[ProyeksiManagement.getRowsNum()+1,'','','','0','0','0','0','0','0','0','0','0','0','0','0','0'],0);
		ProyeksiManagement.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadProyeksimgt();		
});



</script>

@endsection
