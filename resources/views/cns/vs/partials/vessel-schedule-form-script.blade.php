<script>
    var IS_EDIT = {{ $isEdit ? 'true' : 'false' }};

    function normalizeContainerSize(size) {
        var value = String(size || '').toUpperCase();

        if (value.indexOf('40') === 0) {
            return '40 FT';
        }
        if (value.indexOf('ISOTANK') !== -1) {
            return '20 ISOTANK';
        }
        if (value.indexOf('20') === 0) {
            return '20 FT';
        }

        return '';
    }

    function toggleSections() {
        var method = $('#Shipping_Method').val();

        $('#ContainerSection').toggle(method === 'CONTAINER');
        $('#VesselSection').toggle(method === 'VESSEL');
        $('#TruckSection').toggle(method === 'TANK_TRUCK');
    }

    function updateBookingFile() {
        var option = $('#Container_Booking_ID option:selected');
        var url = option.data('file');
        var name = option.data('file-name');
        var target = $('#BookingFileLink');

        target.empty();

        if (url) {
            target.append('<a href="' + url + '" target="_blank">Lihat file booking' + (name ? ' (' + name + ')' : '') + '</a>');
        }
    }

    function loadBookings(contractId, selectedId) {
        var select = $('#Container_Booking_ID');

        select.empty().append('<option value="">-- Pilih Booking --</option>');

        if (!contractId) {
            select.trigger('change.select2');
            updateBookingFile();
            return;
        }

        $.get('cns-vessel-schedule-bookings?contract_id=' + encodeURIComponent(contractId), function (data) {
            data.forEach(function (booking) {
                var option = $('<option>')
                    .val(booking.id)
                    .text(booking.label + ' - ' + booking.booking_no)
                    .attr('data-file', booking.file_url || '')
                    .attr('data-file-name', booking.file_name || '');

                select.append(option);
            });

            if (selectedId) {
                select.val(selectedId);
            }

            select.trigger('change.select2');
            updateBookingFile();
        });
    }

    function prefillContainerFromContract() {
        var option = $('#Contract_ID option:selected');
        var count = option.data('container-count');
        var size = normalizeContainerSize(option.data('container-size'));

        if (count !== undefined && count !== null && count !== '') {
            $('#Container_Qty1').val(count);
        }

        if (size) {
            $('#Container_Qty2').val(size);
        }
    }

    $(function () {
        $('#Contract_ID').select2({ width: '100%', placeholder: '-- Pilih Kontrak --', allowClear: true });
        $('#Container_Booking_ID').select2({ width: '100%', placeholder: '-- Pilih Booking --', allowClear: true });
        $('#Truck_Supplier_ID').select2({ width: '100%', placeholder: '-- Pilih Vendor --', allowClear: true });

        toggleSections();
        updateBookingFile();

        $('#Shipping_Method').on('change', toggleSections);

        $('#Contract_ID').on('change', function () {
            var contractId = $(this).val();
            var option = $('#Contract_ID option:selected');

            $('#Incoterms').val(option.data('incoterms') || '');
            prefillContainerFromContract();
            loadBookings(contractId, null);
        });

        $('#Container_Booking_ID').on('change', updateBookingFile);

        $('#SubmitVesselSchedule').on('click', function () {
            if (!$('#Contract_ID').val()) {
                alert('Pilih kontrak terlebih dahulu.');
                return;
            }

            if (!$('#Shipping_Method').val()) {
                alert('Pilih shipping method terlebih dahulu.');
                return;
            }

            var form = document.getElementById('VesselScheduleForm');
            var formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');

            $.blockUI();
            $.ajax({
                url: '{{ $submitUrl }}?_token={{ csrf_token() }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (data) {
                    $.unblockUI();

                    if (String(data).indexOf('success') !== -1) {
                        alert('Shipping schedule berhasil disimpan.');

                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }

                        $('#divlistOpportunity').html('');
                    } else {
                        alert('Gagal menyimpan shipping schedule.');
                    }
                },
                error: function (xhr) {
                    $.unblockUI();

                    var message = 'Gagal menyimpan shipping schedule.';
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
