@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Asset Lists (HCGA)</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Human Capital & General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Asset & Vehicle Management</a></li>
            <li class="breadcrumb-item active"><span class="active">Asset Lists</span></li>
        </ol>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa fa-boxes-stacked me-2 text-primary"></i>Master Data Assets</h5>
                <small class="text-muted">Klik dua kali pada baris untuk melihat atau memperbarui detail aset.</small>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <select id="filterSubCategory" class="form-select form-select-sm" style="width:auto" onchange="DATAASSETSRefresh();">
                    <option value="">Semua Sub Category</option>
                    <option value="VEHICLE">VEHICLE (Kendaraan)</option>
                </select>
                <a href="hrd-vehicle-management-index" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="fa fa-car me-1"></i> Vehicle Management & Reminder
                </a>
                <a href="hrd-vehicle-operations" class="btn btn-outline-success btn-sm fw-bold">
                    <i class="fa fa-truck me-1"></i> Logbook & Fuel Tracking
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="DATAASSETS" style="width:auto;height:400px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" id="ekselDATAASSETSDet" class="btn btn-warning btn-sm" onclick="DATAASSETS.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="DATAASSETS_Refresh" class="btn btn-info btn-sm text-white" onclick="DATAASSETSRefresh();">
                    <i class="fa fa-sync me-1"></i> Refresh Data
                </button>
                <button type="button" id="dxdata" class="btn btn-primary btn-sm" onclick="NEWDATA();">
                    <i class="fa fa-plus me-1"></i> New Data
                </button>
                <button type="button" id="DATAASSETS_Detail" class="btn btn-secondary btn-sm" onclick="DATAASSETSDBL();">
                    <i class="fa fa-eye me-1"></i> Detail Data
                </button>
            </div>
        </div>
    </div>

    <div id="DATAASSETSDTL" class="mt-3"></div>

<script>
function NEWDATA() {
    LoadM('DATAASSETSDTL', 'hrd-asset-lists-new');
}

function DATAASSETSDBL() {
    var selectedId = DATAASSETS.getSelectedId();
    if (!selectedId) {
        alert('Pilih baris aset terlebih dahulu.');
        return;
    }
    var assetNo = DATAASSETS.cells(selectedId, 1).getValue();
    LoadM('DATAASSETSDTL', 'hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo));
}

DATAASSETS = new dhtmlXGridObject('DATAASSETS');
DATAASSETS.setImagePath("./dhtmlx/imgs/");

DATAASSETS.setHeader("No,Asset No,Asset Category,Asset Sub Category,Asset Type,Asset Code,Asset Description,Brand,Sub Product Type,Manufacture Date,Registration No,Buy Date,Buy Value,Asset Location,Address,City,Departement,Vendor,Contract No,Acc No,Unit Name,Model,Bahan Bakar,CC,No Rangka,No Mesin,Jenis Kendaraan,No BPKB,No Surat Jalan,Asuransi,STNK Tahunan,STNK 5 Tahun,Jadwal KIR,Berkas KIR,Mypertamina,Barcode,Barcode PSAK,Service Terakhir,Service Berikutnya,KM Saat Ini,KM Jadwal PM,Interval PM,Tgl Penghapusan,User Responsible,User Sebelumnya,Link QR,QR Aset,Status,Note");
DATAASSETS.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
DATAASSETS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
DATAASSETS.setInitWidths("60,120,120,120,100,100,200,100,100,100,100,100,110,110,150,100,110,120,100,90,100,100,90,80,130,120,130,100,110,180,100,100,150,120,90,120,130,110,110,100,100,90,100,120,120,150,110,90,200");
DATAASSETS.setColAlign("center,center,center,center,center,center,left,center,center,center,center,center,right,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,left,center,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center,left,center,left");
DATAASSETS.setDateFormat("%Y-%m-%d");
DATAASSETS.setColSorting("int,str,str,str,str,str,str,str,str,date,str,date,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,date,date,str,str,str,str,str,date,date,int,int,int,date,str,str,str,str,str,str");
DATAASSETS.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro");
DATAASSETS.setNumberFormat("0,000", 12, ".", ",");
DATAASSETS.attachEvent("onRowDblClicked", DATAASSETSDBL);
DATAASSETS.enableSmartRendering(true, 150);
DATAASSETS.setSkin("dhx_skyblue");
DATAASSETS.init();

function DATAASSETSRefresh() {
    var subCat = document.getElementById('filterSubCategory').value;
    var url = 'hrd-asset-lists-data' + (subCat ? ('?sub_category=' + encodeURIComponent(subCat)) : '');
    DATAASSETS.clearAndLoad(url);
}

DATAASSETSRefresh();
</script>
@endsection
