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
    <div class="card-header"><h5>All Users</h5></div>
    <div class="card-body">
        <div id="userGrid" style="width:100%;height:300px;background:#fff;overflow:hidden"></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5>Create Candidate Account</h5></div>
    <div class="card-body">
        <p class="text-muted">Double-click a candidate row to create their account. Default password: <code>12345678</code></p>
        <div id="candidateGrid" style="width:100%;height:250px;background:#fff;overflow:hidden"></div>
        <div class="mt-3">
            <input type="button" class="btn btn-success" value="Create Account for Selected" onclick="createSelectedAccount()">
            <div id="accountResult" class="mt-2"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>Manual Create User</h5></div>
    <div class="card-body">
        <form id="createUserForm" class="form-inline">
            @csrf
            <div class="form-group mr-2 mb-2">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group mr-2 mb-2">
                <input type="text" name="Name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="form-group mr-2 mb-2">
                <input type="email" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="form-group mr-2 mb-2">
                <input type="text" name="password" class="form-control" placeholder="Password" value="12345678">
            </div>
            <div class="form-group mr-2 mb-2">
                <select name="Position" class="form-control">
                    <option value="EMPLOYEE">Employee</option>
                    <option value="CANDIDATE">Candidate</option>
                </select>
            </div>
            <div class="form-group mr-2 mb-2">
                <select name="Location" class="form-control">
                    @foreach($locations as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2">Create User</button>
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
userGrid.load('{{ url("/xml-credential-user-list") }}');

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

function refreshData() {
    candidateGrid.clearAndLoad('{{ url("/xml-candidate-without-account") }}');
    userGrid.clearAndLoad('{{ url("/xml-credential-user-list") }}');
}

function createAccount(taskId) {
    if (!confirm('Create account for ' + taskId + '?')) return;
    $.ajax({
        url: '{{ url("/credential-create-candidate-account") }}',
        type: 'POST',
        data: { Task_ID: taskId, _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.indexOf('success') !== -1) {
                alert('Account created! ' + (res.split(':')[1] || ''));
                refreshData();
            } else {
                alert(res.split(':')[1] || res);
            }
        },
        error: function() { alert('Error creating account!'); }
    });
}

function createSelectedAccount() {
    var id = candidateGrid.getSelectedId();
    if (!id) { alert('Please select a candidate row first.'); return; }
    var taskId = candidateGrid.cells(id, 1).getValue();
    createAccount(taskId);
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
                userGrid.clearAndLoad('{{ url("/xml-credential-user-list") }}');
            } else {
                alert(res);
            }
        },
        error: function() { alert('Error creating user!'); }
    });
});

refreshData();
</script>
@endsection
