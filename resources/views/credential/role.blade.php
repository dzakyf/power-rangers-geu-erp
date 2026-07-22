<?php use Illuminate\Support\Facades\Auth; $Saya = Auth::user()->username; ?>
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
        label { margin-bottom: 0 !important; }
        .role-row { cursor: pointer; }
        .role-row.selected { background-color: #d9edf7 !important; }
        .perm-editor-section { margin-top: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; }
        .no-role-msg { padding: 40px; text-align: center; color: #999; font-size: 16px; }
        .perm-checkbox { width: 18px; height: 18px; cursor: pointer; }
        .perm-checkbox:disabled { cursor: not-allowed; opacity: 0.5; }
        .filter-row { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .filter-row label { font-weight: 600; margin-right: 5px; }
        .filter-row select { width: 200px; }
        .adm-badge { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px; }
        #permission-table_wrapper { margin-top: 15px; }
        #permission-table thead th { border-top: 1px solid #dee2e6; }
        .filter-row { margin-bottom: 18px; }
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
                            <option value="__CREDENTIAL__">Credential</option>
                        </select>
                        <button id="toggle-all-btn" class="btn btn-sm btn-outline-primary" style="display:none;">Select All Visible</button>
                    </div>
                    <table id="permission-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Parent Menu</th>
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
                    <div style="margin-top:10px;text-align:right;">
                        <button id="save-permissions-btn" class="btn btn-success" style="display:none;">Save Permissions</button>
                        <span id="save-status" style="margin-left:10px;color:green;display:none;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
var canEdit = {{ Auth::user()->can('master.credential.role.edit') ? 'true' : 'false' }};
var selectedRoleId = null;
var selectedRoleName = null;
var permDt = null;
var permDataCache = {};
var currentPermState = {};

var rolesTable = $('#roles-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("master.credential.role.list") }}',
    columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'users_count' }
    ],
    ordering: true,
    paging: true,
    pageLength: 10,
    rowCallback: function(row, data) {
        $(row).addClass('role-row');
        $(row).on('click', function() {
            $('#roles-table tr').removeClass('selected');
            $(this).addClass('selected');
            loadPermissions(data.id, data.name);
        });
    }
});

function loadPermissions(roleId, roleName) {
    selectedRoleId = roleId;
    selectedRoleName = roleName;
    $('#selected-role-name').text('(' + roleName + ')');
    $('#no-role-message').hide();
    $('#permission-table-container').show();
    $('#perm-loading').show();
    $('#save-status').hide().text('');
    $('#module-filter').val('');

    var isAdm = (roleName === 'ADM');

    if (isAdm) {
        $('#role-badge').show();
        $('#save-permissions-btn').hide();
    } else {
        $('#role-badge').hide();
        if (canEdit) {
            $('#save-permissions-btn').show();
        } else {
            $('#save-permissions-btn').hide();
        }
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
                json.data.forEach(function(row) {
                    var state = {};
                    ['view', 'create', 'edit', 'delete', 'approve'].forEach(function(action) {
                        state[action] = !!row[action];
                    });
                    currentPermState[row.menu_id] = state;
                });
                return json.data;
            }
        },
        columns: [
            { data: 'parent_menu' },
            { data: 'menu_name' },
            { data: 'module' },
            {
                data: 'view',
                render: function(val, type, row) {
                    return renderCheckbox('view', row, isAdm);
                }
            },
            {
                data: 'create',
                render: function(val, type, row) {
                    return renderCheckbox('create', row, isAdm);
                }
            },
            {
                data: 'edit',
                render: function(val, type, row) {
                    return renderCheckbox('edit', row, isAdm);
                }
            },
            {
                data: 'delete',
                render: function(val, type, row) {
                    return renderCheckbox('delete', row, isAdm);
                }
            },
            {
                data: 'approve',
                render: function(val, type, row) {
                    return renderCheckbox('approve', row, isAdm);
                }
            }
        ],
        ordering: true,
        paging: true,
        pageLength: 25,
        columnDefs: [
            { targets: 2, visible: false }
        ],
        drawCallback: function() {
            bindCheckboxEvents(isAdm);
            updateToggleButton();
        },
        initComplete: function() {
            $('#perm-loading').hide();
        }
    });
}

function renderCheckbox(action, row, isAdm) {
    var menuId = row.menu_id;
    var checked = currentPermState[menuId] && currentPermState[menuId][action] !== undefined
        ? currentPermState[menuId][action]
        : !!row[action];
    var disabled = (isAdm || !canEdit) ? 'disabled' : '';
    return '<input type="checkbox" class="perm-checkbox" data-menu-id="' + menuId + '" data-action="' + action + '" ' + (checked ? 'checked' : '') + ' ' + disabled + '>';
}

function bindCheckboxEvents(isAdm) {
    if (isAdm || !canEdit) return;
    $('.perm-checkbox').off('change').on('change', function() {
        var menuId = $(this).data('menu-id');
        var action = $(this).data('action');
        if (!currentPermState[menuId]) currentPermState[menuId] = {};
        currentPermState[menuId][action] = $(this).is(':checked');
        updateToggleButton();
    });
}

function updateToggleButton() {
    var $visible = $('#permission-table tbody tr:visible .perm-checkbox:not(:disabled)');
    if ($visible.length === 0) {
        $('#toggle-all-btn').hide();
        return;
    }
    $('#toggle-all-btn').show();
    var allChecked = $visible.length === $visible.filter(':checked').length;
    $('#toggle-all-btn').text(allChecked ? 'Clear All Permissions' : 'Select All Visible');
}

$('#toggle-all-btn').on('click', function() {
    if (!canEdit) return;
    var label = $('#toggle-all-btn').text().trim();
    if (label === 'Clear All Permissions') {
        Object.keys(currentPermState).forEach(function(menuId) {
            ['view', 'create', 'edit', 'delete', 'approve'].forEach(function(action) {
                currentPermState[menuId][action] = false;
            });
        });
        permDt.draw(false);
        updateToggleButton();
    } else {
        var $visible = $('#permission-table tbody tr:visible .perm-checkbox:not(:disabled)');
        $visible.each(function() {
            $(this).prop('checked', true).trigger('change');
        });
    }
});

$('#module-filter').on('change', function() {
    var val = this.value;
    if (!permDt) return;

    if (val === '__CREDENTIAL__') {
        permDt.column(2).search('__NONE__').draw();
        return;
    }
    permDt.column(2).search(val).draw();
});

$('#save-permissions-btn').on('click', function() {
    if (!selectedRoleId) return;

    if (selectedRoleName === 'ADM') {
        $('#save-status').text('ADM permissions are immutable.').css('color', 'red').show();
        setTimeout(function() { $('#save-status').fadeOut(); }, 3000);
        return;
    }

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
});
</script>
@endpush
