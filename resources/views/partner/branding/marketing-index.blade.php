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
            <form id="updateMarketingDetailForm" action="partner-branding-marketing-update" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Code">Kode Promosi <span style="color: red;">*</span></label>
                            <input type="text" name="Code" id="Code" class="form-control" required readonly />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Name">Nama Materi Promosi <span style="color: red;">*</span></label>
                            <input type="text" name="Name" id="Name" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Type">Jenis Materi <span style="color: red;">*</span></label>
                            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                            <select name="Type" id="Type" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="BANNER">Banner</option>
                                <option value="VIDEO">Video</option>
                                <option value="POSTER">Poster</option>
                                <option value="DIGITAL_ADS">Digital Ads</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Platform">Platform <span style="color: red;">*</span></label>
                            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                            <select name="Platform" id="Platform" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="ONLINE">Online</option>
                                <option value="OFFLINE">Offline</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Channel">Channel <span style="color: red;">*</span></label>
                            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                            <select name="Channel" id="Channel" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="GOOGLE_ADS">Google Ads</option>
                                <option value="TIKTOK">TikTok</option>
                                <option value="FANPAGE">Fanpage</option>
                                <option value="EVENT">Event</option>
                                <option value="INSTAGRAM">Instagram</option>
                                <option value="FACEBOOK">Facebook</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Status">Status <span style="color: red;">*</span></label>
                            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                            <select name="Status" id="Status" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="ACTIVE">Aktif</option>
                                <option value="NONACTIVE">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Description">Deskripsi <span style="color: red;">*</span></label>
                            <input type="text" name="Description" id="Description" class="form-control" required />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Media_Type">Tipe Media <span style="color: red;">*</span></label>
                            <select name="Media_Type" id="Media_Type" class="form-control" required>
                                <option value="">-- Pilih Jenis--</option>
                                <option value="URL">Url</option>
                                <option value="UPLOAD">Upload Dokumen</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="design" class="row" style="display:none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="design">Dokumen Pendukung</label>
                            <input type="file" name="design" id="design" class="form-control" />
                        </div>
                    </div>
                </div>

                <div id="media_url" class="row" style="display:none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Media_Url">Media Url</label>
                            <input type="text" name="Media_Url" id="Media_Url" class="form-control" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
    var marketingId = null;
    $(document).ready(function() {
        initTableGrid1()

        $('#Media_Type').on('change', function(e) {
            let value = $(this).val()

            if (value === "URL") {
                $("#media_url").show()
                $("#design").hide()
            }
            if (value === "UPLOAD") {
                $("#media_url").hide()
                $("#design").show()
            }
        })

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
        Grid1.setColTypes(tHeader.map((e, i) => i === 9 ? 'link' : 'ro').join(','));
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // Grid1.enableAutoWidth(true);
        // Grid1.attachEvent("onRowDblClicked", doubleClickTableGrid1);
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
            url: `partner-branding-marketing-data?marketing_id=${marketingId}`, // replace with your actual endpoint
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

                if ($(cells[9]).text()) {
                    $('#media_url').show()
                    $('#Media_Type').val('URL')
                }

                $('#Grid1Detail').show();
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch detail data:', error);
                alert('Gagal memuat data. Silakan coba lagi.');
            }
        });
    });

    function selectMitra(id, cellIdx) {
        marketingId = Grid1.getRowAttribute(id, 'ID');

        $('#marketingId').val(marketingId)
    }

    function doubleClickTableGrid1() {
        initDetailTableGrid1()
    }

    $('#updateMarketingDetailForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append('Marketing_Id', marketingId)

        $('#submitBtn').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response, status, xhr) {
                alert("Berhasil mengupdate info pengajuan")
                // $('#updateMarketinDetailForm')[0].reset()
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);

            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).text('Submit');
                $('#marketingId').val(marketingId)
            }
        });

    })
</script>
@endsection