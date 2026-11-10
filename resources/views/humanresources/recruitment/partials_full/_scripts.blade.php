<script>
$(document).ready(function() {
    // ========================================
    // TAB NAVIGATION LOGIC (STRICT VALIDATION)
    // ========================================
    var currentTab = 0;
    var totalTabs = 5; // personal, contact, education, documents, review

    // Set tanggal deklarasi otomatis hari ini
    if ($('#declaration_date').length) {
        $('#declaration_date').val(new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }));
    }

    // Initialize progress bar
    updateProgressBar(0);
    updateButtons();

    function getTabId(index) {
        var tabIds = ['personal', 'contact', 'education', 'documents', 'review'];
        return tabIds[index] + '-tab';
    }

    function switchTab(tabId) {
        var targetTrigger = $('#' + tabId);
        if (!targetTrigger.length) {
            targetTrigger = $('#' + tabId + '-tab');
        }

        // Tampilkan tab menggunakan Bootstrap 5 / Bootstrap 4 / fallback manual
        if (targetTrigger.length) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                var tabInstance = bootstrap.Tab.getOrCreateInstance(targetTrigger[0]);
                tabInstance.show();
            } else if (typeof targetTrigger.tab === 'function') {
                targetTrigger.tab('show');
            } else {
                var targetPaneId = targetTrigger.attr('href');
                $('.nav-tabs-recruitment .nav-link').removeClass('active');
                targetTrigger.addClass('active');
                $('.tab-pane').removeClass('show active');
                $(targetPaneId).addClass('show active');
            }
        }

        var order = {
            'personal': 0, 'personal-tab': 0,
            'contact': 1, 'contact-tab': 1,
            'education': 2, 'education-tab': 2,
            'documents': 3, 'documents-tab': 3,
            'review': 4, 'review-tab': 4
        };

        if (order[tabId] !== undefined) {
            currentTab = order[tabId];
        }

        updateButtons();
        updateProgressBar(currentTab);

        if (currentTab === 4) {
            populateReviewData();
        }

        window.scrollTo({ top: $('#recruitmentForm').offset().top - 80, behavior: 'smooth' });
    }

    window.switchToTab = switchTab;

    // INTERCEPT KLIK LANGSUNG PADA TAB: VALIDASI REQUIRED DULU SEBELUM PINDAH
    $('.nav-tabs-recruitment a.nav-link').on('click', function(e) {
        e.preventDefault();
        var clickedId = $(this).attr('id');
        var order = {
            'personal-tab': 0,
            'contact-tab': 1,
            'education-tab': 2,
            'documents-tab': 3,
            'review-tab': 4
        };
        var targetIndex = order[clickedId];

        // Jika user mengklik tab yang sama, abaikan
        if (targetIndex === currentTab) {
            return;
        }

        // Jika user ingin maju ke tab depan, wajib lolos validasi tab saat ini
        if (targetIndex > currentTab) {
            if (!validateCurrentTab()) {
                return;
            }
        }

        // Pindah ke tab tujuan jika valid atau kembali ke tab sebelumnya
        switchTab(clickedId);
    });

    // Previous button click
    $('#btnPrevious').click(function() {
        if (currentTab > 0) {
            currentTab--;
            var tabId = getTabId(currentTab);
            switchTab(tabId);
        }
    });

    // Next button click (wajib validasi tab saat ini)
    $('#btnNext').click(function() {
        if (currentTab < totalTabs - 1) {
            if (validateCurrentTab()) {
                currentTab++;
                var tabId = getTabId(currentTab);
                switchTab(tabId);
            }
        }
    });

    // Submit button click
    $('#btnSubmit').click(function() {
        if (!$('#agree_terms').is(':checked') || !$('#agree_privacy').is(':checked')) {
            showError('Harap centang persetujuan syarat & ketentuan serta kebijakan privasi.');
            return;
        }
        if (!$('#declaration_name').val().trim()) {
            showError('Harap ketik nama lengkap Anda sebagai tanda tangan elektronik.');
            $('#declaration_name').focus();
            return;
        }
        if (validateCurrentTab()) {
            submitForm();
        }
    });

    // Save Draft button click
    $('#btnSaveDraft').click(function() {
        saveDraft();
    });

    function updateButtons() {
        if (currentTab === 0) {
            $('#btnPrevious').prop('disabled', true).css('opacity', '0.5');
        } else {
            $('#btnPrevious').prop('disabled', false).css('opacity', '1');
        }

        if (currentTab === totalTabs - 1) {
            $('#btnNext').hide();
            $('#btnSubmit').show();
        } else {
            $('#btnNext').show();
            $('#btnSubmit').hide();
        }
    }

    function updateProgressBar(tabIndex) {
        var progress = ((tabIndex + 1) / totalTabs) * 100;
        $('#formProgress').css('width', progress + '%').attr('aria-valuenow', tabIndex + 1);

        $('.step-label').removeClass('active text-success fw-bold');
        $('#step-label-' + tabIndex).addClass('active text-success fw-bold');
    }

    function validateCurrentTab() {
        var isValid = true;
        var activeTab = $('.tab-pane.active');

        activeTab.find('input[required], select[required], textarea[required]').each(function() {
            var input = $(this);
            var val = (input.val() || '').trim();

            if (val === '') {
                input.addClass('is-invalid');
                isValid = false;
                return;
            }
            input.removeClass('is-invalid');

            var id = input.attr('id');
            if (id === 'Email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                input.addClass('is-invalid');
                isValid = false;
            }
            if ((id === 'Handphone' || id === 'Phone') && !/^[0-9+\s-]{10,18}$/.test(val)) {
                input.addClass('is-invalid');
                isValid = false;
            }
            if (id === 'KTP' && !/^[0-9]{16,20}$/.test(val.replace(/[\s-]/g, ''))) {
                input.addClass('is-invalid');
                isValid = false;
            }
            if (id === 'Birth_Date' && val !== '') {
                var age = (Date.now() - new Date(val).getTime()) / 31557600000;
                if (age < 17) {
                    input.addClass('is-invalid');
                    isValid = false;
                    showError('Usia kandidat minimal 17 tahun.');
                }
            }
        });

        // Tab Documents file wajib: Foto, CV, KTP, Ijazah
        if (activeTab.attr('id') === 'documents') {
            ['Foto', 'CV_File', 'KTP_File', 'Education_Certificate_File'].forEach(function(fieldId) {
                var fileElem = $('#' + fieldId)[0];
                if (!fileElem || !fileElem.files || fileElem.files.length === 0) {
                    $('#' + fieldId).addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#' + fieldId).removeClass('is-invalid');
                }
            });
        }

        if (!isValid) {
            showError('Harap lengkapi semua bidang isian wajib (*) pada bagian ini sebelum berpindah.');
        }

        return isValid;
    }

    // Populate Data Ringkasan ke Tab Review
    window.populateReviewData = function() {
        var fields = [
            'Name', 'Gender', 'Birth_Place', 'Birth_Date', 'KTP', 'kewarganegaraan',
            'Religion', 'Marital_Status', 'Address', 'Province', 'City', 'District', 'Sub_District', 'ZipCode',
            'Phone', 'Handphone', 'Email', 'Email_Individual', 'Emergency_Contact_Name', 'Last_Education',
            'Last_Education_From', 'Education_Major', 'Graduation_Year', 'GPA', 'Salary_Accepted'
        ];

        fields.forEach(function(id) {
            var val = $('#' + id).val();
            var target = $('#review-' + id);
            if (target.length) {
                target.text(val ? val : '-');
            }
        });

        // Tampilkan nama posisi dari dropdown
        var selectedPos = $('#Requisition_ID option:selected').text();
        if ($('#review-Position').length) {
            $('#review-Position').text(selectedPos ? selectedPos : '-');
        }

        // Tampilkan dokumen yang telah diunggah
        var docHtml = '';
        var hasDocs = false;
        $('input[type="file"]').each(function() {
            if (this.files && this.files.length > 0) {
                hasDocs = true;
                var label = $(this).closest('.form-group').find('label').first().text().replace('*', '').trim();
                docHtml += '<li class="mb-2 text-dark"><i class="fas fa-check-circle text-success me-2"></i><strong>' + label + ':</strong> ' + this.files[0].name + '</li>';
            }
        });

        if ($('#review-documents').length) {
            $('#review-documents').html(hasDocs ? docHtml : '<li class="mb-2 text-muted"><i class="fas fa-times-circle text-danger me-2"></i>Belum ada dokumen yang diunggah</li>');
        }
    };

    function submitForm() {
        showLoading(true);
        var formData = new FormData($('#recruitmentForm')[0]);

        $.ajax({
            url: '{{ url("recruitment-new-submit") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}"
            },
            success: function(response) {
                showLoading(false);
                if (response.status === 'success' || response.status === 'ok') {
                    showSuccess('Data kandidat berhasil disimpan! ID/Task: ' + (response.task_id || response.Task_ID || 'Sukses'));
                    setTimeout(function() {
                        window.location.href = "{{ url('recruitment-data') }}";
                    }, 2000);
                } else {
                    showError(response.message || 'Gagal menyimpan data kandidat. Silakan periksa kembali.');
                }
            },
            error: function(xhr) {
                showLoading(false);
                var errorMsg = 'Terjadi kesalahan saat menyimpan formulir.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showError(errorMsg);
            }
        });
    }

    function saveDraft() {
        showLoading(true);
        var formData = new FormData($('#recruitmentForm')[0]);
        formData.append('is_draft', '1');

        $.ajax({
            url: '{{ url("recruitment-new-submit") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}"
            },
            success: function(response) {
                showLoading(false);
                showSuccess('Draft berhasil disimpan!');
            },
            error: function(xhr) {
                showLoading(false);
                showError('Gagal menyimpan draft: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error server'));
            }
        });
    }

    function showLoading(isLoading) {
        if (isLoading) {
            $('#btnSubmit, #btnNext, #btnPrevious, #btnSaveDraft').prop('disabled', true);
            if (typeof $.blockUI === 'function') {
                $.blockUI({ message: 'Memproses data rekrutmen...' });
            }
        } else {
            $('#btnSubmit, #btnNext, #btnPrevious, #btnSaveDraft').prop('disabled', false);
            if (typeof $.unblockUI === 'function') {
                $.unblockUI();
            }
        }
    }

    function showSuccess(message) {
        $('.alert-notification').remove();
        var alertHtml = '<div class="alert alert-success alert-dismissible fade show alert-notification mb-3" role="alert">' +
            '<i class="fas fa-check-circle me-2"></i>' + message +
            '<button type="button" class="btn-close close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
        $('#recruitmentForm').before(alertHtml);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showError(message) {
        $('.alert-notification').remove();
        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show alert-notification mb-3" role="alert">' +
            '<i class="fas fa-exclamation-circle me-2"></i>' + message +
            '<button type="button" class="btn-close close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
        $('#recruitmentForm').before(alertHtml);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // File Preview dan Validasi Ukuran File
    $('input[type="file"]').on('change', function() {
        var input = $(this);
        var file = this.files[0];
        var id = input.attr('id');

        if (file) {
            var maxSize = (id === 'Foto') ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showError('Ukuran file ' + file.name + ' melebihi batas (' + (maxSize / (1024 * 1024)) + 'MB).');
                input.val('');
                return;
            }

            var fileName = file.name.length > 25 ? file.name.substring(0, 22) + '...' : file.name;
            input.next('.custom-file-label').text(fileName);

            if (file.type.indexOf('image/') === 0) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = $('#preview-' + id);
                    if (preview.length) {
                        preview.html('<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-height: 150px; border-radius: 6px;">');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                $('#info-' + id).html('<span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i> ' + file.name + '</span>');
            }
        }
    });
});
</script>
