<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP  = Auth::user()->Location;
$Saya = Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Sustainability Declaration',
    'breadcrumbs' => [
        ['label' => 'Export Management'],
        ['label' => 'Sustainability Declaration'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Sustainability Declaration List</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <input type="button" id="newSD" value="New SD" class="btn btn-sm btn-primary" onclick="SDNewBtn()" />
            <input type="button" id="detailSD" value="Detail SD" class="btn btn-sm btn-info" onclick="SDKlik()" />
            <input type="button" id="printSD" value="Print SD" class="btn btn-sm btn-secondary" onclick="PrintSD()" />
            <input type="button" class="btn btn-sm btn-warning" value="Export Excel" onclick="SustainabilityDeclaration.toExcel('./export/generate.php');" />
            <button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-grid-wrapper">
            <div id="SustainabilityDeclaration" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
    function PrintSD() {
        var SDID = SustainabilityDeclaration.getSelectedId();
        if (SDID != null) {
            window.open('cns-export-sustainability-declaration-print?Task=' + SustainabilityDeclaration.cells(SDID, 1).getValue(), '_blank');
        } else {
            swal('Perhatian', 'Pilih baris data', 'warning');
        }
    }
    function SDNewBtn() {
        LoadM('divlistOpportunity', 'cns-export-sustainability-declaration-new');
    }
    function SDKlik() {
        var SDID = SustainabilityDeclaration.getSelectedId();
        if (SDID != null) {
            LoadM('divlistOpportunity', 'cns-export-sustainability-declaration-detail?Task=' + SustainabilityDeclaration.cells(SDID, 1).getValue());
        } else {
            swal('Perhatian', 'Pilih baris data', 'warning');
        }
    }

    SustainabilityDeclaration = new dhtmlXGridObject('SustainabilityDeclaration');
    SustainabilityDeclaration.setImagePath("./dhtmlx/imgs/");
    SustainabilityDeclaration.setHeader("No,SD No,Issue Date,Consignee,Contract No,Dispatch Date,Quantity (MT),Country of Origin,Status");
    SustainabilityDeclaration.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    SustainabilityDeclaration.setInitWidths("50,200,110,200,150,110,120,130,100");
    SustainabilityDeclaration.setColAlign("center,left,left,left,left,left,right,left,center");
    SustainabilityDeclaration.setColSorting("int,str,str,str,str,str,str,str,str");
    SustainabilityDeclaration.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,cns_badge");
    SustainabilityDeclaration.attachEvent("onRowDblClicked", SDKlik);
    SustainabilityDeclaration.setSkin("dhx_skyblue");
    SustainabilityDeclaration.init();

    var SustainabilityDeclarationPagination = initCnsGridPagination(SustainabilityDeclaration, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        serverFilter: false,
        getUrl: function (params) {
            var q = [
                'posStart=' + params.posStart,
                'count=' + params.count
            ];
            if (params.sortCol !== null && params.sortCol !== undefined) {
                q.push('sort_col=' + params.sortCol);
                q.push('sort_dir=' + (params.sortDir || 'asc'));
            }
            return 'xml-cns-export-sustainability-declaration?' + q.join('&');
        }
    });

    function refreshdata() {
        SustainabilityDeclarationPagination.reload(1);
    }
    refreshdata();
</script>
@endsection
