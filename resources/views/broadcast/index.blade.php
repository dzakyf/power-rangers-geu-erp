<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";
?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Broadcasting</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="BroadcastList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <!-- <input type="button" id="ekselBroadcastListDet" name="ekselBroadcastListDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="BroadcastList.toExcel('./export/generate.php');" /> -->
        <!-- <input type="button" id="detailBP" name="detailBP" value="Detail Leads" class="btn btn-success mt-2 mr-2" onclick="detailBP()" /> -->
        <input type="button" id="createBroadcast" name="createBroadcast" value="Create Broadcast" class="btn btn-info mt-2 mr-2" onclick="createBroadcast()" />
        <div class="btn-group" role="group">
        </div>
    </div>
</div>


<div id="divlistOpportunity"></div>
<div></div>
<div id="divlistProjectList"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
</script>

<script>
    BroadcastList = new dhtmlXGridObject('BroadcastList');
    BroadcastList.setImagePath("./dhtmlx/imgs/");
    BroadcastList.setHeader("No,Judul Kebijakan, Jenis Kebijakan, Ringkasan Kebijakan, Detail Kebijakan, Tanggal Berlaku, Status Kebijakan, Dokumen Pendukung");
    BroadcastList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    // BroadcastList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    BroadcastList.setInitWidths("60,100,150,300,100,120,120,100");
    BroadcastList.setColAlign("center,left,left,left,left,left,left,left,left,left");
    BroadcastList.setColSorting("int,str,str,str,str,str,str,str,str");
    BroadcastList.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    BroadcastList.enableSmartRendering(true, 150);
    // BroadcastList.setNumberFormat("0,000", 7, ".", ",");
    BroadcastList.attachEvent("onRowDblClicked", BroadcastListKlik);
    BroadcastList.setSkin("dhx_skyblue");
    BroadcastList.init();
    refreshdata();

    function createBroadcast() {
        window.location.href = `approval/progress/${BroadcastList.getSelectedId()}`;
    }

    function BroadcastListKlik() {
        BroadcastListID = BroadcastList.getSelectedId();
        console.log(BroadcastListID)
        $("#divlistOpportunity").show();
        if (BroadcastListID != '') {
            $("#divlistProjectList").hide();
            LoadM('divlistOpportunity', 'partner-approval-detail?ID=' + BroadcastListID);
        }
        return true;
    }

    function refreshdata() {
        BroadcastList.clearAndLoad('xml-partner-business-lists');
    }

    function delsOpportunity() {
        dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

            if (r == true) {
                BroadcastList.deleteSelectedRows();
                PBroadcastList.sendData();
                alert('Data Successfully Deleted!');
                setTimeout(function() {
                    refreshdata();
                }, 1000);
            }
        })
    }

    function detailBP() {
        BroadcastListKlik();
    }


    function UpdateGrid_Opportunity() {
        var out = 0;
        for (var i = 0; i < BroadcastList.getRowsNum(); i++) {
            rowIdMov = BroadcastList.getRowId(i);
            BroadcastListProc.setUpdated(rowIdMov, true);
        }
        return out;
    }

    function SubmitGrid_Opportunity() {
        BroadcastListProc.sendData();
        alert('Data Successfully Updated!');
        BroadcastList.clearAll();
        refreshdata();
    }

    function EditOpportunity() {
        taskid = BroadcastList.cells(BroadcastList.getSelectedId(), 1).getValue();
        LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
    }

    function LihatDetailBroadcastList() {
        taskid = BroadcastList.cells(BroadcastList.getSelectedId(), 1).getValue();
        LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
    }

    function listDoc() {

        DocumentListsDetailID = BroadcastList.cells(BroadcastList.getSelectedId(), 12).getValue();

        $("#divlistProjectList").show();

        if (DocumentListsDetailID != '') {
            $("#divlistOpportunity").hide();
            LoadM('divlistProjectList', 'project-documents-detail?Task_ID=' + DocumentListsDetailID);
        }
        return true;
    }
</script>
@endsection