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
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MARKETING</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail Data" class="btn btn-info mt-2 mr-2" />

            </div>
        </div>
    </div>
</div>


<div id="Grid1Detail" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MARKETING</div>
    <div class="card w-100">
        <div class="card-body">
            <h5>Detail Informasi Marketing</h5>
            <form id="marketingDetailForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Code">Code</label>
                            <input type="text" name="Code" id="Code" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Name">Name</label>
                            <input type="text" name="Name" id="Name" class="form-control" readonly />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Type">Type</label>
                            <input type="text" name="Type" id="Type" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Platform">Platform</label>
                            <input type="text" name="Platform" id="Platform" class="form-control" readonly />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Channel">Channel</label>
                            <input type="text" name="Channel" id="Channel" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Status">Status</label>
                            <input type="text" name="Status" id="Status" class="form-control" readonly />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea name="Description" id="Description" class="form-control" rows="3" readonly></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Created_At">Created At</label>
                            <input type="text" name="Created_At" id="Created_At" class="form-control" readonly />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Media_Url">Media Url</label>
                            <textarea name="Media_Url" id="Media_Url" class="form-control" rows="3" readonly></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var transferApplicationId = null;
    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-branding-marketing-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', 'Code', 'Nama Materi', 'Jenis Materi', 'Platform', 'Channel', 'Deskripsi', 'Status', 'Tanggal Upload', 'Media Url']
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
        Grid1.attachEvent("onRowDblClicked", doubleClickTableGrid1);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('partner-branding-marketing-data');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    $('#detailGrid1').on('click', function() {
        $.ajax({
            url: `partner-branding-marketing-data?marketing_id=${transferApplicationId}`, // replace with your actual endpoint
            type: "GET",
            dataType: "xml",
            success: function(xmlResponse) {
                const xml = $(xmlResponse);
                const cells = xml.find('row').first().find('cell');

                // index matches the order in your buildXmlResponse PHP function
                // 0 = counter, 1 = Code, 2 = Name, 3 = Type, 4 = Platform, 5 = Channel, 6 = Marketing_Desc, 7 = Status, 8 = Marketing_Created_At
                $('#Code').val($(cells[1]).text());
                $('#Name').val($(cells[2]).text());
                $('#Type').val($(cells[3]).text());
                $('#Platform').val($(cells[4]).text());
                $('#Channel').val($(cells[5]).text());
                $('#Description').val($(cells[6]).text());
                $('#Status').val($(cells[7]).text());
                $('#Created_At').val($(cells[8]).text());
                $('#Media_Url').val($(cells[9]).text());

                $('#Grid1Detail').show();
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch detail data:', error);
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    });

    function selectMitra(id, cellIdx) {
        transferApplicationId = Grid1.cells(id, 0).getValue();

        $('#transfer_application_id').val(transferApplicationId)
    }

    function doubleClickTableGrid1() {
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