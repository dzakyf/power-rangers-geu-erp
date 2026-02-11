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
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Approved</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Invoices</span>
        </li>
    </ol>
</div>
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1" size="10" value="<?php echo date("Y-m-d") ?>" readonly="readonly" /> to
		<input type="text" id="Tgl2" size="10" value="<?php echo date("Y-m-d") ?>" readonly="readonly" />

		<input type="button" id="SubmitFilter" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>
</div>
<div class="row">
    <div id="ApprovalInv" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselApprovalInvDet" name="ekselApprovalInvDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ApprovalInv.toExcel('./export/generate.php');" />


        <input type="button" id="ApprovalInvDel2" NAME="ApprovalInvDel2" class="btn btn-danger mt-2 mr-2" value="Cancell All Selected Task" />
		<input type="button" id="ApprovalInvDel" NAME="ApprovalInvDel" class="btn btn-warning mt-2 mr-2" value="Cancel Invoice only Selected Task" /> :: Cancel Reason <input type="text" size="30" id="CancelReason" onchange="isiReason(this.value);" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

    function ApprovalInvKlik() {

        CustomerListsID = ApprovalInv.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-approved-invoice-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ApprovalInv = new dhtmlXGridObject('ApprovalInv');
    ApprovalInv.setImagePath("./dhtmlx/imgs/");
    ApprovalInv.setHeader("No,Inv KSPS,Inv Date,Customer Name,Deskripsi,Kategori,Periode,Nominal (DPP), PPN, Total,Faktur,Created Date,Posted Date,Invoice TO,Status,No. BA,reason");
    ApprovalInv.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#number_filter,#number_filter,#number_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    ApprovalInv.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total");
    ApprovalInv.setInitWidths("60,100,100,100,250,180,100,100,100,100,150,150,150,150,150,150,0");
    ApprovalInv.setColAlign("center,center,center,left,left,left,left,right,right,right,center,center,center,left,left,left,left");
    ApprovalInv.setColSorting("na,str,date,str,str,str,str,int,int,int,str,str,str,str,str,str,str");
    ApprovalInv.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ed,ro");
    ApprovalInv.enableSmartRendering(true, 150);
    ApprovalInv.setNumberFormat("0,000", 7, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 8, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 9, ".", ",");
    ApprovalInv.attachEvent("onRowDblClicked", ApprovalInvKlik);
    PApprovalInv = new dataProcessor("finance-cancel-invoice-update");
    PApprovalInv.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },

    });
    PApprovalInv.init(ApprovalInv);
    ApprovalInv.setSkin("dhx_skyblue");
    ApprovalInv.init();
   
   
    function refreshdata() {
        ApprovalInv.clearAndLoad('xml-finance-approved-invoice?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

    }

    function isiReason(isipesan) {
        ApprovalInvMID = ApprovalInv.getSelectedId();
        if (ApprovalInvMID == '') {
            alert('Mohon tentukan tasklog yang akan di cancel/return!');
        } else {
            ApprovalInvID = ApprovalInv.cells(ApprovalInv.getSelectedId(), 15).setValue(isipesan);
        }

    }
	 $('#SubmitFilter').click(function() {
		 refreshdata();
	 });
    $('#ApprovalInvDel').click(function() {
        isipesans = ApprovalInv.cells(ApprovalInv.getSelectedId(), 15).getValue();
        if (isipesans == '') {
            alert('Please fill the reason for cancel/return!');
            return false;
        }
        dhtmlx.confirm('Are you sure want  to cancel or return this tasklog?', 'Invoice', function(r) {

            if (r == true) {
                //ApprovalInvID = ApprovalInv.cells(ApprovalInv.getSelectedId(),1).getValue();
                //Status = ApprovalInv.cells(ApprovalInv.getSelectedId(),4).getValue();
                ApprovalInv.cells(ApprovalInv.getSelectedId(), 15).setValue($("#CancelReason").val());
                PApprovalInv.setUpdated(ApprovalInv.getSelectedId(), true);

                PApprovalInv.sendData();
                alert('Tasklog was canceled!');
                setTimeout(function() {
                    window.location.href = "finance-invoice-approved";
                }, 1000);



            }
        });

    });
	
	$('#ApprovalInvDel2').click(function() {
        isipesans = ApprovalInv.cells(ApprovalInv.getSelectedId(), 15).getValue();
        if (isipesans == '') {
            alert('Please fill the reason for cancel/return!');
            return false;
        }
        dhtmlx.confirm('Are you sure want  to cancel all this invoice and BA?', 'Invoice', function(r) {

            if (r == true) {
                //ApprovalInvID = ApprovalInv.cells(ApprovalInv.getSelectedId(),1).getValue();
                //Status = ApprovalInv.cells(ApprovalInv.getSelectedId(),4).getValue();
                ApprovalInv.cells(ApprovalInv.getSelectedId(), 0).setValue("ALL");
                ApprovalInv.cells(ApprovalInv.getSelectedId(), 15).setValue($("#CancelReason").val());
                PApprovalInv.setUpdated(ApprovalInv.getSelectedId(), true);

                PApprovalInv.sendData();
                alert('Tasklog  and BA was canceled!');
                setTimeout(function() {
                    window.location.href = "finance-invoice-approved";
                }, 1000);



            }
        });

    });
</script>
@endsection