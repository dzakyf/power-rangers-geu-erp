<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Priv = Auth::user()->Priv_ID;
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div class="mb-2 mb-md-0">
        <h4 class="content-title text-white fw-bold mb-1"
            style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            <i class="fe fe-archive me-2"></i>Penggajian Disetujui (Approved Payroll)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                        Payroll
                    </a>
                </li>
                <li class="breadcrumb-item active text-white" aria-current="page"
                    style="color: #ffffff !important; font-weight: 600;">
                    Approved Archive
                </li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
            style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
            <i class="fe fe-play me-1"></i> Generator Payroll
        </a>
        <a href="{{ url('payroll-approval') }}" class="btn btn-sm rounded-pill shadow-sm"
            style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
            <i class="fe fe-check-circle me-1"></i> Approval Payroll
        </a>
    </div>
</div>
<!-- End Main Content Header -->

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-success">Daftar Batch Payroll yang Telah Disetujui</h5>
        <small class="text-muted">Klik ganda pada baris batch untuk melihat rincian gaji, slip gaji karyawan, dan status transfer bank.</small>
    </div>
    <div class="card-body p-3">
        <div id="PayrollApproval" style="width:100%; height:320px; background-color:white; overflow:hidden;"></div>
        <div class="d-flex flex-wrap gap-2 mt-3" id="CekReload">
            <button type="button" id="ekselPODListDet" class="btn btn-warning rounded-pill px-3 text-dark" onclick="PayrollApproval.toExcel('{{ asset('export/generate.php') }}');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="refreshdata();">
                <i class="fa fa-sync me-1"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div id="divlistProjectOrgx"></div>

<script>
    function PayrollApprovalKlik() {
        var PayrollApprovalID = PayrollApproval.getSelectedId();
        if (PayrollApprovalID != '') {
            LoadM('divlistProjectOrgx', '{{ url('payroll-approved-detail') }}?ID=' + PayrollApprovalID);
        }
        return true;
    }

    PayrollApproval = new dhtmlXGridObject('PayrollApproval');
    PayrollApproval.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    PayrollApproval.setHeader("No.,Salary ID,Period,Total (Rp),Category,Use_For,Note,Created By,Created Date,Status");
    PayrollApproval.setInitWidths("70,220,110,130,110,110,140,120,130,110");
    PayrollApproval.setColAlign("center,left,center,right,center,center,left,center,center,center");
    PayrollApproval.setColTypes("ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
    PayrollApproval.setColSorting("int,str,str,int,str,str,str,str,str,str");
    PayrollApproval.setNumberFormat("0,000", 3, ".", ",");
    PayrollApproval.attachEvent("onRowDblClicked", PayrollApprovalKlik);
    PayrollApproval.setSkin("dhx_skyblue");
    PayrollApproval.enableMultiline(true);
    PayrollApproval.init();
    refreshdata();

    function refreshdata() {
        PayrollApproval.clearAndLoad('{{ url('xml-payroll-approved') }}');
    }
</script>
@endsection
