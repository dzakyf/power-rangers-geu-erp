@extends('layout.mainlayout-bp')
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
    <h5>Buat pengajuan terminasi</h5>

    <div class="col-12">
        <div class="form-group">
            <label for="mitra_select">Pilih Mitra <span style="color:red;">*</span></label>
            <select class="form-control select2" id="mitra_select" name="opportunity_partner_id" style="width:100%" required>
                <option value="">-- Pilih Mitra --</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nomor Kontrak Kemitraan</label>
            <input type="text" class="form-control" id="contract_no" readonly>
        </div>
        <div class="form-group">
            <label for="effective_termination_at">Tanggal efektif Terminasi <span style="color:red;">*</span></label>
            <input type="date" class="form-control" id="effective_termination_at" name="effective_termination_at" required>
        </div>
        <div class="form-group">
            <label for="type">Jenis Terminasi <span style="color:red;">*</span></label>
            <select class="form-control" id="type" name="type" required>
                <option value="">-- Pilih Jenis Terminasi --</option>
                <option value="BERAKHIR_MASA_KONTRAK">Berakhir Masa Kontrak</option>
                <option value="PENGAKHIRAN_OLEH_MITRA">Pengakhiran oleh Mitra</option>
                <option value="PENGAKHIRAN_OLEH_PERUSAHAAN">Pengakhiran oleh Perusahaan</option>
                <option value="KESEPAKATAN_BERSAMA">Kesepakatan Bersama</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reason">Alasan Terminasi <span style="color:red;">*</span></label>
            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
        </div>
        <p class="text-muted">Surat exit / terminasi kemitraan dibuat otomatis oleh sistem menggunakan data kemitraan dan tanda tangan pada profil Anda.</p>
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
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(xmlData) {
                var results = [];
                $(xmlData).find('row').each(function() {
                    results.push({
                        id: $(this).attr('Opportunity_Id'),
                        text: $(this).find('cell').eq(1).text(),
                        lead_id: $(this).find('cell').eq(3).text()
                    });
                });
                return {
                    results: results
                };
            },
            cache: true
        }
    });

    $('#mitra_select').on('select2:select', function(e) {
        $('#contract_no').val((e.params.data && e.params.data.lead_id) ? e.params.data.lead_id : '');
    });
    $('#mitra_select').on('select2:clear', function() { $('#contract_no').val(''); });

    function submitApplication(action) {
        var oppId = $('#mitra_select').val();
        if (!oppId) { alert('Pilih Mitra terlebih dahulu'); return; }

        var effective = $('#effective_termination_at').val();
        if (!effective) { alert('Tanggal efektif Terminasi wajib diisi'); return; }

        var type = $('#type').val();
        if (!type) { alert('Pilih Jenis Terminasi terlebih dahulu'); return; }

        var reason = $('#reason').val();
        if (!reason) { alert('Alasan Terminasi wajib diisi'); return; }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('opportunity_id', oppId);
        formData.append('type', type);
        formData.append('reason', reason);
        formData.append('effective_termination_at', effective);

        $.ajax({
            url: 'partner-termination-' + action + '-application',
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
@include('partner.valuation.list-detail', ['dataUrl' => "xml-partner-valuation-detail-aggregate"])
@endsection