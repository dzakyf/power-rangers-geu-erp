<div class="mt-3">
    <div class="card">
        <div class="card-body">
            <div class="form-group mb-0">
                <label for="Purchase_Group">Pilih Kelompok Pembelian</label>
                <select id="Purchase_Group" class="form-control">
                    <option value="UCO">UCO</option>
                    <option value="Non-UCO">Non-UCO</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="UcoSection">
    @include('cns.procurement.partials.buyer-contract-form', ['contract' => null])
</div>

<div id="NonUcoSection" style="display:none;">
    @include('cns.procurement.partials.non-uco-supplier-form', [
        'supplier' => null,
        'categories' => $nonUcoCategories,
        'formId' => 'NonUcoNewForm',
        'emailPrefix' => 'NonUcoNew',
    ])
</div>

<div class="mb-3 mt-2">
    <button type="button" class="btn btn-success" id="SubmitProcurement">Simpan Kontrak</button>
    <button type="button" class="btn btn-secondary" onclick="$('#BuyerContractDiv').html('');">Batal</button>
</div>

<script>
    var BUYERS = @json($buyersPayload);

    function togglePurchaseGroup() {
        var group = $('#Purchase_Group').val();
        $('#UcoSection').toggle(group === 'UCO');
        $('#NonUcoSection').toggle(group === 'Non-UCO');
        $('#SubmitProcurement').text(group === 'UCO' ? 'Simpan Kontrak' : 'Simpan Supplier');
    }

    function addUcoEmailRow(value) {
        if ($('#UcoEmailRows .uco-email-row').length >= 10) {
            return;
        }
        var row = $('<div class="input-group mb-2 uco-email-row">' +
            '<input type="email" name="Buyer_Emails[]" class="form-control shadow-sm" placeholder="nama@domain.com" />' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-uco-email">Hapus</button></div>' +
            '</div>');
        row.find('input').val(value || '');
        $('#UcoEmailRows').append(row);
    }

    function renderUcoEmails(values) {
        $('#UcoEmailRows').empty();
        if (!values || values.length === 0) {
            values = [''];
        }
        values.slice(0, 10).forEach(function (email) { addUcoEmailRow(email); });
    }

    function updateQtyPreview() {
        var mt = $('#Quantity_MT').val();
        var count = $('#Container_Count').val();
        var size = $('#Container_Size').val();
        if (mt && count) {
            $('#QtyPreview').text(mt + ' MT (' + count + ' x ' + size + ')');
        } else {
            $('#QtyPreview').text('-');
        }
    }

    function addNonUcoNewEmailRow(value) {
        if ($('#NonUcoNewEmailRows .nonucnew-email-row').length >= 10) {
            return;
        }
        var row = $('<div class="input-group mb-2 nonucnew-email-row">' +
            '<input type="email" name="Emails[]" class="form-control shadow-sm" placeholder="nama@domain.com" />' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-nonucnew-email">Hapus</button></div>' +
            '</div>');
        row.find('input').val(value || '');
        $('#NonUcoNewEmailRows').append(row);
    }

    function submitUcoContract() {
        var fileInput = $('#Contract_File')[0];
        if (!fileInput.files[0]) {
            alert('File kontrak wajib diunggah.');
            return;
        }

        var formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('Supplier_ID', $('#Supplier_ID').val() || '');
        formData.append('Buyer_Name', $('#Buyer_Name').val());
        formData.append('Buyer_Address', $('#Buyer_Address').val());
        formData.append('Buyer_Phone', $('#Buyer_Phone').val());
        $('#UcoEmailRows input[name="Buyer_Emails[]"]').each(function () {
            var value = $.trim($(this).val());
            if (value !== '') {
                formData.append('Buyer_Emails[]', value);
            }
        });
        formData.append('Contract_No', $('#Contract_No').val());
        formData.append('Quantity_MT', $('#Quantity_MT').val());
        formData.append('Container_Count', $('#Container_Count').val());
        formData.append('Container_Size', $('#Container_Size').val());
        formData.append('Loading_Start', $('#Loading_Start').val());
        formData.append('Loading_End', $('#Loading_End').val());
        formData.append('Incoterms', $('#Incoterms').val());
        formData.append('Price_Currency', $('#Price_Currency').val());
        formData.append('Price_Per_MT', $('#Price_Per_MT').val());
        formData.append('Status', $('#Status').val());
        formData.append('Note', $('#Note').val());
        formData.append('Contract_File', fileInput.files[0]);

        $.blockUI();
        $.ajax({
            url: 'cns-procurement-buyer-contract-new-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                handleSubmitSuccess(data, 'Kontrak buyer berhasil disimpan.');
            },
            error: handleSubmitError
        });
    }

    function submitNonUcoSupplier() {
        $.blockUI();
        $.ajax({
            url: 'cns-procurement-non-uco-supplier-new-submit',
            type: 'POST',
            data: $('#NonUcoNewForm').find('input, select, textarea').not(':button').serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                handleSubmitSuccess(data, 'Supplier Non-UCO berhasil disimpan.');
            },
            error: handleSubmitError
        });
    }

    function handleSubmitSuccess(data, message) {
        $.unblockUI();
        if (String(data).indexOf('success') !== -1) {
            alert(message);
            if (typeof refreshdata === 'function') { refreshdata(); }
            $('#BuyerContractDiv').html('');
        } else {
            alert('Gagal menyimpan data.');
        }
    }

    function handleSubmitError(xhr) {
        $.unblockUI();
        var message = 'Gagal menyimpan data.';
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

    $(function () {
        $('#Purchase_Group').on('change', togglePurchaseGroup);
        togglePurchaseGroup();

        $('#Supplier_ID').select2({ width: '100%', placeholder: '-- Pilih Buyer --', allowClear: true });
        updateQtyPreview();

        $('#UcoAddEmailBtn').on('click', function () {
            if ($('#UcoEmailRows .uco-email-row').length >= 10) {
                alert('Maksimal 10 alamat email.');
                return;
            }
            addUcoEmailRow('');
        });

        $(document).on('click', '.remove-uco-email', function () {
            if ($('#UcoEmailRows .uco-email-row').length <= 1) {
                $(this).closest('.uco-email-row').find('input').val('');
                return;
            }
            $(this).closest('.uco-email-row').remove();
        });

        $('#NonUcoNewAddEmailBtn').on('click', function () {
            if ($('#NonUcoNewEmailRows .nonucnew-email-row').length >= 10) {
                alert('Maksimal 10 alamat email.');
                return;
            }
            addNonUcoNewEmailRow('');
        });

        $(document).on('click', '.remove-nonucnew-email', function () {
            if ($('#NonUcoNewEmailRows .nonucnew-email-row').length <= 1) {
                $(this).closest('.nonucnew-email-row').find('input').val('');
                return;
            }
            $(this).closest('.nonucnew-email-row').remove();
        });

        $('#Supplier_ID').on('change', function () {
            var id = parseInt($(this).val(), 10);
            var buyer = BUYERS.find(function (item) { return item.id === id; });
            if (!buyer) {
                return;
            }
            $('#Buyer_Name').val(buyer.name || '');
            $('#Buyer_Address').val(buyer.address || '');
            $('#Buyer_Phone').val(buyer.phone || '');
            renderUcoEmails(buyer.emails || []);
        });

        $('#RefreshBuyerBtn').on('click', function () {
            var current = $('#Supplier_ID').val();
            $.get('cns-procurement-buyer-contract-buyers', function (data) {
                BUYERS = data;
                var select = $('#Supplier_ID');
                select.empty().append('<option value="">-- Pilih Buyer --</option>');
                data.forEach(function (buyer) {
                    select.append($('<option>').val(buyer.id).text(buyer.name));
                });
                select.val(current).trigger('change.select2');
                alert('Data buyer diperbarui.');
            });
        });

        $('#Quantity_MT, #Container_Count, #Container_Size').on('input change', updateQtyPreview);

        $('#SubmitProcurement').on('click', function () {
            if ($('#Purchase_Group').val() === 'UCO') {
                submitUcoContract();
            } else {
                submitNonUcoSupplier();
            }
        });
    });
</script>
