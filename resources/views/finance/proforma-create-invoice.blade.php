<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Create</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Invoice</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="FinanceC" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselFinanceCDet" name="ekselFinanceCDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="FinanceC.toExcel('./export/generate.php');" />
        <input type="button" value="Batch Create" id="batchCreate" class="btn btn-primary mt-2 mr-2" />
    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
    function FinanceCKlik() {

        CustomerListsID = FinanceC.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-proforma-create-invoice-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    FinanceC = new dhtmlXGridObject('FinanceC');
    FinanceC.setImagePath("./dhtmlx/imgs/");
    FinanceC.setHeader("No,Proforma No,Subject,Periode,Customer Name,Proforma Type,Nominal(DPP),Management Fee,Total Amount,BA No,Status,Invoice To,Created Date,Checked");
    FinanceC.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#master_checkbox");
    FinanceC.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total");
    FinanceC.setInitWidths("60,120,100,100,100,100,100,100,80,100,100,100,100,100,50");
    FinanceC.setColAlign("center,center,left,center,center,right,right,right,center,center,center,center,left,right,center");
    FinanceC.setColSorting("int,str,str,str,str,str,str,str,str,str,str,int");
    FinanceC.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ch,ch");
    FinanceC.enableSmartRendering(true, 150);
    FinanceC.setNumberFormat("0,000", 6, ".", ",");
    FinanceC.setNumberFormat("0,000", 7, ".", ",");
    FinanceC.setNumberFormat("0,000", 8, ".", ",");
    FinanceC.attachEvent("onRowDblClicked", FinanceCKlik);

    FinanceC.setSkin("dhx_skyblue");
    FinanceC.init();
    refreshdata();

    function refreshdata() {
        FinanceC.clearAndLoad('xml-finance-proforma-create-invoice');

    }


    $("#batchCreate").click(function() {

        var checked = '';

        for (var i = 0; i < FinanceC.getRowsNum(); i++) {


            CheckPR = FinanceC.cells2(i, 13).getValue();


            if (CheckPR == 1) {
                if (checked == '') {
                    checked += FinanceC.cells2(i, 1).getValue();
                } else {
                    checked += "," + FinanceC.cells2(i, 1).getValue();

                }


            }
        }


        if (checked != '') {
            LoadM('divlistcustomer', 'finance-proforma-create-invoice-detail?batch=' + checked);
        }
    });
</script>
@endsection