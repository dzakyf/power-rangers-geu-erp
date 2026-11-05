@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@php
    $category = $category ?? 'ALL';
    if ($category === 'INTERNAL') {
        $title = 'QUALITY CONTROL INTERNAL';
        $sub = 'Internal';
    } elseif ($category === 'EXTERNAL') {
        $title = 'QUALITY CONTROL EXTERNAL';
        $sub = 'External';
    } else {
        $title = 'QUALITY CONTROL';
        $sub = 'All';
    }
@endphp

<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Quality Control</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <span>Quality Control</span>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">{{ $sub }}</span>
        </li>
    </ol>
</div>

<div id="ProjectionSection" class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">{{ $title }}</div>
    <div class="card w-100">
        <div class="card-body">
            <!-- Filter Bar -->
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="filter_qc_category" class="font-weight-bold">Filter Pelaksana Lab:</label>
                    <select id="filter_qc_category" class="form-control" onchange="loadQualityGrid()">
                        <option value="ALL" {{ $category === 'ALL' ? 'selected' : '' }}>-- Semua Pelaksana Lab --</option>
                        <option value="INTERNAL" {{ $category === 'INTERNAL' ? 'selected' : '' }}>Lab Internal</option>
                        <option value="EXTERNAL" {{ $category === 'EXTERNAL' ? 'selected' : '' }}>Lab External</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_qc_type" class="font-weight-bold">Filter Jenis QC:</label>
                    <select id="filter_qc_type" class="form-control" onchange="loadQualityGrid()">
                        <option value="ALL">-- Semua Jenis QC --</option>
                        <option value="INBOUND">INBOUND</option>
                        <option value="OUTBOUND">OUTBOUND</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_date" class="font-weight-bold">Filter Tanggal:</label>
                    <input type="date" id="filter_date" class="form-control" onchange="loadQualityGrid()" />
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary" onclick="$('#filter_qc_category').val('{{ $category }}'); $('#filter_qc_type').val('ALL'); $('#filter_date').val(''); loadQualityGrid();">Reset Filter</button>
                </div>
            </div>

            <div id="ProjectionGrid" style="width:auto;height:340px; background-color:white;overflow:hidden" class="mt-2"></div>
            <div class="mt-2">
                <input type="button" id="CreateQC" name="CreateQC" value="Create" class="btn btn-info mr-2" onclick="createQC()" />
                <input type="button" id="DetailQC" name="DetailQC" value="Detail" class="btn btn-info mr-2" onclick="detailQC()" />
                <input type="button" id="DeleteQC" name="DeleteQC" value="Delete" class="btn btn-danger mr-2" onclick="deleteQC()" />
                <input type="button" id="PrintQC" name="PrintQC" value="Print" class="btn btn-secondary mr-2" onclick="printQC()" />
            </div>
        </div>
    </div>
</div>

@include('cns.quality.create', ['category' => $category, 'contracts' => $contracts])
@include('cns.quality.edit', ['category' => $category, 'contracts' => $contracts])

<script>
    var currentCategory = "{{ $category }}";
    var ProjectionGrid = null;

    $(document).ready(function() {
        initProjectionGrid();
    });

    function loadQualityGrid() {
        if (!ProjectionGrid) return;
        var category = $('#filter_qc_category').val() || 'ALL';
        var type = $('#filter_qc_type').val() || 'ALL';
        var date = $('#filter_date').val() || '';
        var url = 'cns-quality-data?category=' + encodeURIComponent(category) +
                  '&type=' + encodeURIComponent(type) +
                  '&Date=' + encodeURIComponent(date);
        ProjectionGrid.clearAndLoad(url);
    }

    function createQC() {
        $('#FormQualityControl').show();
        $('#FormEditQualityControl').hide();
        $('#create_QC_Type').val('INBOUND').trigger('change');
        var cat = $('#filter_qc_category').val();
        if (cat && cat !== 'ALL') {
            $('#create_QC_Category').val(cat);
        }
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

    function printQC() {
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        var kodeQc = ProjectionGrid.cells(rowId, 1).getValue();
        var tanggal = ProjectionGrid.cells(rowId, 2).getValue();
        var qcType = ProjectionGrid.cells(rowId, 3).getValue();
        var qcCat = ProjectionGrid.cells(rowId, 4).getValue() || currentCategory;
        var date = new Date(tanggal);
        var formatted = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        window.location.href = 'cns-quality-print?date=' + formatted +
                                '&code=' + encodeURIComponent(kodeQc) +
                                '&qc_type=' + encodeURIComponent(qcType) +
                                '&category=' + encodeURIComponent(qcCat);
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

        var kodeQc = ProjectionGrid.cells(rowId, 1).getValue();
        var tanggal = ProjectionGrid.cells(rowId, 2).getValue();
        var qcType = ProjectionGrid.cells(rowId, 3).getValue();
        var qcCat = ProjectionGrid.cells(rowId, 4).getValue() || currentCategory;
        var date = new Date(tanggal);
        var formatted = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        $.ajax({
            url: 'cns-quality-delete',
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                Kode_QC: kodeQc,
                Date: formatted,
                QC_Type: qcType,
                QC_Category: qcCat
            },
            success: function(res) {
                if (res.status === 'success') {
                    loadQualityGrid();
                }
            },
            error: function() {
                alert('Gagal menghapus data');
            }
        });
    }

    function initProjectionGrid() {
        $('#ProjectionSection').show();

        // 38 visible columns:
        // 0: No, 1: Kode QC, 2: Tanggal, 3: Jenis QC, 4: Lab
        // 5..12: Top (8 cols)
        // 13..20: Middle (8 cols)
        // 21..28: Bottom (8 cols)
        // 29..36: Average (8 cols)
        // 37: Attachment
        let tHeader = [
            'No', 'Kode QC', 'Tanggal', 'Jenis QC', 'Lab',
            'Top', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Middle', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Bottom', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Average', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Attachment'
        ];

        let tGroupHeader = [
            '#rspan', '#text_filter', '#text_filter', '#select_filter', '#select_filter',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            '#rspan'
        ];

        let tTopHeader = [
            '#rspan', '#rspan', '#rspan', '#rspan', '#rspan',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '#rspan'
        ];

        let tWidth = [
            '45', '140', '95', '85', '85',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '110'
        ];

        let tColType = tHeader.map(() => 'ro');
        let tColAlign = tHeader.map((e, i) => (i < 5 ? 'left' : (i === 37 ? 'center' : 'right')));
        let tColSort = tHeader.map((e, i) => (i === 0 ? 'int' : (i === 2 ? 'date' : 'str')));

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.attachHeader(tGroupHeader.join(','));
        ProjectionGrid.attachHeader(tTopHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.setColAlign(tColAlign.join(','));
        ProjectionGrid.setColSorting(tColSort.join(','));
        ProjectionGrid.setColTypes(tColType.join(','));
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();

        loadQualityGrid();

        ProjectionGrid.attachEvent('onRowDblClicked', function(rId, cInd) {
            if (cInd === 37) {
                var url = ProjectionGrid.cells(rId, cInd).getValue();
                if (url && url !== '-' && url !== '') {
                    window.open(url, '_blank');
                }
            }
        });
    }
</script>
@endsection
