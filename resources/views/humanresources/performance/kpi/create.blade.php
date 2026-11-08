@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('hrd-performance-kpi-index') }}">KPI & OKR</a></li>
        <li class="breadcrumb-item active"><span class="active">Buat Sasaran Baru</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Form Penetapan Sasaran Kerja (KPI / OKR)</h5>
    </div>
    <div class="card-body">
        <form id="formGoalCreate">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Pilih Karyawan <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select form-control" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->Name }} ({{ $u->Emp_ID ?? '-' }}) - {{ $u->Department ?? '-' }}</option>
                        @endforeach
                    </select>
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
        addRowItem('KPI');
        addRowItem('KPI');
    });

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

