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
        <h1>Finance</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Fast Reports</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Unbilled PO</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
	
		<div id="BA_Collection" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselARAgingDet" name="ekselARAgingDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="BA_Collection.toExcel('./export/generate.php');"  />
		</div>
	</div>	
	<div id="ARCareDetail"></div>

<script>

function reprintba(){
	CustomerListsID = BA_Collection.getSelectedId();

						window.open("badownload?ID=" + CustomerListsID);

}
function BA_CollectionKlik() {

CustomerListsID = BA_Collection.getSelectedId();
bano = BA_Collection.cells(CustomerListsID,8).getValue();
if (CustomerListsID !=''){
	
		LoadM('divlistcustomer','report-unbilled-po-detail?ID=' + CustomerListsID);	

}
return true;
}
BA_Collection = new dhtmlXGridObject('BA_Collection');
BA_Collection.setImagePath("./dhtmlx/imgs/");
BA_Collection.setHeader("No,PO No,PO Date,Customer Name,Subject,Proforma Type,Amount,Qty PO,BA No,BA Date,Aging(s) BA,Created By,Created Date");
BA_Collection.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BA_Collection.setInitWidths("60,120,140,300,250,100,80,100,100,100,100,100,100,100,100");
BA_Collection.setColAlign("center,center,center,left,left,center,right,center,center,center,center,center,center,center,left,right");
BA_Collection.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int");
BA_Collection.setColTypes("ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro");
BA_Collection.enableSmartRendering(true, 150);
BA_Collection.setNumberFormat("0,000",6,".",",");
BA_Collection.attachEvent("onRowDblClicked", BA_CollectionKlik);

BA_Collection.setSkin("dhx_skyblue");
BA_Collection.init();
refreshdata();

function refreshdata(){
	BA_Collection.clearAndLoad('xml-report-unbilled-po');

}
</script>
@endsection