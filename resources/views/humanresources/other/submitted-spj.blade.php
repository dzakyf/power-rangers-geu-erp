<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Human Resources</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Other Data</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Submitted SPJ</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="SubmitSPJ" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <input type="button" id="SpjAdd" NAME="SpjAdd" value="Create Reimbursment" class="btn btn-success mt-2 mr-2" />

</div>

<div class="row">

    <div id="DivReimbursment" class="col-12">
    </div>
</div>

<script>
    SubmitSPJ = new dhtmlXGridObject('SubmitSPJ');
    SubmitSPJ.setImagePath("./dhtmlx/imgs/");
    SubmitSPJ.setHeader("No,Task_ID,Subject,Tujuan,Start,End,Total,Status,Note,File,Ref");
    SubmitSPJ.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    SubmitSPJ.setInitWidths("60,150,250,100,250,120,100,100,120,120,100");
    SubmitSPJ.setColAlign("center,left,left,left,left,center,left,left,left,center,left");
    SubmitSPJ.setColTypes("ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
    SubmitSPJ.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
    SubmitSPJ.enableSmartRendering(true, 150);
    SubmitSPJ.setNumberFormat("0,000", 6, ".", ",");
    SubmitSPJ.setSkin("dhx_skyblue");
    SubmitSPJ.init();
    refreshdata();

    function refreshdata() {
        SubmitSPJ.clearAndLoad('xml-submitted-spj');

    }

    $('#SpjAdd').click(function() {
        source = SubmitSPJ.cells(SubmitSPJ.getSelectedId(), 10).getValue();
        total = SubmitSPJ.cells(SubmitSPJ.getSelectedId(), 6).getValue();
        id_Task = SubmitSPJ.cells(SubmitSPJ.getSelectedId(), 1).getValue();
        status = SubmitSPJ.cells(SubmitSPJ.getSelectedId(), 7).getValue();
        if (status != "SUBMITTED") {
            alert('Pengajuan Telah Selesai');
            return false;
        }

        LoadM('DivReimbursment', 'new-reimbursment-spj?source=' + source + "&total=" + total + "&id=" + id_Task);
        return true;


    });
</script>
@endsection