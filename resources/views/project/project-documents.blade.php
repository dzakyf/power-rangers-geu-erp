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
            <a href="#">Project</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Document Lists</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ProjectDocument" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselProjectDocumentDet" name="ekselProjectDocumentDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectDocument.toExcel('./export/generate.php');" />
    </div>
    <div id="divlistProjectList"></div>

</div>
<script>
    function ProjectDocumentKlik() {

        ProjectDocumentID = ProjectDocument.getSelectedId();

        if (ProjectDocumentID != '') {
            LoadM('divlistProjectList', 'project-documents-detail?Task_ID=' + ProjectDocumentID);
        }
        return true;
    }
    ProjectDocument = new dhtmlXGridObject('ProjectDocument');
    ProjectDocument.setImagePath("./dhtmlx/imgs/");
    ProjectDocument.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
    ProjectDocument.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    ProjectDocument.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
    ProjectDocument.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
    ProjectDocument.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
    ProjectDocument.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
    ProjectDocument.enableSmartRendering(true, 150);
    ProjectDocument.setNumberFormat("0,000", 9, ".", ",");
    ProjectDocument.setNumberFormat("0,000", 10, ".", ",");
    ProjectDocument.setNumberFormat("0,000", 11, ".", ",");
    ProjectDocument.attachEvent("onRowDblClicked", ProjectDocumentKlik);
    ProjectDocument.setSkin("dhx_skyblue");
    ProjectDocument.init();
    refreshdata();

    function refreshdata() {
        ProjectDocument.clearAndLoad('xml-project-documents');

    }
</script>
@endsection