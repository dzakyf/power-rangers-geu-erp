@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-3 pt-1 pb-2 flex-wrap gap-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Lists (HCGA)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; General Affairs</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Asset &amp; Vehicle Management</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Asset Lists</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="hrd-vehicle-management-index" class="btn btn-sm fw-semibold shadow-sm" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-car me-1"></i> Vehicle Management &amp; Reminder
            </a>
            <a href="hrd-vehicle-operations" class="btn btn-sm fw-semibold shadow-sm" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-truck me-1"></i> Logbook &amp; Fuel Tracking
            </a>
            <a href="hrd-asset-ticketing-index" class="btn btn-light btn-sm fw-bold text-primary shadow-sm">
                <i class="fa fa-ticket-alt me-1"></i> Asset Ticketing &amp; Lifecycle Hub
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row mt-1">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Aset</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summary['total'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                        <i class="fa fa-archive fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Aset Aktif</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ number_format($summary['active'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                        <i class="fa fa-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Maintenance / Rusak</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($summary['attention'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                        <i class="fa fa-gears fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Kendaraan (Vehicle)</div>
                        <div class="fs-4 fw-bold text-info mt-1">{{ number_format($summary['vehicle'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-white d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                        <i class="fa fa-car fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa fa-table me-2 text-primary"></i>Master Data Assets</h5>
                <small class="text-muted">Klik dua kali pada baris untuk melihat atau memperbarui detail aset.</small>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <select id="filterSubCategory" class="form-select form-select-sm" style="width:auto" onchange="DATAASSETSRefresh();">
                    <option value="">Semua Sub Category</option>
                    <option value="VEHICLE">VEHICLE (Kendaraan)</option>
                </select>
                <select id="filterStatus" class="form-select form-select-sm" style="width:auto" onchange="DATAASSETSRefresh();">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="NEW">NEW</option>
                    <option value="MAINTENANCE">MAINTENANCE</option>
                    <option value="DISPOSED">DISPOSED</option>
                    <option value="INACTIVE">INACTIVE</option>
                </select>
                <select id="filterKondisi" class="form-select form-select-sm" style="width:auto" onchange="DATAASSETSRefresh();">
                    <option value="">Semua Kondisi</option>
                    <option value="BAIK">BAIK</option>
                    <option value="RUSAK">RUSAK</option>
                </select>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetAssetFilters();" title="Reset Filter">
                    <i class="fa fa-undo"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="DATAASSETS" style="width:auto;height:400px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="dxdata" class="btn btn-primary btn-sm fw-bold" onclick="NEWDATA();">
                        <i class="fa fa-plus me-1"></i> New Data
                    </button>
                    <button type="button" id="DATAASSETS_Detail" class="btn btn-secondary btn-sm" onclick="DATAASSETSDBL();">
                        <i class="fa fa-eye me-1"></i> Detail Data
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="DATAASSETS_Refresh" class="btn btn-info btn-sm text-white" onclick="DATAASSETSRefresh();">
                        <i class="fa fa-sync me-1"></i> Refresh Data
                    </button>
                    <button type="button" id="ekselDATAASSETSDet" class="btn btn-warning btn-sm" onclick="DATAASSETS.toExcel('./export/generate.php');">
                        <i class="fa fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="DATAASSETSDTL" class="mt-3"></div>

<script>
function notifyAsset(icon, title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: icon, title: title, text: text, confirmButtonText: 'OK' });
    } else {
        alert((title ? title + ': ' : '') + (text || ''));
    }
}

function loadAssetPanel(url, loadingLabel) {
    var $target = $('#DATAASSETSDTL');
    $target.html(
        '<div class="card border-0 shadow-sm mb-4">' +
        '  <div class="card-body text-center py-5">' +
        '    <div class="spinner-border text-primary mb-2" role="status"></div>' +
        '    <div class="text-muted small fw-semibold">' + (loadingLabel || 'Memuat data...') + '</div>' +
        '  </div>' +
        '</div>'
    );

    var targetEl = document.getElementById('DATAASSETSDTL');
    if (targetEl && typeof targetEl.scrollIntoView === 'function') {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(html) {
            $target.html(html);
        },
        error: function(xhr) {
            var errMsg = 'Terjadi kesalahan saat memuat panel (' + xhr.status + ' ' + xhr.statusText + ').';
            if (xhr.responseText && xhr.responseText.indexOf('alert-danger') !== -1) {
                $target.html(xhr.responseText);
                return;
            }
            $target.html(
                '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">' +
                '  <i class="fa fa-exclamation-triangle me-2"></i>' +
                '  <strong>Gagal memuat data:</strong> ' + errMsg +
                '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
        }
    });
}

function NEWDATA() {
    loadAssetPanel('hrd-asset-lists-new', 'Memuat form tambah asset baru...');
}

function DATAASSETSDBL() {
    var selectedId = DATAASSETS.getSelectedId();
    if (!selectedId) {
        notifyAsset('warning', 'Perhatian', 'Pilih baris aset terlebih dahulu.');
        return;
    }
    var assetNo = DATAASSETS.cells(selectedId, 1).getValue();
    loadAssetPanel('hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo), 'Memuat detail asset ' + assetNo + '...');
}

function resetAssetFilters() {
    $('#filterSubCategory').val('');
    $('#filterStatus').val('');
    $('#filterKondisi').val('');
    DATAASSETSRefresh();
}

DATAASSETS = new dhtmlXGridObject('DATAASSETS');
DATAASSETS.setImagePath("./dhtmlx/imgs/");

DATAASSETS.setHeader("No,Asset No,Asset Category,Asset Sub Category,Asset Type,Asset Code,Asset Description,Brand,Sub Product Type,Manufacture Date,Registration No,Buy Date,Buy Value,Asset Location,Address,City,Departement,Vendor,Contract No,Acc No,Unit Name,Model,Bahan Bakar,CC,No Rangka,No Mesin,Jenis Kendaraan,No BPKB,No Surat Jalan,Asuransi,STNK Tahunan,STNK 5 Tahun,Jadwal KIR,Berkas KIR,Mypertamina,Barcode,Barcode PSAK,Service Terakhir,Service Berikutnya,KM Saat Ini,KM Jadwal PM,Interval PM,Tgl Penghapusan,User Responsible,User Sebelumnya,Link QR,QR Aset,Status,Kondisi,Note");
DATAASSETS.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
DATAASSETS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
DATAASSETS.setInitWidths("60,120,120,120,100,100,200,100,100,100,100,100,110,110,150,100,110,120,100,90,100,100,90,80,130,120,130,100,110,180,100,100,150,120,90,120,130,110,110,100,100,90,100,120,120,150,110,90,90,200");
DATAASSETS.setColAlign("center,center,center,center,center,center,left,center,center,center,center,center,right,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center,left,center,center,left");
DATAASSETS.setDateFormat("%Y-%m-%d");
DATAASSETS.setColSorting("int,str,str,str,str,str,str,str,str,date,str,date,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,date,date,str,str,str,str,str,date,date,int,int,int,date,str,str,str,str,str,str,str");
DATAASSETS.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro");
DATAASSETS.setNumberFormat("0,000", 12, ".", ",");
DATAASSETS.attachEvent("onRowDblClicked", DATAASSETSDBL);
DATAASSETS.enableSmartRendering(true, 150);
DATAASSETS.setSkin("dhx_skyblue");
DATAASSETS.init();

function DATAASSETSRefresh() {
    var params = [];
    var subCat = document.getElementById('filterSubCategory').value;
    var status = document.getElementById('filterStatus').value;
    var kondisi = document.getElementById('filterKondisi').value;

    if (subCat) params.push('sub_category=' + encodeURIComponent(subCat));
    if (status) params.push('status=' + encodeURIComponent(status));
    if (kondisi) params.push('kondisi=' + encodeURIComponent(kondisi));

    var url = 'hrd-asset-lists-data' + (params.length ? ('?' + params.join('&')) : '');
    DATAASSETS.clearAndLoad(url);
}

DATAASSETSRefresh();
</script>
@endsection
