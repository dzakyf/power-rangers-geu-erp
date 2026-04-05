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
            <span class="active">History Royalty</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HISTORY PEMBAYARAN ROYALTY</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <!-- <input type="button" id="pengajuan" name="pengajuan" value="Buat Pengajuan" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" /> -->
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Pengajuan" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />

                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
</div>


<div id="Grid1Detail" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HISTORY PEMBAYARAN ROYALTY</div>
    <div class="card w-100">
        <div class="card-body">
            <!-- <div id="MembershipGrid" style="width: w-100auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

            </div>
            <h5>Update Informasi Kemitraan</h5>
            <form id="updateApplicationInfoForm" action="partner-profit-sharing-royalty-update" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="Application_Id" id="Application_Id" class="form-control" readonly />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Status">Update Status Ke<span style="color: red;">*</span></label>
                            <select name="Status" id="Status" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="WAITING_PAYMENT">Menunggu Pembayaran</option>
                                <option value="PAID">Dibayar</option>

                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Transfer_Doc">Biaya Administrasi<span style="color: red;">*</span></label>
                            <input type="file" name="Transfer_Doc" id="Transfer_Doc" class="form-control" required />
                        </div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
    var royaltyId = null;
    $(document).ready(function() {
        initTableGrid1()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-profit-sharing-approval-create?id=${royaltyId}`

    }

    function initTableGrid1() {
        let tHeader = ['No', 'Periode', 'Nomor Transaksi', 'Nominal', 'Status', 'Diajukan Oleh',
            'Bukti Transfer'
        ]

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
        Grid1.setColTypes(tHeader.map((e, i) => i === 6 ? 'link' : 'ro').join(','));
        Grid1.attachEvent("onRowDblClicked", doubleClickMitra);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('partner-profit-sharing-royalty-data');
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
    }

    function selectMitra(id, cellIdx) {
        royaltyId = Grid1.getRowAttribute(id, 'ID');

        $('#Application_Id').val(royaltyId)
        console.log(royaltyId)
    }

    function doubleClickMitra() {
        $('#Grid1Detail').show()
    }

    $('#updateApplicationInfoForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $('#submitBtn').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response, status, xhr) {
                alert("Berhasil mengupdate info pengajuan")
                // $('#updateApplicationInfoForm')[0].reset()
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);

            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).text('Submit');
                $('#transfer_application_id').val(royaltyId)
            }
        });

    })
</script>
@endsection