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
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-sliders me-1"></i> Workflow Policy Configuration
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

<div class="card mt-3">
    <div class="card-body">
        <input type="button" class="btn btn-info mt-2 mr-2 me-2" value="Refresh" onclick="ApprovalPolicyRefresh();" />
        <input type="button" class="btn btn-success mt-2 mr-2 me-2" value="New Policy" onclick="ApprovalPolicyNew();" />
        <input type="button" class="btn btn-primary mt-2 mr-2 me-2" value="Edit" onclick="ApprovalPolicyEdit();" />
        <input type="button" class="btn btn-warning mt-2 mr-2 me-2" value="Toggle Active" onclick="ApprovalPolicyToggle();" />
        <a class="btn btn-secondary mt-2 mr-2 me-2" href="hrd-approval-inbox">Back to Inbox</a>
    </div>
</div>

<div id="ApprovalPolicyGrid" style="width:auto;height:350px;background-color:white;overflow:hidden"></div>
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
ApprovalPolicy.init();
ApprovalPolicy.clearAndLoad('hrd-approval-policy-data');
</script>
@endsection