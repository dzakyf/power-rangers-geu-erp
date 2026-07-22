<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$locations = DB::table('users')->select('Location')->distinct()->whereNotNull('Location')->pluck('Location');
?>

@extends('layout.mainlayout')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
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
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Position</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="toggleManualCreate()">
                <i class="fa fa-plus"></i> Manual Create User
            </button>
            <button type="button" class="btn btn-success" onclick="toggleCandidateCreate()">
                <i class="fa fa-plus"></i> Add Candidate
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
                        <option value="EMPLOYEE">Employee</option>
                        <option value="CANDIDATE">Candidate</option>
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
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
var candidateTable;

var usersTable = $('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ url("/master-credential-user-list") }}',
    columns: [
        { data: 'id' },
        { data: 'username' },
        { data: 'name' },
        { data: 'email' },
        { data: 'location' },
        { data: 'position' },
        { data: 'status' }
    ],
    ordering: true,
    paging: true,
    pageLength: 10
});

function loadCandidates() {
    if (candidateTable) {
        candidateTable.ajax.reload();
        return;
    }

    candidateTable = $('#candidates-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url("/master-credential-candidate-without-account") }}',
        columns: [
            { data: 'id' },
            { data: 'task_id' },
            { data: 'name' },
            { data: 'position' },
            { data: 'email' },
            { data: 'handphone' },
            { data: 'created_date' },
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
    var selected = candidateTable ? candidateTable.rows({ selected: true }).data() : [];
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
