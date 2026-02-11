@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Purchase Order</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Receive Invoice</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="ReceiveInv" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ReceiveInv.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Received Invoice" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add New Receive" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="printfpp" NAME="printfpp" value="Print FPP" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="ReceiveInvDiv"></div>


	
	</div>
<script>

function APReceiveKlik() {

	APReceiveID = ReceiveInv.getSelectedId();
	LoadM('ReceiveInvDiv','po-receive-invoice-detail?ID=' + APReceiveID);
}


ReceiveInv = new dhtmlXGridObject('ReceiveInv');
ReceiveInv.setImagePath("./dhtmlx/imgs/");
ReceiveInv.setHeader("No.,Contract Type,Contract No,Reference No,Start Date,End Date,Supplier,Note");
ReceiveInv.setHeader("No.,PO No,Supplier,Department,Purchase Category,Total,Aging,Created Date,Due Date,Status,Rec,Invoice Received Date,Invoice No,Invoice Date");
ReceiveInv.setInitWidths("50,200,180,140,130,120,80,110,110,150,100,100,100");
ReceiveInv.attachHeader(",#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,&nbsp;,#text_filter");
ReceiveInv.setColAlign("center,left,left,left,left,right,center,center,center,center,center,center,left,center,center,center");
ReceiveInv.setColTypes("ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ReceiveInv.setEditable(false);
ReceiveInv.setSkin("dhx_skyblue");
ReceiveInv.attachEvent("onRowDblClicked", APReceiveKlik);

PReceiveInv = new dataProcessor("po-receive-invoice-update");
	PReceiveInv.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PReceiveInv.setUpdateMode("Off");
PReceiveInv.init(ReceiveInv);
ReceiveInv.init();
	reloadgroup();


function reloadgroup(){
	ReceiveInv.clearAndLoad("xml-po-receive-invoice");	

}


$("#printfpp").click(function(){
		APReceiveID = ReceiveInv.getSelectedId();

	window.open("printfpp_po?ID=" + APReceiveID);

});
$("#AddBM").click(function(){
	LoadM('ReceiveInvDiv','add-receive-invoice');

});
$("#DeleteBM").click(function(){
	
	dhtmlx.confirm('Are you sure want to delete the data?', 'Contract Management', function(r) {
			
		if (r==true){
			
			ReceiveInv.deleteSelectedRows();
			PReceiveInv.sendData();
			alert("Data were deleted!");
			reloadgroup();

			
		}
	});
});


		




$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
