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
    <h1>Project Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Job Costing</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">SPJ Request</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="SPJ" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <input type="button" id="SpjAdd" NAME="SpjAdd" value="Add Item" class="btn btn-success mt-2 mr-2" />
</div>
<div class="row">

    <div id="DivAddSpj" class="col-12">
    </div>


    <div id="DivDetailSPJ" class="col-12">
    </div>
</div>



<script>
    function ProjectSPJKlik() {

        ProjectSPJID = ProjectSPJ.cells(ProjectSPJ.getSelectedId(), 1).getValue();

        if (ProjectSPJID != '') {
            LoadM('DivDetailSPJ', 'project-spj-detail?ID=' + ProjectSPJID);


        }
        return true;
    }
    ProjectSPJ = new dhtmlXGridObject('SPJ');
    ProjectSPJ.setImagePath("./dhtmlx/imgs/");
    ProjectSPJ.setHeader(
        "No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs"
    );
    ProjectSPJ.attachHeader(
        "#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
    );
    ProjectSPJ.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
    ProjectSPJ.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
    ProjectSPJ.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
    ProjectSPJ.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
    ProjectSPJ.enableSmartRendering(true, 150);
    ProjectSPJ.setNumberFormat("0,000", 9, ".", ",");
    ProjectSPJ.setNumberFormat("0,000", 10, ".", ",");
    ProjectSPJ.setNumberFormat("0,000", 11, ".", ",");
    ProjectSPJ.attachEvent("onRowDblClicked", ProjectSPJKlik);
    ProjectSPJ.setSkin("dhx_skyblue");
    ProjectSPJ.init();
    refreshdata();

    function refreshdata() {
        ProjectSPJ.clearAndLoad('xml-project-lists');

    }

    $('#SpjAdd').click(function() {
        source = ProjectSPJ.cells(ProjectSPJ.getSelectedId(), 5).getValue();
        ref = ProjectSPJ.cells(ProjectSPJ.getSelectedId(), 6).getValue();
        id = ProjectSPJ.cells(ProjectSPJ.getSelectedId(), 1).getValue();
        if (source != '') {
            LoadM('DivAddSpj', 'project-spj-add?source=' + source + "&ref=" + ref + "&id=" + id);

        }
        return true;

    });
</script>
@endsection