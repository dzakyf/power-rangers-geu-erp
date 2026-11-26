@include('cns.procurement.partials.buyer-contract-form', ['contract' => $contract])

<div class="mb-3 mt-2">
    <button type="button" class="btn btn-success" id="SubmitContractDetail">Simpan Perubahan</button>
    <button type="button" class="btn btn-secondary" onclick="$('#BuyerContractDiv').html('');">Tutup</button>
</div>

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">Booking Confirmation</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Nomor Booking</th>
                        <th>File</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->Label }}</td>
                            <td>{{ $booking->Booking_No }}</td>
                            <td>
                                @if($booking->Booking_File)
                                    <a href="{{ route('cns-procurement-buyer-contract.booking-file', ['ID' => $booking->ID], false) }}" target="_blank">{{ $booking->Booking_File_Name ?: 'Download' }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $booking->Created_Date }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger delete-booking" data-id="{{ $booking->ID }}">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada Booking Confirmation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr>
        <h6 class="font-weight-bold">Tambah Booking Confirmation</h6>
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" id="Booking_Label" class="text-black form-control shadow-sm" value="{{ $nextBookingLabel }}" readonly />
                    <small class="text-muted">Terisi otomatis.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nomor Booking Confirmation</label>
                    <input type="text" id="Booking_No" class="text-black form-control shadow-sm" placeholder="EGLV081600298008" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Upload File Booking Confirmation</label>
                    <input type="file" id="Booking_File" class="text-black form-control-file" accept=".pdf,.doc,.docx" />
                    <small class="text-muted">Maksimal 5 MB (PDF/DOC/DOCX).</small>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-primary" id="AddBookingBtn">Tambah Booking</button>
            </div>
        </div>
    </div>
</div>

<script>
    var CONTRACT_ID = {{ (int) $contract->ID }};
    var BUYERS = @json($buyersPayload);
    var KYCS = @json($kycsPayload);

    function reloadContractDetail() {
        LoadM('BuyerContractDiv', 'cns-procurement-buyer-contract-detail?ID=' + CONTRACT_ID);
    }

    function addEmailRow(value, readonly) {
        if ($('#UcoEmailRows .uco-email-row').length >= 10) {
            return;
        }
        var input = $('<input type="email" name="Buyer_Emails[]" class="text-black form-control shadow-sm" placeholder="nama@domain.com" />');
        input.val(value || '');
        input.prop('readonly', !!readonly);
        $('#UcoEmailRows').append($('<div class="input-group mb-2 uco-email-row"></div>').append(input));
    }

    function renderEmails(values, readonly) {
        $('#UcoEmailRows').empty();
        if (!values || values.length === 0) {
            values = [''];
        }
        values.slice(0, 10).forEach(function (email) { addEmailRow(email, readonly); });
    }

    function updateQtyPreview() {
        var rawMt = $('#Quantity_MT').val();
        var count = $('#Container_Count').val();
        var size = $('#Container_Size').val();
        if (rawMt && count) {
            $('#QtyPreview').text(cnsFormatNumber(cnsParseNumber(rawMt, 0), 2) + ' MT (' + count + ' x ' + size + ')');
        } else {
            $('#QtyPreview').text('-');
        }
    }

    function collectContractForm() {
        var formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('ID', CONTRACT_ID);
        formData.append('kyc_id', $('#kyc_id').val() || '');
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
        formData.append('Quantity_MT', cnsParseNumber($('#Quantity_MT').val(), 0));
        formData.append('Container_Count', $('#Container_Count').val());
        formData.append('Container_Size', $('#Container_Size').val());
        formData.append('Loading_Start', $('#Loading_Start').val());
        formData.append('Loading_End', $('#Loading_End').val());
        formData.append('Incoterms', $('#Incoterms').val());
        formData.append('Price_Currency', $('#Price_Currency').val());
        formData.append('Price_Per_MT', cnsParseNumber($('#Price_Per_MT').val(), 0));
        formData.append('Status', $('#Status').val());
        formData.append('Note', $('#Note').val());

        var fileInput = $('#Contract_File')[0];
        if (fileInput && fileInput.files[0]) {
            formData.append('Contract_File', fileInput.files[0]);
        }

        return formData;
    }

    $(function () {
        $('#kyc_id').select2({ width: '100%', placeholder: '-- Pilih KYC (Approved) --', allowClear: true });
        $('#Supplier_ID').select2({ width: '100%', placeholder: '-- Pilih Buyer --', allowClear: true });
        updateQtyPreview();

        $('#kyc_id').on('change', function () {
            var id = parseInt($(this).val(), 10);
            var kyc = KYCS.find(function (item) { return item.id === id; });
            if (!kyc) {
                $('#Buyer_Name').val('');
                return;
            }
            if (kyc.supplier_id) {
                $('#Supplier_ID').val(String(kyc.supplier_id)).trigger('change');
            } else {
                $('#Supplier_ID').val('').trigger('change');
                $('#Buyer_Address').val(kyc.registered_address || '');
            }
            $('#Buyer_Name').val(kyc.company_name || '');
        });

        $('#Supplier_ID').on('change', function () {
            var id = parseInt($(this).val(), 10);
            var buyer = BUYERS.find(function (item) { return item.id === id; });
            var selectedKycId = parseInt($('#kyc_id').val(), 10);
            var selectedKyc = KYCS.find(function (item) { return item.id === selectedKycId; });

            if (!buyer) {
                $('#Buyer_Address').prop('readonly', false);
                $('#Buyer_Phone').prop('readonly', false);
                $('#UcoEmailRows input[name="Buyer_Emails[]"]').prop('readonly', false);
                if (selectedKyc && !$('#Buyer_Address').val()) {
                    $('#Buyer_Address').val(selectedKyc.registered_address || '');
                }
                return;
            }

            if (!selectedKyc) {
                $('#Buyer_Name').val(buyer.name || '');
            }
            $('#Buyer_Address').val(buyer.address || (selectedKyc ? (selectedKyc.registered_address || '') : '')).prop('readonly', true);
            $('#Buyer_Phone').val(buyer.phone || '').prop('readonly', true);
            renderEmails(buyer.emails || [], true);
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
                swal('Berhasil!', 'Data buyer diperbarui.', 'success');
            });
        });

        $('#Quantity_MT, #Container_Count, #Container_Size').on('input change', updateQtyPreview);

        $('#SubmitContractDetail').on('click', function () {
            $.blockUI();
            $.ajax({
                url: 'cns-procurement-buyer-contract-detail-submit',
                type: 'POST',
                data: collectContractForm(),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    $.unblockUI();
                    if (String(data).indexOf('success') !== -1) {
                        swal('Berhasil!', 'Kontrak berhasil diperbarui.', 'success');
                        if (typeof refreshdata === 'function') { refreshdata(); }
                        reloadContractDetail();
                    } else {
                        swal('Error', 'Gagal memperbarui kontrak.', 'error');
                    }
                },
                error: function (xhr) {
                    $.unblockUI();
                    var message = 'Gagal memperbarui kontrak.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            var first = Object.keys(xhr.responseJSON.errors)[0];
                            message = xhr.responseJSON.errors[first][0];
                        } else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                    }
                    swal('Error', message, 'error');
                }
            });
        });

        $('#AddBookingBtn').on('click', function () {
            var label = $.trim($('#Booking_Label').val());
            var bookingNo = $.trim($('#Booking_No').val());
            var fileInput = $('#Booking_File')[0];

            if (!label || !bookingNo) {
                swal('Perhatian', 'Label dan Nomor Booking wajib diisi.', 'warning');
                return;
            }
            if (!fileInput.files[0]) {
                swal('Perhatian', 'File Booking Confirmation wajib diunggah.', 'warning');
                return;
            }

            var formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('Contract_ID', CONTRACT_ID);
            formData.append('Label', label);
            formData.append('Booking_No', bookingNo);
            formData.append('Booking_File', fileInput.files[0]);

            $.blockUI();
            $.ajax({
                url: 'cns-procurement-buyer-contract-booking-submit',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    $.unblockUI();
                    if (String(data).indexOf('success') !== -1) {
                        swal('Berhasil!', 'Booking Confirmation berhasil ditambahkan.', 'success');
                        reloadContractDetail();
                    } else {
                        swal('Error', 'Gagal menambahkan booking.', 'error');
                    }
                },
                error: function (xhr) {
                    $.unblockUI();
                    var message = 'Gagal menambahkan booking.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            var first = Object.keys(xhr.responseJSON.errors)[0];
                            message = xhr.responseJSON.errors[first][0];
                        } else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                    }
                    swal('Error', message, 'error');
                }
            });
        });

        $(document).on('click', '.delete-booking', function () {
            var bookingId = $(this).data('id');
            swal({
                title: 'Konfirmasi',
                text: 'Hapus booking confirmation ini?',
                icon: 'warning',
                buttons: ['Batal', 'Ya'],
                dangerMode: true
            }).then(function (confirmed) {
                if (!confirmed) return;
                $.ajax({
                    url: 'cns-procurement-buyer-contract-booking-delete',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ID: bookingId,
                        Contract_ID: CONTRACT_ID
                    },
                    success: function (data) {
                        if (String(data).indexOf('success') !== -1) {
                            reloadContractDetail();
                        } else {
                            swal('Error', 'Gagal menghapus booking.', 'error');
                        }
                    },
                    error: function () {
                        swal('Error', 'Gagal menghapus booking.', 'error');
                    }
                });
            });
        });
    });
</script>
