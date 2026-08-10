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
    <h5>Buat pengajuan bagi hasil</h5>
    <form id="requestProfitSharing" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="col-3">
            <div class="form-group">
                <label for="Warehouse">Warehouse <span style="color:red;">*</span></label>
                <select name="Warehouse" id="Warehouse" class="form-control" required>
                    <option value="">-- Pilih Warehouse --</option>
                    @foreach ($warehouses as $wh)
                        <option value="{{ $wh->warehouse_task_id }}">{{ $wh->warehouse_task_id }} - {{ $wh->warehouse_location }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-3">
            <div class="form-group">
                <label for="Periode">Periode Bagi Hasil <span style="color:red;">*</span></label>
                <input type="month" name="Periode" id="Periode" class="form-control" required />
            </div>
        </div>

        <button type="button" class="btn btn-primary" id="submitBtn" style="display:none;">Finalisasi Document</button>
    </form>

    <div class="form-group">
        <label>Upload Tanda Tangan Pengajuan</label>

        @include('ui-components.signature-canvas-field', [
        'id' => 'profit_sharing_sig',
        'name' => 'signature',
        'label' => 'Tanda Tangan',
        'required' => true,
        'height' => 200,
        ])

        <div style="margin-top: 10px;">
            <button type="button" class="btn btn-success" id="previewBtn">Submit & Preview</button>
            <button type="button" class="btn btn-primary" id="finalizeBtn">Finalize Document</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        Signature.init('profit_sharing_sig');

        function getSignatureBlob(callback) {
            var canvas = document.getElementById('sig_canvas_profit_sharing_sig');
            if (!canvas) {
                alert('Canvas not found');
                return;
            }
            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Harap tanda tangan terlebih dahulu');
                    return;
                }
                callback(blob);
            });
        }

        function validateSelection() {
            if (!$('#Warehouse').val()) {
                alert('Pilih warehouse terlebih dahulu');
                return false;
            }
            if (!$('#Periode').val()) {
                alert('Pilih periode terlebih dahulu');
                return false;
            }
            return true;
        }

        $('#previewBtn').on('click', function() {
            if (!validateSelection()) return;
            getSignatureBlob(function(blob) {
                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('signature', blob, 'signature.png');
                fd.append('periode', $('#Periode').val());
                fd.append('warehouse_id', $('#Warehouse').val());

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'partner-profit-sharing-preview-application');
                xhr.responseType = 'blob';
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(xhr.response);
                        link.download = 'profit-sharing-application.docx';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                    } else {
                        alert('Error generating document');
                    }
                };
                xhr.send(fd);
            });
        });

        $('#finalizeBtn').on('click', function() {
            if (!validateSelection()) return;
            getSignatureBlob(function(blob) {
                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('signature', blob, 'signature.png');
                fd.append('periode', $('#Periode').val());
                fd.append('warehouse_id', $('#Warehouse').val());
                fd.append('opportunity_id', '{{ $partner->id }}');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'partner-profit-sharing-submit-application');
                xhr.responseType = 'blob';
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(xhr.response);
                        link.download = 'profit-sharing-application.docx';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                    } else {
                        alert('Error finalizing document');
                    }
                };
                xhr.send(fd);
            });
        });
    });
</script>
@endsection