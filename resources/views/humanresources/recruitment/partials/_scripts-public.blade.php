<script>
    $(document).ready(function() {
        // ========================================
        // PUBLIC MODE: paksa EXTERNAL, kunci field
        // ========================================
        $('#Recruitment_Type').val('EXTERNAL');
        $('#Recruitment_Type').closest('.col-md-6').hide();
        $('#btnSaveDraft').remove();

        // Birth_Date partial = readonly tanpa lib datepicker -> pakai native date.
        var bd = $('#Birth_Date');
        bd.removeAttr('readonly').removeClass('datepicker').attr('type', 'date');
        bd.attr('max', new Date().toISOString().split('T')[0]);

        // Set tanggal deklarasi otomatis hari ini
        if ($('#declaration_date').length) {
            $('#declaration_date').val(new Date().toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
        }

        var currentTab = 0;
        var totalTabs = 5;
        updateProgressBar(0);
        updateButtons();

        function tabId(i) {
            return ['personal', 'contact', 'education', 'documents', 'review'][i] + '-tab';
        }

        function switchTab(id) {
            var targetTrigger = $('#' + id);
            if (!targetTrigger.length) {
                targetTrigger = $('#' + id + '-tab');
            }

            if (targetTrigger.length) {
                if (typeof targetTrigger.tab === 'function') {
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
                'personal-tab': 0,
                'contact-tab': 1,
                'education-tab': 2,
                'documents-tab': 3,
                'review-tab': 4
            };
            currentTab = order[id] !== undefined ? order[id] : currentTab;
            updateButtons();
            updateProgressBar(currentTab);

            if (currentTab === 4) {
                populateReviewData();
            }

            window.scrollTo({
                top: $('#recruitmentForm').offset().top - 80,
                behavior: 'smooth'
            });
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

            if (targetIndex === currentTab) {
                return;
            }

            // Jika user ingin maju ke tab depan, wajib lolos validasi tab saat ini
            if (targetIndex > currentTab) {
                if (!validateCurrentTab()) {
                    return;
                }
            }

            switchTab(clickedId);
        });

        $('#btnPrevious').click(function() {
            if (currentTab > 0) {
                currentTab--;
                switchTab(tabId(currentTab));
            }
        });

        $('#btnNext').click(function() {
            if (currentTab < totalTabs - 1 && validateCurrentTab()) {
                currentTab++;
                switchTab(tabId(currentTab));
            }
        });

        $('#btnSubmit').click(function() {
            if (!$('#agree_terms').is(':checked') || !$('#agree_privacy').is(':checked')) {
                showError(
                    'Centang persetujuan syarat & ketentuan serta kebijakan privasi terlebih dahulu.'
                    );
                return;
            }
            if (!$('#declaration_name').val().trim()) {
                showError('Isi nama lengkap Anda sebagai tanda tangan elektronik.');
                $('#declaration_name').focus();
                return;
            }
            if (validateCurrentTab()) {
                submitForm();
            }
        });

        function updateButtons() {
            $('#btnPrevious').prop('disabled', currentTab === 0).css('opacity', currentTab === 0 ? '0.5' : '1');
            if (currentTab === totalTabs - 1) {
                $('#btnNext').hide();
                $('#btnSubmit').show();
            } else {
                $('#btnNext').show();
                $('#btnSubmit').hide();
            }
        }

        function updateProgressBar(i) {
            var p = ((i + 1) / totalTabs) * 100;
            $('#formProgress').css('width', p + '%').attr('aria-valuenow', i + 1);
            if ($('#progressPercentage').length) {
                $('#progressPercentage').text(Math.round(p) + '% Selesai');
            }

            $('.step-label').removeClass('active font-weight-bold text-success');
            $('#step-label-' + i).addClass('active font-weight-bold text-success');
        }

        function validateCurrentTab() {
            var ok = true;
            var active = $('.tab-pane.active');
            active.find('input[required], select[required], textarea[required]').each(function() {
                var f = $(this),
                    v = (f.val() || '').trim();
                if (v === '') {
                    f.addClass('is-invalid');
                    ok = false;
                    return;
                }
                f.removeClass('is-invalid');
                var id = f.attr('id');
                if (id === 'Email' && v !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
                    f.addClass('is-invalid');
                    ok = false;
                }
                if ((id === 'Handphone' || id === 'Phone') && v !== '' && !/^[0-9+\s-]{10,18}$/.test(
                    v)) {
                    f.addClass('is-invalid');
                    ok = false;
                }
                if (id === 'KTP' && v !== '' && !/^[0-9]{16,20}$/.test(v.replace(/[\s-]/g, ''))) {
                    f.addClass('is-invalid');
                    ok = false;
                }
                if (id === 'Birth_Date' && v !== '') {
                    var age = (Date.now() - new Date(v).getTime()) / 31557600000;
                    if (!(age >= 17)) {
                        f.addClass('is-invalid');
                        ok = false;
                        showError('Usia minimal 17 tahun.');
                    }
                }
            });

            // File wajib di tab documents: CV dan KTP
            if (active.attr('id') === 'documents') {
                ['CV_File', 'KTP_File'].forEach(function(id) {
                    var inp = $('#' + id)[0];
                    if (!inp || !inp.files || inp.files.length === 0) {
                        $('#' + id).addClass('is-invalid');
                        ok = false;
                    } else {
                        $('#' + id).removeClass('is-invalid');
                    }
                });
            }
            if (!ok) showError('Lengkapi semua bidang isian wajib (*) pada bagian ini sebelum berpindah.');
            return ok;
        }

        function submitForm() {
            var fd = new FormData($('#recruitmentForm')[0]);
            fd.set('source', 'public');
            fd.set('Recruitment_Type', 'EXTERNAL');
            showLoading(true);
            $.ajax({
                url: '{{ url('open-recruitment-submit') }}',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(r) {
                    showLoading(false);
                    if (r.status === 'success') {
                        $('#recruitmentCard').hide();
                        if (r.task_id) $('#successTaskId').text(r.task_id);
                        $('#publicSuccess').show();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    } else {
                        showError(r.message || 'Gagal mengirim lamaran.');
                    }
                },
                error: function(x) {
                    showLoading(false);
                    showError((x.responseJSON && x.responseJSON.message) ||
                        'Gagal mengirim lamaran. Silakan periksa kembali berkas dan koneksi Anda.'
                        );
                }
            });
        }

        function showLoading(b) {
            $('#btnSubmit, #btnNext, #btnPrevious').prop('disabled', b);
        }

        function showSuccess(m) {
            $('.alert-notification').remove();
            $('#recruitmentForm').before(
                '<div class="alert alert-success alert-dismissible fade show alert-notification mb-3" role="alert"><i class="fas fa-check-circle mr-2"></i>' +
                m + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        }

        function showError(m) {
            $('.alert-notification').remove();
            $('#recruitmentForm').before(
                '<div class="alert alert-danger alert-dismissible fade show alert-notification mb-3" role="alert"><i class="fas fa-exclamation-circle mr-2"></i>' +
                m + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.populateReviewData = function() {
            var fields = [
                'Name', 'Gender', 'Birth_Date', 'Candidate_Age', 'Marital_Status', 'Address',
                'Province', 'City',
                'Handphone', 'Email',
                'Emergency_Contact_Name', 'Emergency_Relation', 'Emergency_Contact_No',
                'Emergency_Address',
                'Last_Education', 'Last_Education_From', 'Graduation_Year', 'Education_Major', 'GPA'
            ];

            fields.forEach(function(id) {
                var val = $('#' + id).val();
                var target = $('#review-' + id);
                if (target.length) {
                    target.text(val ? val : '-');
                }
            });

            var selectedPos = $('#Position option:selected').text();
            if ($('#review-Position').length) {
                $('#review-Position').text(selectedPos ? selectedPos : '-');
            }

            var docHtml = '';
            var hasDocs = false;
            $('input[type="file"]').each(function() {
                if (this.files && this.files.length > 0) {
                    hasDocs = true;
                    var label = $(this).closest('.form-group').find('label').first().text().replace(
                        '*', '').trim();
                    docHtml +=
                        '<li class="mb-2 text-dark"><i class="fas fa-check-circle text-success mr-2"></i><strong>' +
                        label + ':</strong> ' + this.files[0].name + '</li>';
                }
            });

            if ($('#review-documents').length) {
                $('#review-documents').html(hasDocs ? docHtml :
                    '<li class="mb-2 text-muted"><i class="fas fa-times-circle text-danger mr-2"></i>Belum ada dokumen yang diunggah</li>'
                    );
            }
        };

        // Preview + validasi file (foto max 2MB, lain max 5MB)
        $('input[type="file"]').on('change', function() {
            var inp = $(this),
                file = this.files[0],
                id = inp.attr('id');
            if (!file) return;
            var max = id === 'Foto' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > max) {
                showError('File ' + file.name + ' melebihi batas ' + (max / 1048576) + 'MB.');
                inp.val('');
                return;
            }
            inp.next('.custom-file-label').html(file.name.length > 25 ? file.name.substring(0, 22) +
                '...' : file.name);
            $('#check-' + id).removeClass('text-muted').addClass('text-success');
            if (file.type.indexOf('image/') === 0) {
                var r = new FileReader();
                r.onload = function(e) {
                    $('#preview-' + id).html('<img src="' + e.target.result +
                        '" class="img-thumbnail mt-2" style="max-height:150px;border-radius:6px;">'
                        );
                };
                r.readAsDataURL(file);
            } else {
                $('#info-' + id).html(
                    '<span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> ' +
                    file.name + '</span>');
            }
        });
    });
</script>
