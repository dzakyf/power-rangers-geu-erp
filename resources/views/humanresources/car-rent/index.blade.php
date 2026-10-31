@extends('layout.mainlayout-hrd')

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
            <!-- Filter: Tipe / SPPD / Mobil / Tanggal -->
            <div class="row mb-2">
                <div class="col-md-2 col-6">
                    <select id="CARRENT_F_Trip" class="form-control form-control-sm">
                        <option value="">Semua Tipe</option>
                        <option value="NON_SPPD">NON_SPPD</option>
                        <option value="SPPD">SPPD</option>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <input type="text" id="CARRENT_F_Sppd" class="form-control form-control-sm" placeholder="Cari No SPPD..." />
                </div>
                <div class="col-md-2 col-6">
                    <input type="text" id="CARRENT_F_Mobil" class="form-control form-control-sm" placeholder="Jenis Mobil..." />
                </div>
                <div class="col-md-2 col-6">
                    <input type="text" id="CARRENT_F_From" class="form-control form-control-sm bg-white" placeholder="Tgl dari (YYYY-MM-DD)" readonly />
                </div>
                <div class="col-md-2 col-6">
                    <input type="text" id="CARRENT_F_To" class="form-control form-control-sm bg-white" placeholder="Tgl s/d (YYYY-MM-DD)" readonly />
                </div>
                <div class="col-md-1 col-6">
                    <button type="button" id="CARRENT_Filter" class="btn btn-primary btn-sm btn-block">Filter</button>
                </div>
            </div>
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
            "Kerusakan/Denda,Bukti Pembayaran,Status,Tipe,No SPPD"
        );
        CARRENT.attachHeader(
            "&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter," +
            "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter," +
            "#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
        );

        CARRENT.setInitWidths(
            "60,150,150,100,120,130,100,180,120,200,110,90,120,100,90,130,150,70,80,150"
        );
        CARRENT.setColAlign(
            "center,center,left,center,left,left,left,left,left,left,center,center,center,center,center,left,left,center,center,left"
        );
        CARRENT.setColSorting(
            "int,str,str,str,str,str,str,str,str,str,date,str,date,str,str,str,str,str,str,str"
        );
        CARRENT.setColTypes(
            "ro,ed,ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro"
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
            var q = [];
            if ($('#CARRENT_F_Trip').val()) q.push('Trip_Type=' + encodeURIComponent($('#CARRENT_F_Trip').val()));
            if ($('#CARRENT_F_Sppd').val()) q.push('SPPD_No=' + encodeURIComponent($('#CARRENT_F_Sppd').val()));
            if ($('#CARRENT_F_Mobil').val()) q.push('Jenis_Mobil=' + encodeURIComponent($('#CARRENT_F_Mobil').val()));
            if ($('#CARRENT_F_From').val()) q.push('Tanggal_From=' + encodeURIComponent($('#CARRENT_F_From').val()));
            if ($('#CARRENT_F_To').val()) q.push('Tanggal_To=' + encodeURIComponent($('#CARRENT_F_To').val()));
            var url = 'hrd-car-rent-data' + (q.length ? '?' + q.join('&') : '');
            CARRENT.clearAndLoad(url);
        }

        $('#CARRENT_Filter').on('click', CARRENTLoad);

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
        if (typeof dhtmlXCalendarObject !== 'undefined') {
            var fCal = new dhtmlXCalendarObject(['CARRENT_F_From', 'CARRENT_F_To']);
            fCal.setDateFormat('%Y-%m-%d');
        }
        CARRENTLoad();
    </script>
@endsection
