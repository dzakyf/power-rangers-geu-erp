@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">
            <span class="active">Quality Control</span>
        </li>
    </ol>
</div>




<div id="ProjectionSection" class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">QUALITY CONTROL</div>
    <div class="card">
        <div class="card-body">
            <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
            <div>
                <input type="button" id="CreateQC" name="CreateQC" value="Create" class="btn btn-info mt-2 mr-2" onclick="createQC()" />
                <input type="button" id="DetailQC" name="DetailQC" value="Detail" class="btn btn-info mt-2 mr-2" onclick="detailQC()" />
                <input type="button" id="DeleteQC" name="DeleteQC" value="Delete" class="btn btn-info mt-2 mr-2" onclick="deleteQC()" />

            </div>
        </div>
    </div>
</div>

@include('cns.quality.create')
@include('cns.quality.edit')

<script>
    var mitraId = null;
    $(document).ready(function() {

        initProjectionGrid()

    })

    function createQC() {
        $('#FormQualityControl').show();
        $('#FormEditQualityControl').hide();
    }

    function detailQC() {
        $('#FormQualityControl').hide();
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }
        prefillEditQC(rowId);
    }

    function deleteQC() {
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

        var tanggal = ProjectionGrid.cells(rowId, 1).getValue();
        var date = new Date(tanggal);
        var formatted = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        $.ajax({
            url: 'cns-quality-delete',
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                Date: formatted
            },
            success: function(res) {
                if (res.status === 'success') {
                    ProjectionGrid.clearAndLoad('cns-quality-data');
                }
            },
            error: function() {
                alert('Gagal menghapus data');
            }
        });
    }

    function redirectToCreateProjection() {
        if (!mitraId) {
            alert("Pilh mitra terlebih dahulu")
            return
        }
        window.location.href = `projection-create?id=${mitraId}`
    }



    function initProjectionGrid() {
        $('#ProjectionSection').show()

        let tHeader = [
            'No', 'Tanggal',
            'Top', '#cspan', '#cspan', '#cspan',
            'Middle', '#cspan', '#cspan', '#cspan',
            'Bottom', '#cspan', '#cspan', '#cspan',
            'Average', '#cspan', '#cspan', '#cspan',
            'Attachment'
        ]

        let tGroupHeader = [
            '#rspan', '#text_filter',
            'FFA', '#cspan', 'MC', 'IMP',
            'FFA', '#cspan', 'MC', 'IMP',
            'FFA', '#cspan', 'MC', 'IMP',
            'FFA', '#cspan', 'MC', 'IMP',
            '#rspan'
        ]

        let tTopHeader = [
            '#rspan', '#rspan',
            'Palmitic', 'Oleic', '', '',
            'Palmitic', 'Oleic', '', '',
            'Palmitic', 'Oleic', '', '',
            'Palmitic', 'Oleic', '', '',
            '#rspan'
        ]

        let tWidth = tHeader.map(() => '110')
        let tColType = tHeader.map((e, i) => {

            // if (i === 1) {
            //     return 'dhxCalendar';
            // }

            return 'ro'
        })
        let tColAlign = tHeader.map((e, i) => (i === 0 || i >= 2) ? 'right' : 'left')
        let tColSort = tHeader.map((e, i) => (i === 0 || i >= 2) ? 'int' : 'str')

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.attachHeader(tGroupHeader.join(','));
        ProjectionGrid.attachHeader(tTopHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.setColAlign(tColAlign.join(','));
        ProjectionGrid.setColSorting(tColSort.join(','));
        ProjectionGrid.setColTypes(tColType.join(','));
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        ProjectionGrid.clearAndLoad(`cns-quality-data`);
        ProjectionGrid.attachEvent('onRowDblClicked', function(rId, cInd) {
            console.log(rId, cInd);
            if (cInd == 18) {
                window.open(ProjectionGrid.cells(rId, cInd).getValue(), '_blank');
            }
        })
    }


    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();

    }

    function doubleClickMitra() {

    }
</script>
@endsection