@extends('layout.mainlayout-bp')

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
            <span class="active">Facility</span>
        </li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER FACILITY</div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex gap-2 mb-2">
                <select id="filterHubType" class="form-control" style="width:160px;">
                    <option value="">-- Hub Type --</option>
                    <option value="mobil">Mobil</option>
                    <option value="booth">Booth</option>
                </select>
                <select id="filterFacilityType" class="form-control" style="width:160px;">
                    <option value="">-- Facility Type --</option>
                    <option value="inventory">Inventory</option>
                    <option value="access">Access</option>
                    <option value="support">Support</option>
                </select>
            </div>
            <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <!-- <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Formulir Survey" class="btn btn-info mt-2 mr-2" onclick="redirectToFormSurvey()" /> -->

                <input type="button" id="detailGrid1" name="detailGrid1" value="Edit Data" class="btn btn-info mt-2 mr-2" />
                <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" />
                <input type="button" id="deleteGrid1" value="Hapus Data" class="btn btn-danger mt-2 mr-2" />
                <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />

            </div>
        </div>
    </div>

    <div id="Grid1Detail" class="row ml-3 m-1" style="display:none;">
        <div class="card w-100">
            <div class="card-header">Edit Facility</div>
            <div class="card-body">
                <form id="FacilityEditForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Hub Type</label>
                            <input type="text" class="form-control" id="edit_type" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Category</label>
                            <input type="text" class="form-control" id="edit_category" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <input type="text" class="form-control" name="description" id="edit_description">
                    </div>
                    <div class="form-group">
                        <label>Detail</label>
                        <div id="editDetailList"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addEditDetail">+ Add Detail</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="edit_asset_type">Asset Type</label>
                            <select class="form-control" name="asset_type" id="edit_asset_type">
                                <option value="">-- Pilih --</option>
                                <option value="Asset">Asset</option>
                                <option value="Bukan Asset">Bukan Asset</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_depreciation_year">Depreciation Year</label>
                            <input type="number" min="1" class="form-control" name="depreciation_year" id="edit_depreciation_year">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_shelf_life">Waktu Pakai (Bulan)
                            </label>
                            <input type="number" min="1" class="form-control" name="shelf_life" id="edit_shelf_life">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="edit_price">Harga Satuan</label>
                            <input type="number" min="0" step="0.01" class="form-control" name="price" id="edit_price">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="SubmitFacilityEdit">Update</button>
                    <button type="button" class="btn btn-secondary ml-2" onclick="$('#Grid1Detail').hide()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    var hubType = '';
    var facilityType = '';
    var selectedFacilityId = null;

    $(document).ready(function() {
        initTableGrid1();

        $('#filterHubType, #filterFacilityType').on('change', function() {
            hubType = $('#filterHubType').val();
            facilityType = $('#filterFacilityType').val();
            Grid1.clearAndLoad(`master-facility-data?format=xml&hub_type=${hubType}&type=${facilityType}`);
        });
    })

    function redirectToCreateApplication() {
        window.location.href = `master-facility-create`
    }

    function initTableGrid1() {
        let tHeader = ['No.', 'Description', 'Detail', 'Ekonomis (Tahun)', 'Waktu Pakai (Tahun)', 'Jenis Aset', 'Harga Satuan']
        let tWidth = ['50', '300', '300', '120', '100', '120', '120']
        let headerAttachment = ['#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter']
        let colId = ['no', 'description', 'children', 'depreciation_year', 'shelf_life', 'asset_type', 'price'].join(',')

        Grid1 = new dhtmlXGridObject('MembershipGrid');
        Grid1.setImagePath("./dhtmlx/imgs/");
        Grid1.setHeader(tHeader.join(','));
        Grid1.setInitWidths(tWidth.join(','));
        Grid1.attachHeader(headerAttachment);
        Grid1.setColTypes(tHeader.map(() => 'ro').join(','));
        Grid1.setColumnIds(colId);
        Grid1.attachEvent("onRowSelect", selectMitra);
        Grid1.setSkin("dhx_skyblue");
        Grid1.init();

        Grid1.clearAndLoad(`master-facility-data?format=xml&facility_type=${hubType}&type=${facilityType}`);
    }

    function initDetailTableGrid1() {
        $('#Grid1Detail').show();
        getFaciltyData()
    }


    function addEditDetailItem(value) {
        $('#editDetailList').append(`
            <div class="detail-item input-group mb-1">
                <input type="text" class="form-control" name="children[]" placeholder="Enter detail" value="` + (value || '') + `">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-detail">−</button>
                </div>
            </div>
        `);
    }

    $(document).on('click', '.remove-detail', function() {
        if ($('.detail-item').length > 1) {
            $(this).closest('.detail-item').remove();
        }
    });

    $('#addEditDetail').on('click', function() {
        addEditDetailItem('');
    });

    $('#detailGrid1').on('click', function() {
        if (!selectedFacilityId) {
            alert('Pilih data terlebih dulu');
            return;
        }
        $.get('master-facility-data', {
            format: 'json',
            id: selectedFacilityId
        }, function(res) {
            var row = res.data && res.data[0];
            if (!row) {
                alert('Data tidak ditemukan');
                return;
            }
            $('#edit_id').val(row.id);
            $('#edit_type').val(row.type);
            $('#edit_category').val(row.category);
            $('#edit_description').val(row.description);
            $('#edit_asset_type').val(row.asset_type || '');
            $('#edit_depreciation_year').val(row.depreciation_year ?? '');
            $('#edit_shelf_life').val(row.shelf_life ?? '');
            $('#edit_price').val(row.price ?? '');

            var children = [];
            if (row.children) {
                try {
                    var parsed = JSON.parse(row.children);
                    children = Array.isArray(parsed) ? parsed : [String(parsed)];
                } catch (e) {
                    children = String(row.children).split(',').map(function(s) {
                        return s.trim();
                    });
                }
            }
            if (!children.length) children = [''];
            $('#editDetailList').empty();
            children.forEach(function(c) {
                addEditDetailItem(c);
            });
            $('#Grid1Detail').show();
        }).fail(function() {
            alert('Gagal memuat data');
        });
    });

    $('#SubmitFacilityEdit').on('click', function() {
        var form = document.getElementById('FacilityEditForm');
        var fd = new FormData(form);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: 'master-facility-edit',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert('Facility berhasil diupdate!');
                    $('#Grid1Detail').hide();
                    Grid1.clearAndLoad(`master-facility-data?format=xml&hub_type=${hubType || 'booth'}&type=${facilityType || 'inventory'}`);
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });

    function selectMitra(rowId, cellIdx) {
        selectedFacilityId = Grid1.getRowAttribute(rowId, 'id');
    }

    $('#deleteGrid1').on('click', function() {
        if (!selectedFacilityId) {
            alert('Pilih data terlebih dulu');
            return;
        }

        if (!confirm('Yakin ingin menghapus data ini?')) return;

        $.ajax({
            url: 'master-facility-delete',
            type: 'DELETE',
            data: {
                id: selectedFacilityId
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    Grid1.deleteRow(selectedFacilityId);
                    selectedFacilityId = null;
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });

    function doubleClickTableGrid1() {
        initDetailTableGrid1()
    }
</script>
@endsection