@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Policy Management
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="hrd-approval-inbox" style="color: rgba(255, 255, 255, 0.8) !important;">Approval Inbox</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Policy Management</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-sliders me-1"></i> Atur alur &amp; langkah persetujuan per tipe request
        </span>
    </div>
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

<style>
.ap-card{border:0;border-radius:12px;box-shadow:0 1px 4px rgba(16,24,40,.08);overflow:hidden}
.ap-card .card-body{border-bottom:1px solid #eef0f3}
.ap-toolbar{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between}
.ap-toolbar .btn{border-radius:8px;font-weight:500;display:inline-flex;align-items:center;gap:.4rem}
.ap-sep{width:1px;height:26px;background:#e5e7eb;margin:0 .25rem}
.ap-hint{font-size:.8rem;color:#6b7280}
.ap-sel{font-size:.8rem;padding:.25rem .6rem;border-radius:20px;background:#f3f4f6;color:#6b7280}
.ap-sel.on{background:#e0f2fe;color:#0369a1}
.ap-legend span{font-size:.75rem;color:#6b7280;margin-right:.9rem}
.ap-legend i{margin-right:.25rem}
.ap-grid-wrap{background:#fff;padding:0 .75rem .75rem}
.ap-grid-wrap .objbox,.ap-grid-wrap .xhdr,.ap-grid-wrap .gridbox{border-left:0!important;border-right:0!important}
</style>

<div class="card ap-card mt-3">
    <div class="card-body py-3">
        <div class="ap-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-success" onclick="ApprovalPolicyNew();" title="Buat policy baru"><i class="fa fa-plus"></i> New Policy</button>
                <span class="ap-sep"></span>
                <button type="button" class="btn btn-outline-primary" onclick="ApprovalPolicyEdit();" title="Edit policy terpilih"><i class="fa fa-pencil"></i> Edit</button>
                <button type="button" class="btn btn-outline-warning" onclick="ApprovalPolicyToggle();" title="Aktif/nonaktifkan policy terpilih"><i class="fa fa-power-off"></i> Toggle Active</button>
                <span class="ap-sep"></span>
                <button type="button" class="btn btn-outline-secondary" onclick="ApprovalPolicyRefresh();" title="Muat ulang data"><i class="fa fa-refresh"></i> Refresh</button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="ApprovalPolicySel" class="ap-sel"><i class="fa fa-info-circle me-1"></i>Belum ada policy dipilih</span>
                <a class="btn btn-light border" href="hrd-approval-inbox"><i class="fa fa-arrow-left"></i> Inbox</a>
            </div>
        </div>
        <div class="ap-hint mt-2"><i class="fa fa-lightbulb-o me-1"></i>Klik baris pada tabel untuk memilih policy, lalu gunakan <b>Edit</b> atau <b>Toggle Active</b>. Double-click untuk langsung mengedit. Gunakan filter di bawah header kolom untuk mencari.</div>
    </div>
    <div class="ap-grid-wrap">
        <div id="ApprovalPolicyGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
</div>
<div id="ApprovalPolicyDetail" class="mt-3"></div>

<form id="ApprovalPolicyToggleForm" method="POST" action="{{ url('hrd-approval-policy-toggle') }}" style="display:none;">
    @csrf
    <input type="hidden" name="ID" id="ApprovalPolicyToggleId" />
</form>

<script>
function ApprovalPolicyRefresh() {
    ApprovalPolicy.clearAndLoad('hrd-approval-policy-data');
}

function ApprovalPolicySelectedId() {
    var id = ApprovalPolicy.getSelectedRowId();
    if (id == null) {
        alert('Pilih policy terlebih dahulu.');
        return null;
    }
    return id;
}

function ApprovalPolicyNew() {
    LoadM('ApprovalPolicyDetail', 'hrd-approval-policy-detail?ID=0');
    return false;
}

function ApprovalPolicyEdit() {
    var id = ApprovalPolicySelectedId();
    if (id == null) return false;
    LoadM('ApprovalPolicyDetail', 'hrd-approval-policy-detail?ID=' + encodeURIComponent(id));
    return false;
}

function ApprovalPolicyToggle() {
    var id = ApprovalPolicySelectedId();
    if (id == null) return false;
    if (!confirm('Ubah status policy ini?')) return false;
    document.getElementById('ApprovalPolicyToggleId').value = id;
    document.getElementById('ApprovalPolicyToggleForm').submit();
    return false;
}

var ApprovalPolicy = new dhtmlXGridObject('ApprovalPolicyGrid');
ApprovalPolicy.setImagePath("./dhtmlx/imgs/");
ApprovalPolicy.setHeader("No,Code,Name,Request Type,Location,Company,Department,Version,Rejection,Vacancy,No Higher Level,Steps,Status");
ApprovalPolicy.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#select_filter");
ApprovalPolicy.setInitWidths("50,150,180,130,100,90,120,70,140,90,130,60,90");
ApprovalPolicy.setColAlign("center,left,left,left,center,center,left,center,center,center,center,center,center");
ApprovalPolicy.setColSorting("int,str,str,str,str,str,str,int,str,str,str,int,str");
ApprovalPolicy.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalPolicy.enableSmartRendering(true, 150);
ApprovalPolicy.setSkin("dhx_skyblue");
ApprovalPolicy.attachEvent("onRowSelect", function(id) {
    var name = ApprovalPolicy.cells(id, 2).getValue();
    var el = document.getElementById('ApprovalPolicySel');
    el.className = 'ap-sel on';
    el.innerHTML = '<i class="fa fa-check-circle me-1"></i>Dipilih: ' + $('<div>').text(name).html();
});
ApprovalPolicy.attachEvent("onRowDblClicked", function(id) {
    LoadM('ApprovalPolicyDetail', 'hrd-approval-policy-detail?ID=' + encodeURIComponent(id));
});
ApprovalPolicy.init();
ApprovalPolicy.clearAndLoad('hrd-approval-policy-data');
</script>
@endsection