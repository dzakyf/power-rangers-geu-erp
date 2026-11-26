@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Master Kemasan',
    'breadcrumbs' => [
        ['label' => 'Master Data'],
        ['label' => 'Master Kemasan'],
    ],
])

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Data Master Kemasan</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">Tambah Kemasan</button>
            <button type="button" class="btn btn-sm btn-info" onclick="openEditModal()">Edit Kemasan</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="submitDelete()">Hapus Kemasan</button>
            <button type="button" class="btn btn-sm btn-info" id="ImportKemasanBtn" data-bs-toggle="modal" data-bs-target="#ImportKemasanModal" data-toggle="modal" data-target="#ImportKemasanModal" onclick="$('#ImportKemasanModal').modal('show');">Import Excel</button>
            <button type="button" class="btn btn-sm btn-success" onclick="MasterKemasanGrid.toExcel('./export/generate.php');">Excel</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label for="filterKemasanName" class="d-block mb-1"><small>Nama Kemasan</small></label>
                <input type="text" id="filterKemasanName" class="text-black form-control form-control-sm" style="width:200px;" placeholder="Semua Kemasan" onkeyup="if(event.key==='Enter') refreshdata()" />
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
                <button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
            </div>
        </div>

        <div class="cns-grid-wrapper">
            <div id="MasterKemasanGrid" class="cns-grid" style="width:auto; height:400px;"></div>
        </div>
    </div>
</div>

@include('cns.master.partials.import-modal', [
    'modalId' => 'ImportKemasanModal',
    'title' => 'Import Master Kemasan (Excel)',
    'templateUrl' => url('cns-master-kemasan-import-template'),
    'importUrl' => url('cns-master-kemasan-import'),
    'itemLabel' => 'kemasan',
    'hint' => 'Wajib isi <code>Name</code>, <code>product_type</code> (<code>Kemasan</code>/<code>Export</code>), dan <code>Volume</code> (untuk tipe <code>Kemasan</code>).',
])

<!-- Modal Tambah / Edit Master Kemasan -->
<div class="modal fade" id="modalKemasan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formKemasan" method="POST" action="{{ route('cns-master-kemasan.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalKemasanTitle">Tambah Master Kemasan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalKemasan').modal('hide');"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Kemasan <span class="text-danger">*</span></label>
                        <input type="text" name="Name" id="inputKemasanName" class="text-black form-control form-control-sm" maxlength="150" placeholder="Contoh: Flexibag 24KL / Drum 200L" required />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Product Type <span class="text-danger">*</span></label>
                        <select name="product_type" id="inputKemasanProductType" class="text-black form-control form-control-sm" onchange="toggleMaxStockField()" required>
                            <option value="Kemasan">Kemasan</option>
                            <option value="Product Specification">Product Specification</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Volume (Liter) <span class="text-danger" id="reqKemasanVolume">*</span></label>
                        <input type="text" inputmode="decimal" name="Volume" id="inputKemasanVolume" class="text-black form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 200.00" required />
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" name="Is_Ampas" id="inputKemasanIsAmpas" value="1" onchange="toggleMaxStockField()" />
                        <label class="form-check-label font-weight-bold" for="inputKemasanIsAmpas">Ampas (tanpa Max Stock &amp; Excess)</label>
                    </div>
                    <div class="form-group" id="groupKemasanMaxStock">
                        <label class="font-weight-bold">Max Stock <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" name="Max_Stock" id="inputKemasanMaxStock" class="text-black form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 100.00" required />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Unit</label>
                        <input type="text" name="Unit" id="inputKemasanUnit" class="text-black form-control form-control-sm" maxlength="50" placeholder="Contoh: Pcs / Kg / Liter" />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Price (IDR)</label>
                        <input type="text" inputmode="decimal" name="Price" id="inputKemasanPrice" class="text-black form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 50,000.00" value="0.00" />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Serial Number Tersedia</label>
                        <textarea name="serial_numbers" id="inputKemasanSerialNumbers" class="text-black form-control form-control-sm" rows="2" placeholder="Pisahkan dengan koma atau baris baru (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalKemasan').modal('hide');">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="formDeleteKemasan" method="POST" style="display:none;">
    @csrf
</form>

<script>
    var MasterKemasanGrid = new dhtmlXGridObject('MasterKemasanGrid');
    MasterKemasanGrid.setImagePath("./dhtmlx/imgs/");
    MasterKemasanGrid.setHeader("No.,Kemasan_ID,Name,Product Type,Volume (L),Jenis,Max Stock,Unit,Price,Serial Number,Created At,Updated At");
    MasterKemasanGrid.setInitWidths("50,90,*,140,100,90,100,80,110,180,135,135");
    MasterKemasanGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    MasterKemasanGrid.setColAlign("center,center,left,center,right,center,right,center,right,left,center,center");
    MasterKemasanGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    MasterKemasanGrid.setColSorting("int,int,str,str,int,str,int,str,int,str,str,str");
    MasterKemasanGrid.attachEvent("onRowDblClicked", openEditModal);
    MasterKemasanGrid.setSkin("dhx_skyblue");
    MasterKemasanGrid.init();

    var MasterKemasanPagination = initCnsGridPagination(MasterKemasanGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        getUrl: function (params) {
            var q = new URLSearchParams();
            q.append('posStart', params.posStart);
            q.append('count', params.count);
            if (params.sortCol !== null && params.sortCol !== undefined) {
                q.append('sort_col', params.sortCol);
                q.append('sort_dir', params.sortDir || 'asc');
            }
            if (params.filters) {
                Object.keys(params.filters).forEach(function (colIdx) {
                    q.append('filter[' + colIdx + ']', params.filters[colIdx]);
                });
            }
            var name = $('#filterKemasanName').val();
            if (name) q.append('name', name);
            return "{{ route('cns-master-kemasan.xml') }}" + '?' + q.toString();
        }
    });

    function toggleMaxStockField() {
        var isAmpas = $('#inputKemasanIsAmpas').is(':checked');
        var isSpec = $('#inputKemasanProductType').val() === 'Export';
        $('#inputKemasanVolume').prop('required', !isSpec);
        $('#reqKemasanVolume').toggle(!isSpec);
        $('#inputKemasanMaxStock').prop('disabled', isAmpas).prop('required', !isAmpas && !isSpec);
        $('#groupKemasanMaxStock').toggle(!isAmpas);
        if (isAmpas) {
            $('#inputKemasanMaxStock').val('');
        }
    }

    function refreshdata() {
        MasterKemasanPagination.reload(true);
    }

    function resetFilters() {
        $('#filterKemasanName').val('');
        refreshdata();
    }

    function openCreateModal() {
        $('#modalKemasanTitle').text('Tambah Master Kemasan');
        $('#formKemasan').attr('action', "{{ route('cns-master-kemasan.store') }}");
        $('#inputKemasanName').val('');
        $('#inputKemasanProductType').val('Kemasan');
        $('#inputKemasanVolume').val('');
        $('#inputKemasanIsAmpas').prop('checked', false);
        $('#inputKemasanMaxStock').val('');
        $('#inputKemasanUnit').val('');
        $('#inputKemasanPrice').val('0.00');
        $('#inputKemasanSerialNumbers').val('');
        toggleMaxStockField();
        $('#modalKemasan').modal('show');
    }

    function openEditModal() {
        var id = MasterKemasanGrid.getSelectedId();
        if (!id) {
            swal('Perhatian', 'Pilih data kemasan terlebih dahulu.', 'warning');
            return;
        }
        var productType = MasterKemasanGrid.cells(id, 3).getValue() || 'Kemasan';
        var volume = MasterKemasanGrid.cells(id, 4).getValue();
        var jenis = MasterKemasanGrid.cells(id, 5).getValue();
        var maxStock = MasterKemasanGrid.cells(id, 6).getValue();
        var unit = MasterKemasanGrid.cells(id, 7).getValue();
        var price = MasterKemasanGrid.cells(id, 8).getValue();
        var serialNumbers = MasterKemasanGrid.cells(id, 9).getValue();

        $('#modalKemasanTitle').text('Edit Master Kemasan');
        $('#formKemasan').attr('action', "{{ url('cns-master-kemasan-update') }}/" + id);
        $('#inputKemasanName').val(MasterKemasanGrid.cells(id, 2).getValue());
        $('#inputKemasanProductType').val(productType);
        $('#inputKemasanVolume').val(cnsFormatNumber(cnsParseNumber(volume, 0), 2));
        $('#inputKemasanIsAmpas').prop('checked', jenis === 'Ampas');
        $('#inputKemasanMaxStock').val(maxStock === '-' ? '' : cnsFormatNumber(cnsParseNumber(maxStock, 0), 2));
        $('#inputKemasanUnit').val(unit === '-' ? '' : unit);
        $('#inputKemasanPrice').val(price === '-' || price === '' ? '0.00' : cnsFormatNumber(cnsParseNumber(price, 0), 2));
        $('#inputKemasanSerialNumbers').val(serialNumbers === '-' ? '' : serialNumbers);
        toggleMaxStockField();
        $('#modalKemasan').modal('show');
    }

    function submitDelete() {
        var id = MasterKemasanGrid.getSelectedId();
        if (!id) {
            swal('Perhatian', 'Pilih data kemasan terlebih dahulu.', 'warning');
            return;
        }
        var name = MasterKemasanGrid.cells(id, 2).getValue();
        swal({
            title: 'Konfirmasi',
            text: 'Hapus master kemasan "' + name + '"?',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: true
        }).then(function(confirmed) {
            if (!confirmed) {
                return;
            }
            var form = $('#formDeleteKemasan');
            form.attr('action', "{{ url('cns-master-kemasan-delete') }}/" + id);
            form.submit();
        });
    }

    refreshdata();
</script>
@endsection
