<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";
?>

@extends('layout.mainlayout-bp')

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
        <input type="button" id="createBroadcast" name="createBroadcast" value="Create Broadcast" class="btn btn-info mt-2 mr-2" onclick="createBroadcast()" />
        <input type="button" id="detailBroadcast" name="detailBroadcast" value="Detail Broadcast" class="btn btn-info mt-2 mr-2" onclick="showDetail()" />

        <div class="btn-group" role="group">
        </div>
    </div>
</div>

<div id="divlistOpportunity"></div>
<div id="broadcastDetailContainer" style="display:none;">
    @include('broadcast.detail')
</div>
<div id="divlistProjectList"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
</link>

<script>
    var brId = null;
    BroadcastList = new dhtmlXGridObject('BroadcastList');
    BroadcastList.setImagePath("./dhtmlx/imgs/");
    BroadcastList.setHeader("No,Judul Kebijakan, Jenis Kebijakan, Ringkasan Kebijakan, Detail Kebijakan, Tanggal Berlaku, Status Kebijakan, Dokumen Pendukung");
    BroadcastList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    // BroadcastList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    BroadcastList.setInitWidths("60,100,150,300,100,120,120,100");
    BroadcastList.setColAlign("center,left,left,left,left,left,left,left");
    BroadcastList.setColSorting("int,str,str,str,str,str,str,str");
    BroadcastList.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
    BroadcastList.enableSmartRendering(true, 150);
    // BroadcastList.setNumberFormat("0,000", 7, ".", ",");
    BroadcastList.attachEvent("onRowSelect", broadcastListKlik);
    BroadcastList.setSkin("dhx_skyblue");
    BroadcastList.init();
    refreshdata();

    function createBroadcast() {
        window.location.href = 'broadcast-create'
    }

    function broadcastListKlik(id) {
        brId = BroadcastList.getRowAttribute(id, 'br_id');
    }

    function showDetail() {
        $.get('broadcast-detail-data', {
            ID: brId
        }, function(res) {
            var d = res.data;
            if (!d) {
                alert('Data not found');
                return;
            }

            $('#detail_Title').val(d.Title);
            $('#detail_Type').val(d.Type);
            $('#detail_Short_Desc').val(d.Short_Desc);
            $('#detail_Long_Desc').val(d.Long_Desc);
            $('#detail_Effective_Date').val(d.Effective_Date);
            $('#detail_Status').val(d.Status);

            var filesHtml = '';
            if (d.Files && d.Files.length > 0) {
                d.Files.forEach(function(f) {
                    filesHtml += '<div><a href="' + f.Url + '" target="_blank">' + 'Lihat File' + '</a></div>';
                });
            } else {
                filesHtml = '<span class="text-muted">Tidak ada file</span>';
            }
            $('#detail_Files').html(filesHtml);

            $('#broadcastDetailContainer').show();
        }).fail(function() {
            alert('Failed to load detail');
        });
        return true;
    }

    function refreshdata() {
        BroadcastList.clearAndLoad('broadcast-load-data');
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