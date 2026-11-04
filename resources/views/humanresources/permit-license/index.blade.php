@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Permit &amp; License Management
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">General Affairs</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Permit &amp; License</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-certificate me-1"></i> Legal Compliance &amp; Reminder
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-circle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-triangle-exclamation me-2"></i>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($expiring->count() > 0)
<div class="alert alert-warning alert-dismissible fade show shadow-sm mt-3" role="alert">
    <div class="d-flex align-items-center mb-2">
        <i class="fa fa-triangle-exclamation fs-18 text-warning me-2"></i>
        <h5 class="alert-heading fw-bold mb-0">Masa Berlaku Akan Berakhir</h5>
    </div>
    <p class="mb-2 small">Terdapat <strong>{{ $expiring->count() }} perizinan</strong> yang akan berakhir dalam periode pengingat. Segera lakukan perpanjangan dokumen.</p>
    <div class="table-responsive rounded border bg-white">
        <table class="table table-sm table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3">Nama Perizinan</th>
                    <th>Nomor</th>
                    <th>Kode</th>
                    <th>Berakhir</th>
                    <th>Sisa Hari</th>
                    <th class="text-center pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expiring as $item)
                @php $remaining = $reminderService->daysRemaining($item); @endphp
                <tr>
                    <td class="ps-3 fw-semibold">{{ $item->Name }}</td>
                    <td>{{ $item->Permit_Number ?: '-' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $item->Category_Code ?: '-' }}</span></td>
                    <td>{{ $reminderService->formatDate($item->Valid_To) }}</td>
                    <td>
                        @if($remaining === null)
                        <span class="text-muted">-</span>
                        @elseif($remaining === 0)
                        <span class="badge bg-danger text-white">Hari ini</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ $remaining }} hari lagi</span>
                        @endif
                    </td>
                    <td class="text-center pe-3">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="PermitLicenseEditId({{ (int) $item->ID }});">
                            <i class="fa fa-pen me-1"></i> Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($expiredCount > 0)
<div class="alert alert-danger alert-dismissible fade show shadow-sm mt-3 d-flex align-items-center" role="alert">
    <i class="fa fa-circle-xmark fs-20 text-danger me-2"></i>
    <div>
        <strong>{{ $expiredCount }} perizinan sudah kedaluwarsa.</strong>
        Periksa kolom Status pada daftar di bawah dan segera perbarui dokumennya.
    </div>
</div>
@endif

{{-- Toolbar Card --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="PermitLicenseRefresh();" title="Refresh">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-success btn-sm text-white" onclick="PermitLicenseNew();">
                    <i class="fa fa-plus me-1"></i> New Permit
                </button>
                <button type="button" class="btn btn-primary btn-sm text-white" onclick="PermitLicenseEdit();">
                    <i class="fa fa-pen me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-danger btn-sm text-white" onclick="PermitLicenseDelete();">
                    <i class="fa fa-trash me-1"></i> Delete
                </button>

                <div class="vr my-1 mx-1 text-muted opacity-50"></div>

                <button type="button" class="btn btn-outline-info btn-sm" onclick="PermitLicenseDocument();">
                    <i class="fa fa-file-lines me-1"></i> Lihat Dokumen
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="PermitLicenseDownload();">
                    <i class="fa fa-download me-1"></i> Download
                </button>
                <button type="button" class="btn btn-warning btn-sm text-dark" onclick="PermitLicenseSync();">
                    <i class="fa fa-bell me-1"></i> Sinkron Notifikasi
                </button>

                @if(can_menu('SI01007', 'edit') || (auth()->check() && auth()->user()->hasRole('ADM')))
                <a class="btn btn-outline-secondary btn-sm" href="{{ url('hrd-permit-license-mail-settings') }}">
                    <i class="fa fa-envelope me-1"></i> Template Email
                    @if($mailEnabled)
                    <span class="badge bg-success text-white ms-1">Aktif{{ $mailRecipientCount > 0 ? ' (' . $mailRecipientCount . ')' : '' }}</span>
                    @else
                    <span class="badge bg-secondary text-white ms-1">Nonaktif</span>
                    @endif
                </a>
                @endif
            </div>

            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-success text-white">AKTIF</span>
                <span class="badge bg-warning text-dark">AKAN BERAKHIR</span>
                <span class="badge bg-danger text-white">KEDALUWARSA</span>
                <span class="badge bg-secondary text-white">SESUAI KETENTUAN</span>
            </div>
        </div>
    </div>
</div>

{{-- dhtmlXGrid Container --}}
<div class="card shadow-sm border mb-3">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-dark fs-13">
            <i class="fa fa-list text-primary me-1"></i> Daftar Perizinan &amp; Lisensi Perusahaan
        </span>
        <small class="text-muted"><i class="fa fa-filter me-1"></i> Gunakan kolom header untuk mencari/memfilter</small>
    </div>
    <div class="card-body p-0">
        <div id="PermitLicenseGrid" style="width:100%;height:400px;background-color:white;overflow:hidden"></div>
    </div>
</div>

<div id="PermitLicenseDetail" class="mt-3"></div>

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
