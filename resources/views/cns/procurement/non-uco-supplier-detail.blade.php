@php
    $emailList = count($emails) > 0 ? $emails : [''];
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">DETAIL SUPPLIER NON-UCO</div>
    <div class="card">
        <div class="card-body">
            <div id="ContentPagesNonUco">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="ID" id="ID" value="{{ $supplier->ID }}" />

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Vendor</label>
                            <input type="text" name="Company_Name" id="Company_Name" value="{{ $supplier->Company_Name }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="Phone" id="Phone" value="{{ $supplier->Phone }}" class="form-control shadow-sm" required />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Email (maksimal 10)</label>
                    <div id="EmailRows">
                        @foreach($emailList as $email)
                            <div class="input-group mb-2 email-row">
                                <input type="email" name="Emails[]" value="{{ $email }}" class="form-control shadow-sm" placeholder="nama@domain.com" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger remove-email">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="AddEmailBtn">+ Tambah Email</button>
                    <small class="text-muted d-block">Maksimal 10 alamat email.</small>
                </div>

                <div class="form-group">
                    <label>Kategori Supplier</label>
                    <select name="Supply_Category" id="Supply_Category" class="form-control shadow-sm" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $supplier->Supply_Category === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="button" class="btn btn-success" id="SubmitNonUco">Simpan Perubahan</button>
            <button type="button" class="btn btn-secondary" onclick="$('#NonUcoSupplierDiv').html('');">Tutup</button>
        </div>
    </div>
</div>

<script>
    function addEmailRow(value) {
        if ($('#EmailRows .email-row').length >= 10) {
            return;
        }
        var row = $('<div class="input-group mb-2 email-row">' +
            '<input type="email" name="Emails[]" class="form-control shadow-sm" placeholder="nama@domain.com" />' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-email">Hapus</button></div>' +
            '</div>');
        row.find('input').val(value || '');
        $('#EmailRows').append(row);
    }

    $(function () {
        $('#AddEmailBtn').on('click', function () {
            if ($('#EmailRows .email-row').length >= 10) {
                alert('Maksimal 10 alamat email.');
                return;
            }
            addEmailRow('');
        });

        $(document).on('click', '.remove-email', function () {
            if ($('#EmailRows .email-row').length <= 1) {
                $(this).closest('.email-row').find('input').val('');
                return;
            }
            $(this).closest('.email-row').remove();
        });

        $('#SubmitNonUco').on('click', function () {
            $.blockUI();
            $.ajax({
                url: 'cns-procurement-non-uco-supplier-detail-submit',
                type: 'POST',
                data: $('#ContentPagesNonUco').find('input, select, textarea').not(':button').serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    $.unblockUI();
                    if (String(data).indexOf('success') !== -1) {
                        alert('Supplier Non-UCO berhasil diperbarui.');
                        if (typeof refreshdata === 'function') { refreshdata(); }
                    } else {
                        alert('Gagal memperbarui supplier.');
                    }
                },
                error: function (xhr) {
                    $.unblockUI();
                    var message = 'Gagal memperbarui supplier.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            var first = Object.keys(xhr.responseJSON.errors)[0];
                            message = xhr.responseJSON.errors[first][0];
                        } else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                    }
                    alert(message);
                }
            });
        });
    });
</script>
