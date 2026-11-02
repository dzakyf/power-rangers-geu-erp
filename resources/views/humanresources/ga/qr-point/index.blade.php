@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">General Affairs</a></li>
            <li class="breadcrumb-item active"><span class="active">QR Point</span></li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="GAQrPointGrid" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 mb-3">
                <button type="button" class="btn btn-info btn-sm text-white" onclick="GAQrPointGrid.clearAndLoad('hrd-ga-qr-point-data')">Refresh</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="GAQrPreview()">Preview QR</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="GAQrDeactivate()">Nonaktifkan</button>
                <button type="button" class="btn btn-success btn-sm" onclick="GAQrPrintSelected()">Cetak QR Terpilih</button>
                <a class="btn btn-secondary btn-sm" href="{{ url('hrd-ga-qr-point-print') }}" target="_blank">Cetak Semua QR</a>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3 mb-md-0">
                    <form id="GAQrPointForm">
                        @csrf
                        <input type="hidden" name="ID" />
                        <div class="row g-2">
                            <div class="col-md-3"><input type="text" name="QR_Code" class="form-control" placeholder="Kode QR" maxlength="100" required /></div>
                            <div class="col-md-4"><input type="text" name="Name" class="form-control" placeholder="Nama lokasi" maxlength="150" required /></div>
                            <div class="col-md-3">
                                <select name="Point_Type" class="form-control" required>
                                    <option value="LOCATION">LOCATION</option>
                                    <option value="ROOM">ROOM</option>
                                    <option value="CHECKPOINT">CHECKPOINT</option>
                                    <option value="POST">POST</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="Status" class="form-control">
                                    <option value="ACTIVE" selected>ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                            <div class="col-md-3"><input type="text" name="Building" class="form-control" placeholder="Gedung" maxlength="100" /></div>
                            <div class="col-md-2"><input type="text" name="Floor" class="form-control" placeholder="Lantai" maxlength="50" /></div>
                            <div class="col-md-4"><input type="text" name="Room" class="form-control" placeholder="Ruangan" maxlength="100" /></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Simpan QR Point</button></div>
                        </div>
                        <small class="text-muted d-block mt-1">Klik dua kali baris tabel untuk mengedit QR Point.</small>
                    </form>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border rounded p-2">
                        <img id="GAQrPreview" src="" alt="QR Preview" style="max-width:100%;height:180px;display:none;" />
                        <div id="GAQrPreviewEmpty" class="text-muted p-4">Pilih QR point untuk preview.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function GAQrPreview() {
    var id = GAQrPointGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris QR point terlebih dahulu.');
        return;
    }
    var code = GAQrPointGrid.cells(id, 1).getValue();
    $('#GAQrPreview').attr('src', 'hrd-ga-qr-point-image?code=' + encodeURIComponent(code)).show();
    $('#GAQrPreviewEmpty').hide();
}

function GAQrPrintSelected() {
    var id = GAQrPointGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris QR point terlebih dahulu.');
        return;
    }
    window.open('{{ url('hrd-ga-qr-point-print') }}?filter=1&ids[]=' + encodeURIComponent(id), '_blank');
}

function GAQrDeactivate() {
    var id = GAQrPointGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris QR point terlebih dahulu.');
        return;
    }
    if (!confirm('Nonaktifkan QR point ini?')) return;
    $.post('hrd-ga-qr-point-delete', {ID: id, _token: '{{ csrf_token() }}'}, function (response) {
        alert(response.message);
        GAQrPointGrid.clearAndLoad('hrd-ga-qr-point-data');
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menonaktifkan.');
    });
}

GAQrPointGrid = new dhtmlXGridObject('GAQrPointGrid');
GAQrPointGrid.setImagePath('./dhtmlx/imgs/');
GAQrPointGrid.setHeader('No,Kode QR,Nama,Tipe,Gedung,Lantai,Ruangan,Status');
GAQrPointGrid.attachHeader('&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#select_filter');
GAQrPointGrid.setInitWidths('50,160,220,120,150,90,150,100');
GAQrPointGrid.setColAlign('center,center,left,center,left,center,left,center');
GAQrPointGrid.setColSorting('int,str,str,str,str,str,str,str');
GAQrPointGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro');
GAQrPointGrid.setSkin('dhx_skyblue');
GAQrPointGrid.init();
GAQrPointGrid.attachEvent('onRowDblClicked', function (rowId) {
    if (!rowId) return true;
    var form = $('#GAQrPointForm');
    form.find('input[name="ID"]').val(rowId);
    form.find('input[name="QR_Code"]').val(GAQrPointGrid.cells(rowId, 1).getValue());
    form.find('input[name="Name"]').val(GAQrPointGrid.cells(rowId, 2).getValue());
    form.find('select[name="Point_Type"]').val(GAQrPointGrid.cells(rowId, 3).getValue());
    form.find('input[name="Building"]').val(GAQrPointGrid.cells(rowId, 4).getValue());
    form.find('input[name="Floor"]').val(GAQrPointGrid.cells(rowId, 5).getValue());
    form.find('input[name="Room"]').val(GAQrPointGrid.cells(rowId, 6).getValue());
    form.find('select[name="Status"]').val(GAQrPointGrid.cells(rowId, 7).getValue());
    GAQrPreview();
    return true;
});
GAQrPointGrid.clearAndLoad('hrd-ga-qr-point-data');

$('#GAQrPointForm').on('submit', function (event) {
    event.preventDefault();
    var form = $(this);
    var button = form.find('button[type=submit]');
    button.prop('disabled', true);
    $.post('hrd-ga-qr-point-save', form.serialize(), function (response) {
        if (response.status === 'success') {
            alert(response.message);
            form[0].reset();
            form.find('input[name=ID]').val('');
            GAQrPointGrid.clearAndLoad('hrd-ga-qr-point-data');
        } else {
            alert(response.message || 'Gagal menyimpan.');
        }
    }).fail(function (xhr) {
        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan.');
    }).always(function () {
        button.prop('disabled', false);
    });
});
</script>
@endsection
