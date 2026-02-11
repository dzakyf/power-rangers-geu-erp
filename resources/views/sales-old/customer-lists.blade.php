<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Customer</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="CustomerLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselCustomerListsDet" name="ekselCustomerListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="CustomerLists.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Delete customer" class="btn btn-danger mt-2 mr-2" onclick="delscustomer()"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Add New" onclick="addNew()"class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
		<div id="divlistcustomer"></div>
<script>
function addNew() {

LoadM('divlistcustomer','customer-lists-add');
}
function CustomerListsKlik() {

CustomerListsID = CustomerLists.getSelectedId();

if (CustomerListsID !=''){
LoadM('divlistcustomer','customer-lists-detail?ID=' + CustomerListsID);
}
return true;
}
CustomerLists = new dhtmlXGridObject('CustomerLists');
CustomerLists.setImagePath("./dhtmlx/imgs/");
CustomerLists.setHeader("No,Customer No,Initial Code, Company Name, Address, City, Province, Phone, PIC, PIC Phone, PIC Position, NPWP, Email, Status, C Collector, PIC Billing, PIC Billing No, VAT Name, VAT Address, VAT Prefix, Sales Person, Created By");
CustomerLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CustomerLists.setInitWidths("60,150,100,100,200,120,120,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
CustomerLists.setColAlign("center,left,left,left,left,left,left,center,center,center,center,center,center,center,center,center");
CustomerLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
CustomerLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
CustomerLists.enableSmartRendering(true, 150);
PCustomerLists = new dataProcessor("customer-lists-update");
PCustomerLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCustomerLists.setTransactionMode("POST", true);
PCustomerLists.setUpdateMode("Off");
PCustomerLists.init(CustomerLists);
CustomerLists.attachEvent("onRowDblClicked", CustomerListsKlik);
CustomerLists.setSkin("dhx_skyblue");
CustomerLists.init();
refreshdata();

function refreshdata(){
	CustomerLists.clearAndLoad('xml-sales-customer-lists');

}

function delscustomer(){
	dhtmlx.confirm('Cancel this customer?', 'customer', function(r) {
			
			if (r==true){
				CustomerLists.deleteSelectedRows();
				PCustomerLists.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
}


function UpdateGrid_customer() {
	var out = 0;
    for (var i = 0; i < CustomerLists.getRowsNum(); i++) {
		rowIdMov=CustomerLists.getRowId(i);
        CustomerListsProc.setUpdated(rowIdMov,true);
    }
	return out;
}

function SubmitGrid_customer(){
	CustomerListsProc.sendData();
	alert('Data Successfully Updated!');
	CustomerLists.clearAll();
	refreshdata();
}
function Editcustomer() {
	taskid = CustomerLists.cells(CustomerLists.getSelectedId(),1).getValue();
	LoadM('divlistcustomer','sales-customer-lists-detail?ID='+taskid);
}
function LihatDetailCustomerLists() {
	taskid = CustomerLists.cells(CustomerLists.getSelectedId(),1).getValue();
	LoadM('divlistcustomer','sales-customer-activity?taskid='+taskid);
}

</script>
@endsection