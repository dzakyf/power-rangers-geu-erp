@extends('layout.mainlayout-bp')

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
            <span class="active">Compliance</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LEGAL</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="createLegal" name="createLegal" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateLegal()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<script>
    var transferApplicationId = null;
    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateLegal() {
        window.location.href = `partner-compliance-legal-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', 'Nama Dokumen', 'Keterangan', 'Link Download', 'Dibuat Pada']

        let tWidth = tHeader.map((e) => '200')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        Grid1 = new dhtmlXGridObject('MembershipGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        // Grid1.enableAutoWidth(true);
        Grid1.attachHeader(headerAttachment);
        // Grid1.setColTypes(tHeader.map((e, i) => i === 3 ? 'link' : 'ro').join(','));
        Grid1.setColTypes('ro,ro,ro,link,ro');


        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // Grid1.enableAutoWidth(true);
        Grid1.attachEvent("onRowDblClicked", doubleClickMitra);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('partner-compliance-legal-data');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    function selectMitra(id, cellIdx) {
        transferApplicationId = Grid1.cells(id, 0).getValue();

        $('#transfer_application_id').val(transferApplicationId)
    }

    function doubleClickMitra(rowId, cellIndex) {
        if (cellIndex === 3) {
            let cellValue = Grid1.cells(rowId, cellIndex).getValue();
            let url = cellValue;
            if (url) {
                window.open(url, '_blank');
            }
        }
    }

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
                $('#transfer_application_id').val(transferApplicationId)
            }
        });

    })
</script>
@endsection