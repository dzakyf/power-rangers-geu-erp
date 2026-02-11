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
    <h1>Sales</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Report</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">PO Report</span>
        </li>
    </ol>
</div>

<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Tahun <input type="text" id="Tgl1" size="10" value="<?php echo date('Y') ?>" /> 
        <input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>
    
</div>
<div class="row">
    <div id="OpporActive" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselOpporActiveDet" name="ekselOpporActiveDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpporActive.toExcel('./export/generate.php');" />
    </div>

</div>
<div id="divlistOpportunity"></div>
<script>
	var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

        $("#SubmitCheck").click(function() {
			$.blockUI();
			OpporActive.clearAndLoad('xml-sales-report-po?&Tgl1=' + $("#Tgl1").val(), itungx);
		});

        function itungx() {
			$.unblockUI();
			return true;
		}

    OpporActive = new dhtmlXGridObject('OpporActive');
    OpporActive.setImagePath("./dhtmlx/imgs/");
    OpporActive.setHeader("No,Subject,Company Name,No PO,Start Date,End Date,Nilai PO,January,February,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember,Total");
    OpporActive.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter");
    OpporActive.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
    OpporActive.setInitWidths("60,250,150,180,120,120,120,100,100,100,100,100,100,100,100,100,100,100,100,100");
    OpporActive.setColAlign("center,left,left,left,left,left,right,right,right,right,right,right,right,right,right,right,right,right,right,right");
    OpporActive.setColSorting("int,str,str,str,str,int,int,int,int,int,int,int,int,int,int,int,int,int,int");
    OpporActive.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
    OpporActive.enableSmartRendering(true, 1000);
    OpporActive.setNumberFormat("0,000", 6, ".", ",");
    OpporActive.setNumberFormat("0,000", 7, ".", ",");
    OpporActive.setNumberFormat("0,000", 8, ".", ",");
    OpporActive.setNumberFormat("0,000", 9, ".", ",");
    OpporActive.setNumberFormat("0,000", 10, ".", ",");
    OpporActive.setNumberFormat("0,000", 11, ".", ",");
    OpporActive.setNumberFormat("0,000", 12, ".", ",");
    OpporActive.setNumberFormat("0,000", 13, ".", ",");
    OpporActive.setNumberFormat("0,000", 14, ".", ",");
    OpporActive.setNumberFormat("0,000", 15, ".", ",");
    OpporActive.setNumberFormat("0,000", 16, ".", ",");
    OpporActive.setNumberFormat("0,000", 17, ".", ",");
    OpporActive.setNumberFormat("0,000", 18, ".", ",");
    OpporActive.setNumberFormat("0,000", 19, ".", ",");
    OpporActive.setSkin("dhx_skyblue");
    OpporActive.init();
    refreshdata();

    function refreshdata() {
        OpporActive.clearAndLoad('xml-sales-report-po?&Tgl1=' + $("#Tgl1").val());

    }
</script>
@endsection