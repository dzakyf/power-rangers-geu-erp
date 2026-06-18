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
                <input type="button" id="createCapex" name="createCapex" value="Buat Capex" class="btn btn-info mt-2 mr-2" onclick="redirectToCapexForm()" />
                <input type="button" id="editCapex" value="Ubah" class="btn btn-primary mt-2 mr-2" onclick="editCapex()" />
                <input type="button" id="deleteCapex" value="Hapus" class="btn btn-danger mt-2 mr-2" onclick="deleteCapex()" />
                <input type="button" id="ekselTableGrid1Det" name="ekselTableGrid1Det" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TableGrid1.toExcel('./export/generate.php');" />
                <!-- <input type="button" id="detailTableGrid1" name="detailTableGrid1" value="Detail" class="btn btn-info mt-2 mr-2" onclick="TableGrid1Klik()" /> -->
            </div>

        </div>
    </div>
    <div id="divlistOpportunity"></div>
    <div id="DivProcess"></div>

</div>

<script>
    let opportunityId = null
    $(document).ready(function() {
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


            initTableGrid1(data.opportunityId)
            opportunityId = data.opportunityId
            console.log('Selected:', opportunityId);
        });

        $('#MitraList').on('select2:unselect', function(e) {
            console.log('Unselected');
        });

    })

    function redirectToCapexForm() {

        window.location.href = `partner-capex-create?id=${opportunityId}`
    }

    function editCapex() {
        if (!selectedCapexDbId) {
            alert('Pilih data yang memiliki ID (data tidak teragregasi)');
            return;
        }

        $.get('partner-capex-detail', { id: selectedCapexDbId }, function(res) {
            var data = res.data;
            if (!data) { alert('Data tidak ditemukan'); return; }

            var bodyHtml = '<form id="editCapexForm">' +
                '<input type="hidden" name="id" value="' + data.ID + '">' +
                '<div class="form-group"><label>Qty</label>' +
                '<input type="number" name="Qty" id="edit_Qty" class="form-control" value="' + data.Qty + '" required></div>' +
                '<div class="form-group"><label>Price</label>' +
                '<input type="number" name="Price" id="edit_Price" class="form-control" value="' + data.Price + '" required></div>' +
                '<div class="form-group"><label>Total</label>' +
                '<input type="number" name="Total" id="edit_Total" class="form-control" value="' + data.Total + '" required readonly></div>' +
                '<div class="form-group"><label>Asset / Description</label>' +
                '<input type="text" name="Description" id="edit_Description" class="form-control" value="' + (data.Description || '') + '" required></div>' +
                '</form>';

            AppModal.open({
                title: 'Edit Capex',
                size: 'md',
                body: bodyHtml,
                actions: [
                    { label: 'Submit', class: 'btn-primary', id: 'btnEditSubmit',
                        onClick: function() {
                            var fd = new FormData(document.getElementById('editCapexForm'));
                            fd.append('_token', '{{ csrf_token() }}');
                            $.ajax({
                                url: 'partner-capex-submit-edit',
                                type: 'POST',
                                data: fd,
                                processData: false,
                                contentType: false,
                                success: function(res) {
                                    if (res.status === 'success') {
                                        alert('Data berhasil diubah');
                                        AppModal.close();
                                        initTableGrid1(opportunityId);
                                    } else {
                                        alert('Gagal: ' + (res.message || ''));
                                    }
                                },
                                error: function(xhr) {
                                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                                }
                            });
                            return false;
                        }
                    }
                ]
            });

            // Auto-calculate Total on Qty/Price change
            $('#edit_Qty, #edit_Price').on('input', function() {
                var qty = parseFloat($('#edit_Qty').val()) || 0;
                var price = parseFloat($('#edit_Price').val()) || 0;
                $('#edit_Total').val(qty * price);
            });
        });
    }

    function deleteCapex() {
        if (!selectedCapexDbId) {
            alert('Pilih data yang memiliki ID (data tidak teragregasi)');
            return;
        }
        if (!confirm('Yakin ingin menghapus data ini?')) return;

        $.ajax({
            url: 'partner-capex-delete',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: selectedCapexDbId
            },
            success: function(res) {
                if (res.status === 'success') {
                    alert('Data berhasil dihapus');
                    initTableGrid1(opportunityId);
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    }

    var selectedCapexId = null;
    var selectedCapexDbId = null;

    function initTableGrid1(opportunityId) {
        TableGrid1 = new dhtmlXGridObject('TableGrid1');
        TableGrid1.setImagePath("./dhtmlx/imgs/");
        TableGrid1.setHeader("No,  Deskripsi,  Total");
        TableGrid1.attachHeader("#rspan,#text_filter,#text_filter");
        TableGrid1.setInitWidths("60,*");
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
        TableGrid1.attachEvent("onRowSelect", function(id) {
            selectedCapexId = id;
            selectedCapexDbId = TableGrid1.getRowAttribute(id, 'db_id');
        });
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