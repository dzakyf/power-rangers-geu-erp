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
            <a href="#">Report</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Report Monitoring</span>
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
    OpporActive.setHeader("No,Nama Project,No PO, No BA,Start Date,End Date,Revenue,Status");
    OpporActive.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter");
    OpporActive.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total",
        ["", "", "", "", "", "", "", "text-align:center"]
    );
    OpporActive.setInitWidths("60,380,150,120,120,100,120");
    OpporActive.setColAlign("center,left,left,left,left,left,right,left");
    OpporActive.setColSorting("int,str,str,str,str,str,str,str");
    OpporActive.setColTypes("ro,ro,ro,ro,ro,ro,ron,ro");
    OpporActive.enableSmartRendering(true, 150);
    OpporActive.setNumberFormat("0,000", 6, ".", ",");
    OpporActive.attachEvent("onRowDblClicked", OpporActiveKlik);
    OpporActive.setSkin("dhx_skyblue");
    OpporActive.init();
    refreshdata();

    function refreshdata() {
        OpporActive.clearAndLoad('xml-sales-report-monitoring');

    }
</script>
@endsection