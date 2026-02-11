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
                <span class="active">Master Payroll Group</span>
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
			<input type="button" id="SETLIST" NAME="SETLIST" value="Lists of Benefit" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SETPEGAWAY" NAME="SETPEGAWAY" value="Lists of Employee" class="btn btn-warning mt-2 mr-2"/>

		</div>

		<div id="Payroll_GroupDiv"></div>


	
	</div>
<script>





Payroll_Group = new dhtmlXGridObject('Payroll_Group');
Payroll_Group.setImagePath("./dhtmlx/imgs/");
Payroll_Group.setHeader("No,ID,Group Name,Category,Use For,Num of Employees,Created By,Created Date");
Payroll_Group.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter");
Payroll_Group.setInitWidths("80,100,250,100,100,100,100,100,100");
Payroll_Group.setColAlign("center,left,left,center,left,center,left,center,center,center");
Payroll_Group.setColTypes("ro,ro,ed,coro,coro,ro,ro,ro");
Payroll_Group.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
Payroll_Group.setEditable(true);
//Payroll_Group.setNumberFormat("0,000",6,".",",");
Payroll_Group.setSkin("dhx_skyblue");
PPayroll_Group = new dataProcessor("payroll-group-update");
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
Payroll_Group.getCombo(3).put('INCENTIVE','INCENTIVE');
Payroll_Group.getCombo(3).put('RAPEL','RAPEL');
Payroll_Group.getCombo(3).put('MANAGEMENT_FEE','MANAGEMENT_FEE');
Payroll_Group.getCombo(3).put('LEAVE','LEAVE');
Payroll_Group.getCombo(4).put('INTERNAL','INTERNAL');
Payroll_Group.getCombo(4).put('PROJECT','PROJECT');
Payroll_Group.getCombo(4).put('OUTTASK','OUTTASK');
Payroll_Group.getCombo(4).put('OUTSOURCE','OUTSOURCE');

function Reload_Payroll_Group(){
	Payroll_Group.clearAndLoad("xml-payroll-group");	

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
	LoadM('Payroll_GroupDiv','payroll-group-detail?ID=' + SalesListsID);
		
});
$("#SETLIST").click(function(){
	SalesListsID  = Payroll_Group.cells(Payroll_Group.getSelectedId(),1).getValue();
	LoadM('Payroll_GroupDiv','payroll-group-lists?ID=' + SalesListsID);
		
});


</script>

@endsection
