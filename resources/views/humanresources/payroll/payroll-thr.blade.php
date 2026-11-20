@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf

    <style>
        .thr-kpi-card {
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .thr-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
        }
        .thr-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .thr-form-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            margin-bottom: 0.35rem;
        }
    </style>

    <!-- Main Content Header -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-gift me-2"></i>Perhitungan THR (Tunjangan Hari Raya)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('payroll-generator') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                            Payroll
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">
                        Perhitungan THR
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.6); color: #ffffff !important;">
                <i class="fe fe-cpu me-1"></i> Payroll Generator
            </a>
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Tax &amp; TER
            </a>
            <a href="{{ url('payroll-employee') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Setup Gaji Pegawai
            </a>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="fe fe-info fs-5 text-info"></i>
        <div class="small">
            <strong>Integrasi Payslip Bulan Berjalan:</strong> Batch THR yang berstatus <span class="badge bg-success text-white">LOCKED</span> akan otomatis ditarik ke <strong>Payroll Generator</strong> pada periode payslip terkait sebagai komponen Tunjangan/Bonus kena PPh 21 (TER PP 58/2023) tanpa menaikkan basis potongan BPJS.
        </div>
    </div>

    <!-- Form Generate Draft THR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="fe fe-sliders me-2 text-primary"></i>Parameter Generate Draft THR</h6>
        </div>
        <div class="card-body">
            <form id="thrGenerateForm" class="row g-3">
                <div class="col-md-2">
                    <label class="thr-form-label">Periode Payslip</label>
                    <input type="month" name="salary_period" id="thr_salary_period" class="form-control" value="{{ $defaultPeriod }}" required>
                </div>
                <div class="col-md-3">
                    <label class="thr-form-label">Nama Event THR</label>
                    <input type="text" name="thr_event_name" id="thr_event_name" class="form-control" placeholder="Misal: THR Idul Fitri 1447H" value="THR Tahun {{ date('Y') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="thr-form-label">Tgl Acuan Hari Raya</label>
                    <input type="date" name="reference_date" id="thr_reference_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <input type="hidden" name="religion_filter" id="thr_religion_filter" value="ALL">
                <div class="col-md-5">
                    <label class="thr-form-label">Salary Group (Opsional)</label>
                    <select name="main_id_filter" id="thr_main_id_filter" class="form-select">
                        <option value="">-- Semua Salary Group --</option>
                        @foreach ($mainGroups as $mg)
                            <option value="{{ $mg->Main_ID }}">{{ $mg->Main_ID }} - {{ $mg->Group_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="thr-form-label">Skema Perhitungan THR</label>
                    <select name="prorate_method" id="thr_prorate_method" class="form-select">
                        <option value="FOLLOW_EMPLOYEE" selected>Ikuti Settingan Employee (Employee Lists)</option>
                        <option value="DEFAULT_PERMENAKER">Default (Standar Permenaker)</option>
                        @foreach ($thrSchemes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" id="thrFallbackSchemeWrapper">
                    <label class="thr-form-label">Jika Employee Belum Disetting</label>
                    <select name="fallback_thr_scheme" id="thr_fallback_scheme" class="form-select">
                        <option value="DEFAULT_PERMENAKER" selected>Default (Standar Permenaker)</option>
                        @foreach ($thrSchemes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="thr-form-label">Catatan Batch (Opsional)</label>
                    <input type="text" name="note" id="thr_note" class="form-control" placeholder="Catatan internal HCGA...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" id="btnGenerateThr" class="btn btn-primary w-100 fw-bold rounded-3">
                        <i class="fe fe-zap me-1"></i> Hitung &amp; Buat Draft THR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Selector & KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body">
                    <label class="thr-form-label">Pilih Batch THR</label>
                    <select id="activeBatchSelect" class="form-select mb-2">
                        <option value="">-- Pilih Batch THR --</option>
                        @foreach ($batches as $b)
                            <option value="{{ $b->id }}"
                                data-status="{{ $b->status }}"
                                data-period="{{ $b->salary_period }}"
                                data-emp="{{ $b->total_employees }}"
                                data-amt="{{ $b->total_amount }}">
                                {{ $b->batch_no }} | {{ $b->salary_period }} | {{ $b->thr_event_name }} [{{ $b->status }}]
                            </option>
                        @endforeach
                    </select>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="small text-muted">Status Batch:</span>
                        <span id="batchStatusBadge" class="badge bg-secondary text-white">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-primary text-white">
                        <i class="fe fe-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Karyawan Eligible</div>
                        <div id="kpiTotalEmp" class="fs-4 fw-bold text-dark">0</div>
                        <div id="kpiPeriodLabel" class="small text-muted">Periode: -</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card thr-kpi-card h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="thr-kpi-icon bg-success text-white">
                        <i class="fe fe-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Estimasi THR</div>
                        <div id="kpiTotalAmount" class="fs-4 fw-bold text-success">Rp 0</div>
                        <div class="small text-muted">Basis: Sesuai Skema THR Karyawan (Permenaker / PKWT / MoU)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Rincian Karyawan THR -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark"><i class="fe fe-list me-2 text-primary"></i>Rincian Perhitungan THR Karyawan</h6>
                <small class="text-muted">Pada status <strong>DRAFT</strong>, Anda dapat mencentang/hapus centang kolom <em>Eligible</em>, mengubah <em>Adjustment (+/-)</em>, dan mengedit <em>Keterangan</em> langsung di tabel.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="btnLockBatch" class="btn btn-success btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fe fe-lock me-1"></i> Lock &amp; Gabung ke Payroll
                </button>
                <button type="button" id="btnUnlockBatch" class="btn btn-warning btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fe fe-unlock me-1"></i> Unlock ke Draft
                </button>
                <button type="button" id="btnDeleteBatch" class="btn btn-outline-danger btn-sm fw-bold rounded-pill px-3" disabled>
                    <i class="fe fe-trash-2 me-1"></i> Hapus Draft
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="thrGridBox" style="width: 100%; height: 480px;"></div>
        </div>
    </div>

    <script>
        (function () {
            var thrGrid = null;
            var currentBatchId = 0;
            var currentBatchStatus = '';

            function formatRp(val) {
                var num = parseFloat(val) || 0;
                return 'Rp ' + Math.round(num).toLocaleString('id-ID');
            }

            function getCsrfToken() {
                var el = document.querySelector('input[name="_token"]');
                return el ? el.value : '';
            }

            var thrSchemes = @json($thrSchemes);

            function initThrGrid() {
                thrGrid = new dhtmlXGridObject('thrGridBox');
                thrGrid.setImagePath("{{ asset('dhtmlx/skins/skyblue/imgs/') }}/");
                thrGrid.setHeader("Eligible,NIK,Nama Karyawan,Department,Agama,Tgl Masuk,Masa Kerja,Skema THR (PKWT/MoU),Prorata,Basis Upah,THR Sistem,Adjustment (+/-),THR Final,Keterangan");
                thrGrid.attachHeader("#master_checkbox,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter");
                thrGrid.setInitWidths("65,105,190,120,90,95,125,210,75,115,115,115,120,300");
                thrGrid.setColAlign("center,left,left,left,center,center,center,left,right,right,right,right,right,left");
                thrGrid.setColTypes("ch,ro,ro,ro,ro,ro,ro,coro,ro,ron,ron,edn,ron,ed");
                thrGrid.setColSorting("int,str,str,str,str,str,str,str,str,int,int,int,int,str");
                thrGrid.setNumberFormat("0,000", 9, ".", ",");
                thrGrid.setNumberFormat("0,000", 10, ".", ",");
                thrGrid.setNumberFormat("0,000", 11, ".", ",");
                thrGrid.setNumberFormat("0,000", 12, ".", ",");

                var schemeCombo = thrGrid.getCombo(7);
                schemeCombo.put("DEFAULT_PERMENAKER", "Default (Standar Permenaker)");
                Object.keys(thrSchemes || {}).forEach(function (k) {
                    schemeCombo.put(k, thrSchemes[k]);
                });

                thrGrid.setSkin("dhx_skyblue");
                thrGrid.init();

                thrGrid.attachEvent("onCheck", function (rId, cInd, state) {
                    if (cInd === 0) {
                        saveRowItem(rId, { is_eligible: state ? 1 : 0 });
                    }
                });

                thrGrid.attachEvent("onEditCell", function (stage, rId, cInd, nValue, oValue) {
                    if (currentBatchStatus !== 'DRAFT') {
                        return false;
                    }
                    if (stage === 2 && nValue != oValue) {
                        if (cInd === 7) {
                            saveRowItem(rId, { thr_scheme: nValue });
                        } else if (cInd === 11) {
                            saveRowItem(rId, { thr_adjustment_amount: parseFloat(nValue) || 0 });
                        } else if (cInd === 13) {
                            saveRowItem(rId, { note: nValue });
                        }
                    }
                    return true;
                });
            }

            function saveRowItem(itemId, payload) {
                if (currentBatchStatus !== 'DRAFT') {
                    alert('Hanya batch berstatus DRAFT yang dapat diubah.');
                    loadBatchItems(currentBatchId);
                    return;
                }
                payload.item_id = itemId;
                payload._token = getCsrfToken();

                fetch("{{ url('/api/payroll/thr/item/update') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify(payload)
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.success) {
                        alert(res.message || 'Gagal memperbarui item.');
                        loadBatchItems(currentBatchId);
                        return;
                    }
                    if (res.data) {
                        if (typeof res.data.is_eligible !== 'undefined') {
                            thrGrid.cells(itemId, 0).setValue(res.data.is_eligible ? 1 : 0);
                        }
                        if (typeof res.data.prorate_ratio !== 'undefined') {
                            var pct = Math.round(parseFloat(res.data.prorate_ratio) * 10000) / 100;
                            thrGrid.cells(itemId, 8).setValue(pct + '%');
                        }
                        if (typeof res.data.base_salary_amount !== 'undefined') {
                            thrGrid.cells(itemId, 9).setValue(res.data.base_salary_amount);
                        }
                        if (typeof res.data.thr_calculated_amount !== 'undefined') {
                            thrGrid.cells(itemId, 10).setValue(res.data.thr_calculated_amount);
                        }
                        thrGrid.cells(itemId, 12).setValue(res.data.thr_final_amount);
                        updateKpi(res.data.total_employees, res.data.total_amount);
                    }
                })
                .catch(function () {
                    alert('Terjadi kesalahan jaringan saat menyimpan item.');
                });
            }

            function updateKpi(empCount, totalAmt) {
                document.getElementById('kpiTotalEmp').textContent = empCount || 0;
                document.getElementById('kpiTotalAmount').textContent = formatRp(totalAmt || 0);
            }

            function updateActionButtons(status) {
                currentBatchStatus = status || '';
                var badge = document.getElementById('batchStatusBadge');
                badge.textContent = status || '-';
                badge.className = 'badge text-white ' + (
                    status === 'LOCKED' ? 'bg-success' :
                    status === 'POSTED' ? 'bg-primary' :
                    status === 'DRAFT' ? 'bg-warning text-dark' : 'bg-secondary'
                );

                document.getElementById('btnLockBatch').disabled = (status !== 'DRAFT');
                document.getElementById('btnUnlockBatch').disabled = (status !== 'LOCKED');
                document.getElementById('btnDeleteBatch').disabled = (status !== 'DRAFT');
            }

            function loadBatchItems(batchId) {
                currentBatchId = parseInt(batchId, 10) || 0;
                var sel = document.getElementById('activeBatchSelect');
                var opt = sel.options[sel.selectedIndex];

                if (currentBatchId <= 0 || !opt) {
                    updateActionButtons('');
                    updateKpi(0, 0);
                    document.getElementById('kpiPeriodLabel').textContent = 'Periode: -';
                    if (thrGrid) thrGrid.clearAll();
                    return;
                }

                updateActionButtons(opt.getAttribute('data-status'));
                updateKpi(opt.getAttribute('data-emp'), opt.getAttribute('data-amt'));
                document.getElementById('kpiPeriodLabel').textContent = 'Periode: ' + (opt.getAttribute('data-period') || '-');

                thrGrid.clearAndLoad("{{ url('/xml-payroll-thr-items') }}?batch_id=" + currentBatchId);
            }

            function refreshBatches(selectId) {
                fetch("{{ url('/api/payroll/thr/batches') }}", { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res.success) return;
                        var sel = document.getElementById('activeBatchSelect');
                        sel.innerHTML = '<option value="">-- Pilih Batch THR --</option>';
                        (res.data || []).forEach(function (b) {
                            var o = document.createElement('option');
                            o.value = b.id;
                            o.textContent = b.batch_no + ' | ' + b.salary_period + ' | ' + b.thr_event_name + ' [' + b.status + ']';
                            o.setAttribute('data-status', b.status);
                            o.setAttribute('data-period', b.salary_period);
                            o.setAttribute('data-emp', b.total_employees);
                            o.setAttribute('data-amt', b.total_amount);
                            if (selectId && parseInt(selectId, 10) === parseInt(b.id, 10)) {
                                o.selected = true;
                            }
                            sel.appendChild(o);
                        });
                        if (selectId) {
                            loadBatchItems(selectId);
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', function () {
                initThrGrid();

                var schemeModeSelect = document.getElementById('thr_prorate_method');
                var fallbackWrapper = document.getElementById('thrFallbackSchemeWrapper');
                var fallbackSelect = document.getElementById('thr_fallback_scheme');
                function toggleFallbackScheme() {
                    var isFollow = (schemeModeSelect && schemeModeSelect.value === 'FOLLOW_EMPLOYEE');
                    if (fallbackWrapper) {
                        fallbackWrapper.style.display = isFollow ? '' : 'none';
                    }
                    if (fallbackSelect) {
                        fallbackSelect.disabled = !isFollow;
                    }
                }
                if (schemeModeSelect) {
                    schemeModeSelect.addEventListener('change', toggleFallbackScheme);
                    toggleFallbackScheme();
                }

                var sel = document.getElementById('activeBatchSelect');
                sel.addEventListener('change', function () {
                    loadBatchItems(this.value);
                });

                if (sel.options.length > 1) {
                    sel.selectedIndex = 1;
                    loadBatchItems(sel.value);
                }

                document.getElementById('thrGenerateForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    var btn = document.getElementById('btnGenerateThr');
                    btn.disabled = true;
                    var fd = new FormData(this);
                    fd.append('_token', getCsrfToken());

                    fetch("{{ url('/api/payroll/thr/generate') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: fd
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        btn.disabled = false;
                        alert(res.message || (res.success ? 'Berhasil' : 'Gagal'));
                        if (res.success && res.data) {
                            refreshBatches(res.data.batch_id);
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        alert('Terjadi kesalahan saat menghitung draft THR.');
                    });
                });

                document.getElementById('btnLockBatch').addEventListener('click', function () {
                    if (!currentBatchId || !confirm('Lock batch THR ini agar otomatis masuk ke Payroll Generator bulan berjalan?')) return;
                    fetch("{{ url('/api/payroll/thr/lock') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: JSON.stringify({ batch_id: currentBatchId, _token: getCsrfToken() })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        alert(res.message);
                        if (res.success) refreshBatches(currentBatchId);
                    });
                });

                document.getElementById('btnUnlockBatch').addEventListener('click', function () {
                    if (!currentBatchId || !confirm('Kembalikan batch THR ke status DRAFT?')) return;
                    fetch("{{ url('/api/payroll/thr/unlock') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: JSON.stringify({ batch_id: currentBatchId, _token: getCsrfToken() })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        alert(res.message);
                        if (res.success) refreshBatches(currentBatchId);
                    });
                });

                document.getElementById('btnDeleteBatch').addEventListener('click', function () {
                    if (!currentBatchId || !confirm('Hapus draft batch THR ini?')) return;
                    fetch("{{ url('/api/payroll/thr/delete') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: JSON.stringify({ batch_id: currentBatchId, _token: getCsrfToken() })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        alert(res.message);
                        if (res.success) {
                            refreshBatches(null);
                            loadBatchItems(0);
                        }
                    });
                });
            });
        })();
    </script>
@endsection
