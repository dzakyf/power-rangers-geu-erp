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
            <a href="#">Opportunity</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Request Addendum</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="OpporActive" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselOpporActiveDet" name="ekselOpporActiveDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpporActive.toExcel('./export/generate.php');" />
    </div>

</div>
<div id="divlistOpportunity"></div>
<script>
    function OpporActiveKlik() {

        OpporActiveID = OpporActive.getSelectedId();

        if (OpporActiveID != '') {
            LoadM('divlistOpportunity', 'opportunity-addendum-detail?ID=' + OpporActiveID);
        }
        return true;
    }
    OpporActive = new dhtmlXGridObject('OpporActive');
    OpporActive.setImagePath("./dhtmlx/imgs/");
    OpporActive.setHeader("No,Aging(days),Subject,Company Name,No PO,Start Date,End Date,Revenue");
    OpporActive.attachHeader("#rspan,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    OpporActive.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total",
        ["", "", "", "", "", "", "", "text-align:center"]
    );
    OpporActive.setInitWidths("60,80,380,200,150,120,120,100,100,100,200,*");
    OpporActive.setColAlign("center,right,left,left,left,left,left,right,left,left,left,center");
    OpporActive.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
    OpporActive.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
    OpporActive.enableSmartRendering(true, 150);
    OpporActive.setNumberFormat("0,000", 7, ".", ",");
    OpporActive.attachEvent("onRowDblClicked", OpporActiveKlik);
    OpporActive.setSkin("dhx_skyblue");
    OpporActive.init();
    refreshdata();

    function refreshdata() {
        OpporActive.clearAndLoad('xml-sales-opportunity-active');

    }
</script>
@endsection