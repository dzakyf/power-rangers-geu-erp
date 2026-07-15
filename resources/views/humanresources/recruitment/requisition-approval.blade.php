<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$cekLevel = DB::table('master_hr_approval')->where("Location", $LSP)->whereIn("Level", [2, 3])
    ->where('username', $Saya)->first();


?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Human Resources</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Recruitment</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">PTK</span>
        </li>
    </ol>
</div>

<div>
    <div id="RequisitionLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselRequisitionListsDet" name="ekselRequisitionListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RequisitionLists.toExcel('./export/generate.php');" />
        <div class="card card-body my-3">
            <div class="form-group">
                <label for="txtRequisitionListsDet">Notes:</label>
                <textarea id="txtRequisitionListsDet" name="txtRequisitionListsDet" class="form-control" rows="3"></textarea>
            </div>
            <div class="d-inline-block">
                <input type="button" id="delets" name="delets" value="Cancel PTK" onclick="delsRequisition()" class="btn btn-danger mr-2" onclick="delsRequisition()" />
                <input type="button" id="approvePTK" name="approvePTK" class="btn btn-success mr-2" onclick="closereq()" value="Approve PTK" />
                @if ($cekLevel)
                <!-- <input type="button" id="approveDirPTK" name="approveDirPTK" class="btn btn-success mr-2" onclick="closereqdir()" value="Approve PTK and Request Approve Director" /> -->
                @endif
            </div>
        </div>
    </div>

</div>
<div id="divlistRequisition"></div>
<script>
    RequisitionLists = new dhtmlXGridObject('RequisitionLists');
    RequisitionLists.setImagePath("./dhtmlx/imgs/");
    RequisitionLists.setHeader("Checklist,No.,Task ID,Subject,Job Position,Description,Requirement,Department,Priority,Qty,Aging(days),Start date,End Date,Reference,PIC,Status");
    RequisitionLists.setInitWidths("60,70,100,250,120,120,120,100,100,50,100,100,100,100,100,100");
    RequisitionLists.setColAlign("center,left,left,center,center,center,center,center,center,center,center,center,center,center");
    RequisitionLists.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,coro,ro,ro,ro,ro,ro,combo,ro");
    RequisitionLists.setColSorting("int,int,str,str,str,str,str,str,str,str,str");
    RequisitionLists.setEditable(true);
    PRequisitionLists = new dataProcessor("requisition-approval-update");
    PRequisitionLists.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },

    })
    PRequisitionLists.setUpdateMode("Off");
    PRequisitionLists.init(RequisitionLists);
    RequisitionLists.setSkin("dhx_skyblue");
    RequisitionLists.init();
    PRequisitionLists.attachEvent("onBeforeDataSending", function(id, state, data) {
        // Modify or add payload data here
        let notes = $('#txtRequisitionListsDet').val();
        data.customPayload = {
            notes: notes
        };
        console.log(data); // For debugging purposes
        return true; // Return true to continue processing
    });
    PRequisitionLists.attachEvent("onAfterUpdateFinish", function(id, state, data) {
        alert('Data Updated Successfully!');
        setTimeout(function() {
            refreshdata();
        }, 1000);
    });
    RequisitionLists.getCombo(7).put("NORMAL", "NORMAL");
    RequisitionLists.getCombo(7).put("HIGH", "HIGH");
    RequisitionLists.getCombo(7).put("URGENT", "URGENT");
    combocurr = RequisitionLists.getColumnCombo(13);
    combocurr.enableFilteringMode(true);
    combocurr.load("xml-load-pic-production");
    refreshdata();

    function refreshdata() {
        RequisitionLists.clearAndLoad('xml-recruitment-requisition-approval');

    }

    function closereq() {
        let checkedRows = RequisitionLists.getCheckedRows(0);
        if (!checkedRows) {
            alert('Please select at least one requisition to approve.');
            return;
        }
        dhtmlx.confirm('Approve this Requisition?', 'Requisition', function(r) {
            if (r == true) {
                // Modify or add payload data here
                let notes = $('#txtRequisitionListsDet').val();
                if (!notes && notes.length <= 3) {
                    alert('Please enter notes before approving.');
                    return false; // Prevent data from being sent
                }
                PRequisitionLists.sendData();
            }
        })
    }

    function closereqdir() {
        let checkedRows = RequisitionLists.getCheckedRows(0);
        if (!checkedRows) {
            alert('Please select at least one requisition to cancel.');
            return;
        }
        dhtmlx.confirm('Cancel this Requisition?', 'Requisition', function(r) {
            if (r == true) {
                // Modify or add payload data here
                let notes = $('#txtRequisitionListsDet').val();
                if (!notes && notes.length <= 3) {
                    alert('Please enter notes before approving.');
                    return false; // Prevent data from being sent
                }
                checkedRows.split(',').forEach(function(rowId) {
                    PRequisitionLists.setUpdated(rowId, true, "updated-dir");
                });
                PRequisitionLists.sendData();
            }
        })
    }

    function delsRequisition() {
        let checkedRows = RequisitionLists.getCheckedRows(0);
        if (!checkedRows) {
            alert('Please select at least one requisition to cancel.');
            return;
        }
        dhtmlx.confirm('Cancel this Requisition?', 'Requisition', function(r) {
            if (r == true) {
                // Modify or add payload data here
                let notes = $('#txtRequisitionListsDet').val();
                if (!notes && notes.length <= 3) {
                    alert('Please enter notes before approving.');
                    return false; // Prevent data from being sent
                }
                checkedRows.split(',').forEach(function(rowId) {
                    PRequisitionLists.setUpdated(rowId, true, "deleted");
                });
                PRequisitionLists.sendData();
            }
        })
    }


    function UpdateGrid_Requisition() {
        var out = 0;
        for (var i = 0; i < RequisitionLists.getRowsNum(); i++) {
            rowIdMov = RequisitionLists.getRowId(i);
            RequisitionListsProc.setUpdated(rowIdMov, true);
        }
        return out;
    }

    function SubmitGrid_Requisition() {
        RequisitionListsProc.sendData();
        alert('Data Successfully Updated!');
        RequisitionLists.clearAll();
        refreshdata();
    }
</script>
@endsection