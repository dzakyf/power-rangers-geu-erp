@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Bank Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Banks</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="BankMaster" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="BankMaster.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Data" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="BankMasterDiv"></div>


	
	</div>
<script>





BankMaster = new dhtmlXGridObject('BankMaster');
BankMaster.setImagePath("./dhtmlx/imgs/");
BankMaster.setHeader("No,Bank Name,Bank CoA,Account/No Rek,Bank Branch,Currency,Remark,Status,Created By,Created Date");
BankMaster.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan");
BankMaster.setInitWidths("80,150,150,100,150,100,150,100,100,150");
BankMaster.setColAlign("center,left,left,center,left,center,left,center,center,center");
BankMaster.setColTypes("ro,ro,combo,ed,ed,ed,ed,coro,ro,ro");
BankMaster.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
BankMaster.setEditable(true);
BankMaster.enableSmartRendering(true, 100);
BankMaster.setNumberFormat("0,000",6,".",",");
BankMaster.setSkin("dhx_skyblue");
PBankMaster = new dataProcessor("master-bank-update");
	PBankMaster.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PBankMaster.setUpdateMode("Off");
PBankMaster.init(BankMaster);
BankMaster.getCombo(7).put("ACTIVE","ACTIVE");
BankMaster.getCombo(7).put("INACTIVE","INACTIVE");
BankMaster.enableEditEvents(true,true,true);
BankMaster.init();
combocurr = BankMaster.getColumnCombo(2);
combocurr.enableFilteringMode(true);
combocurr.load("combo-coa");

	reloadbank();


function reloadbank(){
	//$.blockUI();
	BankMaster.clearAndLoad("xml-accounting-master-bank",finishload);	

}
function finishload(){
	//$.unblockUI();
}
$("#SaveBM").click(function(){
		PBankMaster.sendData();
		alert("Bank was Saved!");
		
});
$("#DeleteBM").click(function(){
		BankMaster.deleteSelectedRows();
		PBankMaster.sendData();
		alert("Data were deleted!");
		reloadbank();

		
});
$("#AddBM").click(function(){
	var idbi =BankMaster.uid();
		BankMaster.addRow(idbi,[BankMaster.getRowsNum()+1,''],0);
		BankMaster.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadbank();		
});



</script>

@endsection
