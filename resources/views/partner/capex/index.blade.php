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
            <span class="active">Capital Expenditure</span>
        </li>
    </ol>
</div>

<div>
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CAPITAL EXPENDITURE</div>
    <div class="card">
        <div class="card-body">
            <div class="w-25">
                <select id="MitraList" class="custom-select">
                    <option></option>
                </select>
            </div>
            <div id="TableGrid1" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <!-- <input type="button" id="detailTableGrid1" name="detailTableGrid1" value="Formulir Capital Expenditure" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->
                <input type="button" id="ekselTableGrid1Det" name="ekselTableGrid1Det" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TableGrid1.toExcel('./export/generate.php');" />
                <!-- <input type="button" id="detailTableGrid1" name="detailTableGrid1" value="Detail" class="btn btn-info mt-2 mr-2" onclick="TableGrid1Klik()" /> -->
            </div>

        </div>
    </div>
    <div id="divlistOpportunity"></div>
    <div id="DivProcess"></div>

</div>

<script>
    $(document).ready(function() {
        let opportunityId = null
        initTableGrid1(opportunityId)

        $('#MitraList').select2({
            ajax: {
                url: 'partner-data-active-mitra',
                dataType: 'xml',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(xmlData) {
                    var results = [];

                    $(xmlData).find('row').each(function() {
                        var cells = $(this).find('cell');
                        var opportunityId = $(this).attr('Opportunity_Id');

                        results.push({
                            id: cells.eq(0).text(),
                            text: cells.eq(1).text(),
                            cell2: cells.eq(2).text(),
                            opportunityId: opportunityId,
                        });
                    });

                    return {
                        results: results,
                        pagination: {
                            more: results.length === 20
                        }
                    };
                }
            },
            minimumInputLength: 0,
            placeholder: 'Cari Mitra...',
            allowClear: true
        });

        $('#MitraList').on('select2:select', function(e) {
            var data = e.params.data;
            console.log('Selected:', data);

            initTableGrid1(data.opportunityId)
        });

        $('#MitraList').on('select2:unselect', function(e) {
            console.log('Unselected');
        });

    })

    function initTableGrid1(opportunityId) {
        TableGrid1 = new dhtmlXGridObject('TableGrid1');
        TableGrid1.setImagePath("./dhtmlx/imgs/");
        TableGrid1.setHeader("No, Nama Lengkap, Deskripsi, Jumlah, Total");
        TableGrid1.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        // TableGrid1.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        TableGrid1.setInitWidths("60,100,150,200,100");
        TableGrid1.setColAlign("center,left,left,left,left");
        TableGrid1.setColSorting("int,str,str,str,str")
        TableGrid1.setColTypes("ron,ro,ro,ro,ed,ro");
        TableGrid1.enableSmartRendering(true, 150);
        // TableGrid1.setNumberFormat("0,000", 7, ".", ",");
        // TableGrid1.attachEvent("onRowDblClicked", TableGrid1Klik);
        TableGrid1.attachEvent("onEditCell", function(stage, action, rowId, cellIndex, newValue, oldValue) {
            if (stage == 2) {
                console.log(stage, action, rowId, cellIndex, newValue, oldValue)
                return true
            }
            return true
        })
        TableGrid1.setSkin("dhx_skyblue");
        TableGrid1.init();

        TableGrid1.clearAndLoad(`partner-capex-data?opportunity_id=${opportunityId}`);
    }

    function TableGrid1Klik() {

        TableGrid1ID = TableGrid1.getSelectedId();
        if (TableGrid1ID != null) {
            LoadM('divlistOpportunity', 'partner-warehouse-monitoring-detail?Task=' + TableGrid1ID);
        } else {
            alert("Pilih baris data");
        }
        return true;
    }
</script>
@endsection