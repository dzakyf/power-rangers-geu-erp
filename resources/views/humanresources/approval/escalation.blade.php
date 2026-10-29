@extends('layout.mainlayout')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="hrd-approval-inbox">Approval</a></li>
        <li class="breadcrumb-item active"><span class="active">Escalation Queue</span></li>
    </ol>
</div>

@if(session('success'))
<div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif

<div class="card mt-3">
    <div class="card-body">
        <input type="button" class="btn btn-info mt-2 mr-2" value="Refresh" onclick="ApprovalEscalationRefresh();" />
        <a class="btn btn-secondary mt-2 mr-2" href="hrd-approval-inbox">Back to Inbox</a>
        <a class="btn btn-dark mt-2 mr-2" href="hrd-approval-policy">Policy Management</a>
        <a class="btn btn-primary mt-2 mr-2" href="hrd-approval-position">Position Assignment</a>
    </div>
</div>

<div id="ApprovalEscalationGrid" style="width:auto;height:330px;background-color:white;overflow:hidden"></div>

<div class="card mt-3">
    <div class="card-header"><strong>Assign Candidate to Held Task</strong></div>
    <div class="card-body">
        <p class="text-muted mb-2">Pilih baris bertipe <strong>HELD TASK</strong>, lalu isi User ID yang akan ditugaskan.</p>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>User ID</label>
                <input type="number" id="ApprovalEscalationUserId" class="form-control" min="1" />
            </div>
            <div class="form-group col-md-5">
                <label>Alasan</label>
                <input type="text" id="ApprovalEscalationReason" class="form-control" />
            </div>
            <div class="form-group col-md-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-warning btn-block" onclick="ApprovalEscalationAssign();">Assign</button>
            </div>
        </div>
    </div>
</div>

<script>
var ApprovalEscalationToken = @json(csrf_token());

function ApprovalEscalationRefresh() {
    ApprovalEscalation.clearAndLoad('hrd-approval-escalation-data');
}

function ApprovalEscalationSelectedTaskId() {
    var id = ApprovalEscalation.getSelectedRowId();
    if (id == null) {
        alert('Pilih baris terlebih dahulu.');
        return null;
    }
    if (id.indexOf('task-') !== 0) {
        alert('Assign candidate hanya untuk baris HELD TASK.');
        return null;
    }
    return id.replace('task-', '');
}

function ApprovalEscalationAssign() {
    var taskId = ApprovalEscalationSelectedTaskId();
    if (taskId == null) return false;
    var userId = document.getElementById('ApprovalEscalationUserId').value;
    var reason = document.getElementById('ApprovalEscalationReason').value;
    if (!userId) {
        alert('User ID wajib diisi.');
        return false;
    }

    var body = new URLSearchParams();
    body.append('_token', ApprovalEscalationToken);
    body.append('Task_ID', taskId);
    body.append('User_Id', userId);
    body.append('Reason', reason);

    fetch('hrd-approval-add-candidate', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': ApprovalEscalationToken,
            'Accept': 'application/json'
        },
        body: body.toString()
    }).then(function (response) {
        return response.json().then(function (payload) {
            return { ok: response.ok, payload: payload };
        });
    }).then(function (result) {
        if (!result.ok || result.payload.status === 'error') {
            alert(result.payload.message || 'Gagal menambahkan kandidat.');
            return;
        }
        alert(result.payload.message || 'Kandidat ditambahkan.');
        ApprovalEscalationRefresh();
    }).catch(function () {
        alert('Tidak dapat menghubungi server.');
    });

    return false;
}

var ApprovalEscalation = new dhtmlXGridObject('ApprovalEscalationGrid');
ApprovalEscalation.setImagePath("./dhtmlx/imgs/");
ApprovalEscalation.setHeader("No,Type,Request No,Request Type,Position / Stage,Reason,Requester,Since");
ApprovalEscalation.attachHeader("&nbsp;,#select_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ApprovalEscalation.setInitWidths("50,130,150,140,260,260,160,150");
ApprovalEscalation.setColAlign("center,center,center,left,left,left,left,center");
ApprovalEscalation.setColSorting("int,str,str,str,str,str,str,str");
ApprovalEscalation.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalEscalation.enableSmartRendering(true, 150);
ApprovalEscalation.setSkin("dhx_skyblue");
ApprovalEscalation.init();
ApprovalEscalation.clearAndLoad('hrd-approval-escalation-data');
</script>
@endsection