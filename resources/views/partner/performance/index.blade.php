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
            <span class="active">Performance</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PERFORMANCE - KPI</div>
    <div class="card">
        <div class="card-body">
            <div id="PerformanceGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="createKPI" name="createKPI" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateKPI()" />
                <input type="button" id="editKPI" name="editKPI" value="Ubah Data" class="btn btn-success mt-2 mr-2" onclick="redirectToEditKPI()" />

                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<script>
    var kpiId = null;

    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateKPI() {
        window.location.href = `partner-performance-kpi-create`
    }

    function redirectToEditKPI() {
        window.location.href = `partner-performance-kpi-update?kpi_id=${kpiId}`
    }

    function initTableGrid1() {
        let tHeader = ['No', 'Tahun Periode', 'Area Kerja', 'Indikator', 'Weight (%)', 'Target', 'Realisasi (%)', 'Departement']

        let tWidth = tHeader.map((e) => '200')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        Grid1 = new dhtmlXGridObject('PerformanceGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        Grid1.attachHeader(headerAttachment);
        Grid1.setColTypes(tHeader.map((e, i) => 'ro').join(','));

        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('partner-performance-kpi-data');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    function selectMitra(id, cellIdx) {
        kpiId = Grid1.getRowAttribute(id, 'ID');
    }

    // function doubleClickMitra(rowId, cellIndex) {
    //     if (cellIndex === 3) {
    //         let cellValue = Grid1.cells(rowId, cellIndex).getValue();
    //         let url = cellValue;
    //         if (url) {
    //             window.open(url, '_blank');
    //         }
    //     }
    // }

    $('#updateApplicationInfoForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $('#submitBtn').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: `partner-transfer-membership-update-application`,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response, status, xhr) {
                alert("Berhasil mengupdate info pengajuan")
                $('#updateApplicationInfoForm')[0].reset()
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);

            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).text('Submit');
            }
        });

    })
</script>
@endsection