@include('cns.procurement.partials.non-uco-supplier-form', [
    'supplier' => $supplier,
    'categories' => $categories,
    'formId' => 'NonUcoDetailForm',
    'emailPrefix' => 'NonUcoDetail',
])

<div class="mb-3 mt-2">
    <button type="button" class="btn btn-success" id="SubmitNonUco">Simpan Perubahan</button>
    <button type="button" class="btn btn-secondary" onclick="$('#NonUcoSupplierDiv').html('');">Tutup</button>
</div>

<script>
    function addNonUcoEmailRow(value) {
        if ($('#NonUcoDetailEmailRows .nonucodetail-email-row').length >= 10) {
            return;
        }
        var row = $('<div class="input-group mb-2 nonucodetail-email-row">' +
            '<input type="email" name="Emails[]" class="form-control shadow-sm" placeholder="nama@domain.com" />' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-nonucodetail-email">Hapus</button></div>' +
            '</div>');
        row.find('input').val(value || '');
        $('#NonUcoDetailEmailRows').append(row);
    }

    $(function () {
        $('#NonUcoDetailAddEmailBtn').on('click', function () {
            if ($('#NonUcoDetailEmailRows .nonucodetail-email-row').length >= 10) {
                alert('Maksimal 10 alamat email.');
                return;
            }
            addNonUcoEmailRow('');
        });

        $(document).on('click', '.remove-nonucodetail-email', function () {
            if ($('#NonUcoDetailEmailRows .nonucodetail-email-row').length <= 1) {
                $(this).closest('.nonucodetail-email-row').find('input').val('');
                return;
            }
            $(this).closest('.nonucodetail-email-row').remove();
        });

        $('#SubmitNonUco').on('click', function () {
            $.blockUI();
            $.ajax({
                url: 'cns-procurement-non-uco-supplier-detail-submit',
                type: 'POST',
                data: $('#NonUcoDetailForm').find('input, select, textarea').not(':button').serialize(),
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
