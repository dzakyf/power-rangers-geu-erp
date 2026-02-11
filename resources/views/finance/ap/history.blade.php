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
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Account Payable</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Data History</span>
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
    <div id="APHistory" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselAPHistoryDet" name="ekselAPHistoryDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="APHistory.toExcel('./export/generate.php');" />
    </div>
</div>
<div id="divlistcustomer"></div>

<script>

var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

    function ApprovalInvKlik() {

        CustomerListsID = APHistory.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-ap-history-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    APHistory = new dhtmlXGridObject('APHistory');
    APHistory.setImagePath("./dhtmlx/imgs/");
    APHistory.setHeader("No.,Invoice No,Invoice Date,PO No,Reference,Total AP,Paid,Sisa,Due Date,Subject Payment,Department,Vendor No, Vendor Name,Status,Tgl Bayar,Received Date");
    APHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter");
    APHistory.setInitWidths("60,120,100,100,150,100,100,100,100,360,110,100,120,100,100,100,100");
    APHistory.setColAlign("center,center,center,center,center,right,right,right,center,center,center,left,center,center,center,center,center");
    APHistory.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    APHistory.setColSorting("int,str,date,str,str,int,int,int,date,str");
    APHistory.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total");
    APHistory.setEditable(true);
    APHistory.setSkin("dhx_skyblue");
    APHistory.setDateFormat("%Y-%m-%d");
    APHistory.setNumberFormat("0,000.00", 5, ".", ",");
    APHistory.setNumberFormat("0,000.00", 6, ".", ",");
    APHistory.setNumberFormat("0,000.00", 7, ".", ",");
    APHistory.attachEvent("onRowDblClicked", ApprovalInvKlik);

    APHistory.init();
    refreshdata();

    function refreshdata() {
        APHistory.clearAndLoad('xml-finance-ap-history?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

    }

    function itungx() {
			$.unblockUI();
			return true;
		}
    $("#SubmitFilter").click(function() {
        $.blockUI();
			APHistory.clearAndLoad('xml-finance-ap-history?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val(), itungx);
		});
</script>
@endsection