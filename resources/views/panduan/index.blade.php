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
            <span class="active">Panduan</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="PanduanList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <!-- <input type="button" id="ekselPanduanListDet" name="ekselPanduanListDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="PanduanList.toExcel('./export/generate.php');" /> -->
        <!-- <input type="button" id="detailBP" name="detailBP" value="Detail Leads" class="btn btn-success mt-2 mr-2" onclick="detailBP()" /> -->
        <input type="button" id="createBroadcast" name="createBroadcast" value="Create Panduan" class="btn btn-info mt-2 mr-2" onclick="createPanduan()" />
        <input type="button" id="detailPanduanBtn" name="detailPanduanBtn" value="Detail Panduan" class="btn btn-success mt-2 mr-2" onclick="detailPanduan()" />
        <input type="button" id="deletePanduanBtn" name="deletePanduanBtn" value="Delete Panduan" class="btn btn-danger mt-2 mr-2" onclick="deletePanduan()" />
        <div class="btn-group" role="group">
        </div>
    </div>
</div>

<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
</script>

<script>
    PanduanList = new dhtmlXGridObject('PanduanList');
    PanduanList.setImagePath("./dhtmlx/imgs/");
    PanduanList.setHeader("No,Judul Panduan, Deskripsi Singkat, Tanggal Berlaku, Versi Panduan");
    PanduanList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
    // PanduanList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    PanduanList.setInitWidths("60,100,150,300,100");
    PanduanList.setColAlign("center,left,left,left,left");
    PanduanList.setColSorting("int,str,str,str,str");
    PanduanList.setColTypes("ro,ro,ro,ro,ro");
    PanduanList.enableSmartRendering(true, 150);
    // PanduanList.setNumberFormat("0,000", 7, ".", ",");
    PanduanList.attachEvent("onRowDblClicked", PanduanListKlik);
    PanduanList.setSkin("dhx_skyblue");
    PanduanList.init();
    refreshdata();

    function createPanduan() {
        window.location.href = 'panduan-create'
    }

    function PanduanListKlik() {

        $("#divlistProjectList").hide();
        PanduanListID = PanduanList.getSelectedId();
        $("#divlistOpportunity").show();
        if (PanduanListID != '') {
            $("#divlistProjectList").hide();
            LoadM('divlistOpportunity', 'panduan-detail?ID=' + PanduanListID);
        }
        // return true;
    }

    function refreshdata() {
        PanduanList.clearAndLoad('panduan-load-data');
    }

    function delsOpportunity() {
        dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

            if (r == true) {
                PanduanList.deleteSelectedRows();
                PPanduanList.sendData();
                alert('Data Successfully Deleted!');
                setTimeout(function() {
                    refreshdata();
                }, 1000);
            }
        })
    }

    function detailPanduan() {
        if (!PanduanList.getSelectedId()) {
            alert('Pilih data panduan terlebih dahulu.');
            return;
        }
        PanduanListKlik();
    }

    function deletePanduan() {
        var id = PanduanList.getSelectedId();
        if (!id) {
            alert('Pilih data panduan terlebih dahulu.');
            return;
        }
        if (!confirm('Yakin ingin menghapus panduan ini beserta file-nya?')) {
            return;
        }
        $.ajax({
            url: 'panduan-delete',
            type: 'POST',
            data: {
                ID: id,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    alert('Panduan berhasil dihapus!');
                    refreshdata();
                } else {
                    alert(res.message || 'Gagal menghapus panduan');
                }
            },
            error: function(xhr) {
                alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus panduan');
            }
        });
    }


    function UpdateGrid_Opportunity() {
        var out = 0;
        for (var i = 0; i < PanduanList.getRowsNum(); i++) {
            rowIdMov = PanduanList.getRowId(i);
            PanduanListProc.setUpdated(rowIdMov, true);
        }
        return out;
    }

    function SubmitGrid_Opportunity() {
        PanduanListProc.sendData();
        alert('Data Successfully Updated!');
        PanduanList.clearAll();
        refreshdata();
    }

    function EditOpportunity() {
        taskid = PanduanList.cells(PanduanList.getSelectedId(), 1).getValue();
        LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
    }

    function LihatDetailPanduanList() {
        taskid = PanduanList.cells(PanduanList.getSelectedId(), 1).getValue();
        LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
    }

    function listDoc() {

        DocumentListsDetailID = PanduanList.cells(PanduanList.getSelectedId(), 12).getValue();

        $("#divlistProjectList").show();

        if (DocumentListsDetailID != '') {
            $("#divlistOpportunity").hide();
            LoadM('divlistProjectList', 'project-documents-detail?Task_ID=' + DocumentListsDetailID);
        }
        return true;
    }
</script>
@endsection