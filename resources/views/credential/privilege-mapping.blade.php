@extends('layout.mainlayout-credential')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
    .mapping-stat-card {
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border: 1px solid rgba(0, 0, 0, 0.06);
    }
    .mapping-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .mapping-stat-card.active-filter {
        border-color: var(--primary-bg-color, #0a5221);
        box-shadow: 0 0 0 2px rgba(10, 82, 33, 0.15) !important;
    }
    .stat-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .badge-pos { background-color: #0d6efd; color: #fff; }
    .badge-dir { background-color: #6f42c1; color: #fff; }
    .badge-dept { background-color: #0dcaf0; color: #0b2e35; }
    #mappingTable_wrapper .dataTables_filter {
        display: none;
    }
    #mappingTable_wrapper .dataTables_length select {
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    #mappingTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3rem 0.65rem !important;
        margin-left: 2px;
        border-radius: 0.375rem !important;
    }
    #mappingTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #495057;
        border-bottom-width: 1px;
    }
    .btn-icon-action {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
</style>
@endpush

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between flex-wrap gap-2">
    <div class="my-auto">
        <div class="d-flex align-items-center">
            <h4 class="content-title mb-0 my-auto">Privilege Mapping</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Hak Akses Default (Priv_ID &amp; Role)</span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Konfigurasi otomatisasi penugasan <strong>Priv_ID</strong> dan <strong>Role Spatie</strong> saat kandidat resmi diterima (<em>HIRED</em>) menjadi karyawan.
        </p>
    </div>
    <div class="d-flex my-xl-auto right-content align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="reloadTable()" title="Muat Ulang Data">
            <i class="fe fe-refresh-cw me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold" onclick="openAddModal()">
            <i class="fe fe-plus me-1"></i> Tambah Aturan Mapping
        </button>
    </div>
</div>

<div id="mappingAlert" class="alert d-none alert-dismissible fade show shadow-sm mb-3" role="alert">
    <span id="mappingAlertText"></span>
    <button type="button" class="btn-close" onclick="$('#mappingAlert').addClass('d-none')" aria-label="Close"></button>
</div>

<!-- Summary Stat Cards (Quick Filters) -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card mapping-stat-card shadow-sm mb-0 active-filter" data-filter-type="" data-filter-status="" onclick="applyCardFilter(this)">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Aturan</span>
                    <h4 class="fw-bold mb-0 mt-1">{{ $stats['total'] ?? 0 }}</h4>
                </div>
                <div class="stat-icon-box bg-primary-transparent text-primary">
                    <i class="fe fe-layers"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mapping-stat-card shadow-sm mb-0" data-filter-type="" data-filter-status="ACTIVE" onclick="applyCardFilter(this)">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <span class="text-muted small fw-semibold d-block">Aturan Aktif</span>
                    <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['active'] ?? 0 }}</h4>
                </div>
                <div class="stat-icon-box bg-success-transparent text-success">
                    <i class="fe fe-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mapping-stat-card shadow-sm mb-0" data-filter-type="POSITION" data-filter-status="" onclick="applyCardFilter(this)">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <span class="text-muted small fw-semibold d-block">Mapping Posisi</span>
                    <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['position'] ?? 0 }}</h4>
                </div>
                <div class="stat-icon-box bg-info-transparent text-info">
                    <i class="fe fe-briefcase"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mapping-stat-card shadow-sm mb-0" data-filter-type="DIRECTORAT" data-filter-status="" onclick="applyCardFilter(this)">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <span class="text-muted small fw-semibold d-block">Direktorat &amp; Dept</span>
                    <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['org'] ?? 0 }}</h4>
                </div>
                <div class="stat-icon-box bg-warning-transparent text-warning">
                    <i class="fe fe-git-branch"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-muted mb-1">Kategori Mapping</label>
                <select id="filterType" class="form-select form-select-sm" onchange="reloadTable()">
                    <option value="">Semua Kategori</option>
                    <option value="POSITION">Berdasarkan Posisi (Jabatan)</option>
                    <option value="DIRECTORAT">Berdasarkan Direktorat</option>
                    <option value="DEPARTMENT">Berdasarkan Departemen</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select id="filterStatus" class="form-select form-select-sm" onchange="reloadTable()">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="INACTIVE">INACTIVE</option>
                </select>
            </div>
            <div class="col-md-5 col-sm-8">
                <label class="form-label small fw-semibold text-muted mb-1">Pencarian Cepat</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fe fe-search text-muted"></i></span>
                    <input type="text" id="customSearch" class="form-control" placeholder="Cari kata kunci posisi, Priv_ID, role, atau keterangan...">
                </div>
            </div>
            <div class="col-md-2 col-sm-4 d-grid">
                <button type="button" class="btn btn-light btn-sm border" onclick="resetFilters()">
                    <i class="fe fe-x me-1"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle w-100 mb-0" id="mappingTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 130px;">Kategori</th>
                        <th>Target Kunci (Posisi / Org)</th>
                        <th style="width: 120px;">Priv_ID</th>
                        <th style="width: 170px;">Role Spatie</th>
                        <th>Keterangan</th>
                        <th style="width: 95px;" class="text-center">Status</th>
                        <th style="width: 95px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / EDIT MAPPING --}}
<div class="modal fade" id="modalMapping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Aturan Mapping</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMapping" onsubmit="saveMapping(event)">
                @csrf
                <input type="hidden" name="id" id="mapping_id">
                <div class="modal-body">
                    <div id="modalErrorAlert" class="alert alert-danger d-none small py-2 mb-3"></div>

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Tipe Pemetaan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="mapping_type" id="mapping_type" required onchange="handleTypeChange()">
                                <option value="POSITION">Berdasarkan Posisi (Jabatan)</option>
                                <option value="DIRECTORAT">Berdasarkan Direktorat</option>
                                <option value="DEPARTMENT">Berdasarkan Departemen</option>
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Target Kunci <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="target_key" id="target_key" list="targetSuggestions" required placeholder="Contoh: Manager, IT Support, Human Capital">
                            <datalist id="targetSuggestions"></datalist>
                            <div class="form-text small">Jika posisi/unit kandidat mengandung kata kunci ini, aturan otomatis diterapkan.</div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Priv_ID Default <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm text-uppercase" name="priv_id" id="priv_id" required placeholder="Contoh: STAFF, HEAD, MANAGER, VP">
                            <div class="form-text small">Disimpan pada kolom <code>users.Priv_ID</code>.</div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Role Spatie</label>
                            <select class="form-select form-select-sm" name="default_role_id" id="default_role_id">
                                <option value="">-- Sama dengan Priv_ID --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}{{ $role->department ? ' - ' . $role->department : '' }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Penentu hak akses menu Spatie Permission.</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Keterangan / Deskripsi</label>
                            <textarea class="form-control form-control-sm" name="description" id="description" rows="2" placeholder="Catatan peruntukan hak akses..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status Aturan</label>
                            <select class="form-select form-select-sm" name="status" id="status">
                                <option value="ACTIVE">ACTIVE (Diberlakukan)</option>
                                <option value="INACTIVE">INACTIVE (Nonaktif)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" id="btnSave">
                        <i class="fe fe-save me-1"></i> Simpan Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
<div class="modal fade" id="modalDeleteConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title fw-bold mb-0"><i class="fe fe-alert-triangle me-1"></i> Konfirmasi Hapus</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 fw-semibold">Hapus aturan mapping ini?</p>
                <small class="text-muted" id="deleteTargetLabel"></small>
                <input type="hidden" id="delete_mapping_id">
            </div>
            <div class="modal-footer justify-content-center bg-light py-2">
                <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" id="btnConfirmDelete" onclick="confirmDeleteMapping()">
                    <i class="fe fe-trash-2 me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
    var table;
    var positions = @json($positions->pluck('Position'));
    var directorats = @json($directorats->pluck('Directorat'));
    var departments = @json($departments->pluck('Department'));

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showPageAlert(type, message) {
        $('#mappingAlert')
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type);
        $('#mappingAlertText').text(message);
    }

    $(document).ready(function() {
        table = $('#mappingTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
            language: {
                emptyTable: 'Belum ada aturan privilege mapping.',
                zeroRecords: 'Tidak ada aturan mapping yang cocok dengan pencarian/filter.',
                processing: 'Memuat data...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ aturan',
                infoEmpty: 'Menampilkan 0 aturan',
                paginate: { previous: '&laquo;', next: '&raquo;' }
            },
            ajax: {
                url: "{{ url('credential-privilege-mapping-data') }}",
                data: function(d) {
                    d.mapping_type = $('#filterType').val();
                    d.status = $('#filterStatus').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    className: 'text-center text-muted fw-semibold',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'mapping_type',
                    render: function(data) {
                        if (data === 'POSITION') return '<span class="badge badge-pos px-2 py-1">POSISI</span>';
                        if (data === 'DIRECTORAT') return '<span class="badge badge-dir px-2 py-1">DIREKTORAT</span>';
                        return '<span class="badge badge-dept px-2 py-1">DEPARTEMEN</span>';
                    }
                },
                {
                    data: 'target_key',
                    render: function(d) {
                        return '<span class="fw-bold text-dark">' + escHtml(d) + '</span>';
                    }
                },
                {
                    data: 'priv_id',
                    render: function(d) {
                        return '<span class="badge bg-dark text-white px-2 py-1">' + escHtml(d) + '</span>';
                    }
                },
                {
                    data: 'default_role_label',
                    render: function(d) {
                        return d
                            ? '<span class="badge bg-secondary text-white px-2 py-1">' + escHtml(d) + '</span>'
                            : '<span class="text-muted small fst-italic">Sama dengan Priv_ID</span>';
                    }
                },
                {
                    data: 'description',
                    render: function(d) {
                        return d ? '<span class="small text-muted">' + escHtml(d) + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(d) {
                        return d === 'ACTIVE'
                            ? '<span class="badge bg-success text-white px-2 py-1">ACTIVE</span>'
                            : '<span class="badge bg-danger text-white px-2 py-1">INACTIVE</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function() {
                        return '<button type="button" class="btn btn-outline-primary btn-icon-action me-1 btn-edit-mapping" title="Edit Aturan"><i class="fe fe-edit-2"></i></button>'
                             + '<button type="button" class="btn btn-outline-danger btn-icon-action btn-delete-mapping" title="Hapus Aturan"><i class="fe fe-trash-2"></i></button>';
                    }
                }
            ]
        });

        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('#mappingTable tbody').on('click', '.btn-edit-mapping', function() {
            var row = table.row($(this).closest('tr')).data();
            if (row) editMapping(row);
        });

        $('#mappingTable tbody').on('click', '.btn-delete-mapping', function() {
            var row = table.row($(this).closest('tr')).data();
            if (row) openDeleteModal(row);
        });

        handleTypeChange();
    });

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    function applyCardFilter(el) {
        $('.mapping-stat-card').removeClass('active-filter');
        $(el).addClass('active-filter');
        $('#filterType').val($(el).data('filter-type') || '');
        $('#filterStatus').val($(el).data('filter-status') || '');
        reloadTable();
    }

    function resetFilters() {
        $('#filterType').val('');
        $('#filterStatus').val('');
        $('#customSearch').val('');
        $('.mapping-stat-card').removeClass('active-filter');
        $('.mapping-stat-card').first().addClass('active-filter');
        table.search('').draw();
    }

    function handleTypeChange() {
        var type = $('#mapping_type').val();
        var suggestions = $('#targetSuggestions');
        suggestions.empty();

        var list = [];
        if (type === 'POSITION') list = positions;
        else if (type === 'DIRECTORAT') list = directorats;
        else if (type === 'DEPARTMENT') list = departments;

        list.forEach(function(item) {
            suggestions.append('<option value="' + escHtml(item) + '">');
        });
    }

    function openAddModal() {
        $('#formMapping')[0].reset();
        $('#mapping_id').val('');
        $('#modalErrorAlert').addClass('d-none').text('');
        $('#modalTitle').text('Tambah Aturan Mapping Hak Akses');
        handleTypeChange();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMapping')).show();
    }

    function editMapping(row) {
        $('#modalErrorAlert').addClass('d-none').text('');
        $('#mapping_id').val(row.id);
        $('#mapping_type').val(row.mapping_type);
        handleTypeChange();
        $('#target_key').val(row.target_key);
        $('#priv_id').val(row.priv_id);
        $('#default_role_id').val(row.default_role_id || '');
        $('#description').val(row.description || '');
        $('#status').val(row.status || 'ACTIVE');

        $('#modalTitle').text('Edit Aturan: ' + row.target_key);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMapping')).show();
    }

    function saveMapping(e) {
        e.preventDefault();
        $('#modalErrorAlert').addClass('d-none').text('');
        $('#btnSave').prop('disabled', true).html('<i class="fe fe-loader me-1"></i> Menyimpan...');

        $.ajax({
            url: "{{ url('credential-privilege-mapping-store') }}",
            type: "POST",
            data: $('#formMapping').serialize(),
            success: function(res) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMapping')).hide();
                $('#btnSave').prop('disabled', false).html('<i class="fe fe-save me-1"></i> Simpan Aturan');
                reloadTable();
                showPageAlert('success', res.message || 'Aturan mapping berhasil disimpan.');
            },
            error: function(xhr) {
                $('#btnSave').prop('disabled', false).html('<i class="fe fe-save me-1"></i> Simpan Aturan');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                $('#modalErrorAlert').removeClass('d-none').text(msg);
            }
        });
    }

    function openDeleteModal(row) {
        $('#delete_mapping_id').val(row.id);
        $('#deleteTargetLabel').text(row.mapping_type + ' : ' + row.target_key + ' (' + row.priv_id + ')');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDeleteConfirm')).show();
    }

    function confirmDeleteMapping() {
        var id = $('#delete_mapping_id').val();
        if (!id) return;

        $('#btnConfirmDelete').prop('disabled', true);
        $.ajax({
            url: "{{ url('credential-privilege-mapping-delete') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(res) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDeleteConfirm')).hide();
                $('#btnConfirmDelete').prop('disabled', false);
                reloadTable();
                showPageAlert('success', res.message || 'Aturan mapping berhasil dihapus.');
            },
            error: function(xhr) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDeleteConfirm')).hide();
                $('#btnConfirmDelete').prop('disabled', false);
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                showPageAlert('danger', msg);
            }
        });
    }
</script>
@endpush
