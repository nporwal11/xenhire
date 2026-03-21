jQuery(document).ready(function ($) {

    // Fix for Litespeed Cache: Check cookies client-side
    checkClientSideLogin();

    function checkClientSideLogin() {
        var loggedInCookie = getCookie('xenhire_candidate_logged_in');
        var emailCookie = getCookie('xenhire_candidate_email');

        // If cookies exist but JS says not logged in (likely cached page)
        if (loggedInCookie === 'true' && emailCookie) {

            // Decode email (in case of URL encoding)
            var email = decodeURIComponent(emailCookie);

            // Update Global State
            xenhireAjax.is_logged_in = '1';
            xenhireAjax.candidate_email = email;

            // Update UI
            var $userArea = $('.xhj-nav-user-area');

            if ($userArea.length > 0) {
                if ($userArea.find('.xhj-user-email').length === 0) {
                    var html = '<span class="xhj-user-email">Welcome, <em>' + escapeHtml(email) + '</em></span>' +
                        '<button class="xhj-btn-logout">Logout</button>';
                    $userArea.html(html);
                }
            } else {
                var $navContainer = $('#xhj-navbar-actions');
                // Only inject if not already present
                if ($navContainer.find('.xhj-user-email').length === 0) {
                    // Preserve Brand
                    var brandHtml = $navContainer.find('.xhj-nav-brand').prop('outerHTML') || '';

                    // Basic styling match
                    var html = '<div class="xhj-nav-actions">' +
                        brandHtml +
                        '<div class="xhj-nav-user-area">' +
                        '<span class="xhj-user-email">Welcome, <em>' + escapeHtml(email) + '</em></span>' +
                        '<button class="xhj-btn-logout">Logout</button>' +
                        '</div>' +
                        '</div>';
                    $navContainer.html(html);
                }
            }
        }
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) return match[2];
    }

    function escapeHtml(text) {
        if (!text) return text;
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }



    // --- Redirect Login Logic ---
    $(document).on('click', '.xhj-btn-login', function (e) {
        e.preventDefault();
        var urlParams = new URLSearchParams(window.location.search);
        var jobId = urlParams.get('job_id');
        var loginUrl = (typeof xenhireAjax !== 'undefined') ? xenhireAjax.login_url : '/candidate-login/';

        if (jobId) {
            loginUrl += '?job_id=' + jobId;
        }
        window.location.href = loginUrl;
    });

    // Search Logic
    $('#xhj-search-btn').click(function () {
        filterJobsClientSide();
    });

    $('#xhj-search-input').on('keyup', function (e) {
        filterJobsClientSide();
    });

    function filterJobsClientSide() {
        var value = $('#xhj-search-input').val().toLowerCase();
        $(".xhj-job-card").filter(function () {
            $(this).toggle($(this).find('.xhj-card-title').text().toLowerCase().indexOf(value) > -1)
        });
    }

    var $listView = $('#xhj-list-view');
    var $detailsView = $('#xhj-details-view');
    var $loadingDetails = $('.xhj-loading-details');
    var $detailsContent = $('.xhj-details-content');

    // Pre-fill Candidate Email
    if (xenhireAjax.candidate_email) {
        $('#xhj-email').val(xenhireAjax.candidate_email);
    }

    // Accordion Logic
    $(document).on('click', '.xhj-accordion-header', function () {
        $(this).toggleClass('active');
        $(this).next('.xhj-accordion-body').slideToggle(200);
    });

    // Apply Button Auth Check
    $(document).on('click', '.xhj-btn-apply', function (e) {
        e.preventDefault(); // Always prevent default link behavior

        if (!xenhireAjax.is_logged_in) {
            if (xenhireAjax.login_url) {
                var urlParams = new URLSearchParams(window.location.search);
                var jobId = urlParams.get('job_id');
                var loginUrl = xenhireAjax.login_url;
                var separator = loginUrl.indexOf('?') !== -1 ? '&' : '?';

                if (jobId) {
                    window.location.href = loginUrl + separator + 'job_id=' + jobId;
                } else {
                    var currentUrl = window.location.href;
                    window.location.href = loginUrl + separator + 'redirect_to=' + encodeURIComponent(currentUrl);
                }
            } else {
                alert('Candidate Login Coming Soon');
            }
        } else {
            // User is logged in
            // Switch to Application View
            var jobTitle = $('#xhj-detail-title').text();
            var urlParams = new URLSearchParams(window.location.search);
            var jobId = urlParams.get('job_id');

            // Update URL
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?job_id=' + jobId + '&action=apply';
            window.history.pushState({ path: newUrl }, '', newUrl);

            $('#xhj-app-job-title').text(jobTitle);
            $('#xhj-app-job-id').val(jobId);

            $('#xhj-details-view').hide();
            $('#xhj-application-view').show();
            window.scrollTo(0, 0);

            // 1. Ensure Job Details are loaded (to render Extra Fields)
            ensureJobDetails(jobId, function (jobData) {
                renderExtraFields(jobData);

                // 2. Fetch candidate data to pre-fill
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_public_get_candidate_profile',
                        nonce: xenhireAjax.nonce,
                        job_id: jobId
                    },
                    success: function (res) {
                        if (res.success && res.data) {
                            var data = res.data;
                            // Unwrap Personal if present (it contains FirstName, LastName etc)
                            if (data.Personal) {
                                // Shallow merge Personal with the root data to include Education/Employment arrays
                                var flatData = Object.assign({}, data.Personal);
                                if (data.Employment) flatData.Employment = data.Employment;
                                if (data.Education) flatData.Education = data.Education;
                                if (data.Settings) flatData.Settings = data.Settings;

                                populateCandidateForm(flatData);
                            } else {
                                populateCandidateForm(data);
                            }
                        }
                    }
                });
            });
        }
    });

    $(document).on('click', '.xhj-card-header-toggle', function () {
        $(this).toggleClass('active');
        $(this).next('.xhj-form-body').slideToggle(200);
    });

    // Logout Logic
    $(document).on('click', '.xhj-btn-logout', function () {
        document.cookie = "xenhire_candidate_logged_in=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "xenhire_candidate_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        window.location.reload();
    });

    // Share Button Logic
    $(document).on('click', '.xhj-btn-share', function () {
        var shareData = {
            title: $('#xhj-detail-title').text(),
            text: 'Check out this job opportunity!',
            url: window.location.href
        };

        if (navigator.share) {
            navigator.share(shareData).catch(console.error);
        } else {
            // Fallback to clipboard copy
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(window.location.href).select();
            document.execCommand('copy');
            tempInput.remove();

            // Show tooltip or alert
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!');
            setTimeout(function () {
                $btn.html(originalHtml);
            }, 2000);
        }
    });

    // --- File Upload Logic ---

    // Resume Upload
    $('#xhj-resume-upload').change(function (e) {
        var file = e.target.files[0];
        handleResumeFile(file);
    });

    // Drag and Drop for Resume
    var $uploadArea = $('.xhj-upload-area');
    $uploadArea.on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#e0e7ff');
    });
    $uploadArea.on('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#eef2ff');
    });
    $uploadArea.on('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#eef2ff');
        var file = e.originalEvent.dataTransfer.files[0];
        handleResumeFile(file);
    });

    function handleResumeFile(file) {
        if (file) {
            // Validate file type and size
            var validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (validTypes.indexOf(file.type) === -1) {
                alert('Invalid file type. Please upload PDF, DOC, or DOCX.');
                return;
            }
            if (file.size > 5 * 1024 * 1024) { // Increased to 5MB
                alert('File size exceeds 5MB.');
                return;
            }

            $('.xhj-upload-placeholder').hide();
            $('.xhj-file-preview').css('display', 'inline-flex');
            $('.xhj-file-name').text(file.name);
            $('.xhj-file-size').text('(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');

            // Parse Resume
            parseResume(file);
        }
    }

    function parseResume(file) {
        var formData = new FormData();
        formData.append('action', 'xenhire_parse_resume');
        formData.append('nonce', xenhireAjax.nonce);
        formData.append('resume', file);

        // Pass Job ID
        var jobId = $('#xhj-app-job-id').val();
        if (jobId) {
            formData.append('job_id', jobId);
        }

        // UI State: Parsing
        $('.xhj-skip-container').hide();
        $('#xhj-parsing-loader').fadeIn();
        $('#xhj-application-form-container').hide(); // Ensure form is hidden

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                //console.log('Parse Resume Response:', res);
                $('#xhj-parsing-loader').hide();
                $('#xhj-application-form-container').fadeIn();

                if (res.success && res.data) {
                    var data = res.data;

                    // Store ResumeURL if available
                    if (data.ResumeURL) {
                        window.xenhireResumePath = data.ResumeURL;
                    }

                    // Check for nested Data object and parse if it's a string
                    if (data.Data) {
                        data = data.Data;
                    } else if (data.data) {
                        data = data.data;
                    }

                    if (typeof data === 'string') {
                        try {
                            data = JSON.parse(data);
                        } catch (e) {
                            console.error('Failed to parse inner JSON data', e);
                            return;
                        }
                    }


                    // Use shared population function
                    populateCandidateForm(data);

                } else {
                    console.error('Parsing returned error:', res);
                    alert('Resume parsing failed: ' + (res.data && res.data.message ? res.data.message : 'Unknown error'));
                }
            },
            error: function (err) {
                console.error('Parsing failed', err);
                $('#xhj-parsing-loader').hide();
                $('#xhj-application-form-container').fadeIn(); // Show form anyway on error
                alert('Resume parsing failed. Please fill the form manually.');
            }
        });
    }

    // Store Job Details Globally for Application Form
    if (typeof xenhireJobDetails !== 'undefined') {
        // Already have it?
    }

    // Function to fetch Job Details if missing
    function ensureJobDetails(jobId, callback) {
        if (window.xenhireJobDetails && window.xenhireJobDetails.ID == jobId) {
            callback(window.xenhireJobDetails);
            return;
        }

        // Fetch it
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_public_get_job_details',
                job_id: jobId
                // No nonce for public
            },
            success: function (res) {
                if (res.success) {
                    window.xenhireJobDetails = res.data;
                    callback(res.data);
                } else {
                    console.error('Failed to fetch job details');
                    callback(null);
                }
            }
        });
    }

    // Dynamic Extra Fields Renderer
    function renderExtraFields(jobData) {
        var $container = $('#xhj-extra-fields-grid');
        $container.empty();

        if (!jobData) return;

        for (var i = 1; i <= 4; i++) {
            var label = jobData['ExtraData' + i + 'Label'];
            var type = jobData['ExtraData' + i + 'Type'];
            var optionsRaw = jobData['ExtraData' + i + 'Options'] || '';
            var mandatory = jobData['ExtraData' + i + 'Mandatory'];

            // Normalize Mandatory (can be "1", "true", or boolean true)
            var isMandatory = (mandatory == "1" || String(mandatory).toLowerCase() == "true");

            if (!label) continue; // Skip if no label

            var html = '<div class="xhj-form-group xhj-extra-field-wrapper">';

            // Label
            html += '<label>' + label;
            if (isMandatory) {
                html += '<span>*</span>';
            }
            html += '</label>';

            // Input based on Type
            // Type 1: Short Text
            if (type == "1") {
                html += '<input type="text" class="xhj-input xhj-extra-field" ' +
                    'data-id="' + i + '" data-label="' + label + '" data-mandatory="' + (isMandatory ? '1' : '0') + '" ' +
                    'placeholder="' + label + '">';
            }
            // Type 2: Long Text
            else if (type == "2") {
                html += '<textarea class="xhj-input xhj-extra-field" rows="3" ' +
                    'data-id="' + i + '" data-label="' + label + '" data-mandatory="' + (isMandatory ? '1' : '0') + '" ' +
                    'placeholder="' + label + '"></textarea>';
            }
            // Type 3: Select
            else if (type == "3") {
                html += '<select class="xhj-input xhj-extra-field" ' +
                    'data-id="' + i + '" data-label="' + label + '" data-mandatory="' + (isMandatory ? '1' : '0') + '">';
                html += '<option value="">-- Select --</option>';
                var opts = optionsRaw.split('|');
                opts.forEach(function (opt) {
                    if (opt.trim()) {
                        html += '<option value="' + opt.trim() + '">' + opt.trim() + '</option>';
                    }
                });
                html += '</select>';
            }
            // Type 4: Date
            else if (type == "4") {
                html += '<input type="date" class="xhj-input xhj-extra-field" ' +
                    'data-id="' + i + '" data-label="' + label + '" data-mandatory="' + (isMandatory ? '1' : '0') + '">';
            } else {
                // Fallback to text
                html += '<input type="text" class="xhj-input xhj-extra-field" ' +
                    'data-id="' + i + '" data-label="' + label + '" data-mandatory="' + (isMandatory ? '1' : '0') + '" ' +
                    'placeholder="' + label + '">';
            }

            html += '</div>';
            $container.append(html);
        }
    }

    // Shared Data Population Helper (Updated to fill Extra Fields)
    function populateCandidateForm(data) {
        // Auto-fill fields
        if (data.FirstName) $('#xhj-first-name').val(data.FirstName);
        if (data.LastName) $('#xhj-last-name').val(data.LastName);

        // Only set email if empty
        if (data.Email && !$('#xhj-email').val()) $('#xhj-email').val(data.Email);

        if (data.Mobile) $('#xhj-mobile').val(data.Mobile);

        // Fix for undefined string
        if (data.AltMobile && String(data.AltMobile).toLowerCase() !== 'undefined') $('#xhj-alt-mobile').val(data.AltMobile);
        else $('#xhj-alt-mobile').val('');

        if (data.CurrentCity) $('#xhj-city').val(data.CurrentCity);

        if (data.PreferredCity && String(data.PreferredCity).toLowerCase() !== 'undefined') $('#xhj-pref-city').val(data.PreferredCity);
        else $('#xhj-pref-city').val('');

        if (data.Industry) $('#xhj-industry').val(data.Industry);
        if (data.Keywords) $('#xhj-skills').val(data.Keywords);
        if (data.LinkedInURL) $('#xhj-linkedin').val(data.LinkedInURL);
        if (data.CurrentSalary) $('#xhj-salary').val(data.CurrentSalary);

        // Resume File (if from server)
        if (data.ResumeFILE) {
            // Show preview if URL exists
            window.xenhireResumePath = data.ResumeFILE;
            $('.xhj-upload-placeholder').hide();
            $('.xhj-file-preview').css('display', 'inline-flex');
            $('.xhj-file-name').text('Resume from Profile');
            $('.xhj-file-size').text('');
        } else if (data.ResumeURL) {
            window.xenhireResumePath = data.ResumeURL;
        }

        // DOB
        if (data.DOB) $('#xhj-dob').val(formatDateForInput(data.DOB));

        // Gender
        var genderVal = '';
        if (data.GenderID !== undefined && data.GenderID != 0 && data.GenderID != -1) {
            if (data.GenderID == 1) genderVal = '1';
            else if (data.GenderID == 2) genderVal = '2';
        }

        if (!genderVal && data.Gender) {
            var g = String(data.Gender).toLowerCase();
            if (g === 'male' || g === 'm' || g === '2') genderVal = '2';
            else if (g === 'female' || g === 'f' || g === '1') genderVal = '1';
        }

        if (genderVal) $('#xhj-gender').val(genderVal);

        // Employment
        if (data.Employment && Array.isArray(data.Employment)) {
            $('#xhj-employment-container').empty();
            data.Employment.forEach(function (emp) {
                addEmploymentRow(emp);
            });
            if (data.Employment.length > 0) {
                $('.xhj-form-card:has(#xhj-employment-container) .xhj-card-header-toggle').addClass('active');
                $('.xhj-form-card:has(#xhj-employment-container) .xhj-form-body').show();
            }
        }

        // Education
        if (data.Education && Array.isArray(data.Education)) {
            $('#xhj-education-container').empty();
            data.Education.forEach(function (edu) {
                addEducationRow(edu);
            });
            if (data.Education.length > 0) {
                $('.xhj-form-card:has(#xhj-education-container) .xhj-card-header-toggle').addClass('active');
                $('.xhj-form-card:has(#xhj-education-container) .xhj-form-body').show();
            }
        }


        // Extra Data (Settings) if provided
        $('.xhj-extra-field').each(function () {
            var id = $(this).data('id'); // e.g. 1
            var keyVal = 'ExtraData' + id + 'Val';
            var keyRaw = 'ExtraData' + id;

            // Check flat data (common in Get_Candidate)
            if (data[keyVal] !== undefined) $(this).val(data[keyVal]);
            else if (data[keyRaw] !== undefined) $(this).val(data[keyRaw]);

            // Check nested Settings object
            else if (data.Settings) {
                if (data.Settings[keyVal] !== undefined) $(this).val(data.Settings[keyVal]);
                else if (data.Settings[keyRaw] !== undefined) $(this).val(data.Settings[keyRaw]);
            }
        });
    }

    // --- Server-Side Data Injection Check ---
    if (window.xenhireServerCandidateData) {
        console.log('XenHire: Found Server Candidate Data', window.xenhireServerCandidateData);

        // We assume we are already on the application view or need to switch to it
        // Since this data is loaded, we can populate immediately

        // Personal
        if (window.xenhireServerCandidateData.Personal) {
            populateCandidateForm(window.xenhireServerCandidateData.Personal);
        }

        // Merge separate arrays if structure differs from helper expectation
        // The helper expects {Employment: [], Education: []} inside the main object, 
        // but our server object has them as siblings: {Personal: {}, Employment: [], Education: []}

        if (window.xenhireServerCandidateData.Employment) {
            populateCandidateForm({ Employment: window.xenhireServerCandidateData.Employment });
        }
        if (window.xenhireServerCandidateData.Education) {
            populateCandidateForm({ Education: window.xenhireServerCandidateData.Education });
        }
    }


    // Skip Resume Logic
    $('#xhj-skip-resume').click(function () {
        $('#xhj-upload-section').slideUp();
        $('#xhj-application-form-container').fadeIn();
    });

    $('.xhj-btn-remove-file').click(function (e) {
        e.preventDefault();
        $('#xhj-resume-upload').val('');
        $('.xhj-file-preview').hide();
        $('.xhj-upload-placeholder').show();
    });

    // Profile Photo Upload
    // Profile Photo Upload (New Logic)
    $('#xhj-photo-upload').change(function (e) {
        var file = e.target.files[0];
        if (file) {
            if (file.type.indexOf('image/') === -1) {
                alert('Invalid file type. Please upload an image.');
                return;
            }
            if (file.size > 1 * 1024 * 1024) {
                alert('File size exceeds 1MB.');
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $('#xhj_photo_preview').css('background-image', 'url(' + e.target.result + ')');
                $('#xhj_photo_area').addClass('has-image');
            }
            reader.readAsDataURL(file);
        }
    });

    // Remove Photo
    $('#xhj-remove-photo').click(function () {
        $('#xhj-photo-upload').val('');
        $('#xhj_photo_preview').css('background-image', 'none');
        $('#xhj_photo_area').removeClass('has-image');
    });

    // --- Dynamic Fields Logic ---

    // --- Dynamic Fields Logic ---

    // Add Employment
    $('#xhj-add-employment').click(function () {
        addEmploymentRow();
    });

    function addEmploymentRow(data = {}) {
        var html = `
                    <div class="xhj-dynamic-item" style="border-bottom: 1px dashed #e5e7eb; padding-bottom: 20px; margin-bottom: 20px;">
                        <div class="xhj-form-grid">
                            <div class="xhj-form-group">
                                <label>Company Name</label>
                                <input type="text" class="xhj-input emp-company" placeholder="Company Name" value="${data.Employer || data.Company || ''}">
                            </div>
                            <div class="xhj-form-group">
                                <label>Designation</label>
                                <input type="text" class="xhj-input emp-designation" placeholder="Designation" value="${data.Designation || data.JobTitle || ''}">
                            </div>
                            <div class="xhj-form-group">
                                <label>Start Date</label>
                                <input type="date" class="xhj-input emp-start" value="${formatDateFromStamp(data.StartDatestamp)}">
                            </div>
                            <div class="xhj-form-group">
                                <label>End Date</label>
                                <input type="date" class="xhj-input emp-end" value="${formatDateFromStamp(data.EndDatestamp)}">
                            </div>
                            <div class="xhj-form-group full-width">
                                <label>Description</label>
                                <textarea class="xhj-input emp-description" rows="3" placeholder="Job Description">${data.Description || ''}</textarea>
                            </div>
                        </div>
                        <button class="xhj-btn-remove-item" style="font-size:12.35px; line-height:1.5; margin-top:10px; color:#f8285a; background:none; border:none; cursor:pointer;">Delete</button>
                    </div>
                `;
        $('#xhj-employment-container').append(html);
    }

    // Add Education
    $('#xhj-add-education').click(function () {
        addEducationRow();
    });

    function addEducationRow(data = {}) {
        var html = `
                    <div class="xhj-dynamic-item" style="border-bottom: 1px dashed #e5e7eb; padding-bottom: 20px; margin-bottom: 20px;">
                        <div class="xhj-form-grid">
                            <div class="xhj-form-group">
                                <label>Institute/University</label>
                                <input type="text" class="xhj-input edu-institute" placeholder="Institute Name" value="${data.Institute || data.College || data.University || ''}">
                            </div>
                            <div class="xhj-form-group">
                                <label>Degree</label>
                                <input type="text" class="xhj-input edu-degree" placeholder="Degree" value="${data.Qualification || data.Degree || ''}">
                            </div>
                            <div class="xhj-form-group">
                                <label>Admission Date</label>
                                <input type="date" class="xhj-input edu-start-year" value="${formatDateFromStamp(data.AdmissionDatestamp)}">
                            </div>
                            <div class="xhj-form-group">
                                <label>Passing Date</label>
                                <input type="date" class="xhj-input edu-year" value="${formatDateFromStamp(data.PassingDatestamp)}">
                            </div>
                        </div>
                        <button class="xhj-btn-remove-item" style="font-size:12px; line-height:1.5; margin-top:10px; color:red; background:none; border:none; cursor:pointer;">Delete</button>
                    </div>
                `;
        $('#xhj-education-container').append(html);
    }

    function formatDateFromStamp(datestamp) {
        // Format YYYYMMDD to YYYY-MM-DD
        if (!datestamp || datestamp.length !== 8) return '';
        var y = datestamp.substring(0, 4);
        var m = datestamp.substring(4, 6);
        var d = datestamp.substring(6, 8);
        return y + '-' + m + '-' + d;
    }

    function formatDateForInput(dateString) {
        if (!dateString) return '';
        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) return ''; // Invalid date
            return date.toISOString().split('T')[0];
        } catch (e) {
            return '';
        }
    }

    // Remove Dynamic Item
    $(document).on('click', '.xhj-btn-remove-item', function () {
        $(this).closest('.xhj-dynamic-item').remove();
    });

    // --- Form Submission ---
    $('#xhj-btn-submit-app').click(function () {
        var $btn = $(this);
        $btn.text('Submitting...').prop('disabled', true);

        // Collect Data
        var formData = new FormData();
        formData.append('action', 'xenhire_public_submit_application');
        formData.append('nonce', xenhireAjax.nonce);
        formData.append('job_id', $('#xhj-app-job-id').val());

        // Basic Fields
        formData.append('first_name', $('#xhj-first-name').val());
        formData.append('last_name', $('#xhj-last-name').val());
        formData.append('mobile', $('#xhj-mobile').val());
        formData.append('alt_mobile', $('#xhj-alt-mobile').val());
        formData.append('dob', $('#xhj-dob').val());
        formData.append('email', $('#xhj-email').val());
        formData.append('gender', $('#xhj-gender').val());
        formData.append('salary', $('#xhj-salary').val());
        formData.append('city', $('#xhj-city').val());
        formData.append('pref_city', $('#xhj-pref-city').val());
        formData.append('industry', $('#xhj-industry').val());
        formData.append('linkedin', $('#xhj-linkedin').val());
        formData.append('keywords', $('#xhj-skills').val());

        // Extra Data
        $('.xhj-extra-field').each(function () {
            var id = $(this).data('id');
            var val = $(this).val();
            var label = $(this).data('label');
            var mandatory = $(this).data('mandatory');

            if (mandatory && !val) {
                alert(label + ' is required.');
                $btn.text('Submit & Proceed to Interview').prop('disabled', false);
                throw new Error('Validation Failed'); // Break execution
            }

            formData.append('extra_data_' + id + '_val', val);
            formData.append('extra_data_' + id + '_label', label);
        });

        // Resume File Path (from parsing response, stored in hidden field or variable)
        var resumePath = window.xenhireResumePath || '';
        formData.append('resume_file', resumePath);

        // Files
        var resumeFile = $('#xhj-resume-upload')[0].files[0];
        if (resumeFile) formData.append('resume', resumeFile);

        var photoFile = $('#xhj-photo-upload')[0].files[0];
        if (photoFile) formData.append('photo', photoFile);

        // Employment Data
        var employment = [];
        $('#xhj-employment-container .xhj-dynamic-item').each(function () {
            employment.push({
                company: $(this).find('.emp-company').val(),
                designation: $(this).find('.emp-designation').val(),
                start: $(this).find('.emp-start').val(),
                end: $(this).find('.emp-end').val(),
                description: $(this).find('.emp-description').val()
            });
        });
        formData.append('employment', JSON.stringify(employment));

        // Education Data
        var education = [];
        $('#xhj-education-container .xhj-dynamic-item').each(function () {
            education.push({
                institute: $(this).find('.edu-institute').val(),
                degree: $(this).find('.edu-degree').val(),
                year: $(this).find('.edu-year').val(),
                start_year: $(this).find('.edu-start-year').val()
            });
        });
        formData.append('education', JSON.stringify(education));

        // AJAX Submit
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    var appId = res.data.id;
                    var jobId = $('#xhj-app-job-id').val();

                    if (res.data.candidate_id) {
                        document.cookie = "xenhire_candidate_id=" + res.data.candidate_id + "; path=/; max-age=86400"; // 1 day
                    }

                    // Set Email Cookie (using the email from the form)
                    var candidateEmail = $('#xhj-email').val();
                    if (candidateEmail) {
                        document.cookie = "xenhire_candidate_email=" + candidateEmail + "; path=/; max-age=86400"; // 1 day
                    }

                    // Set OTP Cookie if available
                    if (res.data.otp_code) {
                        document.cookie = "xenhire_candidate_otp=" + res.data.otp_code + "; path=/; max-age=86400"; // 1 day
                    }

                    if (res.data.redirect_url) {
                        window.location.href = res.data.redirect_url;
                    } else {
                        // Default redirect format
                        window.location.href = '/before-you-begin/' + jobId + '?jid=' + appId;
                    }
                } else {
                    console.error('Submission Failed:', res);
                    alert('Error: ' + (res.data.message || 'Submission failed'));
                    $btn.text('Submit & Proceed to Interview').prop('disabled', false);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('Server Error. Please try again.');
                $btn.text('Submit & Proceed to Interview').prop('disabled', false);
            }
        });
    });

    // Back Button Logic
    $('#xhj-back-btn').click(function () {
        // Update URL without reload
        var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.pushState({ path: newUrl }, '', newUrl);
        showList();
    });

    // Check URL params
    var urlParams = new URLSearchParams(window.location.search);
    var jobId = urlParams.get('job_id');
    var action = urlParams.get('action');

    if (jobId) {
        if (action === 'apply' && xenhireAjax.is_logged_in) {
            showDetails(jobId, true);
        } else {
            showDetails(jobId, false);
        }
    } else {
        showList();
    }

    function showList() {
        $detailsView.hide();
        $listView.show();
        $('#xhj-application-view').hide(); // Ensure app view is hidden
        if ($('.xhj-jobs-grid').children().length === 0) {
            loadJobs();
        }
    }

    function showDetails(id, autoApply) {
        $listView.hide();
        $detailsView.show();
        $loadingDetails.show();
        $detailsContent.hide();
        $('#xhj-application-view').hide();

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_public_get_job_details',
                nonce: xenhireAjax.nonce,
                job_id: id
            },
            success: function (res) {
                $loadingDetails.hide();
                if (res.success && res.data) {
                    renderDetails(res.data);
                    if (autoApply) {
                        // Auto-switch to application view
                        var jobTitle = res.data.JobTitle || 'Job Application';
                        $('#xhj-app-job-title').text(jobTitle);
                        $('#xhj-app-job-id').val(id);

                        $('#xhj-details-view').hide();
                        $('#xhj-application-view').show();
                    }
                } else {
                    $detailsContent.html('<div class="xhj-empty">Job not found.</div>').show();
                }
            },
            error: function () {
                $loadingDetails.hide();
                $detailsContent.html('<div class="xhj-empty">Failed to load details.</div>').show();
            }
        });
    }

    function renderDetails(job) {
        // Sidebar Meta
        var workEx = 'N/A';
        if (job.WorkExMin || job.WorkExMax) {
            workEx = (job.WorkExMin || '0') + ' - ' + (job.WorkExMax || '0') + ' Years';
        }
        $('#xhj-meta-exp').text(workEx);

        var salary = job.SalaryText || 'Negotiable';
        if (job.SalaryFrom && job.SalaryTo && (job.SalaryFrom > 0 || job.SalaryTo > 0)) {
            salary = job.SalaryFrom + ' - ' + job.SalaryTo;
        }
        $('#xhj-meta-salary').text(salary);
        $('#xhj-meta-city').text(job.City || 'Remote');
        $('#xhj-meta-type').text(job.EmploymentType || 'Permanent');
        $('#xhj-detail-badge').text(job.EmploymentType || 'Permanent');

        // console.log('Job Details:', job);

        // Format Date
        // Try to find the most relevant "Posted" date
        var dateStr = job.PostedDate || job.StartDate || job.ActiveDate || job.CreatedDate || new Date().toISOString();
        var date = new Date(dateStr);
        var formattedDate = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        $('#xhj-meta-date').text(formattedDate);

        // Last Date
        var deadlineText = 'N/A';
        if (job.DeadlineDatestamp) {
            var dDate = new Date(job.DeadlineDatestamp);
            if (!isNaN(dDate)) {
                deadlineText = dDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            }
        }
        $('#xhj-meta-deadline').text(deadlineText);

        // Already Applied
        $('#xhj-meta-applied').text((job.JobApplications || '0') + ' Applications');

        // Email
        $('#xhj-meta-email').text(job.EmailMain || 'N/A');

        // Action Button
        if (job.IsActive) {
            $('.xhj-meta-action').html('<a href="javascript:void(0);" class="xhj-btn-apply">Apply Now</a>');
        } else {
            $('.xhj-meta-action').html('<button class="xhj-btn xhj-btn-disabled" disabled>Hiring Closed</button>');
        }

        // Main Header
        $('#xhj-detail-title').text(job.JobTitle);
        $('#xhj-detail-badge').html('<i class="las la-hands-helping"></i> ' + (job.EmploymentType));

        // Deadline Badge
        if (deadlineText !== 'N/A') {
            $('#xhj-detail-deadline').html('<i class="las la-calendar"></i> ' + deadlineText).show();
        } else {
            $('#xhj-detail-deadline').hide();
        }

        // Applications Badge
        if (job.JobApplications) {
            $('#xhj-detail-applied').html('<i class="las la-user-check"></i> ' + job.JobApplications + ' Applicants').show();
        } else {
            $('#xhj-detail-applied').hide();
        }

        // Update Navbar Brand
        var brandName = job.CompanyName || job.BrandName || job.EmployerName;
        var brandLogo = job.BrandLogo || job.EmployerLogo || job.Logo || xenhireAjax.brand_logo;

        if (brandLogo) {
            var altText = brandName || xenhireAjax.brand_name || 'Brand Logo';
            $('.xhj-nav-brand').html('<img src="' + brandLogo + '" alt="' + altText + '" style="max-width:180px;max-height: 40px;">');
        } else if (brandName) {
            // Only overwrite if we don't already have an image (safety check)
            if ($('.xhj-nav-brand img').length === 0) {
                $('.xhj-nav-brand').text(brandName);
            }
        }

        // Accordion Content
        var defaultAbout = (brandName || 'Our Company') + ' is a forward-thinking leader in the industry...';
        var aboutCompany = job.AboutCompany || defaultAbout;
        if (job.Employer) {
            // Try to use real data if available, else placeholder
            // aboutCompany = job.EmployerDescription || aboutCompany;
        }
        $('#xhj-detail-company').html(aboutCompany);

        $('#xhj-detail-desc').html(job.JobDescription || '<p>No description available.</p>');
        $('#xhj-detail-roles').html(job.JobRole || '<p>No roles specified.</p>');

        var skills = job.Keywords || job.MandatorySkills || '';
        if (job.OptionalSkills && skills.indexOf(job.OptionalSkills) === -1) {
            skills += (skills ? ', ' : '') + job.OptionalSkills;
        }
        $('#xhj-detail-skills').html(skills || 'No specific skills listed.');

        $detailsContent.show();

        // Render Extra Fields
        renderExtraFields(job);
    }

    function renderExtraFields(job) {
        // var $container = $('#xhj-extra-fields-container'); // Removed container
        var $grid = $('#xhj-extra-fields-grid');
        $grid.empty();

        var hasExtra = false;

        for (var i = 1; i <= 4; i++) {
            var label = job['ExtraData' + i + 'Label'];
            var type = job['ExtraData' + i + 'Type']; // 1=Short, 2=Long, 3=Select, 4=Date
            var options = job['ExtraData' + i + 'Options'];
            var isMandatory = job['ExtraData' + i + 'Mandatory'];

            if (label) {
                hasExtra = true;
                var fieldHtml = '';
                var reqStar = isMandatory ? '<span>*</span>' : '';
                var fieldId = 'xhj-extra-' + i;

                fieldHtml += '<div class="xhj-form-group">';
                fieldHtml += '<label>' + label + reqStar + '</label>';

                if (type == '1') { // Short Text
                    fieldHtml += '<input type="text" class="xhj-input xhj-extra-field" id="' + fieldId + '" data-id="' + i + '" placeholder="' + label + '">';
                } else if (type == '2') { // Long Text
                    fieldHtml += '<textarea class="xhj-input xhj-extra-field" id="' + fieldId + '" data-id="' + i + '" rows="3" placeholder="' + label + '"></textarea>';
                } else if (type == '3') { // Select
                    fieldHtml += '<select class="xhj-input xhj-extra-field" id="' + fieldId + '" data-id="' + i + '">';
                    fieldHtml += '<option value="">Select ' + label + '</option>';
                    if (options) {
                        var opts = [];
                        try {
                            // Try parsing as JSON (Tagify format)
                            var jsonOpts = JSON.parse(options);
                            if (Array.isArray(jsonOpts)) {
                                opts = jsonOpts.map(function (o) { return o.value; });
                            } else {
                                opts = [options];
                            }
                        } catch (e) {
                            // Not JSON, fall back to pipe or comma separation
                            if (options.indexOf('|') > -1) {
                                opts = options.split('|');
                            } else if (options.indexOf(',') > -1) {
                                opts = options.split(',');
                            } else {
                                opts = [options];
                            }
                        }

                        opts.forEach(function (opt) {
                            opt = opt.trim();
                            if (opt) fieldHtml += '<option value="' + opt + '">' + opt + '</option>';
                        });
                    }
                    fieldHtml += '</select>';
                } else if (type == '4') { // Date
                    fieldHtml += '<input type="date" class="xhj-input xhj-extra-field" id="' + fieldId + '" data-id="' + i + '">';
                } else {
                    // Default to text
                    fieldHtml += '<input type="text" class="xhj-input xhj-extra-field" id="' + fieldId + '" data-id="' + i + '" placeholder="' + label + '">';
                }

                // Store label and mandatory status in data attributes for validation
                fieldHtml = $(fieldHtml).find('.xhj-extra-field').attr('data-label', label).attr('data-mandatory', isMandatory).end().prop('outerHTML');

                fieldHtml += '</div>';
                $grid.append(fieldHtml);
            }
        }
    }

    function loadJobs() {
        $('.xhj-loading').show();
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_public_list_jobs',
                nonce: xenhireAjax.nonce,
                IsActive: '1',
                EmployerID: '-1',
                Search: '',
                PageNo: '1',
                PageSize: '50',
                CandidateID: (function () {
                    var match = document.cookie.match(new RegExp('(^| )' + 'xenhire_candidate_id' + '=([^;]+)'));
                    return match ? match[2] : 0;
                })(),
                APIKey: xenhireAjax.api_key || ''
            },
            success: function (res) {
                $('.xhj-loading').hide();
                if (!res.success || !res.data || !res.data.jobs || res.data.jobs.length === 0) {
                    $('.xhj-empty').show();
                    return;
                }

                var jobs = res.data.jobs;
                var gridHtml = '';

                jobs.forEach(function (job) {
                    var previewUrl = '?job_id=' + (job.ID || job.JobId);

                    // Work Experience
                    var workEx = 'Fresher';
                    if (job.WorkExMin || job.WorkExMax) {
                        workEx = (job.WorkExMin || '0') + ' to ' + (job.WorkExMax || '0') + ' years';
                    } else if (job.WorkEx) {
                        workEx = job.WorkEx;
                    }


                    // Salary
                    var salary = job.SalaryText || 'Negotiable';
                    if (job.SalaryFrom && job.SalaryTo && (job.SalaryFrom > 0 || job.SalaryTo > 0)) {
                        salary = '₹ ' + job.SalaryFrom.toLocaleString() + ' - ' + job.SalaryTo.toLocaleString() + ' monthly';
                    } else if (job.Salary) {
                        salary = job.Salary;
                    }

                    // Dates Logic
                    var datesHtml = '';

                    // Last Date
                    if (job.DeadlineDate) {
                        var d = new Date(job.DeadlineDate);
                        if (!isNaN(d)) {
                            var dateStr = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                            datesHtml += '<div class="xhj-card-date">Last Date<span>' + dateStr + '</span></div>';
                        }
                    }

                    var headerRight = '';
                    if (datesHtml) {
                        headerRight = '<div style="display:flex; flex-direction:column; align-items:flex-end;">' + datesHtml + '</div>';
                    }

                    gridHtml += '<div class="xhj-job-card">' +
                        '<div class="xhj-card-header">' +
                        '<h3 class="xhj-card-title"><a href="' + previewUrl + '">' + (job.JobTitle || 'Untitled') + '</a></h3>' +
                        headerRight +
                        '</div>' +

                        '<div class="xhj-card-meta-grid">' +
                        '<div class="xhj-card-meta-item">' +
                        '<span class="xhj-card-meta-label">Experience</span>' +
                        '<span class="xhj-card-meta-value">' + workEx + '</span>' +
                        '</div>' +
                        '<div class="xhj-card-meta-item">' +
                        '<span class="xhj-card-meta-label">Location</span>' +
                        '<span class="xhj-card-meta-value">' + (job.City || 'Remote') + '</span>' +
                        '</div>' +
                        '<div class="xhj-card-meta-item">' +
                        '<span class="xhj-card-meta-label">Employment</span>' +
                        '<span class="xhj-card-meta-value">' + (job.EmploymentType || 'Permanent') + '</span>' +
                        '</div>' +
                        '<div class="xhj-card-meta-item">' +
                        '<span class="xhj-card-meta-label">Salary</span>' +
                        '<span class="xhj-card-meta-value">' + salary + '</span>' +
                        '</div>' +
                        '</div>' +

                        '<div class="xhj-card-footer">' +
                        '<a href="' + previewUrl + '" class="xhj-btn-view-details">Apply Now</a>' +
                        (job.JobApplications > 0 ?
                            '<div class="xhj-appled">' + job.JobApplications + ' already applied</div>'
                            : '') +
                        '</div>' +
                        '</div>';
                });

                $('.xhj-jobs-grid').html(gridHtml).show();
            },
            error: function () {
                $('.xhj-loading').hide();
                $('.xhj-empty').show();
            }
        });
    }

});