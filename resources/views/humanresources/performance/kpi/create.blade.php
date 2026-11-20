@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => 'Buat Sasaran KPI / OKR Baru',
    'parentLabel' => 'KPI & OKR',
    'parentUrl'   => url('hrd-performance-kpi-index'),
    'current'     => 'Buat Sasaran Baru',
    'icon'        => 'fa-bullseye',
    'badgeText'   => 'Target Kerja Baru',
])

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Form Penetapan Sasaran Kerja (KPI / OKR)</h5>
    </div>
    <div class="card-body">
        <form id="formGoalCreate">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Periode Penilaian <span class="text-danger">*</span></label>
                    <select name="period_id" id="period_id" class="form-select form-control" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->status === 'ACTIVE' ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->period_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Kategori KPI</label>
                    <select name="goal_category" id="goal_category" class="form-select form-control" onchange="toggleGoalScope()">
                        <option value="REGULAR" selected>Reguler (Tahunan / Semester)</option>
                        <option value="PROBATION">Khusus Masa Probation (3/6/12 Bln)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Target Penerima KPI</label>
                    <select id="scope_mode" class="form-select form-control" onchange="toggleGoalScope()">
                        <option value="POSITION" selected>Template Posisi / Jabatan</option>
                        <option value="INDIVIDUAL_ADDON">KPI Tambahan Karyawan (Add-on)</option>
                        <option value="INDIVIDUAL_FULL">KPI Khusus Individu</option>
                    </select>
                    <input type="hidden" name="is_addon" id="is_addon" value="0">
                </div>
                <div class="col-md-3" id="wrapPositionSelect">
                    <label class="form-label font-weight-bold">Pilih Posisi / Jabatan <span class="text-danger">*</span></label>
                    <select name="position_id" id="position_id" class="form-select form-control">
                        <option value="">-- Pilih Posisi di Departemen --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->ID }}">[{{ $pos->Department }}] {{ $pos->Position }} ({{ $pos->Initial }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-none" id="wrapEmployeeSelect">
                    <label class="form-label font-weight-bold">Pilih Karyawan <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select form-control">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees ?? [] as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->Name }} ({{ $emp->Emp_ID }} - {{ $emp->Position }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-none" id="wrapProbationMonths">
                    <div class="row g-2 p-2 bg-light border rounded">
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Preset Waktu Penilaian</label>
                            <select id="probation_preset" class="form-select form-control" onchange="applyCreateTimePreset()">
                                <option value="1w">1 Minggu (7 Hari)</option>
                                <option value="2w">2 Minggu (14 Hari)</option>
                                <option value="3w">3 Minggu (21 Hari)</option>
                                <option value="1m">1 Bulan</option>
                                <option value="2m">2 Bulan</option>
                                <option value="3m" selected>3 Bulan</option>
                                <option value="6m">6 Bulan</option>
                                <option value="12m">12 Bulan (1 Tahun)</option>
                                <option value="custom">Custom Tanggal Bebas</option>
                            </select>
                            <input type="hidden" name="probation_months" id="probation_months" value="3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Tanggal Mulai Penilaian</label>
                            <input type="date" name="probation_start_date" id="probation_start_date" class="form-control" value="{{ date('Y-m-d') }}" onchange="applyCreateTimePreset()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Tanggal Berakhir / Evaluasi</label>
                            <input type="date" name="probation_end_date" id="probation_end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 months')) }}" onchange="document.getElementById('probation_preset').value='custom';">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Tipe Sasaran</label>
                    <select name="goal_type" id="goal_type" class="form-select form-control" onchange="toggleGoalType()">
                        <option value="HYBRID" selected>Hybrid (KPI & OKR)</option>
                        <option value="KPI">KPI Only</option>
                        <option value="OKR">OKR Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Level Target</label>
                    <select name="level" id="level" class="form-select form-control">
                        <option value="INDIVIDUAL" selected>Individual</option>
                        <option value="TEAM">Team / Dept</option>
                        <option value="COMPANY">Company</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan arahan atau kesepakatan target..."></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="font-weight-bold mb-0">Daftar Indikator / Target Kerja</h6>
                <div>
                    <span id="badgeTotalWeight" class="badge bg-secondary me-2 p-2">Total Bobot KPI: 0%</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="addRowItem()">
                        <i class="feather-plus me-1"></i> Tambah Item Target
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tableGoalItems">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Tipe</th>
                            <th>Judul Target / Indikator</th>
                            <th style="width: 100px;">Satuan</th>
                            <th style="width: 120px;">Target</th>
                            <th style="width: 110px;">Bobot (%)</th>
                            <th style="width: 140px;">Metode Skor</th>
                            <th style="width: 140px;">Jatuh Tempo</th>
                            <th style="width: 60px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <!-- Item row akan dirender via JS -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ url('hrd-performance-kpi-index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="button" class="btn btn-primary" onclick="submitForm()">
                    <i class="feather-save me-1"></i> Simpan Sebagai Draft
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var itemIndex = 0;

    document.addEventListener("DOMContentLoaded", function () {
        var params = new URLSearchParams(window.location.search);
        if (params.get('goal_category') === 'PROBATION') {
            document.getElementById('goal_category').value = 'PROBATION';
        }
        if (params.get('user_id')) {
            document.getElementById('scope_mode').value = 'INDIVIDUAL_FULL';
            document.getElementById('user_id').value = params.get('user_id');
        }
        toggleGoalScope();
        addRowItem('KPI');
        addRowItem('KPI');
    });

    function toggleGoalScope() {
        var mode = document.getElementById('scope_mode').value;
        var cat = document.getElementById('goal_category').value;
        var wrapPos = document.getElementById('wrapPositionSelect');
        var wrapEmp = document.getElementById('wrapEmployeeSelect');
        var wrapProb = document.getElementById('wrapProbationMonths');

        if (mode === 'POSITION') {
            wrapPos.classList.remove('d-none');
            wrapEmp.classList.add('d-none');
            document.getElementById('user_id').value = '';
            document.getElementById('is_addon').value = '0';
        } else {
            wrapPos.classList.add('d-none');
            wrapEmp.classList.remove('d-none');
            document.getElementById('position_id').value = '';
            document.getElementById('is_addon').value = (mode === 'INDIVIDUAL_ADDON') ? '1' : '0';
        }

        if (cat === 'PROBATION') {
            wrapProb.classList.remove('d-none');
        } else {
            wrapProb.classList.add('d-none');
        }
    }

    function applyCreateTimePreset() {
        var preset = document.getElementById('probation_preset').value;
        var startVal = document.getElementById('probation_start_date').value;
        var hiddenMonths = document.getElementById('probation_months');
        if (preset === 'custom') {
            hiddenMonths.value = '';
            return;
        }
        if (!startVal) return;
        var d = new Date(startVal + 'T00:00:00');
        if (isNaN(d.getTime())) return;
        if (preset.endsWith('w')) {
            var weeks = parseInt(preset, 10);
            d.setDate(d.getDate() + (weeks * 7));
            hiddenMonths.value = '';
        } else if (preset.endsWith('m')) {
            var months = parseInt(preset, 10);
            d.setMonth(d.getMonth() + months);
            hiddenMonths.value = months;
        }
        var yyyy = d.getFullYear();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('probation_end_date').value = yyyy + '-' + mm + '-' + dd;
    }

    function addRowItem(defaultType = 'KPI') {
        var idx = itemIndex++;
        var tr = document.createElement('tr');
        tr.id = 'row_item_' + idx;
        tr.innerHTML = `
            <td>
                <select name="items[${idx}][item_type]" class="form-select form-control form-control-sm select-item-type" onchange="calculateWeight()">
                    <option value="KPI" ${defaultType === 'KPI' ? 'selected' : ''}>KPI</option>
                    <option value="OBJECTIVE" ${defaultType === 'OBJECTIVE' ? 'selected' : ''}>Objective (OKR)</option>
                    <option value="KEY_RESULT" ${defaultType === 'KEY_RESULT' ? 'selected' : ''}>Key Result (OKR)</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][title]" class="form-control form-control-sm mb-1" placeholder="Nama target / KR..." required>
                <textarea name="items[${idx}][description]" class="form-control form-control-sm" rows="1" placeholder="Keterangan cara pengukuran..."></textarea>
            </td>
            <td>
                <input type="text" name="items[${idx}][kpi_unit]" class="form-control form-control-sm" value="%" placeholder="%, IDR, Qty">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${idx}][target_value]" class="form-control form-control-sm" value="100" required>
            </td>
            <td>
                <input type="number" step="0.5" name="items[${idx}][weight_percent]" class="form-control form-control-sm input-weight" value="50" oninput="calculateWeight()">
            </td>
            <td>
                <select name="items[${idx}][scoring_type]" class="form-select form-control form-control-sm">
                    <option value="HIGHER_BETTER">Makin Tinggi Makin Bagus</option>
                    <option value="LOWER_BETTER">Makin Rendah Makin Bagus</option>
                    <option value="MILESTONE">Milestone / Ya-Tidak</option>
                </select>
            </td>
            <td>
                <input type="date" name="items[${idx}][due_date]" class="form-control form-control-sm">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRowItem(${idx})">
                    <i class="feather-trash-2"></i>
                </button>
            </td>
        `;
        document.getElementById('itemsContainer').appendChild(tr);
        calculateWeight();
    }

    function removeRowItem(idx) {
        var el = document.getElementById('row_item_' + idx);
        if (el) {
            el.remove();
            calculateWeight();
        }
    }

    function calculateWeight() {
        var weights = document.querySelectorAll('.input-weight');
        var types = document.querySelectorAll('.select-item-type');
        var total = 0;

        for (var i = 0; i < weights.length; i++) {
            if (types[i].value === 'KPI') {
                total += parseFloat(weights[i].value) || 0;
            }
        }

        var badge = document.getElementById('badgeTotalWeight');
        badge.innerText = 'Total Bobot KPI: ' + total.toFixed(1) + '%';
        if (Math.abs(total - 100.0) < 0.01) {
            badge.className = 'badge bg-success me-2 p-2';
        } else {
            badge.className = 'badge bg-danger me-2 p-2';
        }
    }

    function toggleGoalType() {
        calculateWeight();
    }

    function submitForm() {
        var form = document.getElementById('formGoalCreate');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-kpi-new-submit') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    window.location.href = "{{ url('hrd-performance-kpi-detail') }}/" + res.data.id;
                });
            } else {
                Swal.fire('Gagal Menyimpan', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Kesalahan Sistem', err.message, 'error');
        });
    }
</script>
@endsection

