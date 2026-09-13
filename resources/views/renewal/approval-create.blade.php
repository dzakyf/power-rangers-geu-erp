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
            <label for="mitra_select">Pilih Mitra <span style="color:red;">*</span></label>
            <select class="form-control select2" id="mitra_select" name="opportunity_partner_id" style="width:100%" required>
                <option value="">-- Pilih Mitra --</option>
            </select>
        </div>
        <p class="text-muted">Surat permohonan perpanjangan kemitraan dibuat otomatis oleh sistem menggunakan data kemitraan dan tanda tangan pada profil Anda.</p>
        <button type="button" class="btn btn-success" id="previewBtn">Preview</button>
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

    function submitApplication(action) {
        var oppId = $('#mitra_select').val();
        if (!oppId) { alert('Pilih Mitra terlebih dahulu'); return; }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('opportunity_id', oppId);

        $.ajax({
            url: 'partner-renewal-' + action + '-application',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: { responseType: 'blob' },
            cache: false,
            success: function(response, status, xhr) {
                var filename = 'document.docx';
                var cd = xhr.getResponseHeader('Content-Disposition');
                if (cd) {
                    var match = cd.match(/filename=([^"]+)/);
                    if (match && match[1]) { filename = match[1].replace(/["]/g, ''); }
                }
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                link.click();
                window.URL.revokeObjectURL(link.href);

                if (action === 'finalize') {
                    alert('Pengajuan berhasil disubmit');
                }
            },
            error: function(xhr) {
                alert('Error Internal!');
            }
        });
    }

    $('#previewBtn').on('click', function() { submitApplication('preview'); });
    $('#submitBtn').on('click', function() { submitApplication('finalize'); });
</script>
@endsection