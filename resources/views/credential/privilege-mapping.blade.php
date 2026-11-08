@extends('layout.mainlayout-credential')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
    .badge-pos { background-color: #0d6efd; color: #fff; }
    .badge-dir { background-color: #6f42c1; color: #fff; }
    .badge-dept { background-color: #0dcaf0; color: #000; }
</style>
@endpush

@section('content-wrapper')
<div class="main-content-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Pengelolaan Hak Akses Default (Priv_ID & Role Mapping)</h4>
        <small class="text-muted">Konfigurasi otomatisasi penugasan <strong>Priv_ID</strong> dan <strong>Role Menu</strong> saat kandidat resmi diterima (HIRED) menjadi karyawan.</small>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="openAddModal()">
            <i class="fa fa-plus me-1"></i> Tambah Aturan Mapping
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Filter Kategori Mapping:</label>
                <select id="filterType" class="form-select form-select-sm" onchange="reloadTable()">
                    <option value="">Semua Kategori</option>
                    <option value="POSITION">Berdasarkan Posisi (Jabatan)</option>
                    <option value="DIRECTORAT">Berdasarkan Direktorat</option>
                    <option value="DEPARTMENT">Berdasarkan Departemen</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle w-100" id="mappingTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 140px;">Kategori</th>
                        <th>Target Kunci (Posisi / Direktorat)</th>
                        <th style="width: 120px;">Priv_ID Default</th>
                        <th style="width: 140px;">Role Spatie</th>
                        <th>Keterangan</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / EDIT MAPPING --}}
<div class="modal fade" id="modalMapping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Aturan Mapping</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMapping" onsubmit="saveMapping(event)">
                @csrf
                <input type="hidden" name="id" id="mapping_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipe Pemetaan <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="mapping_type" id="mapping_type" required onchange="handleTypeChange()">
                            <option value="POSITION">Berdasarkan Posisi (Jabatan)</option>
                            <option value="DIRECTORAT">Berdasarkan Direktorat</option>
                            <option value="DEPARTMENT">Berdasarkan Departemen</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Kunci (Nama Posisi / Direktorat) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="target_key" id="target_key" list="targetSuggestions" required placeholder="Contoh: Manager, IT Support, Human Capital">
                        <datalist id="targetSuggestions"></datalist>
                        <div class="form-text fs-11">Jika nama posisi kandidat mengandung kata kunci ini, aturan ini akan diaplikasikan.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Priv_ID Default <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm text-uppercase" name="priv_id" id="priv_id" required placeholder="Contoh: STAFF, HEAD, MANAGER, VP">
                            <div class="form-text fs-11">Disimpan pada <code>users.Priv_ID</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Role Spatie</label>
                            <select class="form-select form-select-sm" name="default_role" id="default_role">
                                <option value="">-- Sama dengan Priv_ID --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                            <div class="form-text fs-11">Penentu hak akses menu Spatie.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan / Deskripsi</label>
                        <textarea class="form-control form-control-sm" name="description" id="description" rows="2" placeholder="Catatan peruntukan hak akses..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status Aturan</label>
                        <select class="form-select form-select-sm" name="status" id="status">
                            <option value="ACTIVE">ACTIVE (Diberlakukan)</option>
                            <option value="INACTIVE">INACTIVE (Nonaktif)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" id="btnSave">Simpan</button>
                </div>
            </form>
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

    $(document).ready(function() {
        table = $('#mappingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('credential-privilege-mapping-data') }}",
                data: function(d) {
                    d.mapping_type = $('#filterType').val();
                }
            },
            columns: [
                { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false },
                { 
                    data: 'mapping_type', 
                    render: function(data) {
                        if (data === 'POSITION') return '<span class="badge badge-pos px-2 py-1">POSISI</span>';
                        if (data === 'DIRECTORAT') return '<span class="badge badge-dir px-2 py-1">DIREKTORAT</span>';
                        return '<span class="badge badge-dept px-2 py-1">DEPARTEMEN</span>';
                    }
                },
                { data: 'target_key', render: function(d) { return '<strong>' + d + '</strong>'; } },
                { data: 'priv_id', render: function(d) { return '<span class="badge bg-dark text-white px-2 py-1">' + d + '</span>'; } },
                { data: 'default_role', render: function(d) { return d ? '<span class="badge bg-secondary text-white px-2 py-1">' + d + '</span>' : '<em class="text-muted">-</em>'; } },
                { data: 'description', render: function(d) { return d ? d : '<em class="text-muted">-</em>'; } },
                { 
                    data: 'status', 
                    render: function(d) { 
                        return d === 'ACTIVE' ? '<span class="badge bg-success text-white">ACTIVE</span>' : '<span class="badge bg-danger text-white">INACTIVE</span>'; 
                    } 
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                            <button type="button" class="btn btn-outline-primary btn-sm me-1" onclick='editMapping(${JSON.stringify(row)})'><i class="fa fa-pencil"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteMapping(${row.id})"><i class="fa fa-trash"></i></button>
                        `;
                    }
                }
            ]
        });

        handleTypeChange();
    });

    function reloadTable() {
        table.ajax.reload();
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
            suggestions.append('<option value="' + item + '">');
        });
    }

    function openAddModal() {
        $('#formMapping')[0].reset();
        $('#mapping_id').val('');
        $('#modalTitle').text('Tambah Aturan Mapping Hak Akses');
        handleTypeChange();
        $('#modalMapping').modal('show');
    }

    function editMapping(row) {
        $('#mapping_id').val(row.id);
        $('#mapping_type').val(row.mapping_type);
        handleTypeChange();
        $('#target_key').val(row.target_key);
        $('#priv_id').val(row.priv_id);
        $('#default_role').val(row.default_role || '');
        $('#description').val(row.description || '');
        $('#status').val(row.status || 'ACTIVE');

        $('#modalTitle').text('Edit Aturan Mapping: ' + row.target_key);
        $('#modalMapping').modal('show');
    }

    function saveMapping(e) {
        e.preventDefault();
        $('#btnSave').prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: "{{ url('credential-privilege-mapping-store') }}",
            type: "POST",
            data: $('#formMapping').serialize(),
            success: function(res) {
                $('#modalMapping').modal('hide');
                $('#btnSave').prop('disabled', false).text('Simpan');
                reloadTable();
                alert(res.message || 'Berhasil disimpan!');
            },
            error: function(xhr) {
                $('#btnSave').prop('disabled', false).text('Simpan');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                alert('Gagal: ' + msg);
            }
        });
    }

    function deleteMapping(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus aturan mapping ini?')) return;

        $.ajax({
            url: "{{ url('credential-privilege-mapping-delete') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(res) {
                reloadTable();
                alert(res.message || 'Aturan berhasil dihapus.');
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                alert('Gagal: ' + msg);
            }
        });
    }
</script>
@endpush

