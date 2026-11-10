@include('cns.procurement.partials.non-uco-supplier-form', [
    'supplier' => $supplier,
    'categories' => $categories,
    'formId' => 'NonUcoDetailForm',
    'fieldPrefix' => 'NonUcoDetail',
])

<div class="mb-3 mt-2">
    <button type="button" class="btn btn-success" id="SubmitNonUco">Simpan Perubahan</button>
    <button type="button" class="btn btn-secondary" onclick="$('#NonUcoSupplierDiv').html('');">Tutup</button>
</div>

<script>
    $(function () {
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
