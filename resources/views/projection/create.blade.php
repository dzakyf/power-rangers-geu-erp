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

    var vault = new dhx.Vault("File_Documents", {
        uploader: {
            target: "projection-upload",
            params: {
                '_token': '{{ csrf_token() }}'
            }

        },
        filesLimit: 1
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