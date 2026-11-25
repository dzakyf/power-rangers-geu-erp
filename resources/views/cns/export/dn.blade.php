<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Delivery Note',
    'breadcrumbs' => [
        ['label' => 'Export Management'],
        ['label' => 'Delivery Note'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Delivery Note List</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <input type="button" id="newDN" name="newDN" value="New DN" class="btn btn-sm btn-primary" onclick="DeliveryNoteNewBtn()" />
            <input type="button" id="detailDN" name="detailDN" value="Detail DN" class="btn btn-sm btn-info" onclick="DeliveryNoteKlik()" />
            <input type="button" id="approveDN" name="approveDN" value="Approve DN" class="btn btn-sm btn-success" onclick="ApproveDN()" />
            <input type="button" id="printDN" name="printDN" value="Print DN" class="btn btn-sm btn-secondary" onclick="PrintDN()" />
            <input type="button" id="ekselDeliveryNoteDet" name="ekselDeliveryNoteDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="DeliveryNote.toExcel('./export/generate.php');" />
            <button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-grid-wrapper">
            <div id="DeliveryNote" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
    function ApproveDN() {
        DeliveryNoteID = DeliveryNote.getSelectedId();
        if (DeliveryNoteID == null) {
            swal('Perhatian', 'Pilih baris data', 'warning');
            return false;
        }
        var dnNo = DeliveryNote.cells(DeliveryNoteID, 1).getValue();
        var status = DeliveryNote.cells(DeliveryNoteID, 9).getValue();
        if (status === 'APPROVED') {
            swal('Informasi', 'Delivery Note ' + dnNo + ' sudah berstatus APPROVED.', 'info');
            return false;
        }
        swal({
            title: 'Konfirmasi Approve',
            text: 'Approve Delivery Note ' + dnNo + '?',
            icon: 'warning',
            buttons: ['Batal', 'Ya, Approve']
        }).then(function (ok) {
            if (!ok) return;
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: 'cns-export-delivery-note-approve?_token={{ csrf_token() }}',
                type: 'POST',
                data: { dn_no: dnNo },
                success: function (res) {
                    if (res && res.status === 'success') {
                        swal('Berhasil!', res.message || 'Delivery Note berhasil di-approve!', 'success');
                        refreshdata();
                    } else {
                        swal('Error', (res && res.message) || 'Gagal approve Delivery Note!', 'error');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Error Internal! Silakan coba lagi.';
                    swal('Error', msg, 'error');
                }
            });
        });
        return true;
    }

    function PrintDN() {
        DeliveryNoteID = DeliveryNote.getSelectedId();
        if (DeliveryNoteID != null) {
            window.open('cns-export-delivery-note-print?Task=' + DeliveryNote.cells(DeliveryNoteID, 1).getValue(), '_blank');
        } else {
            swal('Perhatian', 'Pilih baris data', 'warning');
        }
        return true;
    }
    function DeliveryNoteNewBtn() {
        LoadM('divlistOpportunity', 'cns-export-delivery-note-new');
        return true;
    }
    function DeliveryNoteKlik() {
        DeliveryNoteID = DeliveryNote.getSelectedId();
        if (DeliveryNoteID != null) {
            LoadM('divlistOpportunity', 'cns-export-delivery-note-detail?Task=' + DeliveryNote.cells(DeliveryNoteID, 1).getValue());
        } else {
            swal('Perhatian', 'Pilih baris data', 'warning');
        }
        return true;
    }
    DeliveryNote = new dhtmlXGridObject('DeliveryNote');
    DeliveryNote.setImagePath("./dhtmlx/imgs/");
    DeliveryNote.setHeader("No,DN No,Entitas Penerbit,Delivery Date,Consignee,Contract No,Product,Total Tonnage (MT),Country of Origin,Status");
    DeliveryNote.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    DeliveryNote.setInitWidths("50,160,180,100,160,140,220,120,120,90");
    DeliveryNote.setColAlign("center,left,left,left,left,left,left,right,left,center");
    DeliveryNote.setColSorting("int,str,str,str,str,str,str,str,str,str");
    DeliveryNote.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge");
    DeliveryNote.attachEvent("onRowDblClicked", DeliveryNoteKlik);
    DeliveryNote.setSkin("dhx_skyblue");
    DeliveryNote.init();

    var DeliveryNotePagination = initCnsGridPagination(DeliveryNote, {
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
            return 'xml-cns-export-delivery-note?' + q.join('&');
        }
    });
    refreshdata();

    function refreshdata() {
        DeliveryNotePagination.reload(1);
    }
</script>
@endsection
