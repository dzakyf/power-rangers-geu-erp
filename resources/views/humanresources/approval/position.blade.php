@extends('layout.mainlayout')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="hrd-approval-inbox">Approval</a></li>
        <li class="breadcrumb-item active"><span class="active">Position Assignment</span></li>
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
        <input type="button" class="btn btn-info mt-2 mr-2" value="Refresh" onclick="ApprovalPositionRefresh();" />
        <input type="button" class="btn btn-success mt-2 mr-2" value="New Assignment" onclick="ApprovalPositionNew();" />
        <input type="button" class="btn btn-primary mt-2 mr-2" value="Edit" onclick="ApprovalPositionEdit();" />
        <input type="button" class="btn btn-warning mt-2 mr-2" value="Toggle Active" onclick="ApprovalPositionToggle();" />
        <form method="POST" action="{{ url('hrd-approval-position-sync') }}" class="d-inline" onsubmit="return confirm('Sinkronkan node bagan ke master position dan hierarki approval?');">
            @csrf
            <button type="submit" class="btn btn-success mt-2 mr-2">Sync Bagan</button>
        </form>
        <a class="btn btn-info mt-2 mr-2" href="hrd-organization-chart">Buka Bagan Organisasi</a>
        <a class="btn btn-dark mt-2 mr-2" href="hrd-approval-policy">Policy Management</a>
        <a class="btn btn-secondary mt-2 mr-2" href="hrd-approval-inbox">Back to Inbox</a>
    </div>
</div>

<div id="ApprovalPositionGrid" style="width:auto;height:350px;background-color:white;overflow:hidden"></div>
<div id="ApprovalPositionDetail" class="mt-3"></div>

<form id="ApprovalPositionToggleForm" method="POST" action="{{ url('hrd-approval-position-toggle') }}" style="display:none;">
    @csrf
    <input type="hidden" name="ID" id="ApprovalPositionToggleId" />
</form>

<script>
function ApprovalPositionRefresh() {
    ApprovalPosition.clearAndLoad('hrd-approval-position-data');
}

function ApprovalPositionSelectedId() {
    var id = ApprovalPosition.getSelectedRowId();
    if (id == null) {
        alert('Pilih assignment terlebih dahulu.');
        return null;
    }
    return id;
}

function ApprovalPositionNew() {
    LoadM('ApprovalPositionDetail', 'hrd-approval-position-detail?ID=0');
    return false;
}

function ApprovalPositionEdit() {
    var id = ApprovalPositionSelectedId();
    if (id == null) return false;
    LoadM('ApprovalPositionDetail', 'hrd-approval-position-detail?ID=' + encodeURIComponent(id));
    return false;
}

function ApprovalPositionToggle() {
    var id = ApprovalPositionSelectedId();
    if (id == null) return false;
    if (!confirm('Ubah status assignment ini?')) return false;
    document.getElementById('ApprovalPositionToggleId').value = id;
    document.getElementById('ApprovalPositionToggleForm').submit();
    return false;
}

function ApprovalPositionOrgInfo(select) {
    var option = select.options[select.selectedIndex];
    var code = option ? (option.getAttribute('data-org-code') || '-') : '-';
    var level = option ? (option.getAttribute('data-org-level') || '-') : '-';
    var reports = option ? (option.getAttribute('data-reports-to') || '-') : '-';
    var orgId = option ? (option.getAttribute('data-org-id') || '-') : '-';

    if ($('#ApprovalPositionOrgCode').length) $('#ApprovalPositionOrgCode').text(code);
    if ($('#ApprovalPositionOrgLevel').length) $('#ApprovalPositionOrgLevel').text(level);
    if ($('#ApprovalPositionReportsTo').length) $('#ApprovalPositionReportsTo').text(reports);
    if ($('#ApprovalPositionOrgId').length) $('#ApprovalPositionOrgId').text(orgId);
}

var ApprovalPosition = new dhtmlXGridObject('ApprovalPositionGrid');
ApprovalPosition.setImagePath("./dhtmlx/imgs/");
ApprovalPosition.setHeader("No,Position,Level,Org Code,Reports To,User,Username,Type,Location,Company,Start,End,Status");
ApprovalPosition.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#select_filter");
ApprovalPosition.setInitWidths("50,220,60,110,200,170,130,100,100,90,100,100,90");
ApprovalPosition.setColAlign("center,left,center,center,left,left,left,center,center,center,center,center,center");
ApprovalPosition.setColSorting("int,str,int,str,str,str,str,str,str,str,str,str,str");
ApprovalPosition.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalPosition.enableSmartRendering(true, 150);
ApprovalPosition.setSkin("dhx_skyblue");
ApprovalPosition.init();
ApprovalPosition.clearAndLoad('hrd-approval-position-data');
</script>
@endsection