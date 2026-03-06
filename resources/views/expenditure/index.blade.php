<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Expenditure</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
    <div>
        <!-- <input type="button" id="ekselProjectionGridDet" name="ekselProjectionGridDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectionGrid.toExcel('./export/generate.php');" /> -->
        <!-- <input type="button" id="detailBP" name="detailBP" value="Detail Leads" class="btn btn-success mt-2 mr-2" onclick="detailBP()" /> -->
        <div class="btn-group mt-2 mr-2" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-download"></i> Action
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                <a class="dropdown-item" href="#" onclick="document.getElementById('csvFileInput').click(); return false;">
                    <i class="fas fa-file-csv"></i> Import .csv
                </a>
                <a class="dropdown-item" href="#" onclick="downloadCSVTemplate()">
                    <i class="fas fa-file-csv"></i> Download template (.csv)
                </a>
            </div>
        </div>
    </div>
</div>

<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>

<script>
    function downloadTemplate() {}

    function importCsv() {}

    let tHeader = [
        'No', ' Uraian', ' Standard',
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

    let headerAttachment = [
        '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
    ]

    $(document).ready(function() {
        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.enableAutoWidth(true);
        ProjectionGrid.attachHeader(headerAttachment);
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // ProjectionGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        refreshdata();
    })

    function ProjectionGridKlik() {
        ProjectionGridID = ProjectionGrid.getSelectedId();
        $("#divlistOpportunity").show();
        if (ProjectionGridID != '') {
            $("#divlistProjectList").hide();
            LoadM('divlistOpportunity', 'panduan-detail?ID=' + ProjectionGridID);
        }
        return true;
    }

    function refreshdata() {
        ProjectionGrid.clearAndLoad('expenditure-data-expenditure?user_id=2345&hub_type=booth&projection_type=high&year=1');
    }


    function detailBP() {
        ProjectionGridKlik();
    }


    function LihatDetailProjectionGrid() {
        taskid = ProjectionGrid.cells(ProjectionGrid.getSelectedId(), 1).getValue();
        LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
    }
</script>