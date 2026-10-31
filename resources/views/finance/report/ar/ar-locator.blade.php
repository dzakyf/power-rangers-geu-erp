<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$priv =  Auth::user()->Priv_ID;

?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Report</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">AR Locator</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">


	<div id="ArLocator" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="ArLocatorExp" NAME="ArLocatorExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="ArLocator.toExcel('./export/generate.php');" />
        <input type="button" id="loadcoa2" NAME="loadcoa2" value="Load From File" onclick="loadfromfile()" class="btn btn-info mt-2 mr-2"  />
        <input type="button" id="SaveDataBM" NAME="SaveDataBM" value="Update" class="btn btn-success mt-2 mr-2"/>
	</div>
</div>
<div id='loaddivbatch'></div>

<script>
	function refreshdata() {

		ArLocator.clearAndLoad('xml-finance-report-ar-locator');

	}

    function loadfromfile(){
        LoadM('loaddivbatch','finance-report-ar-locator-upload-file');
    }

	ArLocator = new dhtmlXGridObject('ArLocator');
	ArLocator.setImagePath("./dhtmlx/imgs/");
	ArLocator.setHeader("No,Locator,No Invoice,Tanggal Inv, Jenis Pekerjaan,Total");
	ArLocator.attachHeader("&nbsp;,#combo_filter,#text_filter,#text_filter,#text_filter,#numeric_filter");
	ArLocator.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ArLocator.setInitWidths("50,150,150,100,150,150");
	ArLocator.setColAlign("center,left,left,left,left,right");
	ArLocator.setColTypes("cntr,ed,ro,ro,ro,ron");
	ArLocator.setColSorting("na,str,str,str,str,int");
	ArLocator.setEditable(true);
//	ArLocator.attachEvent("onRowDblClicked", ArLocatorDoubleClick);
    ArLocator.setNumberFormat("0,000", 5, ".", ",");
	ArLocator.setSkin("dhx_skyblue");
    PArLocator = new dataProcessor("finance-report-ar-locator-update");
	PArLocator.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }	
    });
    PArLocator.setUpdateMode("Off");
    PArLocator.init(ArLocator);
	ArLocator.init();
	refreshdata();


    $("#SaveDataBM").click(function(){
		PArLocator.sendData();
		setTimeout(function(){
			alert("Data was Saved!");
			refreshdata();
		},1000);
		
});


</script>


@endsection