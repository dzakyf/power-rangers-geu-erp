@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Work Order Status</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WORK ORDER STATUS</div>
    <div class="card">
        <div class="card-body">
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreate()" />
                <input type="button" id="detailGrid1" name="detailGrid1" value="Detail" class="btn btn-info mt-2 mr-2" />
                <input type="button" id="deleteGrid" name="deleteGrid" value="Hapus" class="btn btn-danger mt-2 mr-2" onclick="deleteSelected()" />
                <input type="button" id="eksel" name="eksel" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="alert('Export Excel belum tersedia');" />
            </div>
        </div>
    </div>

    <div id="Grid1Detail" class="row ml-3 m-1" style="display:none;">
        @include('partner.work-order-status.edit')
    </div>
</div>

<script>
    var selectedId = null;

    $(document).ready(function() {
        initGrid()
    })

    function redirectToCreate() {
        window.location.href = 'master-work-order-status-create'
    }

    function initGrid() {
        let tHeader = ['No', 'Code', 'Name', 'Description', 'Is Active']
        let tWidth = tHeader.map(() => '150')
        let headerAttachment = tHeader.map(() => '#text_filter')

        Grid1 = new dhtmlXGridObject('MembershipGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        Grid1.attachHeader(headerAttachment);
        Grid1.setColTypes(tHeader.map(() => 'ro').join(','));
        Grid1.attachEvent("onRowSelect", selectRow);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();
        Grid1.clearAndLoad('master-work-order-status-data?format=xml');
    }

    function selectRow(id, cellIdx) {
        selectedId = Grid1.getRowAttribute(id, 'ID');
    }

    $('#detailGrid1').on('click', function() {
        if (!selectedId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        $('#edit_id').val(selectedId);
        $('#Grid1Detail').show();

        $.get('master-work-order-status-data', { format: 'json', id: selectedId }, function(res) {
            var data = res.data && res.data[0];
            if (!data) return;
            $('#edit_Name').val(data.Name);
            $('#edit_Code').val(data.Code);
            $('#edit_Description').val(data.Description);
            $('#edit_Is_Active').val(data.Is_Active);
        });
    });

    function deleteSelected() {
        if (!selectedId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        if (!confirm('Yakin ingin menghapus data ini?')) return;

        $.ajax({
            url: 'master-work-order-status-delete',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}', id: selectedId },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil dihapus');
                    Grid1.clearAndLoad('master-work-order-status-data?format=xml');
                }
            }
        });
    }
</script>
@endsection
