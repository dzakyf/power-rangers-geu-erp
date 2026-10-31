<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$locations = DB::table('users')->select('Location')->distinct()->whereNotNull('Location')->pluck('Location');
?>

@extends('layout.mainlayout-credential')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
<style>
    .user-row {
        cursor: pointer;
    }

    .user-row.selected {
        background-color: #d9edf7 !important;
    }
</style>
@endpush

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Credential Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Credential</a></li>
        <li class="breadcrumb-item active"><span>User Management</span></li>
    </ol>
</div>

<div class="card mb-4">
    <div class="card-header p-2">
        <h5 class="mb-0">All Users</h5>
    </div>
    <div class="card-body">
        <div style="overflow-x:auto;">
            <table id="users-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Posisi</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="btn btn-primary m-1" onclick="toggleManualCreate()">
                <i class="fa fa-plus"></i> Manual Create User
            </button>
            <button type="button" class="btn btn-success m-1" onclick="toggleCandidateCreate()">
                <i class="fa fa-plus"></i> Add Candidate
            </button>
            <button type="button" class="btn btn-warning m-1" id="editUserBtn" onclick="openEditModal()" disabled>
                <i class="fa fa-pencil"></i> Edit User
            </button>
            <button type="button" class="btn btn-danger m-1" id="deleteUserBtn" onclick="deleteUser()" disabled>
                <i class="fa fa-trash"></i> Delete User
            </button>
        </div>
    </div>
</div>

<div class="card mb-4 d-none" id="candidateCreateCard">
    <div class="card-header p-2">
        <h5 class="mb-0">Create Candidate Account</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">Click the Create button on a candidate row to create their account. Default password: <code>12345678</code></p>
        <div style="overflow-x:auto;">
            <table id="candidates-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Task ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Email</th>
                        <th>Handphone</th>
                        <th>Apply Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-success" onclick="createSelectedAccount()">Create Account for Selected</button>
            <div id="accountResult" class="mt-2"></div>
        </div>
    </div>
</div>

<div class="card d-none" id="manualCreateCard">
    <div class="card-header p-2">
        <h5 class="mb-0">Manual Create User</h5>
    </div>
    <div class="card-body">
        <form id="createUserForm">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="email">Email / Username <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="Name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="Name" name="Name" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="password">Password</label>
                    <input type="text" id="password" name="password" class="form-control" value="12345678">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                        <label for="Position">Position</label>
                    <select id="Position" name="Position" class="form-control">
                        @foreach($roles as $roleName)
                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="Location">Location</label>
                    <select id="Location" name="Location" class="form-control">
                        @foreach($locations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Create User</button>
                </div>
            </div>
        </form>
        <div id="createResult" class="mt-2"></div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    @csrf
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input type="text" id="editUsername" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Name</label>
                            <input type="text" id="editName" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" id="editEmail" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Location</label>
                            <select id="editLocation" name="location" class="form-control">
                                @foreach($locations as $loc)
                                <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select id="editStatus" name="status" class="form-control">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Posisi (Primary Role)</label>
                            <select id="editPrimaryRole" name="primary_role" class="form-control">
                                @foreach($roles as $roleName)
                                <option value="{{ $roleName }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label>Additional Roles (Secondary Role)</label>
                            <div id="additionalRolesContainer" class="row">
                                @foreach($roles as $roleName)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input additional-role-cb" id="ar_{{ $roleName }}" value="{{ $roleName }}">
                                        <label class="form-check-label" for="ar_{{ $roleName }}">{{ $roleName }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Role tambahan (Secondary Role)</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span id="editStatusMsg" style="color:green;display:none;"></span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEditUser()">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
    var candidateTable;
    var selectedUserData = null;

    var usersTable = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("/master-credential-user-list") }}',
        columns: [{
                data: 'no'
            },
            {
                data: 'username'
            },
            {
                data: 'name'
            },
            {
                data: 'email'
            },
            {
                data: 'location'
            },
            {
                data: 'posisi'
            },
            {
                data: 'status'
            }
        ],
        ordering: true,
        paging: true,
        pageLength: 10,
        rowCallback: function(row, data) {
            $(row).addClass('user-row');
            $(row).on('click', function() {
                $('#users-table tr').removeClass('selected');
                $(this).addClass('selected');
                selectedUserData = data;
                $('#editUserBtn').prop('disabled', false);
                $('#deleteUserBtn').prop('disabled', false);
            });
        }
    });

    function openEditModal() {
        if (!selectedUserData) return;

        var data = selectedUserData;
        $('#editUserId').val(data.id);
        $('#editUsername').val(data.username);
        $('#editName').val(data.name);
        $('#editEmail').val(data.email || '');
        $('#editLocation').val(data.location || '');
        $('#editStatus').val(data.status || 'ACTIVE');
        $('#editPrimaryRole').val(data.priv_id || '');

        $('#additionalRolesContainer .additional-role-cb').prop('checked', false);
        if (data.roles && Array.isArray(data.roles)) {
            data.roles.forEach(function(r) {
                if (r !== data.priv_id) {
                    $('#ar_' + r).prop('checked', true);
                }
            });
        }

        $('#editStatusMsg').hide().text('');
        $('#editUserModal').modal('show');
    }

    function saveEditUser() {
        var userId = $('#editUserId').val();
        var name = $('#editName').val().trim();
        var email = $('#editEmail').val().trim();
        var location = $('#editLocation').val();
        var status = $('#editStatus').val();
        var primaryRole = $('#editPrimaryRole').val();

        if (!name) {
            $('#editStatusMsg').text('Name is required').css('color', 'red').show();
            return;
        }

        var additionalRoles = [];
        $('#additionalRolesContainer .additional-role-cb:checked').each(function() {
            additionalRoles.push($(this).val());
        });

        // Don't send primary_role in additional_roles
        additionalRoles = additionalRoles.filter(function(r) {
            return r !== primaryRole;
        });

        $('#editStatusMsg').text('Saving...').css('color', 'blue').show();

        $.ajax({
            url: '{{ url("/master-credential-user-update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: userId,
                name: name,
                email: email,
                location: location,
                status: status,
                primary_role: primaryRole,
                additional_roles: additionalRoles
            },
            success: function(response) {
                if (response.success) {
                    $('#editStatusMsg').text('Saved successfully!').css('color', 'green').show();
                    setTimeout(function() {
                        $('#editUserModal').modal('hide');
                        usersTable.ajax.reload();
                    }, 800);
                } else {
                    $('#editStatusMsg').text(response.message || 'Error saving').css('color', 'red').show();
                }
            },
            error: function(xhr) {
                var msg = 'Error saving user';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#editStatusMsg').text(msg).css('color', 'red').show();
            }
        });
    }

    function deleteUser() {
        if (!selectedUserData) return;

        if (!confirm('Are you sure you want to deactivate user "' + selectedUserData.username + '"? This will set their status to INACTIVE.')) {
            return;
        }

        $.ajax({
            url: '{{ url("/master-credential-user-delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: selectedUserData.id
            },
            success: function(response) {
                if (response.success) {
                    selectedUserData = null;
                    $('#editUserBtn').prop('disabled', true);
                    $('#deleteUserBtn').prop('disabled', true);
                    usersTable.ajax.reload();
                } else {
                    alert(response.message || 'Error deleting user');
                }
            },
            error: function() {
                alert('Error deleting user');
            }
        });
    }

    function loadCandidates() {
        if (candidateTable) {
            candidateTable.ajax.reload();
            return;
        }

        candidateTable = $('#candidates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ url("/master-credential-candidate-without-account") }}',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'task_id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'position'
                },
                {
                    data: 'email'
                },
                {
                    data: 'handphone'
                },
                {
                    data: 'created_date'
                },
                {
                    data: null,
                    render: function(row) {
                        var btn = document.createElement('button');
                        btn.className = 'btn btn-sm btn-success create-account-btn';
                        btn.textContent = 'Create';
                        btn.dataset.taskId = row.task_id;
                        btn.addEventListener('click', function() {
                            createAccount(this.dataset.taskId);
                        });
                        return btn.outerHTML;
                    }
                }
            ],
            ordering: true,
            paging: true,
            pageLength: 10
        });
    }

    function createAccount(taskId) {
        if (!confirm('Create account for ' + taskId + '?')) return;
        $.ajax({
            url: '{{ url("/credential-create-candidate-account") }}',
            type: 'POST',
            data: {
                Task_ID: taskId,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.indexOf('success') !== -1) {
                    alert('Account created! ' + (res.split(':')[1] || ''));
                    if (candidateTable) candidateTable.ajax.reload();
                    $('#candidateCreateCard').addClass('d-none');
                } else {
                    alert(res.split(':')[1] || res);
                }
            },
            error: function() {
                alert('Error creating account!');
            }
        });
    }

    function createSelectedAccount() {
        var selected = candidateTable ? candidateTable.rows({
            selected: true
        }).data() : [];
        if (selected.length === 0) {
            alert('Please select a candidate row first.');
            return;
        }
        createAccount(selected[0].task_id);
    }

    function toggleManualCreate() {
        $('#manualCreateCard').toggleClass('d-none');
    }

    function toggleCandidateCreate() {
        $('#candidateCreateCard').toggleClass('d-none');
        if (!$('#candidateCreateCard').hasClass('d-none')) {
            loadCandidates();
        }
    }

    $('#createUserForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/credential-create-user-submit") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.indexOf('success') !== -1) {
                    alert('User created!');
                    $('#createUserForm')[0].reset();
                    $('#manualCreateCard').addClass('d-none');
                    usersTable.ajax.reload();
                } else {
                    alert(res);
                }
            },
            error: function() {
                alert('Error creating user!');
            }
        });
    });
</script>
@endpush