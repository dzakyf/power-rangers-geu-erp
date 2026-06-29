@extends('layout.mainlayout-mitra')
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
            <span class="active">Approval</span>
        </li>
    </ol>
</div>

<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Buat Pengajuan Transfer Kemitraan</h5>
    <!-- <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->

    <div class="col-12">
        {{-- <div class="form-group">
            <label for="Hub_Type">Unduh Template Pengajuan<span style="color: red;">*</span></label>
            <div class="partner-profit-sharing-download">
                <a href="partner-renewal-download">Download template</a>
            </div>
        </div> --}}

        {{-- <div class="form-group">
            <label for="File_Documents">Upload Pengajuan</label>
            <div id="File_Documents" class="with-header" style='height:200px;margin:0px;width:auto;'></div>
            <input class='form-control' style="background-color:#fff" type="hidden" size="40" readonly="readonly" id="File_Document" />
        </div> --}}
        <form id="transferMembershipForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="new_mitra_name">Nama Calon Mitra Baru <span style="color: red;">*</span></label>
                        <input type="text" name="new_mitra_name" id="new_mitra_name" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mitra_select">Mitra <span style="color:red;">*</span></label>
                        <select class="form-control select2" id="mitra_select" name="mitra_id" style="width:100%" required>
                            <option value="">-- Pilih Mitra --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="type">Jenis <span style="color: red;">*</span></label>
                    <!-- <input type="text" name="Type" id="Type" class="form-control" required /> -->
                    <select name="type" id="type" class="form-control" required>
                        <option value="">-- Pilih Jenis--</option>
                        <option value="1">Pengalihan Kepemilikan</option>
                        <option value="2">Pengalihan Bagi Hasil</option>
                        <option value="3">Penggantian Pengelola</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reason">Alasan <span style="color: red;">*</span></label>
                        <input type="text" name="reason" id="reason" class="form-control" required />
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="previewBtn">Preview Document</button>
            <button type="button" class="btn btn-primary" id="submitBtn">Finalisasi Document</button>
        </form>

        @include('partner.valuation.list-detail', [
        'dataUrl' => "xml-partner-valuation-detail-aggregate"
        ])
    </div>

</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
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
                            text: $(this).find('cell').eq(1).text()
                        });
                    });
                    return {
                        results: results
                    };
                },
                cache: true
            }
        });
        let type = null;
        let year = null;
        let userId = null;
        let currentAction = 'preview'

        $('#previewBtn').on('click', function() {
            currentAction = 'preview'
            console.log('rpeview')
            $('#transferMembershipForm').submit()
        })

        $('#submitBtn').on('click', function() {
            currentAction = 'finalize'
            $('#transferMembershipForm').submit()
        })

        $('#transferMembershipForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('opportunity_id', $('#mitra_select').val())

            // $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: `partner-transfer-membership-${currentAction}-application`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                xhrFields: {
                    responseType: 'blob'
                },
                cache: false,
                success: function(response, status, xhr) {
                    let filename = 'doc.docx';
                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    if (contentDisposition) {
                        const match = contentDisposition.match(/filename=([^"]+)/);
                        if (match && match[1]) {
                            filename = match[1];
                        }
                    }
                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();

                    window.URL.revokeObjectURL(link.href);


                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

                },
                complete: function() {
                    // $('#submitBtn').prop('disabled', false).text('Submit');
                    $('#submitBtn').show()
                }
            });
        });

    })
</script>
@endsection