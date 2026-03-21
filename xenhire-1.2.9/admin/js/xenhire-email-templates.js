jQuery(document).ready(function($) {
    
    var EID = 0;
    var bg = '#f0f0f1';
    var borderColor = '#c3c4c7';
    var color = '#2c3338';
    
    // Global CKEditor instance
    window.emailEditor = null;
    
    // Initialize CKEditor function
    function initCKEditor(callback) {
        // Remove existing instance if any
        if (window.emailEditor) {
            window.emailEditor.destroy()
                .then(() => {
                    window.emailEditor = null;
                    createEditor(callback);
                })
                .catch(error => {
                    console.error('Error destroying editor:', error);
                });
        } else {
            createEditor(callback);
        }
    }

    function createEditor(callback) {
        var element = document.querySelector("#Body");
        if (element) {
            ClassicEditor
                .create(element, {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
                })
                .then(editor => {
                    window.emailEditor = editor;
                    if (callback) {
                        callback(editor);
                    }
                })
                .catch(error => {
                    console.error('CKEditor init error:', error);
                });
        }
    }


    // Load templates
    GetTemplate();
    
    $("#kt_filter_search").keypress(function (e) {
        if (e.keyCode === 13) GetTemplate();
    });
    
    $("#kt-btn-create").click(function () {
        openTemplateModal();
    });
    
    $('#kt-btn-smtp').on('click', function() {
        openSmtpModal();
    });
    
    $('.xenhire-modal-close, .xenhire-btn-cancel').on('click', function() {
        closeModal();
    });
    
    $('.xenhire-modal-overlay').on('click', function() {
        closeModal();
    });
    
    $('#kt_modal_smtp_test').on('click', function() {
        testSmtpConnection();
    });
    
    $('#kt_modal_smtp_submit').on('click', function(e) {
        e.preventDefault();
        saveSmtpConfig();
    });
    
    $('#kt_modal_add_template_form').on('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    // FUNCTIONS
    function GetTemplate() {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_email_templates',
                nonce: xenhireAjax.nonce,
                search: $("#kt_filter_search").val()
            },
            success: function(response) {
                if (response.success && response.data) {
                    var returnData = response.data;
                    
                    if (returnData.Result == "OK") {
                        $("#tHead, #tBody").empty();
                        $("#tHead").append(`
                            <tr>
                                <th class="column-name" width="120">Name</th>
                                <th class="column-template">Subject</th>
                                <th class="column-actions text-right">Actions</th>
                            </tr>
                        `);
                        
                        var data = JSON.parse(returnData.data);
                        
                        if (data.length === 0) {
                            $("#tBody").append(`
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 40px;">
                                        <p style="color: #999; font-size: 16px;">No email templates found.</p>
                                    </td>
                                </tr>
                            `);
                            return;
                        }
                        
                        for (var i = 0; i < data.length; i++) {
                            var full = data[i];
                            $("#tBody").append(`
                                <tr>
                                    <td class="column-name" width="120"><strong>` + escapeHtml(full.Name) + `</strong></td>
                                    <td class="column-template">` + escapeHtml(full.Subject) + `</td>
                                    <td class="column-actions text-right">
                                        <button type="button" class="xh-btn xh-secondary button-small btn-` + full.ID + `" id="kt_btnEdit_` + i + `">Edit</button>
                                        <button type="button" class="xh-btn xh-danger button-small button-danger" onclick="deleteTemplate(` + full.ID + `);">Delete</button>
                                    </td>
                                </tr>
                            `);
                            
                            $("#kt_btnEdit_" + i).data('template', full);
                            $("#kt_btnEdit_" + i).on('click', function() {
                                editTemplate(this);
                            });
                        }
                        
                        if (EID > 0) editTemplate($(".btn-" + EID)[0]);
                    } else if (returnData.Result == "ERROR") {
                        showError(returnData.Message);
                    } else {
                        showOops();
                    }
                } else {
                    showError(response.data?.message || 'Failed to load templates');
                }
            },
            error: function() { showOops(); }
        });
    }
    
    function openTemplateModal() {
        $("#headTitle").text('Add New Template');
        $("#ID").val("-1");
        $("#Name").val("");
        $("#Subject").val("");
        $("#Body").val("");
        
        // Show modal
        $('#kt_modal_add_template').fadeIn(300).addClass('show');
        $('body').addClass('xenhire-modal-open');
        
        // Initialize CKEditor after modal is shown
        setTimeout(function() {
            initCKEditor(function(editor) {
                editor.setData('');
            });
        }, 100);
    }
    
    function editTemplate(elem) {
        var template = $(elem).data('template');
        if (!template) {
            console.error('No template data found');
            return;
        }
        
        $("#headTitle").text('Edit Template');
        $("#ID").val(template.ID);
        $("#Name").val(template.Name);
        $("#Subject").val(template.Subject);
        
        // Get the body content
        var bodyContent = template.Body || '';
        
        // Decode HTML entities
        var tempDiv = document.createElement('textarea');
        tempDiv.innerHTML = bodyContent;
        bodyContent = tempDiv.value;
        
        // Show modal
        $('#kt_modal_add_template').fadeIn(300).addClass('show');
        $('body').addClass('xenhire-modal-open');
        
        // Initialize CKEditor with content
        setTimeout(function() {
            initCKEditor(function(editor) {
                editor.setData(bodyContent);
            });
        }, 100);
    }
    
    window.deleteTemplate = function(templateId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            customClass: {
                confirmButton: 'button button-primary btn-confirm',
                cancelButton: 'button'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_delete_email_template',
                        nonce: xenhireAjax.nonce,
                        id: templateId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ 
                                title: 'Deleted!', 
                                text: 'Template deleted.', 
                                icon: 'success', 
                                timer: 1500, 
                                showConfirmButton: false 
                            }).then(() => GetTemplate());
                        } else {
                            showError(response.data?.message || 'Failed to delete');
                        }
                    },
                    error: function() { showOops(); }
                });
            }
        });
    };
    
    function saveTemplate() {
    var ID = Number($("#ID").val());
    if ($("#Name").val() === "") { showError("Name is required"); return; }
    if ($("#Subject").val() === "") { showError("Subject is required"); return; }
    
    startProcess('#kt_modal_add_template_submit');
    
    // Get content from CKEditor
    var body = '';
    if (window.emailEditor) {
        body = window.emailEditor.getData();
    } else {
        body = $("#Body").val();
    }
    
    $.ajax({
        url: xenhireAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'xenhire_save_email_template',
            nonce: xenhireAjax.nonce,
            template: {
                ID: ID,
                Name: $("#Name").val(),
                Subject: $("#Subject").val(),
                Body: body
            }
        },
        success: function(response) {
            stopProcess('#kt_modal_add_template_submit');
            
            if (response.success) {
                // Show success message with higher z-index
                Swal.fire({ 
                    title: 'Success!', 
                    text: 'Template saved successfully!', 
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        container: 'swal-high-zindex'
                    },
                    didOpen: () => {
                        // Force z-index higher than modal
                        const swalContainer = document.querySelector('.swal2-container');
                        if (swalContainer) {
                            swalContainer.style.zIndex = '999999';
                        }
                    }
                }).then(() => {
                    closeModal();
                    GetTemplate();
                });
            } else {
                showError(response.data?.message || 'Failed to save');
            }
        },
        error: function() { 
            stopProcess('#kt_modal_add_template_submit');
            showOops(); 
        }
    });
}

    
    function openSmtpModal() {
    $('#kt_modal_smtp').fadeIn(300).addClass('show');
    $('body').addClass('xenhire-modal-open');
}

// Handle upgrade link click
$(document).on('click', '#upgrade-plan-link', function(e) {
    e.preventDefault();
    
    // Close the modal
    closeModal();
    
    window.location.href = 'admin.php?page=xenhire-packages';
});

    
    function saveSmtpConfig() {
        startProcess('#kt_modal_smtp_submit');
        
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_save_smtp',
                nonce: xenhireAjax.nonce,
                smtp: {
                    Email_GatewayURL: $('#Email_GatewayURL').val(),
                    Email_GatewayPortNo: $('#Email_GatewayPortNo').val(),
                    Email_ClientUserName: $('#Email_ClientUserName').val(),
                    Email_Password: $('#Email_Password').val(),
                    Email_From: $('#Email_From').val(),
                    Email_EnableSSL: $('#Email_EnableSSL').is(':checked') ? 1 : 0
                }
            },
            success: function(response) {
                stopProcess('#kt_modal_smtp_submit');
                if (response.success) {
                    Swal.fire({ 
                        title: 'Success!', 
                        text: 'SMTP saved!', 
                        icon: 'success', 
                        timer: 1500, 
                        showConfirmButton: false 
                    }).then(() => closeModal());
                } else {
                    showError(response.data?.message || 'Failed to save');
                }
            },
            error: function() { 
                stopProcess('#kt_modal_smtp_submit');
                showOops(); 
            }
        });
    }
    
    function testSmtpConnection() {
        startProcess('#kt_modal_smtp_test');
        
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_test_smtp',
                nonce: xenhireAjax.nonce,
                smtp: {
                    Email_GatewayURL: $('#Email_GatewayURL').val(),
                    Email_GatewayPortNo: $('#Email_GatewayPortNo').val(),
                    Email_ClientUserName: $('#Email_ClientUserName').val(),
                    Email_Password: $('#Email_Password').val(),
                    Email_From: $('#Email_From').val(),
                    Email_EnableSSL: $('#Email_EnableSSL').is(':checked') ? 1 : 0
                }
            },
            success: function(response) {
                stopProcess('#kt_modal_smtp_test');
                if (response.success) {
                    Swal.fire({ 
                        title: 'Success!', 
                        text: 'SMTP test successful!', 
                        icon: 'success' 
                    });
                } else {
                    Swal.fire({ 
                        title: 'Failed!', 
                        text: response.data?.message || 'Test failed', 
                        icon: 'error' 
                    });
                }
            },
            error: function() { 
                stopProcess('#kt_modal_smtp_test');
                showOops(); 
            }
        });
    }
    
    function closeModal() {
        $('.xenhire-modal').fadeOut(300).removeClass('show');
        $('body').removeClass('xenhire-modal-open');
        $('[data-kt-indicator="on"]').removeAttr('data-kt-indicator');
        $('.button').prop('disabled', false);
        
        // Destroy CKEditor instance when closing modal
        if (window.emailEditor) {
            window.emailEditor.destroy()
                .then(() => {
                    window.emailEditor = null;
                })
                .catch(error => {
                    console.error('Error destroying editor:', error);
                });
        }
    }
    
    function startProcess(selector) {
        $(selector).attr('data-kt-indicator', 'on').prop('disabled', true);
    }
    
    function stopProcess(selector) {
        $(selector).removeAttr('data-kt-indicator').prop('disabled', false);
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function showError(message) {
        Swal.fire({ 
            title: 'Error!', 
            text: message, 
            icon: 'error', 
            confirmButtonText: 'OK' 
        });
    }
    
    function showOops() {
        Swal.fire({ 
            title: 'Oops!', 
            text: 'An unexpected error occurred.', 
            icon: 'error', 
            confirmButtonText: 'OK' 
        });
    }
    
});

