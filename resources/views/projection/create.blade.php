@extends('layout.mainlayout-bp')
@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Proyeksi</span></li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat proyeksi</h5>

    <div class="col-md-6">
        <div class="form-group">
            <label for="Hub_Type">Template Proyeksi<span style="color: red;">*</span></label>
            <div class="">
                <a href="projection-template">Download template</a>
            </div>
        </div>
        <div id="SelectMitra" class="form-group">
            <label for="Mitra">Pilih Mitra <span style="color: red;">*</span></label>
            <select name="Mitra" id="Mitra" class="form-control" required>
                <option value="">-- Pilih Mitra--</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="BEP_At">Bulan BEP <span style="color: red;">*</span></label>
                <input type="number" class="form-control" min="1" max="48" id="BEP_At" name="BEP_At" required>
            </div>
            <div class="form-group col-md-6">
                <label for="BEP_Projection">Besaran BEP <span style="color: red;">*</span></label>
                <input type="number" class="form-control" min="0" id="BEP_Projection" name="BEP_Projection" required>
            </div>
        </div>

        <div class="form-group">
            <label for="File_Documents">Upload Proyeksi</label>
            <input type="file" class="form-control" id="fileInput" accept=".xlsx,.xls" required />
        </div>

        <button type="button" class="btn btn-primary" id="submitBtn">Submit</button>
    </div>
</div>

<script>
    let mitraId = null;
    $(document).ready(function() {
        $('#Mitra').select2({
            ajax: {
                url: 'partner-data-active-mitra',
                dataType: 'xml',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(xmlData) {
                    var results = [];
                    $(xmlData).find('row').each(function() {
                        var cells = $(this).find('cell');
                        results.push({
                            id: $(this).attr('Opportunity_Id'),
                            text: cells.eq(1).text(),
                            cell2: cells.eq(2).text(),
                        });
                    });
                    return {
                        results: results,
                        pagination: {
                            more: results.length === 20
                        }
                    };
                }
            },
            minimumInputLength: 0,
            placeholder: 'Cari Mitra...',
            allowClear: true
        });

        $('#Mitra').on('select2:select', function(e) {
            mitraId = e.params.data.id;
        });

        $('#Mitra').on('select2:unselect', function(e) {
            mitraId = null;
        });

        $('#submitBtn').on('click', function() {
            var mitra = mitraId;
            var bepAt = $('#BEP_At').val();
            var bepProjection = $('#BEP_Projection').val();

            if (!mitra) {
                alert('Pilih Mitra terlebih dulu');
                return;
            }
            if (!bepAt || bepAt < 1 || bepAt > 48) {
                alert('Bulan BEP harus diisi (1-48)');
                return;
            }
            if (!bepProjection) {
                alert('Besaran BEP harus diisi');
                return;
            }

            var fileInput = document.getElementById('fileInput');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Upload file proyeksi terlebih dulu');
                return;
            }

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('mitra_id', mitra);
            formData.append('bep_at', bepAt);
            formData.append('bep_projection', bepProjection);
            formData.append('file', fileInput.files[0]);

            $.blockUI();

            $.ajax({
                url: 'projection-upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $.unblockUI();
                    if (res.success) {
                        alert('Data berhasil disimpan');
                        window.location.href = 'projection-index'
                    } else {
                        alert('Gagal: ' + (res.message || ''));
                    }
                },
                error: function(xhr) {
                    $.unblockUI();
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    });
</script>
@endsection