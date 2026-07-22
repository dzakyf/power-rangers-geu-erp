<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$locations = DB::table('users')->select('Location')->distinct()->whereNotNull('Location')->pluck('Location');
?>

@extends('layout.mainlayout')

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
    <div class="card-body d-flex flex-column" style="height:420px;">
        <div class="flex-fill" style="min-height:0;">
            <div id="userGrid" style="width:100%;height:100%;background:#fff;overflow:hidden"></div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <div class="m-1">

                <button type="button" class="btn btn-primary " onclick="toggleManualCreate()">
                    <i class="fa fa-plus"></i> Manual Create User
                </button>

            </div>
            <div class="m-1">
                <button type="button" class="btn btn-success " onclick="toggleCandidateCreate()">
                    <i class="fa fa-plus"></i> Add Candidate
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 d-none" id="candidateCreateCard">
    <div class="card-header p-2">
        <h5 class="mb-0">Create Candidate Account</h5>
    </div>
    <div class="card-body d-flex flex-column" style="height:420px;">
        <div class="mb-2">
            <p class="text-muted mb-0">Double-click a candidate row to create their account. Default password: <code>12345678</code></p>
        </div>
        <div class="flex-fill" style="min-height:0;">
            <div id="candidateGrid" style="width:100%;height:100%;background:#fff;overflow:hidden"></div>
        </div>
        <div class="mt-3">
            <input type="button" class="btn btn-success" value="Create Account for Selected" onclick="createSelectedAccount()">
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

<script>
    var userGrid = new dhtmlXGridObject('userGrid');
    userGrid.setImagePath('{{ asset("dhtmlx/imgs/") }}/');
    userGrid.setHeader("No.,Username,Name,Email,Location,Position,Status");
    userGrid.setInitWidths("60,150,200,200,100,150,100");
    userGrid.setColAlign("center,left,left,left,center,center,center");
    userGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
    userGrid.setSkin("dhx_skyblue");
    userGrid.init();
    userGrid.load('{{ url("/xml-credential-user-list") }}', function() {
        userGrid.setSizes();
    });

    var candidateGrid = new dhtmlXGridObject('candidateGrid');
    candidateGrid.setImagePath('{{ asset("dhtmlx/imgs/") }}/');
    candidateGrid.setHeader("No.,Task ID,Name,Position,Email,Handphone,Apply Date");
    candidateGrid.setInitWidths("60,150,200,150,200,150,150");
    candidateGrid.setColAlign("center,left,left,left,left,left,center");
    candidateGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
    candidateGrid.setSkin("dhx_skyblue");
    candidateGrid.attachEvent("onRowDblClicked", function(id) {
        var taskId = candidateGrid.cells(id, 1).getValue();
        createAccount(taskId);
    });
    candidateGrid.init();
    candidateGrid.load('{{ url("/xml-candidate-without-account") }}', function() {
        candidateGrid.setSizes();
    });

    $(window).resize(function() {
        if (userGrid.entBox) userGrid.setSizes();
        if (candidateGrid.entBox) candidateGrid.setSizes();
    });

    function refreshData() {
        candidateGrid.clearAndLoad('{{ url("/xml-candidate-without-account") }}', function() {
            candidateGrid.setSizes();
        });
        userGrid.clearAndLoad('{{ url("/xml-credential-user-list") }}', function() {
            userGrid.setSizes();
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
                    refreshData();
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
        var id = candidateGrid.getSelectedId();
        if (!id) {
            alert('Please select a candidate row first.');
            return;
        }
        var taskId = candidateGrid.cells(id, 1).getValue();
        createAccount(taskId);
    }

    function toggleManualCreate() {
        $('#manualCreateCard').toggleClass('d-none');
    }

    function toggleCandidateCreate() {
        $('#candidateCreateCard').toggleClass('d-none');
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
                    userGrid.clearAndLoad('{{ url("/xml-credential-user-list") }}', function() {
                        userGrid.setSizes();
                    });
                } else {
                    alert(res);
                }
            },
            error: function() {
                alert('Error creating user!');
            }
        });
    });

    refreshData();
</script>
@endsection