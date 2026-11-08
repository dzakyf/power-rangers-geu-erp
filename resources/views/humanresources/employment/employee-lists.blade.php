<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

$GridView = 'employee-lists';
$AllCols = employee_grid_columns();
$DefaultCols = employee_grid_default_columns();
$ActiveCols = $DefaultCols;
if (Schema::hasTable('user_grid_prefs')) {
    $SavedJson = DB::table('user_grid_prefs')->where('username', $Saya)->where('view', $GridView)->value('columns_json');
    $SavedCols = $SavedJson ? json_decode($SavedJson, true) : null;
    if (is_array($SavedCols) && count($SavedCols)) {
        $ValidCols = array_values(array_intersect($SavedCols, array_keys($AllCols)));
        if (count($ValidCols)) {
            $ActiveCols = $ValidCols;
        }
    }
}
$Meta = [];
foreach ($AllCols as $ck => $cm) {
    $Meta[$ck] = ['label' => $cm[0], 'width' => $cm[1], 'align' => $cm[2], 'filter' => $cm[3], 'sort' => $cm[4], 'type' => 'ro'];
}

// Quick stats untuk Card Ringkasan
$totalEmp = DB::table('employment')->count();
$activeEmp = DB::table('employment')->where('Status', 'ACTIVE')->count();
$probationEmp = DB::table('employment')->where('Employment_Status', 'LIKE', '%PROBATION%')->count();
$contractEmp = DB::table('employment')
    ->where(function ($q) {
        $q->where('Employment_Status', 'LIKE', '%KONTRAK%')->orWhere('Employment_Status', 'LIKE', '%PKWT%');
    })
    ->count();
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf

    <style>
        .emp-stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .emp-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .emp-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .emp-card-container {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .emp-toolbar {
            padding: 14px 18px;
            background: #fafbfc;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .emp-col-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: #e2e8f0;
            color: #475569;
        }

        .emp-col-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }

        .emp-col-item:hover {
            background: #edf2f7;
            border-color: #cbd5e1;
        }

        .emp-col-item.checked {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            font-weight: 600;
        }

        .emp-top-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .emp-top-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        /* Fix dhtmlXGrid Header Alignment in Adminor Layout */
        div.gridbox_dhx_skyblue table.hdr {
            border-collapse: separate;
        }

        div.gridbox_dhx_skyblue table.hdr tr td {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            font-weight: 700;
            color: #1e293b;
        }
    </style>

    <!-- Main Content Header (Matched with SPPD Style) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                Data Internal Employees
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="#">Employment</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Data Internal Employees</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('employee.mobile.directory') }}" target="_blank" class="btn btn-outline-primary emp-top-btn shadow-sm">
                <i class="fa fa-mobile-screen-button"></i> Versi Mobile HCGA
            </a>
            <button type="button" class="btn btn-success emp-top-btn shadow-sm text-white" onclick="EmployeeLists.toExcel('./export/generate.php');">
                <i class="fa fa-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- KPI / Metric Highlights Row -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="emp-stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Total
                        Karyawan</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalEmp) }}</h3>
                </div>
                <div class="emp-stat-icon bg-primary bg-opacity-10">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="emp-stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                        Karyawan Aktif</div>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($activeEmp) }}</h3>
                </div>
                <div class="emp-stat-icon bg-success bg-opacity-10">
                    <i class="fa fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="emp-stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
                        Kontrak / PKWT</div>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($contractEmp) }}</h3>
                </div>
                <div class="emp-stat-icon bg-info bg-opacity-10">
                    <i class="fa fa-file-contract"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="emp-stat-card">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Probation</div>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($probationEmp) }}</h3>
                </div>
                <div class="emp-stat-icon bg-warning bg-opacity-10">
                    <i class="fa fa-user-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Employee Card Container -->
    <div class="emp-card-container">
        <!-- Toolbar & Action Bar -->
        <div class="emp-toolbar">
            <div class="d-flex align-items-center gap-2">
                @if (employee_grid_can_manage_columns())
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                        onclick="toggleEmpColPanel()">
                        <i class="fa fa-table-columns me-1"></i> Pengaturan Kolom
                        <span class="emp-col-badge ms-1" id="EmpColCount">{{ count($ActiveCols) }}</span>
                    </button>
                @endif
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    onclick="toggleDivPivotTable();">
                    <i class="fa fa-chart-pie me-1"></i> Analisis Pivot
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-md-inline"><i class="fa fa-mouse-pointer text-primary me-1"></i> Klik
                    2x pada baris untuk
                    membuka rincian detail</span>
            </div>
        </div>

        <!-- Panel Pengaturan Kolom Interaktif -->
        @if (employee_grid_can_manage_columns())
            <div id="EmpColPanel" class="border-bottom p-3" style="display:none; background-color: #ffffff;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-sliders text-primary"></i>
                        <span class="fw-bold text-dark small">Pilih Kolom Yang Ingin
                            Ditampilkan</span>
                        <span class="text-muted small" id="EmpColFound">({{ count($AllCols) }}
                            kolom)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fa fa-search text-muted"></i></span>
                            <input type="text" id="EmpColSearch" class="form-control border-start-0"
                                placeholder="Cari nama kolom..." onkeyup="filterEmpCols()" />
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3"
                            onclick="saveEmployeeCols(false)">
                            <i class="fa fa-save me-1"></i> Simpan Pilihan
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                            onclick="saveEmployeeCols(true)">
                            <i class="fa fa-rotate-left me-1"></i> Reset Default
                        </button>
                    </div>
                </div>

                <span id="EmpColMsg" class="small fw-semibold text-success d-block mb-2"></span>

                <!-- Checkbox Kolom Grid -->
                <div class="row g-2" style="max-height: 280px; overflow-y: auto;">
                    @foreach ($AllCols as $ck => $cm)
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 empColItemContainer">
                            <label
                                class="emp-col-item d-flex align-items-center justify-content-between mb-0 w-100 {{ in_array($ck, $ActiveCols) ? 'checked' : '' }}">
                                <span class="text-truncate me-1">{{ $cm[0] }}</span>
                                <input type="checkbox" class="form-check-input empColCheck mt-0"
                                    value="{{ $ck }}" {{ in_array($ck, $ActiveCols) ? 'checked' : '' }}>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pivot Table Container -->
        <div id="divPivotTable" class="border-bottom p-3" style="display:none; background-color: #fafbfc;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark small"><i class="fa fa-chart-column text-primary me-1"></i> Dynamic Pivot
                    Analytics</span>
                <small class="text-muted">Tarik & letakkan label untuk mengelompokkan data
                    karyawan</small>
            </div>
            <div id="EmployeePivotUI"
                style="overflow:auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px;">
            </div>
        </div>

        <!-- dhtmlXGrid Container -->
        <div class="p-0">
            <div id="EmployeeLists" style="width:100%; height:450px; background-color:#ffffff; overflow:hidden"></div>
        </div>
    </div>

    <!-- Container Detail Ajax View (Loads on double click) -->
    <div id="divlistProjectOrg" class="mt-3"></div>

    <script>
        var EmpColMeta = {!! json_encode($Meta) !!};
        var EmpActiveCols = {!! json_encode(array_values($ActiveCols)) !!};
        var EmpDefaultCols = {!! json_encode(array_values($DefaultCols)) !!};
        var EmployeeLists = null;

        function toggleDivPivotTable() {
            $('#divPivotTable').slideToggle(200);
        }

        function toggleEmpColPanel() {
            $("#EmpColPanel").slideToggle(200);
        }

        function filterEmpCols() {
            var q = $("#EmpColSearch").val().toLowerCase();
            var n = 0;
            $(".empColItemContainer").each(function() {
                var hit = $(this).text().toLowerCase().indexOf(q) !== -1;
                $(this).toggle(hit);
                if (hit) n++;
            });
            $("#EmpColFound").text(q === "" ? ("(" + EmpColTotal + " kolom)") : ("(" + n + " cocok)"));
        }

        function updateEmpColCount() {
            var cnt = $(".empColCheck:checked").length;
            $("#EmpColCount").text(cnt);
            $(".empColCheck").each(function() {
                if ($(this).is(":checked")) {
                    $(this).closest(".emp-col-item").addClass("checked");
                } else {
                    $(this).closest(".emp-col-item").removeClass("checked");
                }
            });
        }

        function EmployeeListsKlik() {
            var rid = EmployeeLists.getSelectedId();
            var nma = EmployeeLists.getUserData(rid, "nama");
            if (rid != '' && rid != null) {
                $('html, body').animate({
                    scrollTop: $("#divlistProjectOrg").offset().top - 80
                }, 300);
                $("#divlistProjectOrg").html(`
                                        div class = "card p-4 text-center border-0 shadow-sm" >
                                        <
                                        div class = "spinner-border text-primary mx-auto mb-2"
                                        role = "status" > < /div> <
                                            span class = "small text-muted" > Memuat rincian detail karyawan... < /span> <
                                            /div>
                                        `);
                LoadM('divlistProjectOrg', 'employee-lists-detail?ID=' + rid + "&nama=" + encodeURI(nma));
            }
            return true;
        }

        function buildEmployeeGrid(keys) {
            document.getElementById('EmployeeLists').innerHTML = '';
            EmployeeLists = new dhtmlXGridObject('EmployeeLists');
            EmployeeLists.setImagePath("./dhtmlx/imgs/");
            var header = ['No.'],
                fh = ['#rspan'],
                w = ['50'],
                al = ['center'],
                ty = ['ro'],
                so = ['int'];
            for (var i = 0; i < keys.length; i++) {
                var m = EmpColMeta[keys[i]];
                if (!m) continue;
                header.push(m.label);
                fh.push(m.filter);
                w.push(m.width);
                al.push(m.align);
                ty.push(m.type);
                so.push(m.sort);
            }
            EmployeeLists.setHeader(header.join(','));
            EmployeeLists.attachHeader(fh.join(','));
            EmployeeLists.setInitWidths(w.join(','));
            EmployeeLists.setColAlign(al.join(','));
            EmployeeLists.setColTypes(ty.join(','));
            EmployeeLists.setColSorting(so.join(','));
            EmployeeLists.attachEvent("onRowDblClicked", EmployeeListsKlik);
            EmployeeLists.setSkin("dhx_skyblue");
            EmployeeLists.init();
            EmployeeLists.clearAndLoad('xml-employee-lists?cols=' + encodeURIComponent(keys.join(',')));
        }

        function saveEmployeeCols(reset) {
            var keys = reset ? EmpDefaultCols.slice() : $(".empColCheck:checked").map(function() {
                return this.value;
            }).get();
            if (!keys.length) {
                alert('Pilih minimal 1 kolom.');
                return;
            }
            $("#EmpColMsg").text('Menyimpan preferensi...');
            $.post('employee-lists-columns-save', {
                _token: $("input[name=_token]").val(),
                cols: keys.join(',')
            }, function(res) {
                if (res && res.status === 'ok') {
                    $(".empColCheck").each(function() {
                        this.checked = (keys.indexOf(this.value) !== -1);
                    });
                    updateEmpColCount();
                    buildEmployeeGrid(keys);
                    loadEmployeePivotUI(keys);
                    $("#EmpColMsg").text('Pilihan kolom berhasil diperbarui.').fadeIn().delay(3000).fadeOut();
                } else {
                    $("#EmpColMsg").text('');
                    alert('Gagal menyimpan: ' + ((res && res.message) ? res.message : 'unknown error'));
                }
            }, 'json').fail(function() {
                $("#EmpColMsg").text('');
                alert('Gagal menyimpan pilihan kolom.');
            });
        }

        var EmpColTotal = {!! count($AllCols) !!};
        $(document).on("change", ".empColCheck", updateEmpColCount);
        updateEmpColCount();
        buildEmployeeGrid(EmpActiveCols);
    </script>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
    <script>
        function pivotAutoFields(data) {
            if (!data || !data.length) return {
                rows: [],
                cols: []
            };
            var attrs = Object.keys(data[0]);
            var cat = [];
            for (var i = 0; i < attrs.length; i++) {
                if (typeof data[0][attrs[i]] === "number") continue;
                var seen = {},
                    distinct = 0;
                for (var r = 0; r < data.length; r++) {
                    var v = data[r][attrs[i]];
                    if (!seen.hasOwnProperty(v)) {
                        seen[v] = 1;
                        distinct++;
                    }
                }
                if (distinct > 1 && distinct < data.length) cat.push(attrs[i]);
            }
            var pool = cat.length ? cat : attrs.slice();
            return {
                rows: pool.length ? [pool[0]] : [],
                cols: pool.length > 1 ? [pool[1]] : []
            };
        }

        function loadEmployeePivotUI(activeKeys) {
            var pcols = (activeKeys && activeKeys.length) ? activeKeys.join(",") : "";
            $("#EmployeePivotUI").html(
                '<div class="p-3 text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Memuat data pivot...</div>'
            );
            $.getJSON('xml-employee-pivot?cols=' + encodeURIComponent(pcols), function(data) {
                $("#EmployeePivotUI").pivotUI(data, {
                    rows: pivotAutoFields(data).rows,
                    cols: pivotAutoFields(data).cols,
                    rendererName: "Table",
                    aggregatorName: "Count",
                    renderers: $.pivotUtilities.renderers
                });
            }).fail(function() {
                $("#EmployeePivotUI").html('<div class="p-3 text-danger small">Gagal memuat data pivot.</div>');
            });
        }
        loadEmployeePivotUI(EmpActiveCols);
    </script>
@endpush
