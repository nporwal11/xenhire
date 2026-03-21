jQuery(document).ready(function($) {
    
    // Load ALL email templates from CBO on page load
    loadEmailTemplates();
    
    // Open Create Modal
    $('#kt-btn-create').on('click', function() {
        openCreateModal();
    });
    
    // Open Edit Modal
    $('.xenhire-edit-stage').on('click', function() {
        editStage(this);
    });
    
    // Close Modal
    $('.xenhire-modal-close, .xenhire-btn-cancel').on('click', function() {
        closeModal();
    });
    
    // Close on overlay click
    $('.xenhire-modal-overlay').on('click', function() {
        closeModal();
    });
    
    // Delete Stage
    $('.xenhire-delete-stage').on('click', function() {
        deleteStage(this);
    });
    
    // Search on Enter
    $('#kt_filter_search').on('keypress', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            loadStages();
        }
    });

    // Clear Search
    $('.xenhire-search-clear-btn').on('click', function(e) {
        e.preventDefault();
        $('#kt_filter_search').val('');
        loadStages();
    });

    // Clear Search Link (in no results)
    $(document).on('click', '.xenhire-clear-search-link', function(e) {
        e.preventDefault();
        $('#kt_filter_search').val('');
        loadStages();
    });

    function loadStages() {
        var search = $('#kt_filter_search').val();
        
        // Toggle clear button
        if (search) {
            $('.xenhire-search-clear-btn').show();
        } else {
            $('.xenhire-search-clear-btn').hide();
        }

        // Show loading state (optional, can add a spinner to tbody)
        $('#tBody').html('<tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_list_stages',
                nonce: xenhireAjax.nonce,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    $('#tBody').html(response.data.html);
                    
                    // Re-bind events for dynamic content if needed
                    // Note: We used delegated events for delete, but edit is direct binding
                    // We need to re-bind edit button click
                    $('.xenhire-edit-stage').off('click').on('click', function() {
                        editStage(this);
                    });
                    
                    $('.xenhire-delete-stage').off('click').on('click', function() {
                        deleteStage(this);
                    });
                } else {
                    $('#tBody').html('<tr><td colspan="5" style="text-align: center; color: red;">Error loading stages</td></tr>');
                }
            },
            error: function() {
                $('#tBody').html('<tr><td colspan="5" style="text-align: center; color: red;">Connection error</td></tr>');
            }
        });
    }
    
    // Form Validation & Submit
    $('#kt_modal_stage_form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.fv-feedback').removeClass('fv-invalid').text('');
        $('.xenhire-form-control').removeClass('is-invalid');
        
        var isValid = true;
        
        // Validate Stage Name
        if ($('#Name').val().trim() === '') {
            showError('#Name', 'Stage Name is required');
            isValid = false;
        }
        
        // Validate Color
        if ($('#Color').val() === '') {
            showError('#Color', 'Stage color is required');
            isValid = false;
        }
        
        // Validate Position (only if visible)
        if ($('.ordpos').is(':visible')) {
            if ($('#OrdPos').val() === '' || $('#OrdPos').val() === '1') {
                showError('#OrdPos', 'Position is required');
                isValid = false;
            }
        }
        
        // Validate Email Template
        if ($('#EmailTemplateID').val() === '') {
            showError('#EmailTemplateID', 'Email Template is required');
            isValid = false;
        }
        
        if (!isValid) {
            return false;
        }
        
        // Show loading indicator
        $('#kt_modal_stage_submit').attr('data-kt-indicator', 'on');
        $('#kt_modal_stage_submit').prop('disabled', true);
        
        // Submit form
        saveStage();
    });
    
    // Functions
    function loadEmailTemplates() {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_email_templates_cbo',
                nonce: xenhireAjax.nonce
            },
            success: function(response) {
                
                if (response.success && response.data) {
                    var templates = response.data;
                    
                    // Check if templates is an array
                    if (!Array.isArray(templates)) {
                        console.error('Templates is not an array:', templates);
                        $('#EmailTemplateID').html('<option value="">Invalid data format</option>');
                        return;
                    }
                    
                    if (templates.length === 0) {
                        console.warn('No templates found');
                        $('#EmailTemplateID').html('<option value="">No templates available</option>');
                        return;
                    }
                    
                    var options = '<option value="">Select Template...</option>';
                    
                    // Response format: [{"Value": 871, "DisplayText": "New"}, ...]
                    $.each(templates, function(index, template) {
                        if (template && template.Value && template.DisplayText) {
                            options += '<option value="' + escapeHtml(template.Value) + '">' + escapeHtml(template.DisplayText) + '</option>';
                        }
                    });
                    
                    $('#EmailTemplateID').html(options);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
                    console.error('✗ Failed to load email templates:', errorMsg);
                    $('#EmailTemplateID').html('<option value="">Failed to load templates</option>');
                    
                    if (response.data) {
                        console.error('Error details:', response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('✗ AJAX error:', status, error);
                $('#EmailTemplateID').html('<option value="">Error loading templates</option>');
            }
        });
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (typeof text !== 'string') {
            return text;
        }
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function openCreateModal() {
        $('#headTitle').text('Add Stage');
        $('#ID').val('-1');
        $('#Name').val('');
        $('#Color').val('#000000');
        $('#OrdPos').val('');
        $('#EmailTemplateID').val('');
        
        // Show position field for create
        $('.ordpos').show();
        
        // Clear validation
        $('.fv-feedback').removeClass('fv-invalid').text('');
        $('.xenhire-form-control').removeClass('is-invalid');
        
        // Show modal
        $('#kt_modal_stage').fadeIn(300).addClass('show');
        $('body').addClass('xenhire-modal-open');
    }
    
    function editStage(elem) {
        var id = $(elem).data('id');
        var name = $(elem).data('name');
        var color = $(elem).data('color');
        var ordPos = $(elem).data('ordpos');
        var emailTemplateId = $(elem).data('emailtemplateid');
        
        $('#ID').val(id);
        $('#Name').val(name);
        $('#Color').val(color);
        $('#OrdPos').val(ordPos);
        $('#EmailTemplateID').val(emailTemplateId);
        
        if (ordPos === 1) {
            $('.ordpos').hide();
        } else {
            $('.ordpos').show();
        }
        
        $('#headTitle').text('Edit Stage');
        
        $('.fv-feedback').removeClass('fv-invalid').text('');
        $('.xenhire-form-control').removeClass('is-invalid');
        
        $('#kt_modal_stage').fadeIn(300).addClass('show');
        $('body').addClass('xenhire-modal-open');
    }
    
    function closeModal() {
        $('#kt_modal_stage').fadeOut(300).removeClass('show');
        $('body').removeClass('xenhire-modal-open');
        
        $('#kt_modal_stage_submit').removeAttr('data-kt-indicator');
        $('#kt_modal_stage_submit').prop('disabled', false);
    }
    
    function showError(fieldId, message) {
        $(fieldId).addClass('is-invalid');
        $(fieldId).closest('.fv-row').find('.fv-feedback').addClass('fv-invalid').text(message);
    }
    
    function saveStage() {
        var ordPosValue = $('#OrdPos').val();
        
        if ($('.ordpos').is(':hidden')) {
            ordPosValue = '1';
        }
        
        if (ordPosValue === '' || ordPosValue === null) {
            ordPosValue = '1';
        }
        
        var formData = {
            ID: $('#ID').val(),
            Name: $('#Name').val(),
            Color: $('#Color').val(),
            OrdPos: ordPosValue,
            EmailTemplateID: $('#EmailTemplateID').val()
        };
        
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: {
                action: 'xenhire_save_stage',
                nonce: xenhireAjax.nonce,
                stage: formData
            },
            success: function(response) {
                
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Stage saved successfully!',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        didOpen: () => {
                            $('.swal2-container').css('z-index', '99999999');
                        }
                    }).then(() => {
                        closeModal();
                        location.reload();
                    });
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to save stage';
                    Swal.fire({
                        title: 'Error!',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        didOpen: () => {
                            $('.swal2-container').css('z-index', '99999999');
                        }
                    });
                    
                    $('#kt_modal_stage_submit').removeAttr('data-kt-indicator');
                    $('#kt_modal_stage_submit').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Save error:', status, error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    didOpen: () => {
                        $('.swal2-container').css('z-index', '99999999');
                    }
                });
                
                $('#kt_modal_stage_submit').removeAttr('data-kt-indicator');
                $('#kt_modal_stage_submit').prop('disabled', false);
            }
        });
    }
    
    function deleteStage(elem) {
        var stageId = $(elem).data('id');
        
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'button button-primary',
                cancelButton: 'button'
            },
            buttonsStyling: false,
            didOpen: () => {
                $('.swal2-container').css('z-index', '99999999');
            }
        });
        
        swalWithBootstrapButtons.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_delete_stage',
                        nonce: xenhireAjax.nonce,
                        id: stageId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Stage has been deleted.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false,
                                didOpen: () => {
                                    $('.swal2-container').css('z-index', '99999999');
                                }
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to delete stage';
                            Swal.fire({
                                title: 'Error!',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                didOpen: () => {
                                    $('.swal2-container').css('z-index', '99999999');
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            didOpen: () => {
                                $('.swal2-container').css('z-index', '99999999');
                            }
                        });
                    }
                });
            }
        });
    }
    
});