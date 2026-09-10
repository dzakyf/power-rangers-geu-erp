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

    var currentTab = 0;
    var totalTabs = 5;
    updateProgressBar(0);
    updateButtons();

    function tabId(i) { return ['personal', 'contact', 'education', 'documents', 'review'][i] + '-tab'; }
    function switchTab(id) {
        $('#' + id).tab('show');
        var order = { 'personal-tab': 0, 'contact-tab': 1, 'education-tab': 2, 'documents-tab': 3, 'review-tab': 4 };
        currentTab = order[id] !== undefined ? order[id] : currentTab;
        updateButtons();
        updateProgressBar(currentTab);
    }
    window.switchToTab = switchTab;

    $('#btnPrevious').click(function() {
        if (currentTab > 0) { currentTab--; switchTab(tabId(currentTab)); }
    });
    $('#btnNext').click(function() {
        if (currentTab < totalTabs - 1 && validateCurrentTab()) {
            if (currentTab === 3) populateReviewData();
            currentTab++; switchTab(tabId(currentTab));
        }
    });
    $('#btnSubmit').click(function() {
        if (!$('#agree_terms').is(':checked') || !$('#agree_privacy').is(':checked')) {
            showError('Centang persetujuan dan kebijakan privasi terlebih dahulu.');
            return;
        }
        if (!$('#declaration_name').val().trim()) {
            showError('Isi nama lengkap sebagai tanda tangan elektronik.');
            return;
        }
        if (validateCurrentTab()) submitForm();
    });

    function updateButtons() {
        $('#btnPrevious').prop('disabled', currentTab === 0).css('opacity', currentTab === 0 ? '0.5' : '1');
        if (currentTab === totalTabs - 1) { $('#btnNext').hide(); $('#btnSubmit').show(); }
        else { $('#btnNext').show(); $('#btnSubmit').hide(); }
    }
    function updateProgressBar(i) {
        var p = ((i + 1) / totalTabs) * 100;
        $('#formProgress').css('width', p + '%').attr('aria-valuenow', i + 1);
    }

    function validateCurrentTab() {
        var ok = true;
        var active = $('.tab-pane.active');
        active.find('input[required], select[required], textarea[required]').each(function() {
            var f = $(this), v = (f.val() || '').trim();
            if (v === '') { f.addClass('is-invalid'); ok = false; return; }
            f.removeClass('is-invalid');
            var id = f.attr('id');
            if (id === 'Email' && v !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { f.addClass('is-invalid'); ok = false; }
            if ((id === 'Handphone' || id === 'Phone') && v !== '' && !/^[0-9+\s-]{10,18}$/.test(v)) { f.addClass('is-invalid'); ok = false; }
            if (id === 'KTP' && v !== '' && !/^[0-9]{16,20}$/.test(v.replace(/[\s-]/g, ''))) { f.addClass('is-invalid'); ok = false; }
            if (id === 'Birth_Date' && v !== '') {
                var age = (Date.now() - new Date(v).getTime()) / 31557600000;
                if (!(age >= 17)) { f.addClass('is-invalid'); ok = false; showError('Usia minimal 17 tahun.'); }
            }
        });
        // File wajib di tab documents: Foto, CV, KTP, Ijazah.
        if (active.attr('id') === 'documents') {
            ['Foto', 'CV_File', 'KTP_File', 'Education_Certificate_File'].forEach(function(id) {
                var inp = $('#' + id)[0];
                if (!inp || inp.files.length === 0) { $('#' + id).addClass('is-invalid'); ok = false; }
            });
        }
        if (!ok) showError('Lengkapi semua field wajib (*) pada bagian ini.');
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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(r) {
                showLoading(false);
                if (r.status === 'success') {
                    $('#recruitmentCard').hide();
                    if (r.task_id) $('#successTaskId').text(r.task_id);
                    $('#publicSuccess').show();
                    window.scrollTo(0, 0);
                } else showError(r.message || 'Gagal mengirim lamaran.');
            },
            error: function(x) {
                showLoading(false);
                showError((x.responseJSON && x.responseJSON.message) || 'Gagal mengirim. Coba lagi.');
            }
        });
    }

    function showLoading(b) { $('#btnSubmit, #btnNext, #btnPrevious').prop('disabled', b); }
    function showSuccess(m) {
        $('.alert-notification').remove();
        $('#recruitmentForm').before('<div class="alert alert-success alert-dismissible fade show alert-notification" role="alert"><i class="fas fa-check-circle"></i> ' + m + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
    }
    function showError(m) {
        $('.alert-notification').remove();
        $('#recruitmentForm').before('<div class="alert alert-danger alert-dismissible fade show alert-notification" role="alert"><i class="fas fa-exclamation-circle"></i> ' + m + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        window.scrollTo(0, 0);
    }
    window.populateReviewData = function() {
        ['Name', 'Gender', 'Birth_Place', 'Birth_Date', 'KTP', 'kewarganegaraan', 'Religion', 'Marital_Status', 'Address', 'City', 'Phone', 'Handphone', 'Email', 'Emergency_Contact_Name', 'Last_Education', 'Last_Education_From', 'Education_Major', 'GPA'].forEach(function(id) {
            var el = $('#review-' + id);
            if (el.length) el.text($('#' + id).val() || '-');
        });
        var html = '', has = false;
        $('input[type="file"]').each(function() {
            if (this.files.length > 0) {
                has = true;
                var label = $(this).closest('.form-group').find('label').first().text().replace('*', '').trim();
                html += '<li class="mb-2"><i class="fas fa-check-circle text-success"></i> ' + label + ': <strong>' + this.files[0].name + '</strong></li>';
            }
        });
        $('#review-documents').html(has ? html : '<li class="mb-2"><i class="fas fa-times-circle text-danger"></i> <span class="text-muted">Belum ada dokumen</span></li>');
    };
    $('a[href="#review"]').on('shown.bs.tab', function() { populateReviewData(); });

    // Preview + validasi file (foto max 2MB, lain max 5MB, mime sesuai accept).
    $('input[type="file"]').on('change', function() {
        var inp = $(this), file = this.files[0], id = inp.attr('id');
        if (!file) return;
        var max = id === 'Foto' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
        if (file.size > max) {
            showError('File ' + file.name + ' melebihi batas ' + (max / 1048576) + 'MB.');
            inp.val('');
            return;
        }
        inp.next('.custom-file-label').html(file.name.length > 25 ? file.name.substring(0, 22) + '...' : file.name);
        $('#check-' + id).removeClass('text-muted').addClass('text-success');
        if (file.type.indexOf('image/') === 0) {
            var r = new FileReader();
            r.onload = function(e) { $('#preview-' + id).html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-width:200px;max-height:200px;">'); };
            r.readAsDataURL(file);
        } else {
            $('#info-' + id).html('<i class="fas fa-check-circle"></i> ' + file.name);
        }
    });
});
</script>
