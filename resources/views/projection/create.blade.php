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

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat proyeksi</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->
    <div class="col-12">
        <div class="form-group">
            <label for="Type">Tipe Proyeksi <span style="color: red;">*</span></label>
            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
            <select name="Type" id="Type" class="form-control" required>
                <option value="">-- Pilih Jenis--</option>
                <option value="low">Rendah</option>
                <option value="high">Tinggi</option>
            </select>
        </div>
        <div class="form-group">
            <label for="Year">Tahun Proyeksi<span style="color: red;">*</span></label>
            <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
            <select name="Year" id="Year" class="form-control" required>
                <option value="">-- Pilih Jenis--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div>
        <div class="form-group">
            <label for="File_Documents">Upload Proyeksi</label>
            <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        initPartnerGrid()
    })
    let type = null;
    let year = null;

    $("#Type, #Year").on('change', function() {
        $("#File_Documents").data('type', $("#Type").val());
        $("#File_Documents").data('year', $("#Year").val());
    });

    var vault = new dhx.Vault("File_Documents", {
        uploader: {
            target: "projection-upload",
            params: {
                '_token': '{{ csrf_token() }}',
                'type': $("#Type").val(),
                'year': $('#Year').val()
            }
        },
        filesLimit: 1
    });

    vault.events.on("BeforeUploadFile", function(file) {
        if (vault && vault.uploader && vault.uploader.config) {
            vault.uploader.config.params.type = $("#Type").val();
            vault.uploader.config.params.year = $("#Year").val();
            console.log('Updated params:', vault.uploader.config.params);
        }
    });

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
        // PartnerGrid.attachEvent("onRowDblClicked", clickMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad('partner-data');
    }
</script>
@endsection