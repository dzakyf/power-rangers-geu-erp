@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem;">
            Approval Delegation Management
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="hrd-approval-inbox" style="color: rgba(255,255,255,0.8) !important;">Approval Inbox</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Delegation</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a class="btn btn-light btn-sm" href="hrd-approval-inbox"><i class="fa fa-arrow-left me-1"></i> Kembali ke Inbox</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
    <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="fa fa-user-plus me-1 text-primary"></i> Tambah Delegasi Approval</h6>
        <form id="DelegationForm" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label mb-1">Delegator (Pemberi Wewenang)</label>
                @if($isAdmin)
                <select name="Delegator_Id" class="form-select form-select-sm" required>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (int)$u->id === (int)$currentUser->id ? 'selected' : '' }}>
                            {{ $u->Name ?: $u->username }} ({{ $u->Department ?: '-' }})
                        </option>
                    @endforeach
                </select>
                @else
                <input type="hidden" name="Delegator_Id" value="{{ $currentUser->id }}">
                <input type="text" class="form-control form-control-sm" value="{{ $currentUser->Name ?: $currentUser->username }}" readonly>
                @endif
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Delegate (Penerima Wewenang)</label>
                <select name="Delegate_Id" class="form-select form-select-sm" required>
                    <option value="">-- Pilih Penerima --</option>
                    @foreach($users as $u)
                        @if((int)$u->id !== (int)$currentUser->id || $isAdmin)
                        <option value="{{ $u->id }}">{{ $u->Name ?: $u->username }} ({{ $u->Department ?: '-' }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Jabatan (Opsional)</label>
                <select name="Position_ID" class="form-select form-select-sm">
                    <option value="">Semua Jabatan</option>
                    @foreach($positions as $p)
                        <option value="{{ $p->ID }}">{{ $p->Initial ? $p->Initial . ' - ' : '' }}{{ $p->Position }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Request Type</label>
                <select name="Request_Type" class="form-select form-select-sm">
                    <option value="">SEMUA TIPE</option>
                    @foreach($requestTypes as $rt)
                        <option value="{{ $rt }}">{{ $rt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Mulai</label>
                <input type="date" name="Start_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Selesai</label>
                <input type="date" name="End_Date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Location</label>
                <input type="text" name="Location" class="form-control form-control-sm" value="{{ $currentUser->Location ?? 'GEU-HO' }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Company</label>
                <input type="text" name="Company_ID" class="form-control form-control-sm" value="{{ $currentUser->Company_ID ?? 'GEU' }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1">Alasan</label>
                <input type="text" name="Reason" class="form-control form-control-sm" placeholder="Contoh: Cuti tahunan / Dinas luar">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="DelegationRefresh();"><i class="fa fa-refresh me-1"></i> Refresh</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="DelegationRevoke();"><i class="fa fa-ban me-1"></i> Cabut Delegasi Terpilih</button>
            </div>
            <span id="DelegationSelectedHint" class="badge bg-light text-dark border">Belum ada baris dipilih</span>
        </div>
        <div id="DelegationGridBox" style="width:100%; height:420px; background:#fff;"></div>
    </div>
</div>

<script>
var delegationGrid;
document.addEventListener('DOMContentLoaded', function () {
    delegationGrid = new dhtmlXGridObject('DelegationGridBox');
    delegationGrid.setImagePath('{{ asset("dhtmlx/imgs") }}/');
    delegationGrid.setHeader('No,Delegator,Delegate,Jabatan,Request Type,Scope,Periode,Status,Alasan');
    delegationGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter,#text_filter');
    delegationGrid.setInitWidths('45,170,170,150,140,120,165,90,*');
    delegationGrid.setColAlign('center,left,left,left,left,left,center,center,left');
    delegationGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro');
    delegationGrid.setSkin('dhx_skyblue');
    delegationGrid.init();
    delegationGrid.attachEvent('onRowSelect', function (id) {
        var st = delegationGrid.getUserData(id, 'status') || '-';
        document.getElementById('DelegationSelectedHint').innerText = 'ID #' + id + ' (' + st + ')';
    });
    DelegationRefresh();

    document.getElementById('DelegationForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        fetch('hrd-approval-delegation-save', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (res.status === 'success') {
                alert(res.message);
                DelegationRefresh();
            } else {
                alert(res.message || 'Gagal menyimpan delegasi.');
            }
        }).catch(function () { alert('Terjadi kesalahan jaringan.'); });
    });
});

function DelegationRefresh() {
    delegationGrid.clearAndLoad('hrd-approval-delegation-data');
}

function DelegationRevoke() {
    var id = delegationGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris delegasi terlebih dahulu.');
        return;
    }
    if (!confirm('Cabut delegasi ID #' + id + '?')) {
        return;
    }
    var fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('ID', id);
    fetch('hrd-approval-delegation-revoke', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    }).then(function (r) { return r.json(); }).then(function (res) {
        alert(res.message || 'Selesai');
        if (res.status === 'success') {
            DelegationRefresh();
        }
    }).catch(function () { alert('Terjadi kesalahan jaringan.'); });
}
</script>
@endsection
