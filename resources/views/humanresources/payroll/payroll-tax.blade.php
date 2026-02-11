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
                <span class="active">Master Tax Reduction Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="TaxList" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="TaxList.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="TaxListDiv"></div>


	
	</div>
<script>





TaxList = new dhtmlXGridObject('TaxList');
TaxList.setImagePath("./dhtmlx/imgs/");
TaxList.setHeader("No,Marital ID,Description,Reduction Amount,Marital Status,Childrens,Created By,Created Date,Last Modified By,Last Modified Date,Last Seen By, Last Seen Date");
TaxList.setInitWidths("80,100,250,100,100,100,100,100,100,100,100,100,100,100");
TaxList.setColAlign("center,left,left,center,left,center,left,center,center,center,center,center,center,center,center");
TaxList.setColTypes("ro,ed,ed,edn,coro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
TaxList.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
TaxList.setEditable(true);
TaxList.setNumberFormat("0,000",3,".",",");
TaxList.setSkin("dhx_skyblue");
PTaxList = new dataProcessor("payroll-tax-update");
	PTaxList.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PTaxList.setUpdateMode("Off");
PTaxList.init(TaxList);
TaxList.init();
Reload_TaxList();
TaxList.getCombo(4).put("1","MARRIED");
TaxList.getCombo(4).put("0","NOT MARRIED");

function Reload_TaxList(){
	TaxList.clearAndLoad("xml-payroll-tax");	

}

$("#SaveBM").click(function(){
		PTaxList.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_TaxList();
		},2000);
});
$("#DeleteBM").click(function(){
		TaxList.deleteSelectedRows();
		PTaxList.sendData();
		alert("Data were deleted!");
		Reload_TaxList();

		
});
$("#AddBM").click(function(){
	var idbi =TaxList.uid();
		TaxList.addRow(idbi,[TaxList.getRowsNum()+1,'','','','','',''],0);
		TaxList.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	Reload_TaxList();		
});



</script>

@endsection
