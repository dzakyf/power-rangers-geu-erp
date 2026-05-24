<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/">Warehouse</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Survey</span>
        </li>
    </ol>
</div>

<div>
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SURVEY LOKASI</div>
    <div class="card">
        <div class="card-body">
            <div id="Survey" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="detailSurvey" name="detailSurvey" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" />
                <input type="button" id="ekselSurveyDet" name="ekselSurveyDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="Survey.toExcel('./export/generate.php');" />
                <input type="button" id="detailSurvey" name="detailSurvey" value="Detail" class="btn btn-info mt-2 mr-2" onclick="SurveyKlik()" />
            </div>
        </div>
    </div>
    <div id="divlistOpportunity"></div>
    <div id="DivProcess"></div>
    <div id="divSurveyEdit" style="display:none;">
        @include('partner.survey.edit')
    </div>
</div>

<script>
    function redirectToFormSurvey() {
        window.location.href = 'partner-survey-create'
    }

    function SurveyKlik() {
        SurveyID = Survey.getSelectedId();
        if (SurveyID != null) {
            $('#divSurveyEdit').show();
            $('html, body').animate({ scrollTop: $('#divSurveyEdit').offset().top }, 400);
            getSurveyData(SurveyID);
        } else {
            alert("Pilih baris data");
        }
        return true;
    }
    Survey = new dhtmlXGridObject('Survey');
    Survey.setImagePath("./dhtmlx/imgs/");
    Survey.setHeader("No, Nama Lokasi, Rekomendasi, Status, Tanggal Survey, Total Skor, Rekomendasi");
    Survey.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    // Survey.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    Survey.setInitWidths("60,100,150,200,100, 100,100");
    Survey.setColAlign("center,left,left,left,left, left,left");
    Survey.setColSorting("int,str,str,str,str,str,str");
    Survey.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro");
    Survey.enableSmartRendering(true, 150);
    // Survey.setNumberFormat("0,000", 7, ".", ",");
    Survey.attachEvent("onRowDblClicked", SurveyKlik);
    Survey.setSkin("dhx_skyblue");
    Survey.init();
    refreshdata();

    function refreshdata() {
        Survey.clearAndLoad('partner-survey-data');
    }
</script>
@endsection