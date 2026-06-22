@extends('layout.mainlayout-mitra')
@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Approval</span></li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat pengajuan renewal</h5>

    <div class="col-12">
        <div class="form-group">
            <label for="Hub_Type">Unduh Template Pengajuan<span style="color: red;">*</span></label>
            <div class="partner-profit-sharing-download">
                <a href="partner-renewal-download">Download template</a>
            </div>
        </div>
        <div class="form-group">
            <label for="mitra_select">Pilih Mitra <span style="color:red;">*</span></label>
            <select class="form-control select2" id="mitra_select" name="opportunity_partner_id" style="width:100%" required>
                <option value="">-- Pilih Mitra --</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fileInput">Upload Pengajuan</label>
            <input type="file" class="form-control" id="fileInput" name="file" accept=".docx,.pdf,.xlsx" required />
        </div>
        <button type="button" class="btn btn-primary" id="submitBtn">Submit</button>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $('#mitra_select').select2({
        placeholder: '-- Pilih Mitra --',
        allowClear: true,
        width: '100%',
        ajax: {
            url: 'partner-data-active-mitra',
            dataType: 'xml',
            delay: 300,
            data: function(params) {
                return { search: params.term, page: params.page || 1 };
            },
            processResults: function(xmlData) {
                var results = [];
                $(xmlData).find('row').each(function() {
                    results.push({
                        id: $(this).attr('Opportunity_Id'),
                        text: $(this).find('cell').eq(1).text()
                    });
                });
                return { results: results };
            },
            cache: true
        }
    });

    $('#submitBtn').on('click', function() {
        var oppId = $('#mitra_select').val();
        if (!oppId) { alert('Pilih Mitra terlebih dahulu'); return; }

        var fileInput = document.getElementById('fileInput');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Pilih file terlebih dahulu');
            return;
        }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('file', fileInput.files[0]);
        formData.append('user_id', new URLSearchParams(window.location.search).get('id'));
        formData.append('opportunity_partner_id', oppId);

        $.ajax({
            url: 'partner-renewal-submit-application',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.message === 'success') {
                    alert('Pengajuan berhasil disubmit');
                    window.location.href = 'partner-renewal-renewal-index';
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });
</script>
@endsection