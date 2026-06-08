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
                <input type="button" value="Lengkapi Data" class="btn btn-primary mt-2 mr-2" onclick="redirectToEditSurvey()" />
                <input type="button" value="Reply" class="btn btn-success mt-2 mr-2" onclick="redirectToReplySurvey()" />
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

    function SurveyKlik(id) {
        SurveyID = window._selectedSurveyId;
        if (SurveyID != null) {
            $('#divSurveyEdit').show();
            $('html, body').animate({
                scrollTop: $('#divSurveyEdit').offset().top
            }, 400);
            getSurveyData(SurveyID);
        } else {
            alert("Pilih baris data");
        }
        return true;
    }
    Survey = new dhtmlXGridObject('Survey');
    Survey.enableSmartRendering(true, 150);
    Survey.setImagePath("./dhtmlx/imgs/");

    const columns = [{
            header: "No",
            width: 60,
            align: "center",
            sorting: "int",
            type: "ron"
        },
        {
            header: "Nama Lokasi",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Leads ID",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Survey ID",
            width: 200,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Tipe",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Rekomendasi",
            width: 150,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Status",
            width: 200,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Tanggal Survey",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Total Skor",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
        {
            header: "Rekomendasi",
            width: 100,
            align: "left",
            sorting: "str",
            type: "ro"
        },
    ];

    Survey.setHeader(columns.map(c => c.header).join(","));
    Survey.attachHeader(columns.map((c, i) => i === 0 ? "#rspan" : "#text_filter").join(","));
    Survey.setInitWidths(columns.map(c => c.width).join(","));
    Survey.setColAlign(columns.map(c => c.align).join(","));
    Survey.setColSorting(columns.map(c => c.sorting).join(","));
    Survey.setColTypes(columns.map(c => c.type).join(","));
    // Survey.setNumberFormat("0,000", 7, ".", ",");
    Survey.attachEvent("onRowDblClicked", SurveyKlik);
    Survey.attachEvent("onRowSelect", function(id) {
        window._selectedSurveyId = Survey.getRowAttribute(id, "ID");
        console.log(_selectedSurveyId)
    });
    Survey.setSkin("dhx_skyblue");
    Survey.init();
    refreshdata();

    function redirectToEditSurvey() {
        var id = window._selectedSurveyId;
        if (!id) {
            alert("Pilih baris data");
            return;
        }
        window.location.href = "partner-survey-create?id=" + id;
    }

    function redirectToReplySurvey() {
        var id = window._selectedSurveyId;
        if (!id) {
            alert("Pilih baris data");
            return;
        }
        window.location.href = "partner-survey-create-reply?parent_id=" + id;
    }

    function refreshdata() {
        Survey.clearAndLoad('partner-survey-data');
    }
</script>
@endsection