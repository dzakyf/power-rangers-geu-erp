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
                <span class="active">CoA Config</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="CustomerLists" style="width:auto;height:420px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselCustomerListsDet" name="ekselCustomerListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="CustomerLists.toExcel('./export/generate.php');"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Update" onclick="delscustomer()"class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
		<div id="divlistcustomer"></div>
<script>

CustomerLists = new dhtmlXGridObject('CustomerLists');
CustomerLists.setImagePath("./dhtmlx/imgs/");
CustomerLists.setHeader("No,Customer No,Initial Code, Company Name, Address, City,CoA Revenue,CoA Man Fee,CoA Costs,NPWP");
CustomerLists.setInitWidths("60,150,100,200,400,120,100,100,100,100,100,120");
CustomerLists.setColAlign("center,left,left,left,left,left,left,center,center,center,center,center,center,center,center,center");
CustomerLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
CustomerLists.setColTypes("ro,ro,ro,ro,ro,ro,ed,ed,ed,ed");
CustomerLists.enableSmartRendering(true, 150);
PCustomerLists = new dataProcessor("accounting-master-customer-update");
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
CustomerLists.setSkin("dhx_skyblue");
CustomerLists.init();
refreshdata();

function refreshdata(){
	CustomerLists.clearAndLoad('xml-accounting-master-customer');

}

function delscustomer(){
	dhtmlx.confirm('Update CoA customer?', 'CoA Customer', function(r) {
			
			if (r==true){
				PCustomerLists.sendData();
					alert('Data Successfully Updated!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
}



</script>
@endsection