@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Escalation Queue
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="hrd-approval-inbox" style="color: rgba(255, 255, 255, 0.8) !important;">Approval Inbox</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Escalation Queue</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-triangle-exclamation me-1"></i> Held &amp; Escalation Monitoring
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif

<style>
.ap-card{border:0;border-radius:12px;box-shadow:0 1px 4px rgba(16,24,40,.08);overflow:hidden}
.ap-card .card-body{border-bottom:1px solid #eef0f3}
.ap-toolbar{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}
.ap-toolbar .btn{border-radius:8px;font-weight:500;display:inline-flex;align-items:center;gap:.4rem}
.ap-sep{width:1px;height:26px;background:#e5e7eb;margin:0 .25rem}
.ap-hint{font-size:.8rem;color:#6b7280}
.ap-sel{font-size:.8rem;padding:.25rem .6rem;border-radius:20px;background:#f3f4f6;color:#6b7280}
.ap-sel.on{background:#e0f2fe;color:#0369a1}
.ap-grid-wrap{background:#fff;padding:0 .75rem .75rem}
.ap-grid-wrap .objbox,.ap-grid-wrap .xhdr,.ap-grid-wrap .gridbox{border-left:0!important;border-right:0!important}
.ap-assign{background:#fafbfc;padding:1rem}
.ap-assign.disabled{opacity:.55}
</style>

<div class="card ap-card mt-3">
    <div class="card-body py-3">
        <div class="ap-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="ApprovalEscalationRefresh();" title="Muat ulang data"><i class="fa fa-refresh"></i> Refresh</button>
                <span class="ap-sep"></span>
                <a class="btn btn-outline-dark" href="hrd-approval-policy" title="Kelola policy approval"><i class="fa fa-sliders"></i> Policy Management</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="ApprovalEscalationSel" class="ap-sel"><i class="fa fa-info-circle me-1"></i>Belum ada baris dipilih</span>
                <a class="btn btn-light border" href="hrd-approval-inbox"><i class="fa fa-arrow-left"></i> Inbox</a>
            </div>
        </div>
        <div class="ap-hint mt-2"><i class="fa fa-lightbulb-o me-1"></i>Klik baris bertipe <b>HELD TASK</b> untuk memilih, lalu isi form <b>Assign Candidate</b> di bawah tabel. Gunakan filter di bawah header kolom untuk mencari.</div>
    </div>
    <div class="ap-grid-wrap">
        <div id="ApprovalEscalationGrid" style="width:100%;height:330px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div class="card ap-card mt-3">
    <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <div class="fw-semibold"><i class="fa fa-user-plus text-warning me-1"></i> Assign Candidate to Held Task</div>
            <div class="ap-hint">Tugaskan approver untuk task yang tertahan (HELD TASK).</div>
        </div>
    </div>
    <div class="ap-assign">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Approver (User / Karyawan)</label>
                <input type="text" id="ApprovalEscalationUserSearch" class="form-control form-control-sm mb-1" placeholder="Cari nama / NIK / posisi..." oninput="ApprovalEscalationFilterUsers();" disabled />
                <select id="ApprovalEscalationUserId" class="form-select" disabled>
                    <option value="">-- Pilih HELD TASK terlebih dahulu --</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Alasan</label>
                <input type="text" id="ApprovalEscalationReason" class="form-control" placeholder="Alasan penugasan (opsional)" />
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-warning w-100" style="border-radius:8px" onclick="ApprovalEscalationAssign();"><i class="fa fa-user-plus me-1"></i> Assign</button>
            </div>
        </div>
        <div class="ap-hint mt-2"><i class="fa fa-info-circle me-1"></i>Daftar berisi user ACTIVE (join data employment), tanpa requester dan kandidat yang sudah ditugaskan. Setelah di-assign, user menerima notifikasi lalu dapat <b>Claim</b> &amp; Approve/Reject dari Inbox. Task tetap berstatus HELD sampai di-claim.</div>
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
var ApprovalEscalationUsers = [];

function ApprovalEscalationRenderUsers(list) {
    var sel = document.getElementById('ApprovalEscalationUserId');
    var html = '<option value="">-- Pilih approver (' + list.length + ') --</option>';
    list.forEach(function (u) {
        var label = u.Name + ' (' + (u.Emp_ID || '-') + ')' + (u.Position ? ' - ' + u.Position : '') + (u.Department ? ' / ' + u.Department : '');
        html += '<option value="' + u.id + '">' + $('<div>').text(label).html() + '</option>';
    });
    sel.innerHTML = html;
}

function ApprovalEscalationFilterUsers() {
    var q = document.getElementById('ApprovalEscalationUserSearch').value.toLowerCase();
    ApprovalEscalationRenderUsers(ApprovalEscalationUsers.filter(function (u) {
        return [u.Name, u.Emp_ID, u.Position, u.Department].join(' ').toLowerCase().indexOf(q) !== -1;
    }));
}

function ApprovalEscalationLoadUsers(taskId) {
    var sel = document.getElementById('ApprovalEscalationUserId');
    var search = document.getElementById('ApprovalEscalationUserSearch');
    if (!taskId) {
        sel.disabled = true; search.disabled = true; search.value = '';
        sel.innerHTML = '<option value="">-- Pilih HELD TASK terlebih dahulu --</option>';
        return;
    }
    sel.innerHTML = '<option value="">Memuat...</option>';
    fetch('hrd-approval-candidate-options?task_id=' + encodeURIComponent(taskId), {
        credentials: 'same-origin', headers: {'Accept': 'application/json'}
    }).then(function (r) { return r.json(); }).then(function (res) {
        ApprovalEscalationUsers = res.data || [];
        sel.disabled = false; search.disabled = false; search.value = '';
        ApprovalEscalationRenderUsers(ApprovalEscalationUsers);
    }).catch(function () {
        sel.innerHTML = '<option value="">Gagal memuat daftar user</option>';
    });
}

ApprovalEscalation.attachEvent("onRowSelect", function(id) {
    var el = document.getElementById('ApprovalEscalationSel');
    var held = String(id).indexOf('task-') === 0;
    ApprovalEscalationLoadUsers(held ? String(id).replace('task-', '') : null);
    el.className = 'ap-sel on';
    el.innerHTML = held
        ? '<i class="fa fa-check-circle me-1"></i>HELD TASK dipilih: ' + $('<div>').text(ApprovalEscalation.cells(id, 2).getValue()).html()
        : '<i class="fa fa-info-circle me-1"></i>Baris ini bukan HELD TASK (tidak bisa di-assign)';
});
ApprovalEscalation.init();
ApprovalEscalation.clearAndLoad('hrd-approval-escalation-data');
</script>
@endsection