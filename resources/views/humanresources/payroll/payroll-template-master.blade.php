@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Payroll</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Master Template</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="Payroll_Group" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Payroll_Group.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/> &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
			<input type="button" id="SETLIST" NAME="SETLIST" value="Components" class="btn btn-info mt-2 mr-2"/>

		</div>

		<div id="Payroll_GroupDiv"></div>


	
	</div>
<script>

Payroll_Group = new dhtmlXGridObject('Payroll_Group');
Payroll_Group.setImagePath("./dhtmlx/imgs/");
Payroll_Group.setHeader("No,ID,Group Name,Category,Use For,Reference,Created By,Created Date");
Payroll_Group.setInitWidths("80,100,250,100,100,100,100,100,100");
Payroll_Group.setColAlign("center,left,left,center,left,center,left,center,center,center");
Payroll_Group.setColTypes("ro,ro,ed,coro,coro,ed,ro,ro");
Payroll_Group.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
Payroll_Group.setEditable(true);
Payroll_Group.setSkin("dhx_skyblue");
PPayroll_Group = new dataProcessor("payroll-template-master-update");
	PPayroll_Group.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PPayroll_Group.setUpdateMode("Off");
PPayroll_Group.init(Payroll_Group);
Payroll_Group.init();
Reload_Payroll_Group();
Payroll_Group.getCombo(3).put('SALARY','SALARY');
Payroll_Group.getCombo(3).put('BONUS','BONUS');
Payroll_Group.getCombo(4).put('ALL','ALL');
Payroll_Group.getCombo(4).put('INTERNAL','INTERNAL');
Payroll_Group.getCombo(4).put('OUTSOURCE','OUTSOURCE');

function Reload_Payroll_Group(){
	Payroll_Group.clearAndLoad("xml-payroll-template-master");	

}

$("#SaveBM").click(function(){
		PPayroll_Group.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_Payroll_Group();
		},2000);
});
$("#DeleteBM").click(function(){
		Payroll_Group.deleteSelectedRows();
		PPayroll_Group.sendData();
		alert("Data were deleted!");
		Reload_Payroll_Group();

		
});
$("#AddBM").click(function(){
	var idbi =Payroll_Group.uid();
		Payroll_Group.addRow(idbi,[Payroll_Group.getRowsNum()+1,''],0);
		Payroll_Group.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	Reload_Payroll_Group();		
});



$("#SETPEGAWAY").click(function(){
	SalesListsID  = Payroll_Group.cells(Payroll_Group.getSelectedId(),1).getValue();
	LoadM('Payroll_GroupDiv','payroll-template-master-detail?ID=' + SalesListsID);
		
});
$("#SETLIST").click(function(){
	SalesListsID  = Payroll_Group.cells(Payroll_Group.getSelectedId(),1).getValue();
	LoadM('Payroll_GroupDiv','payroll-template-master-lists?ID=' + SalesListsID);
		
});


</script>

@endsection
