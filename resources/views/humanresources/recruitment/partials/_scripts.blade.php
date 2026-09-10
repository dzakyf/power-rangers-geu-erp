<script>
$(document).ready(function() {
    // ========================================
    // TAB NAVIGATION LOGIC
    // ========================================
    
    var currentTab = 0;
    var totalTabs = 5; // personal, contact, education, documents, review
    
    // Initialize progress bar
    updateProgressBar(0);
    
    // ========================================
    // TAB SWITCHING
    // ========================================
    
    function switchTab(tabId) {
        $('#' + tabId).tab('show');
        
        if (tabId === 'personal') currentTab = 0;
        else if (tabId === 'contact') currentTab = 1;
        else if (tabId === 'education') currentTab = 2;
        else if (tabId === 'documents') currentTab = 3;
        else if (tabId === 'review') currentTab = 4;
        
        updateButtons();
        updateProgressBar(currentTab);
    }
    
    // Previous button click
    $('#btnPrevious').click(function() {
        if (currentTab > 0) {
            currentTab--;
            var tabId = getTabId(currentTab);
            switchTab(tabId);
        }
    });
    
    // Next button click
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
        if (validateCurrentTab()) {
            submitForm();
        }
    });
    
    // Save Draft button click
    $('#btnSaveDraft').click(function() {
        saveDraft();
    });
    
    // ========================================
    // HELPER FUNCTIONS
    // ========================================
    
    function getTabId(index) {
        var tabIds = ['personal', 'contact', 'education', 'documents', 'review'];
        return tabIds[index] + '-tab';
    }
    
    function updateButtons() {
        if (currentTab === 0) {
            $('#btnPrevious').prop('disabled', true);
            $('#btnPrevious').css('opacity', '0.5');
        } else {
            $('#btnPrevious').prop('disabled', false);
            $('#btnPrevious').css('opacity', '1');
        }
        
        if (currentTab === totalTabs - 1) {
            $('#btnNext').css('display', 'none');
            $('#btnSubmit').css('display', 'inline-block');
        } else {
            $('#btnNext').css('display', 'inline-block');
            $('#btnSubmit').css('display', 'none');
        }
    }
    
    function updateProgressBar(tabIndex) {
        var progress = ((tabIndex + 1) / totalTabs) * 100;
        $('#formProgress').css('width', progress + '%');
        $('#formProgress').attr('aria-valuenow', tabIndex + 1);
    }
    
    // ========================================
    // VALIDATION
    // ========================================
    
    function validateCurrentTab() {
        var isValid = true;
        var activeTab = $('.tab-pane.active');
        
        activeTab.find('input[required], select[required], textarea[required]').each(function() {
            var field = $(this);
            var fieldId = field.attr('id');
            var fieldValue = field.val().trim();
            
            if (fieldValue === '') {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid');
                
                if (fieldId === 'Email') {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(fieldValue)) {
                        field.addClass('is-invalid');
                        isValid = false;
                    }
                }
                
                if (fieldId === 'Handphone' || fieldId === 'Phone') {
                    var phoneRegex = /^[0-9]{10,15}$/;
                    if (!phoneRegex.test(fieldValue.replace(/[\s-]/g, ''))) {
                        field.addClass('is-invalid');
                        isValid = false;
                    }
                }
            }
        });
        
        if (!isValid) {
            showError('Please complete all required fields in this section.');
        }
        
        return isValid;
    }
    
    // ========================================
    // FORM SUBMISSION
    // ========================================
    
    function submitForm() {
        var formData = new FormData();
        
        $('#recruitmentForm input:not([type="file"]):not([type="button"]), ' +
          '#recruitmentForm select, ' +
          '#recruitmentForm textarea').each(function() {
            var field = $(this);
            if (field.attr('type') !== 'hidden' || field.attr('id') !== 'Task_ID') {
                formData.append(field.attr('name'), field.val());
            }
        });
        
        $('#recruitmentForm input[type="file"]').each(function() {
            if (this.files.length > 0) {
                formData.append(this.name, this.files[0]);
            }
        });
        
        showLoading(true);
        
        $.ajax({
            url: '{{ url('recruitment-new-submit') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showLoading(false);
                
                if (response.status === 'success') {
                    showSuccess('Data submitted successfully! Candidate ID: ' + response.data.id);
                    setTimeout(function() {
                        window.location.href = '{{ url('recruitment') }}';
                    }, 2000);
                } else {
                    showError(response.message || 'Submission failed. Please try again.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showLoading(false);
                var errorMsg = 'Submission failed.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                showError(errorMsg);
            }
        });
    }
    
    function saveDraft() {
        var formData = new FormData();
        
        $('#recruitmentForm input:not([type="file"]):not([type="button"]), ' +
          '#recruitmentForm select, ' +
          '#recruitmentForm textarea').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        
        $('#recruitmentForm input[type="file"]').each(function() {
            if (this.files.length > 0) {
                formData.append(this.name, this.files[0]);
            }
        });
        
        showLoading(true);
        
        $.ajax({
            url: '{{ url('recruitment-new-submit') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showLoading(false);
                if (response.status === 'success') {
                    showSuccess('Draft saved successfully!');
                } else {
                    showError(response.message);
                }
            },
            error: function(jqXHR) {
                showLoading(false);
                var errorMsg = 'Failed to save draft.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                showError(errorMsg);
            }
        });
    }
    
    // ========================================
    // UI HELPER FUNCTIONS
    // ========================================
    
    function showLoading(isLoading) {
        if (isLoading) {
            $('#btnSubmit, #btnNext, #btnPrevious, #btnSaveDraft').prop('disabled', true);
            $('.btn').addClass('loading');
        } else {
            $('#btnSubmit, #btnNext, #btnPrevious, #btnSaveDraft').prop('disabled', false);
            $('.btn').removeClass('loading');
        }
    }
    
    function showSuccess(message) {
        $('.alert-success-notification').remove();
        
        var alertHtml = '<div class="alert alert-success alert-dismissible fade show alert-success-notification" role="alert">' +
            '<i class="fas fa-check-circle"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            '</div>';
        
        $('#recruitmentForm').before(alertHtml);
        setTimeout(function() {
            $('.alert-success-notification').alert('close');
        }, 5000);
    }
    
    function showError(message) {
        $('.alert-danger-notification').remove();
        
        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show alert-danger-notification" role="alert">' +
            '<i class="fas fa-exclamation-circle"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            '</div>';
        
        $('#recruitmentForm').before(alertHtml);
        setTimeout(function() {
            $('.alert-danger-notification').alert('close');
        }, 8000);
    }
    
    // ========================================
    // AUTO-SAVE (every 30 seconds)
    // ========================================
    
    var autoSaveInterval = setInterval(function() {
        if ($('#recruitmentForm.isDirty')) {
            saveDraft();
        }
    }, 30000);
    
    $('#recruitmentForm input, #recruitmentForm select, #recruitmentForm textarea').on('change input', function() {
        $('#recruitmentForm').addClass('isDirty');
    });
    
    // ========================================
    // FILE UPLOAD PREVIEW
    // ========================================
    
    $('input[type="file"]').on('change', function() {
        var fileInput = $(this);
        var file = this.files[0];
        var fileId = fileInput.attr('id');
        var fileLabel = fileInput.next('.custom-file-label');
        
        if (file) {
            var fileName = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name;
            fileLabel.html(fileName);
            
            var maxSize = (file.type.startsWith('image/')) ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            
            if (file.size > maxSize) {
                showError('File size exceeds limit (' + (maxSize / (1024 * 1024)).toFixed(1) + 'MB). Please choose a smaller file.');
                fileInput.val('');
                fileLabel.html('Choose file');
                return;
            }
            
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var previewContainer = $('#preview-' + fileId);
                    if (previewContainer.length) {
                        previewContainer.html(
                            '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">'
                        );
                    }
                };
                reader.readAsDataURL(file);
            }
            
            $('#check-' + fileId).removeClass('text-muted').addClass('text-success');
        }
    });
    
    // ========================================
    // CITY-TO-PROVINCE AUTO-FILL
    // ========================================
    
    var cityProvinceMap = {
        'jakarta': 'DKI Jakarta',
        'bandung': 'West Java',
        'surabaya': 'East Java',
        'medan': 'North Sumatra',
        'makassar': 'South Sulawesi',
        'yogyakarta': 'Yogyakarta',
        'semarang': 'Central Java',
        'bali': 'Bali',
        'banjarmasin': 'South Kalimantan',
        'palembang': 'South Sumatra'
    };
    
    $('#City').on('change input', function() {
        var city = $(this).val().toLowerCase();
        var matchedProvince = null;
        
        for (var key in cityProvinceMap) {
            if (city.includes(key)) {
                matchedProvince = cityProvinceMap[key];
                break;
            }
        }
        
        $('#Province').val(matchedProvince || '');
    });
});
</script>
