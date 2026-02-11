<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$tanggal1 = isset($_GET['Tgl1']) ? $_GET['Tgl1'] : date("Y-m-d", strtotime("first day of january this year"));
$tanggal2 = isset($_GET['Tgl2']) ? $_GET['Tgl2'] : date("Y-m-d");

?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Sales</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Proforma</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Monitoring Proforma</span>
        </li>
    </ol>
</div>

<div class="row">
    <div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
        Periode <input type="text" id="Tgl1" size="10" value="<?php echo $tanggal1 ?>" readonly="readonly" /> to
        <input type="text" id="Tgl2" size="10" value="<?php echo $tanggal2 ?>" readonly="readonly" />

        <input type="button" id="SubmitFilter" value="Submit" class="btn btn-success mt-2 mr-2" />
    </div>
</div>


<div class="row">
    <div id="ProformaMonitoring" style="width:auto;height:420px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselProformaApprovedDet" name="ekselProformaApprovedDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaApproved.toExcel('./export/generate.php');" />
        <input type="button" id="ekselBA_CollectionDet2" name="ekselBA_CollectionDet2" class="btn btn-info mt-2 mr-2" value="Reprint BA" onclick="reprintba();" /> :: <input type="button" id="ekselBA_CollectionDet4" name="ekselBA_CollectionDet4" class="btn btn-warning mt-2 mr-2" value="Print BA LA" onclick="printbala();" />
        <input type="button" id="ekselBA_CollectionDet4" name="ekselBA_CollectionDet5" class="btn btn-success mt-2 mr-2" value="Print BA LA 2" onclick="printbala2();" />
        <input type="button" id="ekselBA_CollectionDet3" name="ekselBA_CollectionDet3" class="btn btn-info mt-2 mr-2" value="Print FPP" onclick="reprintfpp();" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
    var SHPREVDAY_Cal;
    SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
    SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

    function reprintba() {
        CustomerListsID = ProformaApproved.getSelectedId();

        window.open("badownload?ID=" + CustomerListsID);

    }

    function printbala() {
        CustomerListsID = ProformaApproved.getSelectedId();

        window.open("baladownload?ID=" + CustomerListsID);

    }

    function printbala2() {
        CustomerListsID = ProformaApproved.getSelectedId();

        window.open("bala2download?ID=" + CustomerListsID);

    }

    function reprintfpp() {
        CustomerListsID = ProformaApproved.getSelectedId();

        window.open("fppdownload?ID=" + CustomerListsID);

    }


    function ProformaApprovedKlik() {

        CustomerListsID = ProformaApproved.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'sales-proforma-monitoring-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ProformaApproved = new dhtmlXGridObject('ProformaMonitoring');
    ProformaApproved.setImagePath("./dhtmlx/imgs/");
    ProformaApproved.setHeader("No,Proforma Type,BA No,PO No,Subject,Periode,Periode Sistem,Total Amount,No. BA,Status,FPP DATE,Date Status,No. Inv,No. Mgm Fee,Inv Status,Created Date,Customer Name,Invoice To");
    ProformaApproved.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter");
    ProformaApproved.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    ProformaApproved.setInitWidths("60,120,100,100,200,100,100,100,120,160,180,200,100,100,180,100,100,150");
    ProformaApproved.setColAlign("center,center,left,left,left,left,left,center,center,right,center,center,left,left,left,left,left,left,left");
    ProformaApproved.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,date,date,str,str,str");
    ProformaApproved.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    ProformaApproved.enableSmartRendering(true, 10000);
    ProformaApproved.setNumberFormat("0,000", 7, ".", ",");
    ProformaApproved.attachEvent("onRowDblClicked", ProformaApprovedKlik);

    ProformaApproved.setSkin("dhx_skyblue");
    ProformaApproved.init();
    refreshdata();

    function refreshdata() {
        ProformaApproved.clearAndLoad('xml-sales-proforma-monitoring?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

    }

    function itungx() {
        $.unblockUI();
        return true;
    }

    $("#SubmitFilter").click(function() {
        $.blockUI();
        ProformaApproved.clearAndLoad('xml-sales-proforma-monitoring?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val(), itungx);
    });
</script>
@endsection