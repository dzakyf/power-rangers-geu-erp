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
                        <option value="DAILY_QC">DAILY QC</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_date" class="font-weight-bold">Filter Tanggal:</label>
                    <input type="date" id="filter_date" class="form-control" onchange="loadQualityGrid()" />
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-secondary" onclick="$('#filter_qc_category').val('{{ $category }}'); $('#filter_qc_type').val('ALL'); $('#filter_date').val(''); $('#filter_stat_month').val('{{ date('Y-m') }}'); loadQualityGrid();">Reset Filter</button>
                </div>
            </div>

            <!-- Monthly Min/Max Parameter Summary Table (Collapsible) -->
            <div class="card mb-3 border shadow-sm">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center py-2" style="cursor: pointer;" onclick="toggleMonthlyStats()">
                    <div class="d-flex align-items-center">
                        <strong class="text-dark">
                            <i class="fa fa-chart-line text-primary mr-1"></i> Nilai Minimum & Maksimum Parameter (1 Bulan)
                        </strong>
                        <span class="badge badge-info ml-2" id="qcMonthlyTotalBadge">0 Data Pengujian</span>
                    </div>
                    <div class="d-flex align-items-center mt-1 mt-md-0" onclick="event.stopPropagation();">
                        <label for="filter_stat_month" class="mb-0 mr-2 font-weight-bold text-secondary" style="font-size: 0.85rem;">Periode:</label>
                        @php
                            $curMonthVal = date('Y-m');
                            $monthNames = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                        @endphp
                        <select id="filter_stat_month" class="form-control form-control-sm mr-1" style="width: 170px;" onchange="loadMonthlyStats()">
                            @for ($i = 3; $i >= -24; $i--)
                                @php
                                    $dt = \Carbon\Carbon::now()->addMonths($i);
                                    $val = $dt->format('Y-m');
                                    $label = $monthNames[$dt->format('m')] . ' ' . $dt->format('Y');
                                @endphp
                                <option value="{{ $val }}" {{ $val === $curMonthVal ? 'selected' : '' }}>{{ $label }}</option>
                            @endfor
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary ml-1" onclick="toggleMonthlyStats()" title="Buka/Tutup Ringkasan">
                            <i id="qcStatsToggleIcon" class="fa fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div id="qcMonthlyStatsCollapse" style="display: none;">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped mb-0 text-center" style="font-size: 0.88rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 35%; text-align: left;">Parameter</th>
                                                <th style="width: 20%; color: #000;">Satuan</th>
                                                <th style="width: 22%; color: #28a745;">Min</th>
                                                <th style="width: 23%; color: #dc3545;">Max</th>
                                            </tr>
                                        </thead>
                                        <tbody id="qcMonthlyStatsBodyLeft">
                                            <tr><td colspan="4" class="text-muted text-center">Memuat data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped mb-0 text-center" style="font-size: 0.88rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 35%; text-align: left;">Parameter</th>
                                                <th style="width: 20%; color: #000;">Satuan</th>
                                                <th style="width: 22%; color: #28a745;">Min</th>
                                                <th style="width: 23%; color: #dc3545;">Max</th>
                                            </tr>
                                        </thead>
                                        <tbody id="qcMonthlyStatsBodyRight">
                                            <tr><td colspan="4" class="text-muted text-center">Memuat data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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

@include('cns.quality.create', ['category' => $category, 'contracts' => $contracts, 'tanks' => $tanks ?? collect()])
@include('cns.quality.edit', ['category' => $category, 'contracts' => $contracts, 'tanks' => $tanks ?? collect()])

<script>
    var currentCategory = "{{ $category }}";
    var ProjectionGrid = null;

    $(document).ready(function() {
        initProjectionGrid();
    });

    function loadQualityGrid() {
        if (ProjectionGrid) {
            var category = $('#filter_qc_category').val() || 'ALL';
            var type = $('#filter_qc_type').val() || 'ALL';
            var date = $('#filter_date').val() || '';
            var url = 'cns-quality-data?category=' + encodeURIComponent(category) +
                      '&type=' + encodeURIComponent(type) +
                      '&Date=' + encodeURIComponent(date);
            ProjectionGrid.clearAndLoad(url);
        }
        loadMonthlyStats();
    }

    function loadMonthlyStats() {
        var category = $('#filter_qc_category').val() || currentCategory || 'ALL';
        var type = $('#filter_qc_type').val() || 'ALL';
        var month = $('#filter_stat_month').val() || '{{ date('Y-m') }}';

        $.ajax({
            url: 'cns-quality-monthly-stats',
            type: 'GET',
            data: {
                category: category,
                type: type,
                month: month
            },
            success: function(res) {
                if (res.status === 'success') {
                    renderMonthlyStats(res);
                }
            },
            error: function() {
                console.error('Gagal memuat statistik bulanan QC');
            }
        });
    }

    function renderMonthlyStats(res) {
        var count = res.total_tests || 0;
        $('#qcMonthlyTotalBadge').text(count + ' Data Pengujian');

        var leftTbody = $('#qcMonthlyStatsBodyLeft');
        var rightTbody = $('#qcMonthlyStatsBodyRight');
        leftTbody.empty();
        rightTbody.empty();

        var params = res.parameters || [];
        params.forEach(function(p, idx) {
            var minVal = (p.min !== null && p.min !== undefined) ? Number(p.min).toFixed(2) : '-';
            var maxVal = (p.max !== null && p.max !== undefined) ? Number(p.max).toFixed(2) : '-';
            var rowHtml = '<tr>' +
                '<td class="text-left font-weight-bold">' + p.name + '</td>' +
                '<td style="color: #000; font-weight: 600;">' + p.unit + '</td>' +
                '<td class="text-right text-success font-weight-bold">' + minVal + '</td>' +
                '<td class="text-right text-danger font-weight-bold">' + maxVal + '</td>' +
                '</tr>';
            if (idx < 4) {
                leftTbody.append(rowHtml);
            } else {
                rightTbody.append(rowHtml);
            }
        });
    }

    function toggleMonthlyStats() {
        $('#qcMonthlyStatsCollapse').slideToggle(200, function() {
            var isVisible = $(this).is(':visible');
            $('#qcStatsToggleIcon').attr('class', isVisible ? 'fa fa-chevron-up' : 'fa fa-chevron-down');
        });
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

        // 42 columns (38 visible + 4 hidden):
        // 0: No, 1: Kode QC, 2: Tanggal, 3: Jenis QC, 4: Lab
        // 5..12: Top (8 cols)
        // 13..20: Middle (8 cols)
        // 21..28: Bottom (8 cols)
        // 29..36: Average (8 cols)
        // 37: Attachment
        // 38: Contract_ID (hidden), 39: Booking_ID (hidden), 40: QC_Category (hidden), 41: Tank_ID (hidden)
        let tHeader = [
            'No', 'Kode QC', 'Tanggal', 'Jenis QC', 'Lab', 'Source',
            'Top', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Middle', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Bottom', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Average', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan',
            'Attachment',
            'Contract_ID', 'Booking_ID', 'QC_Category', 'Tank_ID'
        ];

        let tGroupHeader = [
            '#rspan', '#text_filter', '#text_filter', '#select_filter', '#select_filter', '#select_filter',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals',
            '#rspan',
            '#rspan', '#rspan', '#rspan', '#rspan'
        ];

        let tTopHeader = [
            '#rspan', '#rspan', '#rspan', '#rspan', '#rspan', '#rspan',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '%', '%', '%', 'ppm', 'g I2', 'ppm', 'ppm', 'ppm',
            '#rspan',
            '#rspan', '#rspan', '#rspan', '#rspan'
        ];

        let tWidth = [
            '45', '140', '95', '85', '85', '85',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '85', '85', '75', '75', '75', '75', '80', '80',
            '110',
            '50', '50', '50', '50'
        ];

        let tColType = tHeader.map(() => 'ro');
        let tColAlign = tHeader.map((e, i) => (i < 6 ? 'center' : (i === 37 ? 'center' : (i >= 38 ? 'left' : 'right'))));
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

        ProjectionGrid.setColumnHidden(38, true);
        ProjectionGrid.setColumnHidden(39, true);
        ProjectionGrid.setColumnHidden(40, true);
        ProjectionGrid.setColumnHidden(41, true);

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
