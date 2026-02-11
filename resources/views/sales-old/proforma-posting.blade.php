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
            <a href="#">Proforma</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Confirm to Post</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ProformaPost" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
    <div id="divtom">
		<input type="button" id="BankAdvanceExp" NAME="BankAdvanceExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="ProformaPost.toExcel('./export/generate.php');" />


	</div>
    <div id="divlistcustomer"></div>
</div>

<script>
    function ProformaPostKlik() {

        CustomerListsID = ProformaPost.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'sales-proforma-posting-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ProformaPost = new dhtmlXGridObject('ProformaPost');
    ProformaPost.setImagePath("./dhtmlx/imgs/");
    ProformaPost.setHeader("No,No BA, Tanggal BA,Pekerjaan,Periode,Total Amount,User,Divisi,Last Update");
    ProformaPost.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total",
        ["", "", "", "", "", "text-align:right"]
    );
    ProformaPost.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#text_filter,#text_filter,#text_filter");
    ProformaPost.setInitWidths("60,180,100,250,120,150,150,150,150");
    ProformaPost.setColAlign("center,left,center,left,left,right,left,left,left");
    ProformaPost.setColSorting("int,str,str,str,str,int,str,str,str");
    ProformaPost.setColTypes("ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
    ProformaPost.enableSmartRendering(true, 150);
    ProformaPost.setNumberFormat("0,000", 5, ".", ",");
    ProformaPost.attachEvent("onRowDblClicked", ProformaPostKlik);

    ProformaPost.setSkin("dhx_skyblue");
    ProformaPost.init();
    refreshdata();

    function refreshdata() {
        ProformaPost.clearAndLoad('xml-sales-proforma-posting');

    }
</script>
@endsection