@extends('layout.mainlayout-cns')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
    .table-cns {
        font-size: 12px !important;
        margin-bottom: 0 !important;
    }
    .table-cns thead th {
        background-color: #174375 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 6px 8px !important;
        border: 1px solid #ffffff !important;
        white-space: nowrap !important;
    }
    .table-cns tbody td {
        font-size: 12px !important;
        vertical-align: middle !important;
        padding: 5px 8px !important;
    }
    .role-row {
        cursor: pointer;
    }
    .role-row.selected {
        background-color: #d9edf7 !important;
    }
    .perm-editor-section {
        margin-top: 15px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 12px;
    }
    .no-role-msg {
        padding: 30px;
        text-align: center;
        color: #999;
        font-size: 13px;
    }
    .perm-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        vertical-align: middle;
    }
    .col-toggle {
        width: 15px;
        height: 15px;
        cursor: pointer;
        vertical-align: middle;
        margin-top: 2px;
    }
    .adm-badge {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 8px;
    }
    .dataTables_wrapper {
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 2px 8px !important;
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        font-size: 12px !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        height: 28px !important;
        font-size: 12px !important;
        padding: 2px 6px !important;
    }
    .dataTables_wrapper .dataTables_length select {
        height: 28px !important;
        font-size: 12px !important;
        padding: 2px 6px !important;
    }
</style>
@endpush

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Akses & Pengguna</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Akses & Pengguna</a></li>
        <li class="breadcrumb-item active"><span class="active">Role & Hak Akses</span></li>
    </ol>
</div>
<!-- End Main Content Header -->

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DAFTAR ROLE PENGGUNA</div>

    <div class="card w-100">
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-striped table-bordered text-nowrap table-cns" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Nama Role</th>
                            <th style="width: 180px;">Jumlah Pengguna CNS</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Action buttons located below table -->
            <div class="mt-2 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary mr-1" id="newRoleBtn" onclick="openCreateRoleModal()">
                    <i class="fa fa-plus mr-1"></i> New Role
                </button>
                <button type="button" class="btn btn-warning mr-1" id="editRoleBtn" onclick="openEditRoleModal()" disabled>
                    <i class="fa fa-pencil mr-1"></i> Edit Nama Role
                </button>
                <button type="button" class="btn btn-danger" id="deleteRoleBtn" onclick="deleteRole()" disabled>
                    <i class="fa fa-trash mr-1"></i> Hapus Role
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row m-1 mt-3">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MATRIKS HAK AKSES MODUL CNS</div>

    <div class="card w-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold" style="font-size: 13px;">Role Terpilih:</span>
                    <span id="selected-role-name" class="ml-2 font-weight-bold text-primary" style="font-size: 13px;">-</span>
                    <span id="role-badge" class="adm-badge" style="display:none;">Superadmin (Full Access)</span>
                    <span id="perm-loading" style="display:none;color:#999;margin-left:10px;"><i class="fa fa-spinner fa-spin"></i> Memuat...</span>
                </div>
                <div id="perm-action-buttons" style="display:none;" class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="grantAllCns()">
                        <i class="fa fa-check-square mr-1"></i> Pilih Semua CNS
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger mr-1" onclick="revokeAllCns()">
                        <i class="fa fa-square-o mr-1"></i> Kosongkan
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="savePermissions()">
                        <i class="fa fa-save mr-1"></i> Simpan Hak Akses
                    </button>
                </div>
            </div>

            <div id="no-role-message" class="no-role-msg">
                <i class="fa fa-info-circle mr-1"></i> Silakan klik salah satu role pada tabel di atas untuk menampilkan dan mengatur hak akses modul CNS.
            </div>

            <div id="permission-table-container" style="display:none;">
                <div class="table-responsive">
                    <table id="permission-table" class="table table-striped table-bordered text-nowrap table-cns" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Menu ID</th>
                                <th>Nama Menu CNS</th>
                                <th>Path / Route</th>
                                <th class="text-center" style="width: 70px;">
                                    View<br>
                                    <input type="checkbox" class="col-toggle" data-action="view" title="Pilih Semua View">
                                </th>
                                <th class="text-center" style="width: 70px;">
                                    Create<br>
                                    <input type="checkbox" class="col-toggle" data-action="create" title="Pilih Semua Create">
                                </th>
                                <th class="text-center" style="width: 70px;">
                                    Edit<br>
                                    <input type="checkbox" class="col-toggle" data-action="edit" title="Pilih Semua Edit">
                                </th>
                                <th class="text-center" style="width: 70px;">
                                    Delete<br>
                                    <input type="checkbox" class="col-toggle" data-action="delete" title="Pilih Semua Delete">
                                </th>
                                <th class="text-center" style="width: 70px;">
                                    Approve<br>
                                    <input type="checkbox" class="col-toggle" data-action="approve" title="Pilih Semua Approve">
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- Action buttons located below table -->
                <div class="mt-2 d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="grantAllCns()">
                            <i class="fa fa-check-square mr-1"></i> Pilih Semua CNS
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="revokeAllCns()">
                            <i class="fa fa-square-o mr-1"></i> Kosongkan
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" onclick="savePermissions()">
                            <i class="fa fa-save mr-1"></i> Simpan Hak Akses
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Role Modal --}}
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title text-white font-weight-bold"><i class="fa fa-plus mr-1"></i> Tambah Role Baru</h6>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#createRoleModal').modal('hide');">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 13px;">
                <form id="createRoleForm">
                    @csrf
                    <div class="form-group mb-2">
                        <label for="createRoleName" class="font-weight-bold"><small>Nama Role <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control form-control-sm" id="createRoleName" name="name" placeholder="Misal: CNS_LOGISTIC" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#createRoleModal').modal('hide');">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="submitCreateRole()"><i class="fa fa-save mr-1"></i> Simpan Role</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Role Modal --}}
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-pencil mr-1"></i> Edit Nama Role</h6>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#editRoleModal').modal('hide');">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 13px;">
                <form id="editRoleForm">
                    @csrf
                    <input type="hidden" id="editRoleId" name="id">
                    <div class="form-group mb-2">
                        <label for="editRoleName" class="font-weight-bold"><small>Nama Role <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control form-control-sm" id="editRoleName" name="name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#editRoleModal').modal('hide');">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="submitEditRole()"><i class="fa fa-save mr-1"></i> Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
    var selectedRole = null;
    var permissionTable = null;

    var rolesTable = $('#roles-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: '{{ url("cns-role-management-data") }}',
        columns: [
            { data: 'id', className: 'text-center' },
            { data: 'name' },
            { data: 'users_count', className: 'text-center' }
        ],
        createdRow: function(row, data) {
            $(row).addClass('role-row').attr('data-role-id', data.id);
        }
    });

    $('#roles-table tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            selectedRole = null;
            $('#editRoleBtn').prop('disabled', true);
            $('#deleteRoleBtn').prop('disabled', true);
            showNoRoleMessage();
        } else {
            rolesTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            selectedRole = rolesTable.row(this).data();
            $('#editRoleBtn').prop('disabled', false);
            $('#deleteRoleBtn').prop('disabled', false);
            loadPermissionsForRole(selectedRole);
        }
    });

    function showNoRoleMessage() {
        $('#no-role-message').show();
        $('#permission-table-container').hide();
        $('#perm-action-buttons').hide();
        $('#selected-role-name').text('-');
        $('#role-badge').hide();
    }

    function loadPermissionsForRole(role) {
        $('#selected-role-name').text(role.name);
        $('#perm-loading').show();
        $('#no-role-message').hide();
        $('#role-badge').toggle(role.name === 'ADM');
        $('#perm-action-buttons').show();

        if (permissionTable) {
            permissionTable.destroy();
            $('#permission-table tbody').empty();
        }

        permissionTable = $('#permission-table').DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            info: false,
            ajax: {
                url: '{{ url("cns-role-management-permissions") }}',
                data: { role_id: role.id }
            },
            columns: [
                { data: 'menu_id', className: 'text-center' },
                {
                    data: 'menu_name',
                    render: function(data, type, row) {
                        var indent = '';
                        var icon = '<i class="fa fa-file-text-o mr-1 text-muted"></i>';
                        if (row.position === 3) {
                            icon = '<i class="fa fa-folder-open-o mr-1 text-warning"></i>';
                        } else if (row.position === 4) {
                            indent = '&nbsp;&nbsp;&nbsp;&nbsp;↳ ';
                        }
                        var nameStyle = (row.position < 4) ? 'font-weight: bold; color: #174375;' : '';
                        return indent + icon + '<span style="' + nameStyle + '">' + (data || '(tanpa nama)') + '</span>';
                    }
                },
                {
                    data: 'path_loc',
                    render: function(data) {
                        return data ? '<code>' + data + '</code>' : '-';
                    }
                },
                {
                    data: 'view',
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderCheckbox(row.menu_id, 'view', data, row.position, row.parent_menu);
                    }
                },
                {
                    data: 'create',
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderCheckbox(row.menu_id, 'create', data, row.position, row.parent_menu);
                    }
                },
                {
                    data: 'edit',
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderCheckbox(row.menu_id, 'edit', data, row.position, row.parent_menu);
                    }
                },
                {
                    data: 'delete',
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderCheckbox(row.menu_id, 'delete', data, row.position, row.parent_menu);
                    }
                },
                {
                    data: 'approve',
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderCheckbox(row.menu_id, 'approve', data, row.position, row.parent_menu);
                    }
                }
            ],
            initComplete: function() {
                $('#perm-loading').hide();
                $('#permission-table-container').show();
            }
        });
    }

    function renderCheckbox(menuId, action, checked, position, parentMenu) {
        if (position && position < 4 && action !== 'view') {
            return '<span class="text-muted">-</span>';
        }
        var chk = checked ? 'checked' : '';
        return '<input type="checkbox" class="perm-checkbox action-' + action + '" data-menu-id="' + menuId + '" data-parent-menu="' + (parentMenu || '') + '" data-position="' + (position || '') + '" data-action="' + action + '" ' + chk + '>';
    }

    $(document).on('change', '.perm-checkbox', function() {
        var $el = $(this);
        var menuId = $el.data('menu-id');
        var action = $el.data('action');
        var isChecked = $el.is(':checked');
        var position = parseInt($el.data('position'), 10);

        if (position < 4 && action === 'view') {
            // Uncheck or check all descendant menus
            var uncheckDescendants = function(parentId, checkedState) {
                $('.perm-checkbox[data-parent-menu="' + parentId + '"]').each(function() {
                    var $child = $(this);
                    var childId = $child.data('menu-id');
                    if (!checkedState) {
                        // If parent unchecked, turn off all actions of descendants
                        $('.perm-checkbox[data-menu-id="' + childId + '"]').prop('checked', false);
                    } else if ($child.data('action') === 'view') {
                        $child.prop('checked', true);
                    }
                    uncheckDescendants(childId, checkedState);
                });
            };
            uncheckDescendants(menuId, isChecked);
        } else if (position === 4 && isChecked && action === 'view') {
            // If child view checked, auto-check ancestors view
            var checkAncestors = function(parentId) {
                if (!parentId) return;
                var $parent = $('.perm-checkbox[data-menu-id="' + parentId + '"][data-action="view"]');
                $parent.prop('checked', true);
                var grandParentId = $parent.data('parent-menu');
                if (grandParentId) {
                    checkAncestors(grandParentId);
                }
            };
            checkAncestors($el.data('parent-menu'));
        }
    });

    $(document).on('change', '.col-toggle', function() {
        var action = $(this).data('action');
        var isChecked = $(this).is(':checked');
        $('.action-' + action).prop('checked', isChecked);
    });

    function savePermissions() {
        if (!selectedRole) return;

        var permissions = {};
        $('.perm-checkbox').each(function() {
            var menuId = $(this).data('menu-id');
            var action = $(this).data('action');
            if (!permissions[menuId]) {
                permissions[menuId] = {};
            }
            permissions[menuId][action] = $(this).is(':checked') ? 1 : 0;
        });

        $('#perm-loading').show();
        $.ajax({
            url: '{{ url("cns-role-management-permissions-save") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                role_id: selectedRole.id,
                permissions: permissions
            },
            success: function() {
                $('#perm-loading').hide();
                swal('Berhasil!', 'Hak akses role ' + selectedRole.name + ' berhasil disimpan.', 'success');
            },
            error: function(xhr) {
                $('#perm-loading').hide();
                var msg = 'Gagal menyimpan hak akses.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal('Error', msg, 'error');
            }
        });
    }

    function grantAllCns() {
        if (!selectedRole) return;
        swal({
            title: "Pilih Semua Akses CNS?",
            text: "Role " + selectedRole.name + " akan diberikan semua hak akses pada modul CNS.",
            type: "info",
            showCancelButton: true,
            confirmButtonText: "Ya, Berikan Semua",
            cancelButtonText: "Batal"
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: '{{ url("cns-role-management-grant-all") }}/' + selectedRole.id,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        loadPermissionsForRole(selectedRole);
                        swal('Berhasil!', 'Semua hak akses modul CNS telah diberikan.', 'success');
                    },
                    error: function() {
                        swal('Error', 'Gagal memproses permintaan.', 'error');
                    }
                });
            }
        });
    }

    function revokeAllCns() {
        if (!selectedRole) return;
        swal({
            title: "Kosongkan Akses CNS?",
            text: "Semua hak akses modul CNS untuk role " + selectedRole.name + " akan dihapus.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Kosongkan",
            cancelButtonText: "Batal"
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: '{{ url("cns-role-management-revoke-all") }}/' + selectedRole.id,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        loadPermissionsForRole(selectedRole);
                        swal('Berhasil!', 'Semua hak akses modul CNS berhasil dikosongkan.', 'success');
                    },
                    error: function() {
                        swal('Error', 'Gagal memproses permintaan.', 'error');
                    }
                });
            }
        });
    }

    function openCreateRoleModal() {
        $('#createRoleForm')[0].reset();
        $('#createRoleModal').modal('show');
    }

    function submitCreateRole() {
        var name = $('#createRoleName').val().trim();
        if (!name) {
            swal('Perhatian', 'Nama role wajib diisi.', 'warning');
            return;
        }

        $.ajax({
            url: '{{ url("cns-role-management-store") }}',
            type: 'POST',
            data: $('#createRoleForm').serialize(),
            success: function() {
                $('#createRoleModal').modal('hide');
                rolesTable.ajax.reload(null, false);
                swal('Berhasil!', 'Role baru berhasil dibuat.', 'success');
            },
            error: function(xhr) {
                var msg = 'Gagal membuat role.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal('Error', msg, 'error');
            }
        });
    }

    function openEditRoleModal() {
        if (!selectedRole) return;
        $('#editRoleId').val(selectedRole.id);
        $('#editRoleName').val(selectedRole.name);
        $('#editRoleModal').modal('show');
    }

    function submitEditRole() {
        var name = $('#editRoleName').val().trim();
        if (!name) {
            swal('Perhatian', 'Nama role wajib diisi.', 'warning');
            return;
        }

        $.ajax({
            url: '{{ url("cns-role-management-update") }}/' + $('#editRoleId').val(),
            type: 'POST',
            data: $('#editRoleForm').serialize(),
            success: function() {
                $('#editRoleModal').modal('hide');
                rolesTable.ajax.reload(null, false);
                swal('Berhasil!', 'Nama role berhasil diperbarui.', 'success');
            },
            error: function(xhr) {
                var msg = 'Gagal memperbarui role.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal('Error', msg, 'error');
            }
        });
    }

    function deleteRole() {
        if (!selectedRole) return;
        swal({
            title: "Hapus Role?",
            text: "Role " + selectedRole.name + " akan dihapus secara permanen.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: '{{ url("cns-role-management-delete") }}/' + selectedRole.id,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        rolesTable.ajax.reload(null, false);
                        selectedRole = null;
                        $('#editRoleBtn').prop('disabled', true);
                        $('#deleteRoleBtn').prop('disabled', true);
                        showNoRoleMessage();
                        swal('Berhasil!', 'Role telah dihapus.', 'success');
                    },
                    error: function(xhr) {
                        var msg = 'Gagal menghapus role.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        swal('Error', msg, 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
