@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>CNS</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active"><span class="active">Master Kemasan</span></li>
    </ol>
</div>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER KEMASAN</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card w-100">
        <div class="card-body">
            <div class="d-flex align-items-end flex-wrap gap-2 mb-3">
                <div class="m-1">
                    <label class="d-block mb-1"><small>Nama Kemasan</small></label>
                    <input type="text" id="filterKemasanName" class="form-control form-control-sm" style="width:200px;" placeholder="Semua Kemasan" onkeyup="if(event.key==='Enter') refreshdata()" />
                </div>
                <div class="m-1">
                    <button class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
                    <button class="btn btn-sm btn-danger ml-1" onclick="resetFilters()">Reset</button>
                </div>
            </div>

            <div class="m-2">
                <div id="MasterKemasanGrid" style="width:auto; height:400px;"></div>
            </div>

            <div class="mt-2">
                <button class="btn btn-primary" onclick="openCreateModal()">Tambah Kemasan</button>
                <button class="btn btn-info" onclick="openEditModal()">Edit Kemasan</button>
                <button class="btn btn-danger" onclick="submitDelete()">Hapus Kemasan</button>
                <button class="btn btn-success" onclick="MasterKemasanGrid.toExcel('./export/generate.php');">Excel</button>
            </div>
        </div>
    </div>
</div>

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
                        <input type="text" name="Name" id="inputKemasanName" class="form-control form-control-sm" maxlength="150" placeholder="Contoh: Flexibag 24KL / Drum 200L" required />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Volume (Liter) <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" name="Volume" id="inputKemasanVolume" class="form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 200.00" required />
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" name="Is_Ampas" id="inputKemasanIsAmpas" value="1" onchange="toggleMaxStockField()" />
                        <label class="form-check-label font-weight-bold" for="inputKemasanIsAmpas">Ampas (tanpa Max Stock &amp; Excess)</label>
                    </div>
                    <div class="form-group" id="groupKemasanMaxStock">
                        <label class="font-weight-bold">Max Stock <span class="text-danger">*</span></label>
                        <input type="text" inputmode="decimal" name="Max_Stock" id="inputKemasanMaxStock" class="form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 100.00" required />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Unit</label>
                        <input type="text" name="Unit" id="inputKemasanUnit" class="form-control form-control-sm" maxlength="50" placeholder="Contoh: Pcs / Kg / Liter" />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Price (IDR)</label>
                        <input type="text" inputmode="decimal" name="Price" id="inputKemasanPrice" class="form-control form-control-sm text-right cns-decimal" placeholder="Contoh: 50,000.00" value="0.00" />
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
    MasterKemasanGrid.setHeader("No.,Kemasan_ID,Name,Volume (L),Jenis,Max Stock,Unit,Price,Created At,Updated At");
    MasterKemasanGrid.setInitWidths("50,100,*,110,100,110,90,120,145,145");
    MasterKemasanGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    MasterKemasanGrid.setColAlign("center,center,left,right,center,right,center,right,center,center");
    MasterKemasanGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
    MasterKemasanGrid.setColSorting("int,int,str,int,str,int,str,int,str,str");
    MasterKemasanGrid.attachEvent("onRowDblClicked", openEditModal);
    MasterKemasanGrid.enableSmartRendering(true, 50);
    MasterKemasanGrid.setSkin("dhx_skyblue");
    MasterKemasanGrid.init();

    function toggleMaxStockField() {
        var isAmpas = $('#inputKemasanIsAmpas').is(':checked');
        $('#inputKemasanMaxStock').prop('disabled', isAmpas).prop('required', !isAmpas);
        $('#groupKemasanMaxStock').toggle(!isAmpas);
        if (isAmpas) {
            $('#inputKemasanMaxStock').val('');
        }
    }

    function refreshdata() {
        var name = $('#filterKemasanName').val();
        var url = "{{ route('cns-master-kemasan.xml') }}" + (name ? '?name=' + encodeURIComponent(name) : '');
        MasterKemasanGrid.clearAndLoad(url);
    }

    function resetFilters() {
        $('#filterKemasanName').val('');
        refreshdata();
    }

    function openCreateModal() {
        $('#modalKemasanTitle').text('Tambah Master Kemasan');
        $('#formKemasan').attr('action', "{{ route('cns-master-kemasan.store') }}");
        $('#inputKemasanName').val('');
        $('#inputKemasanVolume').val('');
        $('#inputKemasanIsAmpas').prop('checked', false);
        $('#inputKemasanMaxStock').val('');
        $('#inputKemasanUnit').val('');
        $('#inputKemasanPrice').val('0.00');
        toggleMaxStockField();
        $('#modalKemasan').modal('show');
    }

    function openEditModal() {
        var id = MasterKemasanGrid.getSelectedId();
        if (!id) {
            alert('Pilih data kemasan terlebih dahulu.');
            return;
        }
        var jenis = MasterKemasanGrid.cells(id, 4).getValue();
        var maxStock = MasterKemasanGrid.cells(id, 5).getValue();
        var unit = MasterKemasanGrid.cells(id, 6).getValue();
        var price = MasterKemasanGrid.cells(id, 7).getValue();

        $('#modalKemasanTitle').text('Edit Master Kemasan');
        $('#formKemasan').attr('action', "{{ url('cns-master-kemasan-update') }}/" + id);
        $('#inputKemasanName').val(MasterKemasanGrid.cells(id, 2).getValue());
        $('#inputKemasanVolume').val(cnsFormatNumber(cnsParseNumber(MasterKemasanGrid.cells(id, 3).getValue(), 0), 2));
        $('#inputKemasanIsAmpas').prop('checked', jenis === 'Ampas');
        $('#inputKemasanMaxStock').val(maxStock === '-' ? '' : cnsFormatNumber(cnsParseNumber(maxStock, 0), 2));
        $('#inputKemasanUnit').val(unit === '-' ? '' : unit);
        $('#inputKemasanPrice').val(price === '-' || price === '' ? '0.00' : cnsFormatNumber(cnsParseNumber(price, 0), 2));
        toggleMaxStockField();
        $('#modalKemasan').modal('show');
    }

    function submitDelete() {
        var id = MasterKemasanGrid.getSelectedId();
        if (!id) {
            alert('Pilih data kemasan terlebih dahulu.');
            return;
        }
        var name = MasterKemasanGrid.cells(id, 2).getValue();
        if (!confirm('Hapus master kemasan "' + name + '"?')) {
            return;
        }
        var form = $('#formDeleteKemasan');
        form.attr('action', "{{ url('cns-master-kemasan-delete') }}/" + id);
        form.submit();
    }

    refreshdata();
</script>
@endsection
