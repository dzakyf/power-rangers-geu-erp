@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Approval</a></li>
        <li class="breadcrumb-item active"><span class="active">Approval Inbox</span></li>
    </ol>
</div>

@if(session('success'))
<div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger mt-3">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card mt-3">
    <div class="card-body">
        <input type="button" class="btn btn-info mt-2 mr-2 me-2" value="Refresh" onclick="ApprovalInboxRefresh();" />
        <input type="button" class="btn btn-primary mt-2 mr-2 me-2" value="Detail" onclick="ApprovalInboxDetail();" />
        <input type="button" class="btn btn-success mt-2 mr-2 me-2" value="Claim" onclick="ApprovalInboxClaim();" />
        <input type="button" class="btn btn-primary mt-2 mr-2 me-2" value="Approve" onclick="ApprovalInboxAct('APPROVE');" />
        <input type="button" class="btn btn-danger mt-2 mr-2 me-2" value="Reject" onclick="ApprovalInboxAct('REJECT');" />
        @if($isAdmin)
        <input type="button" class="btn btn-warning mt-2 mr-2 me-2" value="Release Claim" onclick="ApprovalInboxRelease();" />
        <a class="btn btn-secondary mt-2 mr-2 me-2" href="hrd-approval-escalation">Escalation Queue</a>
        <a class="btn btn-dark mt-2 mr-2 me-2" href="hrd-approval-policy">Policy Management</a>
        <a class="btn btn-info mt-2 mr-2 me-2" href="hrd-organization-chart">Bagan Organisasi</a>
        @endif
    </div>
</div>

<div id="ApprovalInboxGrid" style="width:auto;height:380px;background-color:white;overflow:hidden"></div>
<div id="ApprovalInboxDetail" class="mt-3"></div>

<script>
var ApprovalInboxToken = @json(csrf_token());

function ApprovalInboxRefresh() {
    ApprovalInbox.clearAndLoad('hrd-approval-inbox-data');
}

function ApprovalInboxSelectedId() {
    var id = ApprovalInbox.getSelectedRowId();
    if (id == null) {
        alert('Pilih baris terlebih dahulu.');
        return null;
    }
    return id;
}

function ApprovalInboxDetail() {
    var id = ApprovalInboxSelectedId();
    if (id == null) return false;
    LoadM('ApprovalInboxDetail', 'hrd-approval-detail?ID=' + encodeURIComponent(id));
    return false;
}

function ApprovalInboxPost(url, data, onSuccess) {
    var body = new URLSearchParams();
    body.append('_token', ApprovalInboxToken);
    Object.keys(data).forEach(function (key) {
        body.append(key, data[key]);
    });

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': ApprovalInboxToken,
            'Accept': 'application/json'
        },
        body: body.toString()
    }).then(function (response) {
        return response.json().then(function (payload) {
            return { ok: response.ok, payload: payload };
        });
    }).then(function (result) {
        if (!result.ok || result.payload.status === 'error') {
            alert(result.payload.message || 'Aksi approval gagal.');
            return;
        }
        alert(result.payload.message || 'Aksi approval berhasil.');
        ApprovalInboxRefresh();
        if (typeof onSuccess === 'function') onSuccess(result.payload);
    }).catch(function () {
        alert('Tidak dapat menghubungi server.');
    });
}

function ApprovalInboxClaim() {
    var id = ApprovalInboxSelectedId();
    if (id == null) return false;
    ApprovalInboxPost('hrd-approval-claim', { Task_ID: id }, function () {
        ApprovalInboxDetail();
    });
    return false;
}

function ApprovalInboxAct(action) {
    var id = ApprovalInboxSelectedId();
    if (id == null) return false;
    var notes = prompt('Catatan ' + action + ' (opsional):', '');
    if (notes === null) return false;
    ApprovalInboxPost('hrd-approval-act', { Task_ID: id, Action: action, Notes: notes }, function () {
        ApprovalInboxDetail();
    });
    return false;
}

function ApprovalInboxRelease() {
    var id = ApprovalInboxSelectedId();
    if (id == null) return false;
    var reason = prompt('Alasan release claim (wajib):', '');
    if (reason === null) return false;
    if (reason.trim() === '') {
        alert('Alasan release wajib diisi.');
        return false;
    }
    ApprovalInboxPost('hrd-approval-release', { Task_ID: id, Reason: reason }, function () {
        ApprovalInboxDetail();
    });
    return false;
}

var ApprovalInbox = new dhtmlXGridObject('ApprovalInboxGrid');
ApprovalInbox.setImagePath("./dhtmlx/imgs/");
ApprovalInbox.setHeader("No,Request No,Type,Position,Stage,Level,Mode,Task Status,Requester,Location,Submitted");
ApprovalInbox.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#text_filter");
ApprovalInbox.setInitWidths("50,150,130,220,60,60,110,130,150,100,150");
ApprovalInbox.setColAlign("center,center,center,left,center,center,center,center,left,center,center");
ApprovalInbox.setColSorting("int,str,str,str,int,int,str,str,str,str,str");
ApprovalInbox.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalInbox.enableSmartRendering(true, 150);
ApprovalInbox.setSkin("dhx_skyblue");
ApprovalInbox.init();
ApprovalInbox.clearAndLoad('hrd-approval-inbox-data');
</script>
@endsection