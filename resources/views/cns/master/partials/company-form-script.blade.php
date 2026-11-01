<script>
    function isValidImage(file) {
        var allowedExtensions = ['jpg', 'jpeg', 'png'];
        var fileName = file.name.toLowerCase();
        var extension = fileName.split('.').pop();

        return allowedExtensions.includes(extension);
    }

    function addCompanyEmailRow(value) {
        if ($('#CompanyEmailRows .company-email-row').length >= 10) {
            alert('Maksimal 10 alamat email.');
            return;
        }

        var row = $('<div class="input-group mb-2 company-email-row">' +
            '<input type="email" name="Emails[]" class="form-control shadow-sm company-email-input" required />' +
            '<div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-company-email">Hapus</button></div>' +
            '</div>');
        row.find('input').val(value || '');
        $('#CompanyEmailRows').append(row);
    }

    function getAllValues() {
        var inputValues = [];

        $('#ContentPages input, select, textarea').each(function () {
            if ($(this).hasClass('company-email-input')) {
                return;
            }

            var id = $(this).attr('id');
            if (!id) {
                return;
            }

            var type = $(this).attr('type');

            if (type === 'button' || type === 'submit' || type === 'file') {
                return;
            }

            if ((type === 'checkbox' || type === 'radio') && !this.checked) {
                return;
            }

            inputValues.push(id + '=' + $(this).val());
        });

        return inputValues.join('&');
    }

    function Submitkan() {
        var emailValues = [];
        $('#CompanyEmailRows .company-email-input').each(function () {
            var value = $.trim($(this).val());
            if (value !== '') {
                emailValues.push(value);
            }
        });

        if (emailValues.length === 0) {
            alert('Minimal satu email wajib diisi.');
            return;
        }

        if (emailValues.length > 10) {
            alert('Maksimal 10 alamat email.');
            return;
        }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('param', getAllValues());
        formData.append('Is_Supplier', $('#Is_Supplier').is(':checked') ? 1 : 0);
        formData.append('Is_Consignee', $('#Is_Consignee').is(':checked') ? 1 : 0);

        emailValues.forEach(function (email) {
            formData.append('Emails[]', email);
        });

        formData.append('LetterHead', $('#LetterHead')[0].files[0]);
        if ($('#LetterHead')[0].files[0] && !isValidImage($('#LetterHead')[0].files[0])) {
            dhtmlx.alert('File Kop Surat harus jpg, jpeg, atau png');
        }

        formData.append('Footer', $('#Footer')[0].files[0]);
        if ($('#Footer')[0].files[0] && !isValidImage($('#Footer')[0].files[0])) {
            dhtmlx.alert('File Footer harus jpg, jpeg, atau png');
        }

        formData.append('Logo', $('#Logo')[0].files[0]);
        if ($('#Logo')[0].files[0] && !isValidImage($('#Logo')[0].files[0])) {
            dhtmlx.alert('File Logo harus jpg, jpeg, atau png');
        }

        formData.append('Logo_Inverse', $('#Logo_Inverse')[0].files[0]);
        if ($('#Logo_Inverse')[0].files[0] && !isValidImage($('#Logo_Inverse')[0].files[0])) {
            dhtmlx.alert('File Logo Inverse harus jpg, jpeg, atau png');
        }

        formData.append('Stamps', $('#Stamps')[0].files[0]);
        if ($('#Stamps')[0].files[0] && !isValidImage($('#Stamps')[0].files[0])) {
            dhtmlx.alert('File Stamps harus jpg, jpeg, atau png');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            url: '{{ $submitUrl }}?_token={{ csrf_token() }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                if (data === 'success') {
                    alert('{{ $successMessage }}');

                    setTimeout(function () {
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        $('#MasterCompanyDet').html('');
                    }, 1000);
                }
            },
            error: function (xhr) {
                var message = 'error';
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
    }

    $(function () {
        $('#AddCompanyEmailBtn').on('click', function () {
            if ($('#CompanyEmailRows .company-email-row').length >= 10) {
                alert('Maksimal 10 alamat email.');
                return;
            }
            addCompanyEmailRow('');
        });

        $(document).on('click', '.remove-company-email', function () {
            if ($('#CompanyEmailRows .company-email-row').length <= 1) {
                $(this).closest('.company-email-row').find('input').val('');
                return;
            }
            $(this).closest('.company-email-row').remove();
        });
    });
</script>
