@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="partner-outstock">Outstock</a></li>
        <li class="breadcrumb-item active"><span class="active">Create Draft</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Buat Outstock Draft</h5>
                </div>
                <div class="card-body">
                    <form id="outstockForm">
                        @csrf
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="Date" id="Date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Kategori Kendaraan</label>
                            <select class="form-control" name="Vehicle_Category" id="Vehicle_Category">
                                <option value="">-- Pilih --</option>
                                <option value="mobil">Mobil</option>
                                <option value="booth">Booth</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Fleet</label>
                            <select class="form-control" id="Fleet_ID" name="Fleet_ID" style="width:100%;"></select>
                        </div>

                        <div class="form-group">
                            <label>Warehouse Asal</label>
                            <select class="form-control" id="Warehouse_IDs" name="Warehouse_IDs[]" multiple="multiple" style="width:100%;"></select>
                        </div>

                        <div class="form-group">
                            <label>Warehouse Tujuan</label>
                            <input type="text" class="form-control" disabled value="(Kosong - akan diisi nanti)">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="Notes" rows="3"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary" id="btnSubmit">Simpan Draft</button>
                        <a href="partner-outstock" class="btn btn-secondary ml-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#Fleet_ID').select2({
            placeholder: '-- Pilih Fleet --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'master-fleet-data',
                dataType: 'json',
                delay: 250,
                data: function(p) { return { q: p.term || '' }; },
                processResults: function(res) {
                    return { results: (res.data || []).map(function(r) {
                        return { id: r.ID, text: r.Police_Number + ' - ' + (r.Fleet_Brand || '') };
                    })};
                },
                cache: true
            }
        });

        $('#Warehouse_IDs').select2({
            placeholder: '-- Pilih Warehouse --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'partner-warehouse-data',
                dataType: 'json',
                delay: 250,
                data: function(p) { return { q: p.term || '' }; },
                processResults: function(res) {
                    return { results: (res.data || []).map(function(r) {
                        return { id: r.id, text: r.warehouse_location + ' - ' + (r.warehouse_task_id || '') };
                    })};
                },
                cache: true
            }
        });

        $('#btnSubmit').on('click', function() {
            var form = document.getElementById('outstockForm');
            if (!form.checkValidity()) { form.reportValidity(); return; }

            $.ajax({
                url: 'partner-outstock-create-submit',
                type: 'POST',
                data: new FormData(form),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Draft berhasil dibuat');
                        window.location.href = 'partner-outstock';
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    });
</script>
@endsection
