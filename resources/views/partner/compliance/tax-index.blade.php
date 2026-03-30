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

                <input type="button" id="editTax" name="editTax" value="Ubah Data" class="btn btn-success mt-2 mr-2" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<div id="Grid1Detail" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPDATE PAJAK KEMITRAAN</div>
    <div class="card w-100">
        <div class="card-body">
            <h5>Update Pajak Kemitraan</h5>
            <form id="updateApplicationInfoForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Type">Jenis Pajak</label>
                            <input type="text" name="Type" id="Type" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Object">Objek Pajak</label>
                            <input type="text" name="Object" id="Object" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Percentile">Persentase</label>
                            <input type="text" name="Percentile" id="Percentile" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Base_Calculation">Dasar Perhitungan</label>
                            <input type="text" name="Base_Calculation" id="Base_Calculation" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="In_Charge">Penanggung Jawab</label>
                            <input type="text" name="In_Charge" id="In_Charge" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <input type="text" name="Description" id="Description" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Min_Percentile">Min Percentile (%)</label>
                            <input type="text" name="Min_Percentile" id="Min_Percentile" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Max_Percentile">Max Percentile (%)</label>
                            <input type="text" name="Max_Percentile" id="Max_Percentile" class="form-control" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
    var taxId = null;
    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-branding-marketing-create`
    }

    function initTableGrid1() {
        let tHeader = ['No', 'Jenis Pajak', 'Objek Pajak', 'Persentase', 'Dasar Perhitungan', 'Penanggung Jawab', 'Keterangan', 'Tipe Hub']

        let tWidth = tHeader.map((e) => '200')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '', '', '#text_filter'
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
        Grid1.clearAndLoad('partner-compliance-tax-data');
    }

    $('#editTax').on('click', function() {
        $.ajax({
            url: `partner-compliance-tax-data?tax_id=${taxId}`, // replace with your actual endpoint
            type: "GET",
            dataType: "xml",
            success: function(xmlResponse) {
                const xml = $(xmlResponse);
                const cells = xml.find('row').first().find('cell');

                $('#Type').val($(cells[1]).text());
                $('#Object').val($(cells[2]).text());
                $('#Percentile').val($(cells[3]).text());
                $('#Base_Calculation').val($(cells[4]).text());
                $('#In_Charge').val($(cells[5]).text());
                $('#Description').val($(cells[6]).text());
                $('#Min_Percentile').val($(cells[7]).text());
                $('#Max_Percentile').val($(cells[8]).text());

                $('#Grid1Detail').show();
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch detail data:', error);
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    });


    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    function selectMitra(id, cellIdx) {
        taxId = Grid1.getRowAttribute(id, 'Tax_Id');

    }



    function doubleClickMitra() {
        initDetailTableGrid1()
    }

    $('#updateApplicationInfoForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        formData.append('tax_id', taxId);

        $('#submitBtn').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: `partner-compliance-tax-update`,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response, status, xhr) {
                alert("Berhasil mengupdate info pajak")
                $('#updateApplicationInfoForm')[0].reset()
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);

            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).text('Submit');
                $('#transfer_application_id').val(taxId)
            }
        });

    })
</script>
@endsection