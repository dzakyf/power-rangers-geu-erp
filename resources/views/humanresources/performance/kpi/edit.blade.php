@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
@include('humanresources.performance.partials.page-header', [
    'title'       => 'Edit & Revisi Target KPI',
    'parentLabel' => 'KPI & OKR',
    'parentUrl'   => url('hrd-performance-kpi-index'),
    'current'     => 'Edit ' . $goal->goal_no,
    'icon'        => 'fa-bullseye',
    'badgeText'   => 'Revisi Goal Sheet',
])

@if($goal->status === 'REJECTED')
    <div class="alert alert-danger">
        <strong><i class="feather-alert-triangle me-1"></i> KPI Ditolak pada Approval:</strong>
        {{ $goal->rejection_reason ?? 'Silakan revisi target dan bobot indikator kerja lalu ajukan approval ulang.' }}
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Revisi Sasaran Kerja ({{ $goal->goal_no }})</h5>
        <span class="badge bg-{{ $goal->status === 'REJECTED' ? 'danger' : 'warning' }} text-white p-2">{{ $goal->status }}</span>
    </div>
    <div class="card-body">
        <form id="formGoalEdit">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Periode Penilaian</label>
                    <input type="text" class="form-control" value="{{ $goal->period->name ?? '-' }} ({{ $goal->period->period_code ?? '-' }})" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label font-weight-bold">{{ $goal->scope_type === 'POSITION' ? 'Posisi / Jabatan' : 'Karyawan' }}</label>
                    <input type="text" class="form-control" value="{{ $goal->scope_type === 'POSITION' ? ($goal->position . ' - ' . $goal->department) : (($goal->user->Name ?? '-') . ' (' . ($goal->emp_id ?? '-') . ')') }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Tipe Sasaran</label>
                    <select name="goal_type" id="goal_type" class="form-select form-control" onchange="calculateWeight()">
                        <option value="HYBRID" {{ $goal->goal_type === 'HYBRID' ? 'selected' : '' }}>Hybrid (KPI & OKR)</option>
                        <option value="KPI" {{ $goal->goal_type === 'KPI' ? 'selected' : '' }}>KPI Only</option>
                        <option value="OKR" {{ $goal->goal_type === 'OKR' ? 'selected' : '' }}>OKR Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label font-weight-bold">Level Target</label>
                    <select name="level" id="level" class="form-select form-control">
                        <option value="INDIVIDUAL" {{ $goal->level === 'INDIVIDUAL' ? 'selected' : '' }}>Individual</option>
                        <option value="TEAM" {{ $goal->level === 'TEAM' ? 'selected' : '' }}>Team / Dept</option>
                        <option value="COMPANY" {{ $goal->level === 'COMPANY' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ $goal->notes }}</textarea>
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
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ url('hrd-performance-kpi-detail', $goal->id) }}" class="btn btn-secondary me-2">Batal</a>
                <button type="button" class="btn btn-primary" onclick="submitEditForm()">
                    <i class="feather-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var itemIndex = 0;
    var existingItems = @json($goal->items);

    document.addEventListener("DOMContentLoaded", function () {
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(function (it) {
                addRowItem(it);
            });
        } else {
            addRowItem({ item_type: 'KPI', kpi_unit: '%', target_value: 100, weight_percent: 50, scoring_type: 'HIGHER_BETTER' });
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function addRowItem(item = {}) {
        var idx = itemIndex++;
        var type = item.item_type || 'KPI';
        var scoring = item.scoring_type || 'HIGHER_BETTER';
        var dueDate = item.due_date ? String(item.due_date).substring(0, 10) : '';
        var tr = document.createElement('tr');
        tr.id = 'row_item_' + idx;
        tr.innerHTML = `
            <td>
                <select name="items[${idx}][item_type]" class="form-select form-control form-control-sm select-item-type" onchange="calculateWeight()">
                    <option value="KPI" ${type === 'KPI' ? 'selected' : ''}>KPI</option>
                    <option value="OBJECTIVE" ${type === 'OBJECTIVE' ? 'selected' : ''}>Objective (OKR)</option>
                    <option value="KEY_RESULT" ${type === 'KEY_RESULT' ? 'selected' : ''}>Key Result (OKR)</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][title]" class="form-control form-control-sm mb-1" value="${escapeHtml(item.title)}" placeholder="Nama target / KR..." required>
                <textarea name="items[${idx}][description]" class="form-control form-control-sm" rows="1" placeholder="Keterangan cara pengukuran...">${escapeHtml(item.description)}</textarea>
            </td>
            <td>
                <input type="text" name="items[${idx}][kpi_unit]" class="form-control form-control-sm" value="${escapeHtml(item.kpi_unit || '%')}">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${idx}][target_value]" class="form-control form-control-sm" value="${item.target_value !== undefined ? item.target_value : 100}" required>
            </td>
            <td>
                <input type="number" step="0.5" name="items[${idx}][weight_percent]" class="form-control form-control-sm input-weight" value="${item.weight_percent !== undefined ? item.weight_percent : 0}" oninput="calculateWeight()">
            </td>
            <td>
                <select name="items[${idx}][scoring_type]" class="form-select form-control form-control-sm">
                    <option value="HIGHER_BETTER" ${scoring === 'HIGHER_BETTER' ? 'selected' : ''}>Makin Tinggi Makin Bagus</option>
                    <option value="LOWER_BETTER" ${scoring === 'LOWER_BETTER' ? 'selected' : ''}>Makin Rendah Makin Bagus</option>
                    <option value="MILESTONE" ${scoring === 'MILESTONE' ? 'selected' : ''}>Milestone / Ya-Tidak</option>
                </select>
            </td>
            <td>
                <input type="date" name="items[${idx}][due_date]" class="form-control form-control-sm" value="${dueDate}">
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
        badge.className = Math.abs(total - 100.0) < 0.01 ? 'badge bg-success text-white me-2 p-2' : 'badge bg-danger text-white me-2 p-2';
    }

    function submitEditForm() {
        var form = document.getElementById('formGoalEdit');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var formData = new FormData(form);
        fetch("{{ url('hrd-performance-kpi-update', $goal->id) }}", {
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
                    window.location.href = "{{ url('hrd-performance-kpi-detail', $goal->id) }}";
                });
            } else {
                Swal.fire('Gagal Menyimpan', res.message, 'error');
            }
        })
        .catch(err => Swal.fire('Kesalahan Sistem', err.message, 'error'));
    }
</script>
@endsection
