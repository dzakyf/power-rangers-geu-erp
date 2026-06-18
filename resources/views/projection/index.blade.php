@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Proyeksi</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MITRA</div>
    <div class="card">
        <div class="card-body">
            <!-- <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

            </div> -->
            <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <input type="button" id="detailBP" name="detailBP" value="Buat Proyeksi" class="btn btn-success mb-2" onclick="redirectToCreateProjection()" />
            <input type="button" id="lihatProyeksi" name="lihatProyeksi" value="Lihat Proyeksi" class="btn btn-info mb-2 ml-2" onclick="lihatProyeksi()" />
            <!-- <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
            <div> -->
        </div>
    </div>
</div>


<div id="HiddenExpenditureSection" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HIDDEN EXPENDITURE</div>
    <div class="card">
        <div class="card-body">
            <!-- <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

            </div> -->
            <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <input type="button" id="detailBP" name="detailBP" value="Buat Proyeksi" class="btn btn-success mb-2" onclick="redirectToCreateProjection()" />
            <input type="button" id="lihatProyeksi" name="lihatProyeksi" value="Lihat Proyeksi" class="btn btn-info mb-2 ml-2" onclick="lihatProyeksi()" /> -->
            <div id="ExpenditureGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <!-- <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div> -->
            <div>
            </div>
        </div>
    </div>
</div>

<div id="ProjectionSection" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PROYEKSI</div>
    <div class="card">
        <div class="card-body">
            <!-- <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

            </div> -->
            <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <input type="button" id="detailBP" name="detailBP" value="Buat Proyeksi" class="btn btn-success mb-2" onclick="redirectToCreateProjection()" />
            <input type="button" id="lihatProyeksi" name="lihatProyeksi" value="Lihat Proyeksi" class="btn btn-info mb-2 ml-2" onclick="lihatProyeksi()" /> -->

            <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
            <div>
            </div>
        </div>
    </div>
</div>

<div class="row m-1">


    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->


    <!-- <input type="button" id="ekselProjectionGridDet" name="ekselProjectionGridDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectionGrid.toExcel('./export/generate.php');" /> -->
    <!-- <input type="button" id="createBroadcast" name="createBroadcast" value="Create Panduan" class="btn btn-info mt-2 mr-2" onclick="createPanduan()" /> -->
    <div class="btn-group" role="group">
    </div>
</div>
</div>

<script>
    var mitraId = null;
    $(document).ready(function() {
        initPartnerGrid()

    })

    function redirectToCreateProjection() {
        if (!mitraId) {
            alert("Pilh mitra terlebih dahulu")
            return
        }
        window.location.href = `projection-create?id=${mitraId}`
    }

    function initPartnerGrid() {
        let tHeader = [
            'No', 'Nama', 'Alamat'
        ]

        let tWidth = tHeader.map((e) => '110')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter'
        ]
        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        // PartnerGrid.enableAutoWidth(true);
        PartnerGrid.attachHeader(headerAttachment);
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        PartnerGrid.attachEvent("onRowDblClicked", doubleClickMitra);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad('partner-data-active-mitra');
    }

    function initProjectionGrid() {
        $('#ProjectionSection').show()
        let tHeader = [
            'No', 'Tipe Hub', 'Tipe Proyeksi', ' Uraian', ' Standard',
            ' %', 'Tahun Ke-', ' Month 1 ', '',
            '', '', ' Month 2',
            '', '', '',
            ' Month 3', '', '',
            ' ', ' Month 4', '',
            ' ', '', ' Month 5',
            '', '', '',
            ' Month 6', '', '',
            ' ', 'Month 7', '',
            '', '', 'Month 8',
            '', '', '',
            ' Month 9', '', '',
            '', 'Month 10', '',
            '', '', 'Month 11',
            '', '', '',
            ' Month 12', '', '',
            ''
        ]

        let tWidth = tHeader.map((e) => '110')
        let tColType = tHeader.map((e, key) => {
            if (key == 0 || key == 4 || key == 5 || key >= 7) {
                return 'ron'
            } else {
                return 'ro'
            }
        })
        let tColAlign = tHeader.map((e, key) => {
            if (key == 0 || key == 4 || key == 5 || key >= 7) {
                return 'right'
            } else {
                return 'left'
            }
        })
        let tColSort = tHeader.map((e, key) => {
            if (key == 0 || key == 4 || key == 5 || key >= 7) {
                return 'int'
            } else {
                return 'str'
            }
        })
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter',
            'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)', 'A', 'B', 'Total',
            'Offset (%)'
        ]
        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.setColAlign(tColAlign.join(','));
        ProjectionGrid.setColSorting(tColSort.join(','));
        ProjectionGrid.setColTypes(tColType.join(','));
        ProjectionGrid.attachHeader(headerAttachment);
        ProjectionGrid.setNumberFormat("0,000", 4, ".", ",");
        ProjectionGrid.setNumberFormat("0,000", 5, ".", ",");
        for (let ind = 7; ind < tHeader.length; ind++) {
            ProjectionGrid.setNumberFormat("0,000", ind, ".", ",");
        }
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        ProjectionGrid.clearAndLoad(`projection-data-projection?user_id=${mitraId}&hub_type=&projection_type=&year=`);
    }


    function initExpenditureGrid() {
        $('#HiddenExpenditureSection').show()
        let tHeader = [
            'No', 'Tipe Hub', 'Tipe Proyeksi', 'Uraian', ' Standard',
            ' %', 'Tahun Ke-', ' Month 1 ',
            ' Month 2',
            ' Month 3',
            ' Month 4',
            ' Month 5',
            ' Month 6',
            'Month 7',
            'Month 8',
            'Month 9',
            'Month 10',
            'Month 11',
            ' Month 12',
        ]
        let tWidth = tHeader.map((e) => '140')

        let tColType = tHeader.map((e, key) => {
            if (key == 0 || key == 5 || key >= 7) {
                return 'ron'
            } else {
                return 'ro'
            }
        })
        let tColAlign = tHeader.map((e, key) => {
            if (key == 0 || key == 5 || key >= 7) {
                return 'right'
            } else {
                return 'left'
            }
        })
        let tColSort = tHeader.map((e, key) => {
            if (key == 0 || key == 5 || key >= 7) {
                return 'int'
            } else {
                return 'str'
            }
        })

        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]

        ExpenditureGrid = new dhtmlXGridObject('ExpenditureGrid');
        ExpenditureGrid.setImagePath("./dhtmlx/imgs/");
        ExpenditureGrid.setHeader(tHeader.join(','));
        ExpenditureGrid.setInitWidths(tWidth.join(','));
        ExpenditureGrid.setColAlign(tColAlign.join(','));
        ExpenditureGrid.setColSorting(tColSort.join(','));
        ExpenditureGrid.setColTypes(tColType.join(','));
        ExpenditureGrid.attachHeader(headerAttachment);
        ExpenditureGrid.setNumberFormat("0,000", 5, ".", ",");
        for (let ind = 7; ind < tHeader.length; ind++) {
            ExpenditureGrid.setNumberFormat("0,000", ind, ".", ",");
        }
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // ProjectionGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ExpenditureGrid.setSkin("dhx_skyblue");
        ExpenditureGrid.init();
        ExpenditureGrid.clearAndLoad(`expenditure-data-expenditure?user_id=${mitraId}&hub_type=&projection_type=&year=`);

    }

    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();

    }

    function doubleClickMitra() {
        const position = "{{ $session->Position }}"

        if (position !== "MITRA") {
            initExpenditureGrid()
        }

        initProjectionGrid()
    }

    function lihatProyeksi() {
        if (!mitraId) {
            alert("Pilih mitra terlebih dahulu");
            return;
        }
        const position = "{{ $session->Position }}";
        if (position !== "MITRA") {
            initExpenditureGrid();
        }
        initProjectionGrid();
    }
</script>
@endsection