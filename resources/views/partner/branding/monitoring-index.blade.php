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
            <span class="active">Branding</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MONITORING PROMOSI</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<!-- <div id="Grid1Detail" class="row m-1" style="display:none;"> -->
<!-- <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PROMOTION</div>
<div class="card w-100">
    <div class="card-body">
        <div>
        </div>
        <h5>Update Informasi Kemitraan</h5>
        <form id="updateApplicationInfoForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="transfer_application_id" id="transfer_application_id" class="form-control" readonly />
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="result">Hasil Due Dilligence<span style="color: red;">*</span></label>
                        <input type="text" name="result" id="result" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="effective_transfer_at">Tanggal Efektif Transfer<span style="color: red;">*</span></label>
                        <input type="date" name="effective_transfer_at" id="effective_transfer_at" class="form-control" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status Kontrak Setelah Transfer <span style="color: red;">*</span></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">-- Pilih Jenis--</option>
                            <option value="ACTIVE">AKTIF</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fee">Biaya Administrasi<span style="color: red;">*</span></label>
                        <input type="text" name="fee" id="fee" class="form-control" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <input type="text" name="notes" id="notes" class="form-control" />
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
        </form>
    </div>
</div>
</div> -->

<script>
    var transferApplicationId = null;
    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-branding-monitoring-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', 'Code Promosi', 'Tanggal Mulai', 'Tanggal Berakhir', 'Platform', 'Budger Realisasi', 'Status Campaign', 'PIC']

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
        Grid1.setColTypes(tHeader.map((e) => 'ro').join(','));
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // Grid1.enableAutoWidth(true);
        // Grid1.attachEvent("onRowDblClicked", doubleClickMitra);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('partner-branding-monitoring-data');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    function selectMitra(id, cellIdx) {
        transferApplicationId = Grid1.cells(id, 0).getValue();

        $('#transfer_application_id').val(transferApplicationId)
    }

    function doubleClickMitra() {
        initDetailTableGrid1()
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