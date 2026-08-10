<?php

use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username; ?>
@extends('layout.mainlayout')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
@endpush

@section('content-wrapper')
<div class="main-content-header">
    <h1>Role & Permission Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Credential</a></li>
        <li class="breadcrumb-item active"><span class="active">Role</span></li>
    </ol>
</div>

<style>
    label {
        margin-bottom: 0 !important;
    }

    .role-row {
        cursor: pointer;
    }

    .role-row.selected {
        background-color: #d9edf7 !important;
    }

    .perm-row {
        cursor: pointer;
    }

    .perm-row.selected {
        background-color: #d9edf7 !important;
    }

    .perm-editor-section {
        margin-top: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
    }

    .no-role-msg {
        padding: 40px;
        text-align: center;
        color: #999;
        font-size: 16px;
    }

    .perm-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .perm-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .filter-row {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-row label {
        font-weight: 600;
        margin-right: 5px;
    }

    .filter-row select {
        width: 200px;
    }

    .adm-badge {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-left: 10px;
    }

    #permission-table_wrapper {
        margin-top: 15px;
    }

    #permission-table thead th {
        border-top: 1px solid #dee2e6;
    }

    .filter-row {
        margin-bottom: 18px;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Roles</h5>
                <table id="roles-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Users Count</th>
                        </tr>
                    </thead>
                </table>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn btn-primary m-1" onclick="openCreateRoleModal()">
                        <i class="fa fa-plus"></i> Add Role
                    </button>
                    <button type="button" class="btn btn-warning m-1" id="editRoleBtn" onclick="openEditRoleModal()" disabled>
                        <i class="fa fa-pencil"></i> Edit Role
                    </button>
                    <button type="button" class="btn btn-danger m-1" id="deleteRoleBtn" onclick="deleteRole()" disabled>
                        <i class="fa fa-trash"></i> Delete Role
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="perm-editor-section" id="permission-editor-section">
            <h5 class="card-title">Role Permissions
                <span id="selected-role-name" style="color:#0066cc;"></span>
                <span id="role-badge" class="adm-badge" style="display:none;">ADM (immutable)</span>
                <span id="perm-loading" style="display:none;color:#999;margin-left:10px;">Loading...</span>
            </h5>
            <div id="no-role-message" class="no-role-msg">Select a role from the table above to view and edit permissions.</div>
            <div id="permission-table-container" style="display:none;">
                <div class="filter-row">
                    <label>Module:</label>
                    <select id="module-filter" class="form-control">
                        <option value="">All Modules</option>
                        <option value="FINANCE">Finances</option>
                        <option value="HRD">Human Resources</option>
                        <option value="MITRA">Business Partner</option>
                        <option value="CNS">CNS</option>
                        <option value="CREDENTIAL">Credential</option>
                    </select>
                    <button id="toggle-all-btn" class="btn btn-sm btn-outline-primary" style="display:none;">Pilih semua role</button>
                </div>
                <table id="permission-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Menu Name</th>
                            <th>Module</th>
                            <th>View</th>
                            <th>Create</th>
                            <th>Edit</th>
                            <th>Delete</th>
                            <th>Approve</th>
                        </tr>
                    </thead>
                </table>
                <div style="margin-top:10px;">
                    <button type="button" class="btn btn-success" id="save-permissions-btn" onclick="triggerSavePermissions()" disabled>
                        <i class="fa fa-save"></i> Save Permissions
                    </button>
                                        <button id="add-menu-btn" class="btn btn-success" disabled onclick="openCreateMenuModal()">
                        <i class="fa fa-plus"></i> Add Menu Item
                    </button>
                    <button id="edit-menu-name-btn" class="btn btn-primary" disabled onclick="openEditMenuModal()">
                        <i class="fa fa-pencil"></i> Edit Menu
                    </button>
                    <button id="delete-menu-btn" class="btn btn-danger" disabled onclick="deleteSelectedMenu()">
                        <i class="fa fa-trash"></i> Delete Menu
                    </button>
                    <span id="save-status" style="margin-left:10px;color:green;display:none;"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        @csrf
                        <input type="hidden" id="can-edit-flag" value="{{ Auth::user()->can('master.credential.role.edit') ? 'true' : 'false' }}">
                        <input type="hidden" id="roleFormMode" value="create">
                        <input type="hidden" id="editRoleId" value="">
                        <div class="form-group">
                            <label for="roleName">Role Name</label>
                            <input type="text" id="roleName" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <span id="roleModalStatus" style="display:none;"></span>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveRole()">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
    <script>
        var canEdit = $('#can-edit-flag').val() === 'true';
        var selectedMenuId = null;
        var selectedMenuName = null;
        var selectedRoleId = null;
        var selectedRoleName = null;
        var permDt = null;
        var permDataCache = {};
        var currentPermState = {};

        var rolesTable = $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("master.credential.role.list") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'users_count'
                }
            ],
            ordering: true,
            paging: true,
            pageLength: 10,
            rowCallback: function(row, data) {
                $(row).addClass('role-row');
                $(row).on('click', function() {
                    $('#roles-table tr').removeClass('selected');
                    $(this).addClass('selected');
                    selectedRoleId = data.id;
                    selectedRoleName = data.name;

                    var isAdm = (data.name === 'ADM');
                    if (isAdm) {
                        $('#editRoleBtn').prop('disabled', !canEdit);
                        $('#deleteRoleBtn').prop('disabled', true);
                    } else {
                        $('#editRoleBtn').prop('disabled', !canEdit);
                        $('#deleteRoleBtn').prop('disabled', !canEdit);
                    }

                    loadPermissions(data.id, data.name);
                });
            }
        });

        function loadPermissions(roleId, roleName) {
            selectedRoleId = roleId;
            selectedRoleName = roleName;
            selectedMenuId = null;
            selectedMenuName = null;
            $('#edit-menu-name-btn').prop('disabled', true);
            $('#delete-menu-btn').prop('disabled', true);
            $('#selected-role-name').text('(' + roleName + ')');
            $('#no-role-message').hide();
            $('#permission-table-container').show();
            $('#perm-loading').show();
            $('#role-badge').hide();
            if (canEdit) {
                $('#save-permissions-btn').prop('disabled', false).show();
            } else {
                $('#save-permissions-btn').hide();
            }

            if (permDt) {
                permDt.destroy();
                $('#permission-table').empty();
            }

            permDt = $('#permission-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route("master.credential.role.permission") }}',
                    data: function(d) {
                        d.role_id = roleId;
                    },
                    dataSrc: function(json) {
                        permDataCache[roleId] = json.data;
                        currentPermState = {};
                        var allGranted = json.data.length > 0;
                        json.data.forEach(function(row) {
                            var state = {};
                            ['view', 'create', 'edit', 'delete', 'approve'].forEach(function(action) {
                                state[action] = !!row[action];
                                if (allGranted && !row[action]) {
                                    allGranted = false;
                                }
                            });
                            currentPermState[row.menu_id] = state;
                        });
                        if (allGranted && canEdit) {
                            $('#toggle-all-btn').show().text('Hapus semua role');
                        } else {
                            $('#toggle-all-btn').show().text('Pilih semua role');
                        }
                        return json.data;
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                    },
                    {
                        data: 'menu_name',
                        render: function(val, type, row) {
                            var pos = parseInt(row.position) || 1;
                            var pad = (pos - 1) * 20;
                            var bold = pos === 1 ? 'font-weight:bold;' : '';
                            return '<span style="padding-left:' + pad + 'px;' + bold + '">' + $('<div>').text(val || '').html() + '</span>';
                        }
                    },
                    {
                        data: 'module'
                    },
                    {
                        data: 'view',
                        render: function(val, type, row) {
                            return renderCheckbox('view', row);
                        }
                    },
                    {
                        data: 'create',
                        render: function(val, type, row) {
                            return renderCheckbox('create', row);
                        }
                    },
                    {
                        data: 'edit',
                        render: function(val, type, row) {
                            return renderCheckbox('edit', row);
                        }
                    },
                    {
                        data: 'delete',
                        render: function(val, type, row) {
                            return renderCheckbox('delete', row);
                        }
                    },
                    {
                        data: 'approve',
                        render: function(val, type, row) {
                            return renderCheckbox('approve', row);
                        }
                    }
                ],
                ordering: true,
                paging: true,
                pageLength: 25,
                columnDefs: [{
                    targets: 2,
                    visible: false
                }],
                drawCallback: function() {
                    bindCheckboxEvents();
                },
                rowCallback: function(row, data) {
                    $(row).addClass('perm-row');
                    $(row).on('click', function() {
                        $('#permission-table tr').removeClass('selected');
                        $(this).addClass('selected');
                        selectedMenuId = data.id;
                        selectedMenuName = data.menu_name;
                        if (canEdit) {
                            $('#edit-menu-name-btn').prop('disabled', false);
                            $('#add-menu-btn').prop('disabled', false);
                            $('#delete-menu-btn').prop('disabled', false);
                        }
                    });
                },
                initComplete: function() {
                    $('#perm-loading').hide();
                }
            });
        }

        function renderCheckbox(action, row) {
            var menuId = row.menu_id;
            var checked = currentPermState[menuId] && currentPermState[menuId][action] !== undefined ?
                currentPermState[menuId][action] :
                !!row[action];
            var disabled = !canEdit ? 'disabled' : '';
            return '<input type="checkbox" class="perm-checkbox" data-menu-id="' + menuId + '" data-action="' + action + '" ' + (checked ? 'checked' : '') + ' ' + disabled + '>';
        }

        function bindCheckboxEvents() {
            if (!canEdit) return;
            $('.perm-checkbox').off('change').on('change', function() {
                var menuId = $(this).data('menu-id');
                var action = $(this).data('action');
                if (!currentPermState[menuId]) currentPermState[menuId] = {};
                currentPermState[menuId][action] = $(this).is(':checked');
            });
        }

        $('#module-filter').on('change', function() {
            var val = this.value;
            if (!permDt) return;
            permDt.column(2).search(val).draw();
        });

        $('#toggle-all-btn').on('click', function() {
            if (!canEdit || !selectedRoleId) return;
            var label = $('#toggle-all-btn').text().trim();
            var url, msg;

            if (label === 'Hapus semua role') {
                url = '{{ url("/master-credential-role-revoke-all") }}/' + selectedRoleId;
                msg = 'Removing all permissions...';
            } else {
                url = '{{ url("/master-credential-role-grant-all") }}/' + selectedRoleId;
                msg = 'Granting all permissions...';
            }

            $('#toggle-all-btn').prop('disabled', true).text(msg);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Error');
                        $('#toggle-all-btn').prop('disabled', false);
                        $('#toggle-all-btn').text(label);
                    }
                },
                error: function() {
                    alert('Request failed');
                    $('#toggle-all-btn').prop('disabled', false);
                    $('#toggle-all-btn').text(label);
                }
            });
        });


        function openCreateRoleModal() {
            $('#roleModalTitle').text('Add Role');
            $('#roleFormMode').val('create');
            $('#editRoleId').val('');
            $('#roleName').val('');
            $('#roleModalStatus').hide().text('');
            $('#roleModal').modal('show');
        }

        function openEditRoleModal() {
            if (!selectedRoleId || !selectedRoleName) return;
            $('#roleModalTitle').text('Edit Role');
            $('#roleFormMode').val('edit');
            $('#editRoleId').val(selectedRoleId);
            $('#roleName').val(selectedRoleName);
            $('#roleModalStatus').hide().text('');
            $('#roleModal').modal('show');
        }

        function saveRole() {
            var mode = $('#roleFormMode').val();
            var name = $('#roleName').val().trim();
            if (!name) {
                $('#roleModalStatus').text('Role name is required').css('color', 'red').show();
                return;
            }

            var url, data;
            if (mode === 'create') {
                url = '{{ url("/master-credential-role-create") }}';
                data = {
                    _token: '{{ csrf_token() }}',
                    name: name
                };
            } else {
                var id = $('#editRoleId').val();
                url = '{{ url("/master-credential-role-update") }}/' + id;
                data = {
                    _token: '{{ csrf_token() }}',
                    name: name
                };
            }

            $('#roleModalStatus').text('Saving...').css('color', 'blue').show();

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#roleModal').modal('hide');
                        rolesTable.ajax.reload();
                    } else {
                        $('#roleModalStatus').text(response.message || 'Error').css('color', 'red').show();
                    }
                },
                error: function(xhr) {
                    var msg = 'Error saving role';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#roleModalStatus').text(msg).css('color', 'red').show();
                }
            });
        }

        function deleteRole() {
            if (!selectedRoleId || !selectedRoleName) return;
            if (!confirm('Are you sure you want to delete role "' + selectedRoleName + '"?')) return;

            $.ajax({
                url: '{{ url("/master-credential-role-delete") }}/' + selectedRoleId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        selectedRoleId = null;
                        selectedRoleName = null;
                        $('#editRoleBtn').prop('disabled', true);
                        $('#deleteRoleBtn').prop('disabled', true);
                        rolesTable.ajax.reload();
                    } else {
                        alert(response.message || 'Error deleting role');
                    }
                },
                error: function() {
                    alert('Error deleting role');
                }
            });
        }
    function triggerSavePermissions() {
        if (!selectedRoleId) return;

        var permissions = {};
        Object.keys(currentPermState).forEach(function(menuId) {
        permissions[menuId] = currentPermState[menuId];
        });

        $('#save-permissions-btn').prop('disabled', true).text('Saving...');
        $('#save-status').hide();

        $.ajax({
        url: '{{ route("master.credential.role.permission.save") }}',
        method: 'POST',
        data: {
        _token: '{{ csrf_token() }}',
        role_id: selectedRoleId,
        permissions: permissions
        },
        success: function(response) {
        if (response.success) {
        $('#save-status').text('Permissions saved successfully!').css('color', 'green').show();
        setTimeout(function() { $('#save-status').fadeOut(); }, 3000);
        } else {
        $('#save-status').text(response.message || 'Error saving permissions').css('color', 'red').show();
        setTimeout(function() { $('#save-status').fadeOut(); }, 3000);
        }
        },
        error: function(xhr) {
        var msg = 'Error saving permissions';
        if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
        }
        $('#save-status').text(msg).css('color', 'red').show();
        },
        complete: function() {
        $('#save-permissions-btn').prop('disabled', false).text('Save Permissions');
        }
        });
    }

    function buildParentOptions(module, selected) {
        var opts = '<option value="">-- Root (no parent) --</option>';
        var rows = permDataCache[selectedRoleId] || [];
        rows.forEach(function(r) {
            if (r.module === module) {
                var sel = (r.menu_id === selected) ? ' selected' : '';
                opts += '<option value="' + r.menu_id + '"' + sel + '>' + r.menu_name + '</option>';
            }
        });
        return opts;
    }

    function openMenuModal(mode) {
        var isCreate = (mode === 'create');
        var row = null;
        if (!isCreate) {
            var rows = permDataCache[selectedRoleId] || [];
            rows.forEach(function(r) { if (String(r.id) === String(selectedMenuId)) row = r; });
        }

        var module = isCreate ? (permDataCache[selectedRoleId] && permDataCache[selectedRoleId][0] ? permDataCache[selectedRoleId][0].module : 'MITRA') : (row ? row.module : 'MITRA');
        var parent = isCreate ? '' : (row ? row.parent_menu : '');
        var position = isCreate ? 4 : (row ? row.position : 4);
        var name = isCreate ? '' : (row ? row.menu_name : '');
        var path = isCreate ? '' : (row ? (row.path_loc || '') : '');
        var urutan = isCreate ? 1 : (row ? (row.urutan || 1) : 1);

        var body = '<form id="menuForm">' +
            '<input type="hidden" id="menuFormMode" value="' + mode + '">' +
            '<input type="hidden" id="menuFormRowId" value="' + (isCreate ? '' : selectedMenuId) + '">' +
            '<input type="hidden" id="menuFormMenuId" value="' + (isCreate ? '' : (row ? row.menu_id : '')) + '">' +
            '<div class="form-group"><label>Menu Name</label><input type="text" id="menuFormName" class="form-control" value="' + $('<div>').text(name).html() + '"></div>' +
            '<div class="form-group"><label>Module</label><select id="menuFormModule" class="form-control">' +
            '<option value="MITRA"' + (module === 'MITRA' ? ' selected' : '') + '>Business Partner</option>' +
            '<option value="FINANCE"' + (module === 'FINANCE' ? ' selected' : '') + '>Finances</option>' +
            '<option value="HRD"' + (module === 'HRD' ? ' selected' : '') + '>Human Resources</option>' +
            '<option value="CNS"' + (module === 'CNS' ? ' selected' : '') + '>CNS</option>' +
            '<option value="CANDIDATE"' + (module === 'CANDIDATE' ? ' selected' : '') + '>Candidate</option>' +
            '<option value="CREDENTIAL"' + (module === 'CREDENTIAL' ? ' selected' : '') + '>Credential</option>' +
            '</select></div>' +
            '<div class="form-group"><label>Parent Menu</label><select id="menuFormParent" class="form-control">' +
            buildParentOptions(module, parent) +
            '</select></div>' +
            '<div class="form-group"><label>Position (Level)</label><select id="menuFormPosition" class="form-control">' +
            '<option value="1"' + (position == 1 ? ' selected' : '') + '>1 - Top Module</option>' +
            '<option value="2"' + (position == 2 ? ' selected' : '') + '>2 - Menu Group</option>' +
            '<option value="3"' + (position == 3 ? ' selected' : '') + '>3 - Sub Menu</option>' +
            '<option value="4"' + (position == 4 ? ' selected' : '') + '>4 - Leaf Item</option>' +
            '</select></div>' +
            '<div class="form-group"><label>Path / Route</label><input type="text" id="menuFormPath" class="form-control" value="' + $('<div>').text(path).html() + '"></div>' +
            '<div class="form-group"><label>Urutan</label><input type="number" min="1" id="menuFormUrutan" class="form-control" value="' + urutan + '"></div>' +
            '</form>';

        AppModal.open({
            title: isCreate ? 'Add Menu Item' : 'Edit Menu',
            size: 'md',
            body: body,
            actions: [{
                label: 'Cancel',
                class: 'btn-secondary',
                onClick: function() { return true; }
            }, {
                label: isCreate ? 'Create' : 'Save',
                class: 'btn-primary',
                onClick: function() {
                    var name = $('#menuFormName').val().trim();
                    var module = $('#menuFormModule').val();
                    if (!name) {
                        alert('Menu name is required.');
                        return false;
                    }
                    var fd = new FormData();
                    fd.append('_token', '{{ csrf_token() }}');
                    fd.append('id', $('#menuFormRowId').val());
                    fd.append('menu_id', $('#menuFormMenuId').val());
                    fd.append('name', name);
                    fd.append('module', module);
                    fd.append('parent_menu', $('#menuFormParent').val());
                    fd.append('position', $('#menuFormPosition').val());
                    fd.append('path_loc', $('#menuFormPath').val());
                    fd.append('urutan', $('#menuFormUrutan').val());

                    var url = isCreate ? '{{ url("/master-credential-role-menu-create") }}' : '{{ url("/master-credential-role-menu-update") }}';

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                AppModal.close();
                                if (permDt) permDt.ajax.reload();
                            } else {
                                alert(res.message || 'Error');
                            }
                        },
                        error: function() {
                            alert('Request failed');
                        }
                    });
                    return false;
                }
            }]
        });

        $('#appModal').on('shown.bs.modal', function() {
            if ($('#menuFormModule').hasClass('select2-hidden-accessible')) {
                $('#menuFormModule').select2('destroy');
            }
            if ($('#menuFormParent').hasClass('select2-hidden-accessible')) {
                $('#menuFormParent').select2('destroy');
            }
            $('#menuFormModule').select2({
                width: '100%',
                dropdownParent: $('#appModal')
            });
            $('#menuFormParent').select2({
                width: '100%',
                dropdownParent: $('#appModal')
            });
            $('#menuFormModule').off('change').on('change', function() {
                $('#menuFormParent').html(buildParentOptions(this.value, ''));
                $('#menuFormParent').trigger('change');
            });
        });
    }

    function openCreateMenuModal() {
        if (!canEdit || !selectedRoleId) return;
        openMenuModal('create');
    }

    function openEditMenuModal() {
        if (!canEdit || !selectedMenuId) return;
        openMenuModal('edit');
    }

    function deleteSelectedMenu() {
        if (!canEdit || !selectedMenuId) return;
        if (!confirm('Yakin ingin menghapus menu "' + selectedMenuName + '"?')) return;

        $.ajax({
            url: '{{ url("/master-credential-role-menu-delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: selectedMenuId
            },
            success: function(res) {
                if (res.success) {
                    selectedMenuId = null;
                    selectedMenuName = null;
                    $('#edit-menu-name-btn').prop('disabled', true);
                    $('#delete-menu-btn').prop('disabled', true);
                    if (permDt) permDt.ajax.reload();
                } else {
                    alert(res.message || 'Error deleting menu');
                }
            },
            error: function() {
                alert('Request failed');
            }
        });
    }

</script>
    @endpush