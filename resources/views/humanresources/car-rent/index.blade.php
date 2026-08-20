@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PEMINJAMAN MOBIL OPERASIONAL</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Human Resources</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Peminjaman Mobil</span>
            </li>
        </ol>
    </div>

    <div class="card border-primary shadow-sm mb-4">
        <div class="card-body">
            <!-- Toolbar -->
            <div id="CARRENT_TOOLBAR" class="d-flex flex-wrap align-items-center mb-2">
                <button type="button" id="CARRENT_New" class="btn btn-primary mr-2">
                    <i class="fas fa-plus mr-1"></i> New Data
                </button>
                <button type="button" id="CARRENT_Detail" class="btn btn-info mr-2">
                    <i class="fas fa-eye mr-1"></i> Detail Data
                </button>
                <button type="button" id="CARRENT_Export" class="btn btn-warning mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </button>
                <button type="button" id="CARRENT_Refresh" class="btn btn-secondary">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Data
                </button>
            </div>

            <!-- Grid -->
            <div id="CARRENT_GRID" style="width:auto;height:450px;background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Detail Panel -->
    <div id="CARRENT_DTL"></div>

    <script>
        // ── Grid object ──
        CARRENT = new dhtmlXGridObject('CARRENT_GRID');
        CARRENT.setImagePath('./dhtmlx/imgs/');
        CARRENT.setSkin('dhx_skyblue');
        CARRENT.setEditable(true);
        CARRENT.enableSmartRendering(true, 150);

        CARRENT.setHeader(
            "No.,Car Rent No,User Name,NIK,Jabatan,Departemen,Site/Office,SIM Link," +
            "Keperluan,Tujuan Penggunaan,Tanggal Peminjaman,Jam Peminjaman," +
            "Tanggal Pengembalian,Jam Pengembalian,Jenis Mobil," +
            "Kerusakan/Denda,Bukti Pembayaran,Status"
        );
        CARRENT.attachHeader(
            "&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter," +
            "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter," +
            "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
        );

        CARRENT.setInitWidths(
            "60,150,150,100,120,130,100,180,120,200,110,90,120,100,90,130,150,70"
        );
        CARRENT.setColAlign(
            "center,center,left,center,left,left,left,left,left,left,center,center,center,center,center,left,left,center"
        );
        CARRENT.setColSorting(
            "int,str,str,str,str,str,str,str,str,str,date,str,date,str,str,str,str,str"
        );
        CARRENT.setColTypes(
            "ro,ed,ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro"
        );
        CARRENT.setDateFormat("%Y-%m-%d");
        CARRENT.setNumberFormat("0", 0, ".", "");

        // ── Row double-click → Detail ──
        CARRENT.attachEvent("onRowDblClicked", function(id) {
            let carRentNo = CARRENT.cells(id, 1).getValue();
            LoadM('CARRENT_DTL', 'hrd-car-rent-detail?Car_Rent_No=' + encodeURIComponent(carRentNo));
        });

        // ── Load data ──
        function CARRENTLoad() {
            CARRENT.clearAndLoad('hrd-car-rent-data');
        }

        // ── Toolbar handlers ──
        $('#CARRENT_New').on('click', function() {
            LoadM('CARRENT_DTL', 'hrd-car-rent-new');
        });

        $('#CARRENT_Detail').on('click', function() {
            let selectedId = CARRENT.getSelectedId();
            if (!selectedId) {
                alert('Mohon pilih baris terlebih dahulu.');
                return;
            }
            let carRentNo = CARRENT.cells(selectedId, 1).getValue();
            LoadM('CARRENT_DTL', 'hrd-car-rent-detail?Car_Rent_No=' + encodeURIComponent(carRentNo));
        });

        $('#CARRENT_Export').on('click', function() {
            CARRENT.toExcel('./export/generate.php');
        });

        $('#CARRENT_Refresh').on('click', function() {
            CARRENTLoad();
        });

        // ── Init ──
        CARRENTLoad();
    </script>
@endsection
