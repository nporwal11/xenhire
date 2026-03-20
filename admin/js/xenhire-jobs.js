jQuery(document).ready(function ($) {

    var currentPage = 1;
    var currentInviteJob = null;
    window.ckEditors = {}; // Store CKEditor instances

    loadEmployers();
    bindDataTable(1);

    $('#kt_filter_btn').click(function () {
        $('#kt_filter_dropdown').slideToggle(300);
    });

    // Close filter dropdown when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#kt_filter_dropdown, #kt_filter_btn').length) {
            $('#kt_filter_dropdown').slideUp(300);
        }
    });

    // Search Button Click
    $('#kt_search_btn').click(function () {
        bindDataTable(1);
    });

    // Enter Key on Search Input
    $('#kt_filter_search').on('keypress', function (e) {
        if (e.which === 13) {
            bindDataTable(1);
        }
    });

    // Show/Hide Clear Button
    $('#kt_filter_search').on('input', function () {
        if ($(this).val().length > 0) {
            $('#kt_clear_search').show();
        } else {
            $('#kt_clear_search').hide();
        }
    });

    // Clear Button Click
    $('#kt_clear_search').click(function () {
        $('#kt_filter_search').val('');
        $(this).hide();
        bindDataTable(1);
    });

    $('#kt_button_status').click(function () {
        $('#kt_filter_dropdown').slideUp(300);
        bindDataTable(1);
    });

    $('#kt_reset_status').click(function () {
        $('#EmployerID').val('-1');
        $('#kt_filter_status').val('-1');
        $('#kt_filter_search').val('');
        $('#kt_clear_search').hide();
        $('#kt_filter_dropdown').slideUp(300);
        bindDataTable(1);
    });

    $('#selectPager').change(function () {
        bindDataTable(1);
    });

    // Pagination Click Handler (Event Delegation)
    $(document).on('click', '.page-btn', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        // Ensure page is a number and valid
        if (page && !$(this).prop('disabled') && !$(this).hasClass('active')) {
            bindDataTable(page);
        }
    });

    $(document).on('click', '[data-dismiss="modal"]', function () {
        closeInviteModal();
    });

    $(document).on('click', '.invite-modal-overlay', function (e) {
        if ($(e.target).hasClass('invite-modal-overlay')) {
            closeInviteModal();
        }
    });

    $(document).on('click', '.invite-tab-btn', function () {
        var tabName = $(this).data('tab');
        $('.invite-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.invite-tab-pane').removeClass('active');
        $('#' + tabName + '-tab').addClass('active');
    });

    $(document).on('click', '#btnCopyLink', function () {
        copyToClipboard('ShareURL');
    });

    $(document).on('click', '#btnShareDropdown', function (e) {
        e.stopPropagation();
        $('#shareDropdownMenu').toggle();
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#btnShareDropdown, #shareDropdownMenu').length) {
            $('#shareDropdownMenu').hide();
        }
    });

    $(document).on('submit', '#kt_modal_SendEmail_form', function (e) {
        e.preventDefault();
        sendInviteEmail();
    });

    $(document).on('change', '.job-status-toggle', function () {
        var jobId = $(this).data('job-id');
        var isActive = $(this).is(':checked');
        changeJobStatus(jobId, isActive, this);
    });

    $(document).on('click', '.job-btn-invite', function (e) {
        e.preventDefault();
        var jobData = $(this).data('job-data');
        if (jobData) {
            openInviteModal(jobData);
        }
    });

    function getCurrentPage() {
        return currentPage;
    }

    function loadEmployers() {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_employers',
                nonce: xenhireAjax.nonce
            },
            success: function (response) {
                if (response.success && response.data) {
                    var employers = response.data;
                    var options = '<option value="-1">Select Employer...</option>';
                    for (var i = 0; i < employers.length; i++) {
                        options += '<option value="' + employers[i].Value + '">' + escapeHtml(employers[i].DisplayText) + '</option>';
                    }
                    $('#EmployerID').html(options);
                }
            },
            error: function () {
                console.error('Failed to load employers');
            }
        });
    }

    function bindDataTable(ActivePageNo, showLoading) {
        var PageNo = (ActivePageNo == undefined || ActivePageNo < 1 ? 1 : ActivePageNo);
        currentPage = PageNo;
        var PageSize = ($('#selectPager').val() == undefined ? 10 : $('#selectPager').val());

        if (showLoading !== false) {
            $('#tBody').html('<tr><td colspan="6" class="loading-row"><div class="loading-spinner">Loading jobs...</div></td></tr>');
        }

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_list_jobs',
                nonce: xenhireAjax.nonce,
                filters: {
                    IsActive: $('#kt_filter_status').val(),
                    EmployerID: $('#EmployerID').val() || '-1',
                    Search: $('#kt_filter_search').val(),
                    PageNo: PageNo,
                    PageSize: PageSize
                }
            },
            success: function (response) {
                if (response.success && response.data) {
                    var data = response.data.jobs;
                    var metadata = response.data.metadata;
                    $('#tBody').empty();

                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            renderJobRow(data[i], i);
                        }
                        var TotalRecordCount = metadata.TotalRecordCount;
                        var TotalPages = Math.ceil(TotalRecordCount / PageSize);
                        renderPagination(TotalRecordCount, TotalPages, PageNo, PageSize);
                        $('#pagerDiv').show();

                        if (metadata.TotalRecordCount >= 2) {
                            $('#btnAddJob').off('click').attr('href', '#').on('click', function (e) {
                                e.preventDefault();
                                Swal.fire({
                                    text: "You can only create a maximum of 2 jobs. Please upgrade your package to create more jobs.",
                                    icon: "warning",
                                    buttonsStyling: false,
                                    confirmButtonText: "Upgrade",
                                    showCancelButton: true,
                                    cancelButtonText: "Cancel",
                                    customClass: {
                                        confirmButton: "btn btn-primary",
                                        cancelButton: "btn btn-light"
                                    }
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        window.location.href = "admin.php?page=xenhire-packages";
                                    }
                                });
                            });
                        } else if (metadata.IsAllowJobAdd) {
                            $('#btnAddJob').off('click').attr('href', '' + xenhireAjax.job_add_url + '');
                        } else {
                            $('#btnAddJob').off('click').attr('href', '#').on('click', function (e) {
                                e.preventDefault();
                                showSubscription(metadata.Message);
                            });
                        }
                    } else {
                        $('#tBody').html('<tr><td colspan="6" class="no-jobs-found"><div style="padding: 40px;"><span style="font-size: 16px; color: #9ca3af;">No jobs found</span></div></td></tr>');
                        $('#pagerDiv').hide();
                    }
                } else {
                    showError(response.data?.message || 'Failed to load jobs');
                }
            },
            error: function () {
                $('#tBody').html('<tr><td colspan="6" class="loading-row"><div class="loading-spinner" style="color: #ef4444;">Error loading jobs</div></td></tr>');
                showOops();
            }
        });
    }

    function renderJobRow(job, index) {
        var placeholderUrl = (typeof xenhireAjax !== 'undefined' && xenhireAjax.plugin_url ? xenhireAjax.plugin_url : '') + 'public/images/placeholder.png';
        var logoUrl = job.LogoIMG || placeholderUrl;
        var className = index % 2 === 0 ? 'even' : 'odd';

        var row = '<tr class="' + className + '">' +
            '<td><div class="employer-logo-wrapper"><img src="' + logoUrl + '" class="employer-logo" alt="' + escapeHtml(job.Employer) + '" onerror="this.src=\'' + placeholderUrl + '\'" /></div></td>' +
            '<td><span class="job-title">' + escapeHtml(job.JobTitle) + '</span><span class="job-location">' + escapeHtml(job.City || '') + '</span></td>' +
            '<td><div class="job-detail-item"><i class="ki-outline ki-briefcase ki-fs-2"></i><span>' + escapeHtml(job.WorkEx || 'Not Specified') + '</span></div>' +
            '<div class="job-detail-item"><i class="ki-outline ki-dollar ki-fs-2"></i><span>' + escapeHtml(job.Salary || 'Not Mentioned') + '</span></div></td>' +
            '<td style="text-align: center;"><label class="job-toggle"><input type="checkbox" class="job-status-toggle" ' + (job.IsActive ? 'checked' : '') + ' data-job-id="' + job.ID + '" /><span class="toggle-slider"></span></label></td>' +
            '<td><a href="#" class="xh-btn xh-light-primary" data-job-id="' + job.ID + '">' + (job.JobApplications || 0) + ' Applications</a></td>' +
            '<td class="xh-action"><a href="admin.php?page=xenhire-job-add&id=' + job.ID + '" class="xh-btn xh-secondary" data-job-id="' + job.ID + '">Edit</a>' +
            '<a href="' + xenhireAjax.site_url + '/jobs/?job_id=' + job.ID + '" target="_blank" class="xh-btn xh-info">Preview</a>' +
            '<!--<button type="button" class="xh-btn xh-success job-btn-invite" data-job-index="' + index + '">Invite</button>--></td>' +
            '</tr>';

        $('#tBody').append(row);
        $('.job-btn-invite[data-job-index="' + index + '"]').data('job-data', job);
    }

    function changeJobStatus(jobId, isActive, toggleElement) {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_toggle_job_status',
                nonce: xenhireAjax.nonce,
                job_id: jobId
            },
            success: function (response) {
                if (response.success && response.data) {
                    var message = response.data.Message || '';
                    var isError = response.data.IsError || false;

                    if (message === '') {
                        showSuccess('Job status changed');
                        bindDataTable(getCurrentPage(), false);
                    } else {
                        $(toggleElement).prop('checked', !isActive);
                        if (isError) {
                            showError(message);
                        } else {
                            showSubscription(message);
                        }
                    }
                } else {
                    $(toggleElement).prop('checked', !isActive);
                    showError(response.data?.message || 'Failed to update status');
                }
            },
            error: function () {
                $(toggleElement).prop('checked', !isActive);
                showOops();
            }
        });
    }

    function renderPagination(totalRecords, totalPages, currentPageNum, pageSize) {
        var start = ((currentPageNum - 1) * pageSize) + 1;
        var end = Math.min(currentPageNum * pageSize, totalRecords);
        $('#record-info').text('showing ' + start + ' to ' + end + ' of ' + totalRecords + ' records');

        var pagination = '';
        // First & Previous
        pagination += '<button type="button" class="page-btn" ' + (currentPageNum === 1 ? 'disabled' : '') + ' data-page="1"><i class="ki-duotone ki-double-left ki-fs-3"><span class="path1"></span><span class="path2"></span></i></button>';
        pagination += '<button type="button" class="page-btn" ' + (currentPageNum === 1 ? 'disabled' : '') + ' data-page="' + (currentPageNum - 1) + '"><i class="ki-duotone ki-left ki-fs-3"></i></button>';

        // Smart Pagination: Max 5 pages
        var maxVisible = 5;
        var startPage = Math.max(1, currentPageNum - Math.floor(maxVisible / 2));
        var endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        for (var i = startPage; i <= endPage; i++) {
            pagination += '<button type="button" class="page-btn ' + (i === currentPageNum ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>';
        }

        // Next & Last
        pagination += '<button type="button" class="page-btn" ' + (currentPageNum === totalPages ? 'disabled' : '') + ' data-page="' + (currentPageNum + 1) + '"><i class="ki-duotone ki-right ki-fs-3"></i></button>';
        pagination += '<button type="button" class="page-btn" ' + (currentPageNum === totalPages ? 'disabled' : '') + ' data-page="' + totalPages + '"><i class="ki-duotone ki-double-right ki-fs-3"><span class="path1"></span><span class="path2"></span></i></button>';

        $('#Pager').html(pagination);
    }

    function openInviteModal(job) {
        currentInviteJob = job;

        // Use WordPress Jobs page URL instead of API PreviewURL
        var shareUrl = xenhireAjax.site_url + '/jobs/?job_id=' + job.ID;
        $('#ShareURL').val(shareUrl);

        // Setup social share links
        $('.linkedin').attr('href', '' + encodeURIComponent(shareUrl));
        $('.facebook').attr('href', '' + encodeURIComponent(shareUrl));
        $('.whatsapp').attr('href', ' for Interview for ' + encodeURIComponent(job.JobTitle) + ' at ' + encodeURIComponent(job.Employer) + '%0D%0A%0D%0A' + encodeURIComponent(shareUrl));
        $('.twitter').attr('href', '' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(job.JobTitle + ' at ' + job.Employer));

        // Initialize Tagify for Emails
        var input = document.querySelector("#InviteCCEmails");
        if (input && !input.tagify) {
            if (typeof Tagify !== 'undefined') {
                try {
                    input.tagify = new Tagify(input, {
                        pattern: /^.{0,20}$/, // Validate typed tag(s) by Regex. Here maximum 20 chars.
                        delimiters: ", ",        // add new tags when a comma or a space character is entered
                        maxTags: 5,
                        blacklist: ["forbidden", "words"],
                        keepInvalidTags: true, // do not remove invalid tags (but keep them marked as invalid)
                        // transformTag: function (tagData) {
                        //     tagData.style = "--tag-bg:" + getRandomColor();
                        // },
                        dropdown: {
                            enabled: 1, // suggest tags after a single character input
                            classname: "extra-properties" // custom class for the suggestions dropdown
                        }
                    });
                } catch (e) {
                    console.error('Tagify init failed:', e);
                }
            } else {
                console.warn('Tagify library not loaded.');
            }
        }

        // Invite Text
        var inviteText = '<p>🚀 <strong>We\'re Hiring! Become a ' + escapeHtml(job.JobTitle) + ' at ' + escapeHtml(job.Employer) + '!</strong> ☕️</p>' +
            '<p>Are you looking for a new opportunity? We want <strong>YOU</strong> to join our team!</p>' +
            '<p><strong>How to Apply:</strong></p>' +
            '<ol>' +
            '<li>Visit <a href="' + shareUrl + '" target="_blank">this link</a></li>' +
            '<li>Log in with your email and verify</li>' +
            '<li>Record your responses to our questions</li>' +
            '</ol>' +
            '<p>Don\'t miss out on this chance to be a part of an amazing team! Complete your video interview at your convenience. If you have any questions, our team is here to help.</p>' +
            '<p>We can\'t wait to meet you! 😊</p>' +
            '<p>#Hiring #JobOpportunity #JoinOurTeam #CareerOpportunity #NowHiring</p>';

        // Initialize CKEditor for Invite Text
        if (!window.ckEditors['InviteText']) {
            ClassicEditor
                .create(document.querySelector('#InviteText'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    removePlugins: ['MediaEmbed']
                })
                .then(editor => {
                    window.ckEditors['InviteText'] = editor;
                    editor.setData(inviteText);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            window.ckEditors['InviteText'].setData(inviteText);
        }

        // Email form
        // Clear Tagify if initialized
        if (input && input.tagify) {
            input.tagify.removeAllTags();
        } else {
            $('#InviteCCEmails').val('');
        }

        $('#InviteSubject').val('Invitation for Interview for ' + job.JobTitle + ' at ' + job.Employer);

        // Email body
        var mailBody = 'Dear Candidate,<br><br>Congratulations! You have been chosen to interview for the ' +
            escapeHtml(job.JobTitle) + ' position at ' + escapeHtml(job.Employer) +
            '. We believe you could be a great fit for our team.<br><br>' +
            'To move forward, we would like to invite you to participate in the interview of our selection process.<br><br>' +
            'Instructions to appear for the interview:<br><br>' +
            'Steps to Complete:<br><br>' +
            '1. Click on the link <a href="' + shareUrl + '" target="_blank">' + shareUrl + '</a> to start<br>' +
            '2. Login with your email and verify<br>' +
            '3. Record your responses to our questions<br><br>' +
            'Please complete the interview at your earliest convenience. If you have any questions or need assistance, our team is here to help.<br><br>' +
            'We are excited to learn more about you and look forward to your participation.<br><br>' +
            'Best regards,<br><br>HR Team<br>' + escapeHtml(job.Employer);

        // Initialize CKEditor for Email Body
        if (!window.ckEditors['Body']) {
            ClassicEditor
                .create(document.querySelector('#Body'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    removePlugins: ['MediaEmbed']
                })
                .then(editor => {
                    window.ckEditors['Body'] = editor;
                    editor.setData(mailBody);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            window.ckEditors['Body'].setData(mailBody);
        }

        $('#kt_modal_Invite').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }

    function closeInviteModal() {
        // Don't destroy CKEditors, just hide modal. 
        $('#kt_modal_Invite').fadeOut(300);
        $('body').css('overflow', '');
        $('#shareDropdownMenu').hide();
        currentInviteJob = null;
    }

    function copyToClipboard(elemId) {
        var elem = document.getElementById(elemId);
        if (!elem) return;
        elem.select();
        elem.setSelectionRange(0, 99999);
        try {
            document.execCommand("copy");
            showSuccess('Link copied to clipboard');
        } catch (err) {
            console.error('Copy failed:', err);
        }
    }

    function getRandomColor() {
        function c() {
            var hex = Math.floor(Math.random() * 256).toString(16);
            return ("0" + String(hex)).substr(-2); // pad with zero
        }
        return "#" + c() + c() + c();
    }

    function sendInviteEmail() {
        var submitBtn = $('#kt_modal_SendEmail_submit');
        var emailsVal = $('#InviteCCEmails').val();
        var subject = $('#InviteSubject').val().trim();

        // Get data from CKEditor
        var body = '';
        if (window.ckEditors['Body']) {
            body = window.ckEditors['Body'].getData();
        }

        // Parse Emails from Tagify JSON
        var emails = "";
        if (emailsVal) {
            try {
                emails = JSON.parse(emailsVal).map(function (item) { return item.value; }).join(';');
            } catch (e) {
                console.error('Error parsing emails:', e);
                emails = emailsVal; // Fallback
            }
        }

        if (!emails) { showError('Please enter at least one email address'); return; }
        if (!subject) { showError('Please enter email subject'); return; }
        if (!body) { showError('Please enter email message'); return; }

        submitBtn.find('.indicator-label').hide();
        submitBtn.find('.indicator-progress').show();
        submitBtn.prop('disabled', true);

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_send_mail_admin',
                nonce: xenhireAjax.nonce,
                emails: emails,
                subject: subject,
                body: body
            },
            success: function (response) {
                if (response.success) {
                    var message = response.data && response.data.message ? response.data.message : 'Invitation emails sent successfully';
                    showSuccess(message);
                    // Clear Tagify
                    var input = document.querySelector("#InviteCCEmails");
                    if (input && input.tagify) {
                        input.tagify.removeAllTags();
                    } else {
                        $('#InviteCCEmails').val('');
                    }
                    setTimeout(function () { closeInviteModal(); }, 2000);
                } else {
                    showError(response.data?.message || 'Failed to send emails');
                }
            },
            error: function () {
                showOops();
            },
            complete: function () {
                submitBtn.find('.indicator-label').show();
                submitBtn.find('.indicator-progress').hide();
                submitBtn.prop('disabled', false);
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    function showSuccess(message) {
        toastr.success(message, '', {
            timeOut: 2000,
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center"
        });
    }

    function showError(message) {
        toastr.error(message, 'Error', {
            timeOut: 5000,
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center"
        });
    }

    function showOops() {
        toastr.error('An unexpected error occurred', 'Oops!', {
            timeOut: 5000,
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center"
        });
    }

    function showSubscription(message) {
        toastr.info(message || 'Please upgrade your plan', 'Upgrade Required', {
            timeOut: 5000,
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-center"
        });
    }

    // window.bindDataTableGlobal = bindDataTable; // Removed global assignment

});