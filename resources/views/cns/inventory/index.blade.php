@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">
            <span class="active">UCO Tracking</span>
        </li>
    </ol>
</div>

<div id="ProjectionSection" class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UCO TRACKING</div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-end gap-2 mb-2">
                <div class="m-1">
                    <label class="d-block mb-1"><small>Bulan</small></label>
                    <input type="month" id="filterMonth" class="form-control form-control-sm" style="width:160px;" />
                </div>
                <!-- <div class="m-1" style="width:280px;">
                    <label class="d-block mb-1"><small>No. Polisi</small></label>
                    <select id="filterPoliceNo" style="width:100%;">
                        <option value="">-- Semua --</option>
                    </select>
                </div> -->
                <div>
                    <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                    <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                </div>
            </div>
            <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
            <div>
                <!-- <input type="button" id="CreateQC" name="CreateQC" value="Create" class="btn btn-info mt-2 mr-2" onclick="createQC()" /> -->
                <input type="button" id="DetailQC" name="DetailQC" value="Detail" class="btn btn-info mt-2 mr-2" onclick="detailQC()" />
                <!-- <input type="button" id="DeleteQC" name="DeleteQC" value="Delete" class="btn btn-info mt-2 mr-2" onclick="deleteQC()" /> -->
            </div>
        </div>
    </div>
</div>

@include('cns.inventory.edit')
<script>
    var mitraId = null;
    $(document).ready(function() {
        var now = new Date();
        $('#filterMonth').val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
        initProjectionGrid()
        loadPoliceNoOptions()
    })

    function loadPoliceNoOptions() {
        var $select = $('#filterPoliceNo')
        $select.select2({
            placeholder: '-- Semua --',
            allowClear: true,
            width: '100%',
        })
        $.get('cns-data-police-license', function(res) {
            $.each(res.data, function(_, item) {
                $select.append(
                    $('<option>', {
                        value: item.police_license,
                        text: item.police_license + ' — ' + item.vessel_name
                    })
                )
            })
            $select.trigger('change')
        })
    }

    function createQC() {
        $('#FormQualityControl').show();
        $('#FormEditQualityControl').hide();
    }

    function detailQC() {
        $('#FormQualityControl').hide();
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }
        prefillEditQC(rowId);
    }

    function resetFilters() {
        $('#filterMonth').val('')
        $('#filterPoliceNo').val('').trigger('change')
        ProjectionGrid.clearAndLoad('cns-inventory-tracking-data')
    }

    function applyFilters() {
        var month = $('#filterMonth').val()
        var policeNo = $('#filterPoliceNo').val()
        var params = []
        if (month) params.push('month=' + month)
        if (policeNo) params.push('police_no=' + encodeURIComponent(policeNo))
        var url = 'cns-inventory-tracking-data' + (params.length ? '?' + params.join('&') : '')
        ProjectionGrid.clearAndLoad(url)
    }

    function deleteQC() {
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

        var tanggal = ProjectionGrid.cells(rowId, 1).getValue();
        var date = new Date(tanggal);
        var formatted = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        $.ajax({
            url: 'cns-quality-delete',
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                Date: formatted
            },
            success: function(res) {
                if (res.status === 'success') {
                    ProjectionGrid.clearAndLoad('cns-quality-data');
                }
            },
            error: function() {
                alert('Gagal menghapus data');
            }
        });
    }

    function redirectToCreateProjection() {
        if (!mitraId) {
            alert("Pilh mitra terlebih dahulu")
            return
        }
        window.location.href = `projection-create?id=${mitraId}`
    }



    function initProjectionGrid() {
        $('#ProjectionSection').show()

        let tHeader = [
            'No', 'Bulan', 'Tanggal',
            'Asal Warehouse', 'Qty Outbound PTU', 'Qty Inbound Pelindo (Kg)',
            'Qty Outbound Pelindo (Kg)', 'Jumlah Stok Pelindo (Kg)', 'Qty Sounding', 'Remarks', 'Attachment Nota Timbang'
        ]


        let tTopHeader = [
            '#rspan', '#text_filter', '#text_filter',
            '#text_filter', '#text_filter', '#rspan', '#rspan', '#rspan', '#rspan', '#rspan', '#rspan'
        ]

        let tWidth = tHeader.map(() => '*')
        let tColType = tHeader.map((e, i) => {

            // if (i === 1) {
            //     return 'dhxCalendar';
            // }

            return 'ro'
        })
        let tColAlign = tHeader.map((e, i) => (i === 0 || i >= 2) ? 'right' : 'left')
        let tColSort = tHeader.map((e, i) => (i === 0 || i >= 2) ? 'int' : 'str')

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.attachHeader(tTopHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.setColAlign(tColAlign.join(','));
        ProjectionGrid.setColSorting(tColSort.join(','));
        ProjectionGrid.setColTypes(tColType.join(','));
        ProjectionGrid.setSkin("dhx_skyblue");
        let tFooter = [
            'Total',
            '#cspan',
            '#cspan',
            '#cspan',
            '#stat_total',
            '#stat_total',
            '#stat_total',
            '#stat_total',
            '#stat_total',

        ]
        let tFooterStyle = [
            'text-align:right;font-weight:bold;',
            '',
            '',
            '',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',

        ]
        ProjectionGrid.attachFooter(tFooter.join(','), tFooterStyle);
        // ProjectionGrid.enableAutoWidth(true);
        ProjectionGrid.init();
        ProjectionGrid.attachEvent("onXLE", function() {
            var rowId = ProjectionGrid.getRowId(0);
            if (rowId) {
                ProjectionGrid.setRowTextStyle(rowId, "font-weight:bold;");
                ProjectionGrid.setRowColor(rowId, "#E8F4FD");
                ProjectionGrid.cells(rowId, 0).setValue('');
            }
        });
        ProjectionGrid.clearAndLoad(`cns-inventory-tracking-data?month=${$('#filterMonth').val()}`);
    }


    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();
    }

    function doubleClickMitra() {

    }
</script>
@endsection