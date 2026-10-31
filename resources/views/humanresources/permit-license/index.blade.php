@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Human Capital and General Affairs</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">GA</a></li>
        <li class="breadcrumb-item active"><span class="active">Permit &amp; License</span></li>
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

@if($expiring->count() > 0)
<div class="alert alert-warning mt-3">
    <h5 class="alert-heading mb-2">Masa Berlaku Akan Berakhir</h5>
    <p class="mb-2">{{ $expiring->count() }} perizinan akan berakhir dalam periode pengingatnya. Segera lakukan perpanjangan.</p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered bg-white mb-0">
            <thead>
                <tr>
                    <th>Nama Perizinan</th>
                    <th>Nomor</th>
                    <th>Kode</th>
                    <th>Berakhir</th>
                    <th>Sisa Hari</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiring as $item)
                @php $remaining = $reminderService->daysRemaining($item); @endphp
                <tr>
                    <td>{{ $item->Name }}</td>
                    <td>{{ $item->Permit_Number ?: '-' }}</td>
                    <td>{{ $item->Category_Code ?: '-' }}</td>
                    <td>{{ $reminderService->formatDate($item->Valid_To) }}</td>
                    <td>
                        @if($remaining === null)
                        -
                        @elseif($remaining === 0)
                        Hari ini
                        @else
                        {{ $remaining }} hari
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="PermitLicenseEditId({{ (int) $item->ID }});">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($expiredCount > 0)
<div class="alert alert-danger mt-3">
    <strong>{{ $expiredCount }} perizinan sudah kedaluwarsa.</strong>
    Periksa kolom Status pada daftar di bawah dan segera perbarui dokumennya.
</div>
@endif

<div class="card mt-3">
    <div class="card-body">
        <input type="button" class="btn btn-info mt-2 mr-2" value="Refresh" onclick="PermitLicenseRefresh();" />
        <input type="button" class="btn btn-success mt-2 mr-2" value="New Permit" onclick="PermitLicenseNew();" />
        <input type="button" class="btn btn-primary mt-2 mr-2" value="Edit" onclick="PermitLicenseEdit();" />
        <input type="button" class="btn btn-danger mt-2 mr-2" value="Delete" onclick="PermitLicenseDelete();" />
        <input type="button" class="btn btn-outline-info mt-2 mr-2" value="Lihat Dokumen" onclick="PermitLicenseDocument();" />
        <input type="button" class="btn btn-outline-primary mt-2 mr-2" value="Download" onclick="PermitLicenseDownload();" />
        <input type="button" class="btn btn-warning mt-2 mr-2" value="Sinkron Notifikasi" onclick="PermitLicenseSync();" />
        @if(can_menu('SI01007', 'edit') || (auth()->check() && auth()->user()->hasRole('ADM')))
        <a class="btn btn-outline-secondary mt-2 mr-2" href="{{ url('hrd-permit-license-mail-settings') }}">
            Template Email
            @if($mailEnabled)
            <span class="badge badge-success">Email Aktif{{ $mailRecipientCount > 0 ? ' (' . $mailRecipientCount . ')' : '' }}</span>
            @else
            <span class="badge badge-secondary">Email Nonaktif</span>
            @endif
        </a>
        @endif
    </div>
</div>

<div id="PermitLicenseGrid" style="width:auto;height:400px;background-color:white;overflow:hidden"></div>
<div id="PermitLicenseDetail" class="mt-3"></div>

<div class="mt-2">
    <span class="badge badge-success">AKTIF</span>
    <span class="badge badge-warning">AKAN BERAKHIR</span>
    <span class="badge badge-danger">KEDALUWARSA</span>
    <span class="badge badge-secondary">SESUAI KETENTUAN</span>
</div>

<form id="PermitLicenseDeleteForm" method="POST" action="{{ url('hrd-permit-license-delete') }}" style="display:none;">
    @csrf
    <input type="hidden" name="ID" id="PermitLicenseDeleteId" />
</form>

<form id="PermitLicenseSyncForm" method="POST" action="{{ url('hrd-permit-license-sync-reminder') }}" style="display:none;">
    @csrf
</form>

<script>
function PermitLicenseRefresh() {
    PermitLicenseGrid.clearAndLoad('hrd-permit-license-data');
}

function PermitLicenseSelectedId() {
    var id = PermitLicenseGrid.getSelectedRowId();
    if (id == null) {
        alert('Pilih baris terlebih dahulu.');
        return null;
    }
    return id;
}

function PermitLicenseNew() {
    LoadM('PermitLicenseDetail', 'hrd-permit-license-detail?ID=0');
    return false;
}

function PermitLicenseEdit() {
    var id = PermitLicenseSelectedId();
    if (id == null) return false;
    PermitLicenseEditId(id);
    return false;
}

function PermitLicenseEditId(id) {
    LoadM('PermitLicenseDetail', 'hrd-permit-license-detail?ID=' + encodeURIComponent(id));
    var detail = document.getElementById('PermitLicenseDetail');
    if (detail && detail.scrollIntoView) {
        detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    return false;
}

function PermitLicenseDelete() {
    var id = PermitLicenseSelectedId();
    if (id == null) return false;
    if (!confirm('Hapus data permit/license ini beserta dokumennya?')) return false;
    document.getElementById('PermitLicenseDeleteId').value = id;
    document.getElementById('PermitLicenseDeleteForm').submit();
    return false;
}

function PermitLicenseDocument() {
    var id = PermitLicenseSelectedId();
    if (id == null) return false;
    window.open('hrd-permit-license-document?ID=' + encodeURIComponent(id), '_blank');
    return false;
}

function PermitLicenseDownload() {
    var id = PermitLicenseSelectedId();
    if (id == null) return false;
    window.location.href = 'hrd-permit-license-download?ID=' + encodeURIComponent(id);
    return false;
}

function PermitLicenseSync() {
    if (!confirm('Buat notifikasi untuk semua izin yang mendekati masa berlaku?')) return false;
    document.getElementById('PermitLicenseSyncForm').submit();
    return false;
}

var PermitLicenseGrid = new dhtmlXGridObject('PermitLicenseGrid');
PermitLicenseGrid.setImagePath("./dhtmlx/imgs/");
PermitLicenseGrid.setHeader("No,Kode,Kategori,Nama Perizinan,Instansi,Nomor Perizinan,Tanggal Terbit,Masa Berlaku,Kontak PIC,Dokumen,Status");
PermitLicenseGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter");
PermitLicenseGrid.setInitWidths("50,70,160,240,190,150,110,210,170,80,180");
PermitLicenseGrid.setColAlign("center,center,left,left,left,left,center,left,left,center,center");
PermitLicenseGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
PermitLicenseGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PermitLicenseGrid.enableSmartRendering(true, 150);
PermitLicenseGrid.setSkin("dhx_skyblue");
PermitLicenseGrid.init();
PermitLicenseGrid.clearAndLoad('hrd-permit-license-data');
</script>

@if(session()->has('open_permit_detail'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    LoadM('PermitLicenseDetail', 'hrd-permit-license-detail?ID={{ (int) session('open_permit_detail') }}');
});
</script>
@endif
@endsection
