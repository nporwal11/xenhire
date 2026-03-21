jQuery(document).ready(function ($) {

    // Toastr Helper Functions (Copied from Jobs Page)
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

    // Get Application ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const appId = urlParams.get('id');

    let currentStageId = null;
    let stagesLoaded = false;
    let InterviewData = [];
    let AllStages = []; // Store all stages

    let IsMockInterview = false; // Default to false
    let currentFeedbackId = 0; // Store feedback ID for updates

    // Load Stages first
    loadStages();

    if (appId) {
        loadApplicationDetails(appId);
        loadFeedback(appId);
    } else {
        console.error('No Application ID found in URL');
    }

    function loadStages() {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_cbo_items',
                nonce: xenhireAjax.nonce,
                key: 'Stage'
            },
            success: function (res) {
                if (res.success && res.data) {
                    let items = res.data;
                    let options = '';

                    // Handle if data is a JSON string
                    if (typeof items === 'string') {
                        try {
                            items = JSON.parse(items);
                        } catch (e) {
                            console.error('Failed to parse Stage data string', e);
                            items = [];
                        }
                    }

                    // Handle wrapped arrays (e.g. { Options: [...] } or { Items: [...] })
                    if (!Array.isArray(items)) {
                        if (items.Options && Array.isArray(items.Options)) {
                            items = items.Options;
                        } else if (items.Items && Array.isArray(items.Items)) {
                            items = items.Items;
                        } else {
                            // Fallback: try to find any array property
                            var keys = Object.keys(items);
                            for (var i = 0; i < keys.length; i++) {
                                if (Array.isArray(items[keys[i]])) {
                                    items = items[keys[i]];
                                    break;
                                }
                            }
                        }
                    }

                    if (Array.isArray(items)) {
                        AllStages = items; // Store for later use
                        items.forEach(function (item) {
                            var value, text;
                            if (typeof item === 'string') {
                                value = item;
                                text = item;
                            } else if (typeof item === 'object') {
                                value = item.Value || item.Key || item.ID || item.id;
                                text = item.DisplayText || item.Text || item.Name || item.Value || item.description;
                            }
                            if (value && text) {
                                options += `<option value="${value}">${text}</option>`;
                            }
                        });
                    }

                    $('.xh-cd-stage-select').html(options);
                    stagesLoaded = true;

                    // If application details already loaded, set the value
                    if (currentStageId) {
                        $('.xh-cd-stage-select').val(currentStageId);
                    }
                } else {
                    console.error('Failed to load stages');
                    $('.xh-cd-stage-select').html('<option>Error loading stages</option>');
                }
            },
            error: function () {
                console.error('Server error loading stages');
                $('.xh-cd-stage-select').html('<option>Error loading stages</option>');
            }
        });
    }

    function loadApplicationDetails(id) {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_job_application_details',
                nonce: xenhireAjax.nonce,
                app_id: id
            },
            success: function (res) {
                if (res.success && res.data) {
                    // Handle new response structure
                    if (res.data.details) {
                        InterviewData = res.data.interview_data || [];
                        renderApplicationDetails(res.data.details);
                        renderExperience(res.data.experience || []);
                        renderEducation(res.data.education || []);
                        renderExtraInfo(res.data.details);
                        renderMatchScore(res.data.details); // Pass details which contains Match* fields

                        // Initialize Interview UI if data exists
                        if (InterviewData.length > 0) {
                            initInterviewUI();
                        } else {
                            $('#xh-interview-not-attempted').show();
                            $('#xh-interview-section').hide();
                        }
                    } else {
                        // Fallback
                        renderApplicationDetails(res.data);
                    }
                } else {
                    console.error(res.data.message || 'Failed to load application details');
                }
            },
            error: function () {
                console.error('Server error loading application details');
            }
        });
    }

    function loadFeedback(id) {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_feedback',
                nonce: xenhireAjax.nonce,
                app_id: id
            },
            success: function (res) {
                if (res.success && res.data) {
                    const data = res.data;
                    const ownerFeedback = data.owner;
                    const otherFeedbacks = data.others;

                    // 1. Populate Owner Feedback (My Feedback)
                    if (ownerFeedback) {
                        if (ownerFeedback.ID) {
                            currentFeedbackId = ownerFeedback.ID;
                        }
                        if (ownerFeedback.Remarks) {
                            $('.xh-cd-textarea').val(ownerFeedback.Remarks);
                        }
                        if (ownerFeedback.RatingID) {
                            const rating = parseInt(ownerFeedback.RatingID);
                            const $stars = $('.xh-cd-feedback-row .xh-cd-stars i');
                            $stars.removeClass('ki-duotone ki-star').addClass('ki-outline ki-star');
                            $stars.each(function (i) {
                                if (i < rating) {
                                    $(this).removeClass('ki-outline ki-star').addClass('ki-duotone ki-star');
                                }
                            });
                        }
                    }

                    // 2. Populate Other Feedbacks
                    const $othersContainer = $('.xh-cd-sidebar .xh-cd-card:last-child');
                    // Find or create the container for list (excluding title and button)
                    let $listContainer = $othersContainer.find('.xh-cd-others-list');
                    if ($listContainer.length === 0) {
                        // Create it after the header div
                        $othersContainer.find('.xh-cd-empty-state').remove(); // remove default empty state
                        $listContainer = $('<div class="xh-cd-others-list"></div>');
                        $othersContainer.append($listContainer);
                    }

                    $listContainer.empty();

                    if (otherFeedbacks && otherFeedbacks.length > 0) {
                        otherFeedbacks.forEach(function (feedback) {
                            // Determine name
                            const name = feedback.VendorUserName || feedback.Username || 'Unknown User';
                            const rating = parseInt(feedback.RatingID) || 0;
                            const remarks = feedback.Remarks || ''; // Can be empty or null

                            // Generate HTML
                            let starsHtml = '';
                            for (let i = 1; i <= 5; i++) {
                                if (i <= rating) {
                                    starsHtml += '<span class="las la-star" style="font-size:14px; color:#fbbf24;"></span>';
                                } else {
                                    starsHtml += '<span class="lar la-star" style="font-size:14px; color:#d1d5db;"></span>';
                                }
                            }

                            const itemHtml = `
                                <div class="xh-cd-feedback-item" style="border-bottom:1px solid #f3f4f6; padding: 12px 0; font-size:13px;">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                                        <div style="font-weight:600; color:#1f2937;">${name}</div>
                                        <div style="display:flex;">${starsHtml}</div>
                                    </div>
                                    <div style="color:#4b5563; font-style:italic;">"${remarks}"</div>
                                </div>
                             `;
                            $listContainer.append(itemHtml);
                        });
                    } else {
                        $listContainer.html('<div class="xh-cd-empty-state" style="text-align:center; padding: 20px 0;">No other feedbacks</div>');
                    }
                }
            },
            error: function () {
                console.error('Failed to load feedback');
            }
        });
    }

    function initInterviewUI() {
        $('#xh-interview-section').show();
        var $quesSelect = $('#Ques');
        $quesSelect.empty();

        if (InterviewData && InterviewData.length > 0) {
            $('#xh-interview-not-attempted').hide();
            InterviewData.forEach(function (ques, index) {
                var qNum = index + 1;
                $quesSelect.append(`<option value="${ques.ID}">Question ${qNum}</option>`);
            });

            // Bind Events
            $quesSelect.off('change').on('change', function () {
                getQues();
            });

            $('#PrevQues').off('click').on('click', function () {
                var currentIndex = $quesSelect.prop('selectedIndex');
                if (currentIndex > 0) {
                    $quesSelect.prop('selectedIndex', currentIndex - 1).trigger('change');
                }
            });

            $('#NextQues').off('click').on('click', function () {
                var currentIndex = $quesSelect.prop('selectedIndex');
                if (currentIndex < $quesSelect.find('option').length - 1) {
                    $quesSelect.prop('selectedIndex', currentIndex + 1).trigger('change');
                }
            });

            // Trigger first question
            $quesSelect.trigger('change');
        } else {
            $('#xh-interview-section').hide();
            $('#xh-interview-not-attempted').show();
        }

        // Video Event Bindings
        $('#play-pause-icon, #controls-overlay').off('click').on('click', togglePlayPause);
        $('#rewind-icon').off('click').on('click', rewind);
        $('#forward-icon').off('click').on('click', forward);
        $('#custom-progress-bar').off('click').on('click', seek);
        $('#volume-slider').off('input').on('input', updateVolume);

        const video = document.getElementById('VideoURL');
        if (video) {
            video.removeEventListener('timeupdate', updateProgress);
            video.addEventListener('timeupdate', updateProgress);

            video.removeEventListener('ended', function () { });
            video.addEventListener('ended', function () {
                $('#play-pause-icon').removeClass('playing').html('<i class="las la-play"></i>');
            });

            video.removeEventListener('loadedmetadata', resizeCanvas);
            video.addEventListener('loadedmetadata', resizeCanvas);
        }
    }

    function renderApplicationDetails(data) {

        // Header Info
        $('.xh-cd-name').text(data.Candidate || 'Unknown Name');
        $('.xh-cd-role').text(data.Designation || '');
        $('.xh-cd-company').text(data.Employer || '');

        // Avatar
        if (data.PhotoIMG) {
            $('.xh-cd-avatar, .xh-cd-profile-section img').attr('src', data.PhotoIMG);
        } else {
            // Use local placeholder
            var placeholderUrl = (xenhireAjax.plugin_url || '') + 'public/images/placeholder.png';
            $('.xh-cd-avatar, .xh-cd-profile-section img').attr('src', placeholderUrl);
        }

        // Resume Match
        if (data.MatchName) {
            $('.xh-cd-resume-match').html(`<span class="xh-cd-match-dot" style="background-color:${data.MatchColor || '#ccc'}"></span> ${data.MatchName}`);
        }

        // Meta Info
        $('#xh-cd-location').html(`<span class="las la-map-marker"></span> ${data.CurrentCity || 'Not Specified'}`);

        let phoneHtml = `<span class="las la-phone"></span> `;
        let phoneNumbers = [];
        if (data.Mobile) phoneNumbers.push(data.Mobile);
        if (data.AltMobile) phoneNumbers.push(data.AltMobile);

        if (phoneNumbers.length > 0) {
            phoneHtml += phoneNumbers.map(num => `<a href="tel:${num}">${num}</a>`).join(', ');
            phoneHtml += ` <span class="ki-outline ki-copy xh-copy-btn" title="Copy" data-text="${phoneNumbers.join(', ')}" style="cursor:pointer; font-size:14px;"></span>`;
        } else {
            phoneHtml += '';
        }
        $('#xh-cd-phone').html(phoneHtml);

        let emailHtml = `<span class="las la-envelope"></span> `;
        if (data.Email) {
            emailHtml += `<a href="mailto:${data.Email}">${data.Email}</a> <span class="ki-outline ki-copy xh-copy-btn" title="Copy" data-text="${data.Email}" style="cursor:pointer; font-size:14px;"></span>`;
        } else {
            emailHtml += '';
        }
        $('#xh-cd-email').html(emailHtml);

        $('#xh-cd-experience').html(`<span class="las la-business-time"></span> ${data.ExpInYears || ''}`);
        $('#xh-cd-salary').html(`<span class="las la-wallet"></span> ${data.CurrentSalary || ''}`);

        let linkedInHtml = '';
        if (data.LinkedInURL) {
            linkedInHtml += `<span class="lab la-linkedin"></span> `;
            let url = data.LinkedInURL.startsWith('http') ? data.LinkedInURL : 'https://' + data.LinkedInURL;
            linkedInHtml += `<a href="${url}" target="_blank">Click to open</a> <span class="ki-outline ki-copy xh-copy-btn" title="Copy" data-text="${url}" style="cursor:pointer; font-size:13px;"></span>`;
        }
        $('#xh-cd-linkedin').html(linkedInHtml);

        let ageHtml = `<span class="las la-calendar"></span> `;
        if (data.Age) {
            ageHtml += `${data.Age} years`;
        } else {
            ageHtml += 'Not Specified';
        }
        $('#xh-cd-age').html(ageHtml + (data.DOB ? ' (' + data.DOB + ')' : ''));

        // Resume Link
        if (data.ResumeFILE) {
            $('.xh-cd-resume-btn').attr('href', data.ResumeFILE).show();
        } else {
            $('.xh-cd-resume-btn').hide();
        }

        // Public Link
        // Use ShareKey if available, otherwise AppID (legacy)
        let shareId = data.ShareKey || appId;

        let publicLink = window.location.origin + '/web/share/' + shareId;
        if (xenhireAjax && xenhireAjax.home_url) {
            publicLink = xenhireAjax.home_url + '/share/' + shareId;
        }

        $('.xh-cd-public-link a').attr('href', publicLink).text(publicLink);
        // Ensure public link icon has class and data
        $('.xh-cd-public-link .ki-outline.ki-copy').addClass('xh-copy-btn').attr('data-text', publicLink).css('cursor', 'pointer');

        // Stage
        if (data.StageID) {
            currentStageId = data.StageID;
            if (stagesLoaded) {
                $('.xh-cd-stage-select').val(data.StageID);
            }
        }

        // Skills (Keywords)
        if (data.Keywords) {
            let skills = data.Keywords.split(',');
            let skillsHtml = '';
            skills.forEach(skill => {
                if (skill.trim()) {
                    skillsHtml += `<span class="xh-cd-tag">${skill.trim()}</span>`;
                }
            });
            $('.xh-cd-tags').html(skillsHtml);
        }

        // Candidate Extra Information
        if (data.NoticePeriod) {
            $('#xh-extra-info-card').show();
            $('#xh-extra-info-content').html(`<div style="margin-bottom:5px;"><strong>Notice Period :</strong> <span style="color:#6b7280;">${data.NoticePeriod}</span></div>`);
        } else {
            $('#xh-extra-info-card').hide();
        }

        // Update Applied For text
        $('.xh-cd-info span:contains("Applied for")').next('div').text(`${data.JobTitle || ''}, ${data.CompanyName || ''}`);

        // Populate Resume
        if (data.ResumeFILE) {
            const resumeUrl = data.ResumeFILE;
            const s3BaseUrl = (typeof xenhireAjax !== 'undefined' && xenhireAjax.s3_base_url) ? xenhireAjax.s3_base_url : '';

            // Determine full URL
            let fullUrl = resumeUrl;
            if (!resumeUrl.startsWith('http')) {
                fullUrl = s3BaseUrl + (resumeUrl.startsWith('/') ? '' : '/') + resumeUrl;
            }

            // Clear previous content
            $("#divResume").empty();
            $("#btnResumeDownload").addClass('d-none');

            if (fullUrl.toLowerCase().indexOf('.pdf') > -1) {
                // Render PDF
                pdfjsLib.getDocument(fullUrl).promise.then(function (pdf) {
                    const numPages = pdf.numPages;
                    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                        pdf.getPage(pageNum).then(function (page) {
                            const scale = 1.0;
                            const viewport = page.getViewport({ scale: scale });
                            const canvas = document.createElement('canvas');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            canvas.style.width = "100%"; // Responsive width
                            canvas.style.height = "auto";
                            $("#divResume").append(canvas);

                            const context = canvas.getContext('2d');
                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };
                            page.render(renderContext);
                        });
                    }
                }, function (reason) {
                    console.error('Error rendering PDF:', reason);
                    $("#divResume").html('<div class="xh-cd-empty-state">Error loading PDF resume. <a href="' + fullUrl + '" target="_blank">Click here to view</a></div>');
                });
            } else {
                // Render Office Doc
                const officeViewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(fullUrl);
                $('#divResume').html('<iframe src="' + officeViewerUrl + '" width="100%" height="500px" frameborder="0"></iframe>');
            }

            // Setup Download Button
            $("#btnResumeDownload").removeClass('d-none').data('url', fullUrl);
            $("#btnResumeDownload").off('click').click(function () {
                const url = $(this).data('url');
                window.open(url, '_blank');
            });

        } else {
            $('#xh-tab-resume').html('<div class="xh-cd-empty-state">No resume available</div>');
        }

        // Populate Match Score
        let matchHtml = '';
        if (data.MatchName) {
            matchHtml += `<div style="margin-bottom:15px;">
                        <strong>Match Status:</strong> 
                        <span class="xh-cd-match-dot" style="background-color:${data.MatchColor || '#ccc'}; display:inline-block; margin-left:5px;"></span> 
                        ${data.MatchName}
                    </div>`;

            // Hide Get Match Score button if match exists
            $('#btnGetMatchScore').removeClass('d-block').hide();
        } else {
            // Show Get Match Score button if no match
            $('#btnGetMatchScore').addClass('d-block').attr('style', 'text-decoration:none; font-size:13px; display:block !important; cursor:pointer;');
        }

        if (data.MatchScore) {
            matchHtml += `<div style="margin-bottom:15px;"><strong>Score:</strong> ${data.MatchScore}</div>`;
        }

        if (data.MatchDescription) {
            matchHtml += `<div style="margin-bottom:15px;"><strong>Description:</strong><div style="margin-top:5px; white-space: pre-wrap;">${data.MatchDescription}</div></div>`;
        }

        if (data.MatchComment) {
            matchHtml += `<div style="margin-bottom:15px;"><strong>Comment:</strong><div style="margin-top:5px; white-space: pre-wrap;">${data.MatchComment}</div></div>`;
        }

        if (data.MatchStrengths) {
            matchHtml += `<div style="margin-bottom:15px;"><strong>Strengths:</strong><div style="margin-top:5px; white-space: pre-wrap;">${data.MatchStrengths}</div></div>`;
        }

        if (data.MatchSkillsNo) {
            matchHtml += `<div style="margin-bottom:15px;"><strong>Skills:</strong> ${data.MatchSkillsNo}</div>`;
        }

        if (!matchHtml) {
            matchHtml = '<div class="xh-cd-empty-state">No match score details available</div>';
        }

        $('#xh-match-score-details').html(matchHtml);

        // Overall AI Score
        if (data.AIScore && parseFloat(data.AIScore) >= 1) {
            let starRating = Math.round(parseFloat(data.AIScore));
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= starRating) {
                    starsHtml += `<i class="ki-duotone ki-star active"></i>`;
                } else {
                    starsHtml += `<i class="ki-duotone ki-star"></i>`;
                }
            }
            $('.xh-cd-ai-score-section .xh-rating').html(starsHtml);
            $('.xh-cd-ai-score-section').show(); // Ensure section is shown
        } else {
            $('.xh-cd-ai-score-section').hide();
        }
    }

    // Get Match Score Click Handler
    $('#btnGetMatchScore').on('click', function () {
        CalculateMatch();
    });

    function CalculateMatch() {
        const $btn = $('#btnGetMatchScore');

        // Show Loading
        $btn.text('Loading...').css('pointer-events', 'none');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_match_score',
                nonce: xenhireAjax.nonce,
                app_id: appId // Use the global appId
            },
            success: function (res) {
                if (res.success) {
                    // Check if we received the full application details (chained response)
                    if (res.data && res.data.details) {
                        const data = res.data;

                        // Update Global Data
                        if (typeof InterviewData !== 'undefined') InterviewData = data.interview_data || [];
                        // currentDetails global might be used elsewhere, update it if exists
                        // (Assuming currentDetails is defined globally based on usage in renderApplicationDetails? No, renderApplicationDetails takes arg)

                        renderApplicationDetails(data.details);
                        if (typeof renderExperience === 'function') renderExperience(data.experience || []);
                        if (typeof renderEducation === 'function') renderEducation(data.education || []);
                        if (typeof renderExtraInfo === 'function') renderExtraInfo(data.details);
                        if (typeof renderMatchScore === 'function') renderMatchScore(data.details);

                        // Initialize Interview UI
                        if (InterviewData.length > 0) {
                            if (typeof initInterviewUI === 'function') initInterviewUI();
                        } else {
                            $('#xh-interview-not-attempted').show();
                            $('#xh-interview-section').hide();
                        }

                        loadFeedback(appId);
                    } else {
                        // Fallback to separate call if data logic fails
                        loadApplicationDetails(appId);
                        loadFeedback(appId);
                    }
                } else {
                    showError(res.data.message || 'Failed to calculate match score');
                    $btn.text('Get Match Score').css('pointer-events', 'auto');
                }
            },
            error: function () {
                showOops();
                $btn.text('Get Match Score').css('pointer-events', 'auto');
            }
        });
    }

    // Toggle Card Visibility (Resume/Match)
    $(document).on('click', '.xh-cd-resume-btn, .xh-cd-resume-match', function (e) {
        e.preventDefault();
        const $card = $('#xh-resume-card');
        const $btn = $('.xh-cd-resume-btn'); // Target the resume button specifically
        const isBtnClick = $(this).hasClass('xh-cd-resume-btn');

        if ($card.is(':visible')) {
            $card.slideUp();
            // Only update button if BUTTON was clicked or if it was active
            if (isBtnClick || $btn.hasClass('active')) {
                $btn.text('View Resume').removeClass('active');
            }
        } else {
            $card.slideDown();

            if (isBtnClick) {
                // If button clicked, update button state and show resume
                $btn.text('Hide Resume').addClass('active');
                $('.xh-cd-tab[data-tab="resume"]').click();
            } else {
                // If match clicked, show match (button state remains "View Resume")
                $('.xh-cd-tab[data-tab="match"]').click();
            }
        }
    });

    // Resume/Match Tabs
    $(document).on('click', '.xh-cd-tab', function () {
        const tab = $(this).data('tab');
        $('.xh-cd-tab').removeClass('active');
        $(this).addClass('active');

        $('.xh-cd-tab-content').hide();
        $('#xh-tab-' + tab).show();

        if (tab === 'resume') {
            $('#btnResumeDownload').removeClass('d-none');
        } else {
            $('#btnResumeDownload').addClass('d-none');
        }
    });

    // Close Card
    $('.xh-card-close').on('click', function () {
        $('#xh-resume-card').slideUp();
        $('.xh-cd-resume-btn').text('View Resume').removeClass('active');
    });

    // Copy to Clipboard Handler
    $(document).on('click', '.xh-copy-btn', function () {
        const text = $(this).attr('data-text');
        if (text) {
            navigator.clipboard.writeText(text).then(function () {
                showSuccess('Copied to clipboard');
            }, function (err) {
                console.error('Could not copy text: ', err);
            });
        }
    });

    // Feedback Star Rating Interaction
    $(document).on('click', '.xh-cd-feedback-row .xh-cd-stars i', function () {
        const $stars = $(this).parent().children('i');
        const index = $(this).index();

        $stars.removeClass('ki-duotone ki-star').addClass('ki-outline ki-star');

        $stars.each(function (i) {
            if (i <= index) {
                $(this).removeClass('ki-outline ki-star').addClass('ki-duotone ki-star');
            }
        });
    });

    // Save Feedback Handler
    $('.xh-cd-btn-save').on('click', function () {
        if (!appId) return;

        const $btn = $(this);
        const remarks = $('.xh-cd-textarea').val();

        // Calculate rating from filled stars
        let rating = 0;
        $('.xh-cd-feedback-row .xh-cd-stars i').each(function () {
            if ($(this).hasClass('ki-duotone') && $(this).hasClass('ki-star')) {
                rating++;
            }
        });

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_save_feedback',
                nonce: xenhireAjax.nonce,
                app_id: appId,
                rating: rating,
                remarks: remarks,
                map_id: currentFeedbackId
            },
            success: function (res) {
                if (res.success) {
                    // Reload feedback to get the new ID and update UI
                    loadFeedback(appId);
                    showSuccess('Feedback saved successfully');
                } else {
                    showError(res.data.message || 'Failed to save feedback');
                }
            },
            error: function () {
                showOops();
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save Score & Feedback');
            }
        });
    });

    // Stage Change Handler
    let pendingStageId = null;
    let emailEditor = null;

    // Initialize CKEditor
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#xh-email-editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                placeholder: 'Type your email content here...'
            })
            .then(editor => {
                emailEditor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    }

    $(document).on('change', '.xh-cd-stage-select', function () {
        pendingStageId = $(this).val();

        if (!appId || !pendingStageId) return;

        // 1. Update Stage Immediately
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_set_job_application_stage',
                nonce: xenhireAjax.nonce,
                app_id: appId,
                stage_id: pendingStageId
            },
            success: function (res) {
                if (res.success) {
                    currentStageId = pendingStageId; // Update current stage
                    showSuccess('Stage updated');
                } else {
                    console.error('Failed to update stage');
                    showError('Failed to update stage');
                    // Revert selection
                    $('.xh-cd-stage-select').val(currentStageId);
                }
            },
            error: function () {
                console.error('Server error updating stage');
                showOops();
                $('.xh-cd-stage-select').val(currentStageId);
            }
        });

        // 2. Get Email Template & Open Modal
        // Find the stage object to get EmailTemplateID
        let stageObj = AllStages.find(s => (s.Value == pendingStageId || s.ID == pendingStageId));

        let emailTemplateId = stageObj ? (stageObj.templateid || stageObj.EmailTemplateID || 0) : 0;

        if (emailTemplateId) {
            $.ajax({
                url: xenhireAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'xenhire_get_email_template',
                    nonce: xenhireAjax.nonce,
                    app_id: appId,
                    stage_id: emailTemplateId
                },
                success: function (res) {
                    if (res.success && res.data) {
                        // API returns { success: true, data: "[[...]]", message: "OK" }
                        // So we need res.data.data or res.data depending on structure
                        let template = res.data.data || res.data;

                        // Parse if it's a string
                        if (typeof template === 'string') {
                            try {
                                template = JSON.parse(template);
                            } catch (e) {
                                console.error('Failed to parse template data', e);
                            }
                        }

                        // Handle if it's an array (API often returns [[Object]])
                        if (Array.isArray(template)) {
                            // Check for nested array [[...]]
                            if (Array.isArray(template[0])) {
                                template = template[0];
                            }
                            if (template.length > 0) {
                                template = template[0];
                            }
                        }

                        // Handle if wrapped in Data/Items (just in case)
                        if (template.Data) template = template.Data;

                        // Open Modal
                        $('#xh-stage-modal').fadeIn();

                        // Pre-fill email fields
                        const candidateEmail = $('#xh-cd-email a').text().trim();
                        if (candidateEmail && candidateEmail !== '-') {
                            $('#xh-email-to').val(candidateEmail);
                        }

                        // Pre-fill subject & body
                        $('#xh-email-subject').val(template.Subject || '');

                        if (emailEditor) {
                            emailEditor.setData(template.Body || template.HTMLBody || '');
                        }
                    } else {
                        console.error('Failed to fetch email template');
                    }
                }
            });
        } else {
            // Fallback if no template ID (or just open modal empty?)
            // For now, let's open modal with default logic if desired, or just do nothing?
            // User said: "the email template data populate into poup"
            // If no template, maybe we don't open popup? Or open empty?
            // Let's open it with defaults as before just in case
            $('#xh-stage-modal').fadeIn();

            const candidateEmail = $('#xh-cd-email a').text().trim();
            if (candidateEmail && candidateEmail !== '-') {
                $('#xh-email-to').val(candidateEmail);
            }

            const stageName = $('.xh-cd-stage-select option:selected').text();
            const jobTitle = $('.xh-cd-info span:contains("Applied for")').next('div').text().trim();
            $('#xh-email-subject').val(`Update on your application for ${jobTitle} - ${stageName}`);

            if (emailEditor) {
                const candidateName = $('.xh-cd-name').text().trim();
                emailEditor.setData(`<p>Dear ${candidateName},</p><p>We are writing to inform you that your application has been moved to the <strong>${stageName}</strong> stage.</p><p>Best regards,<br>Hiring Team</p>`);
            }
        }
    });

    // Close Modal
    $('.xh-close-modal, .xh-btn-close').on('click', function () {
        $('#xh-stage-modal').fadeOut();
        // Revert selection if cancelled
        if (currentStageId) {
            $('.xh-cd-stage-select').val(currentStageId);
        }
        pendingStageId = null;
    });

    // Send Email & Update Stage
    // Send Email Only
    $('.xh-btn-send').on('click', function () {
        if (!appId) return; // Stage is already set

        const emailTo = $('#xh-email-to').val();
        const emailCc = $('#xh-email-cc').val();
        const subject = $('#xh-email-subject').val();
        const body = emailEditor ? emailEditor.getData() : '';

        const $btn = $(this);
        $btn.prop('disabled', true).text('Sending...');

        // Call Send Mail Admin
        if (emailTo && subject && body) {
            $.ajax({
                url: xenhireAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'xenhire_send_mail_admin',
                    nonce: xenhireAjax.nonce,
                    emails: emailTo,
                    cc: emailCc,
                    subject: subject,
                    body: body
                },
                success: function (mailRes) {

                    if (typeof toastr !== 'undefined') {
                        toastr.success('Email sent successfully');
                    } else if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Email sent successfully'
                        });
                    } else {
                        alert('Email sent successfully');
                    }

                    $('#xh-stage-modal').fadeOut();
                },
                error: function () {
                    alert('Failed to send email');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Send Email');
                }
            });
        } else {
            alert('Please fill all fields');
            $btn.prop('disabled', false).text('Send Email');
        }
    });

    // Close modal when clicking outside
    $(window).on('click', function (event) {
        if ($(event.target).is('#xh-stage-modal')) {
            $('#xh-stage-modal').fadeOut();
            if (currentStageId) {
                $('.xh-cd-stage-select').val(currentStageId);
            }
        }
    });



    function selectPrevOption() {
        var $select = $('#Ques');
        var currentIndex = $select.prop('selectedIndex');
        var prevIndex = currentIndex - 1;
        if (prevIndex >= 0) {
            $select.prop('selectedIndex', prevIndex);
            $select.trigger('change');
        }
    }

    function getQues() {
        var val = $("#Ques").val();
        var data = InterviewData.find(function (ques) { return ques.ID == val; });
        var currentIndex = $("#Ques").prop('selectedIndex');
        var totalQuestions = $("#Ques option").length;

        // Update Counter
        $("#xh-question-counter").text(`Question ${currentIndex + 1} of ${totalQuestions}`);

        // Update Nav Buttons State
        if (currentIndex === 0) {
            $("#PrevQues").addClass('disabled').css('opacity', 0.5);
        } else {
            $("#PrevQues").removeClass('disabled').css('opacity', 1);
        }

        if (currentIndex === totalQuestions - 1) {
            $("#NextQues").addClass('disabled').css('opacity', 0.5);
        } else {
            $("#NextQues").removeClass('disabled').css('opacity', 1);
        }

        if (data != null) {
            // Question Text
            $("#Question").html(`<strong class='me-2'>Q.${currentIndex + 1}</strong> <span>${data.Question || ''}</span>`);

            if (data.IsOverallScore) {
                $(".div-ques").hide();
            } else {
                var aPanel = $("#divAnswer"); aPanel.empty();
                var transPanel = $("#divTranscription");
                var transContent = transPanel.find('.xh-transcript-content');

                $(".div-ques").show();

                // Video Question
                if (data.QuestionTypeID === 1 || !data.QuestionTypeID) {
                    // Video Logic
                    var width = $("#VideoURL").parent().width() || 600;

                    $("#VideoURL .webm-source").attr('src', data.VideoURL);
                    $("#VideoURL .mp4-source").attr('src', data.VideoURL ? data.VideoURL.replace(/.webm/g, '.mp4') : '');
                    $("#VideoURL").attr('data-duration', data.Duration || 0);

                    setTimeout(function () { updateBlurredBackground(); }, 500);
                    $("#VideoURL")[0].load();

                    // Transcript
                    if (data.Transcription) {
                        transPanel.show();
                        transContent.html(data.Transcription);

                        // Unbind previous click to avoid stacking
                        transPanel.find('.xh-transcript-toggle').off('click').on('click', function (e) {
                            e.preventDefault();
                            transContent.slideToggle();
                            var $icon = $(this).find('.dashicons');
                            if (transContent.is(':visible')) {
                                $icon.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
                            } else {
                                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
                            }
                        });
                    } else {
                        transPanel.hide();
                    }

                    $('.other-types').hide();
                    $('.video-wrapper').show();
                } else {
                    // Text/Other Questions
                    $('.video-wrapper').hide();
                    $('.other-types').show();
                    transPanel.hide();

                    if (data.QuestionTypeID === 2 || data.QuestionTypeID === 3 || data.QuestionTypeID === 8) {
                        aPanel.append(`<textarea rows="3" class="form-control" disabled>${decodeURIComponent(data.VideoURL || '')}</textarea>`);
                    } else {
                        aPanel.append(`<div class="p-3 border bg-light">${data.VideoURL || ''}</div>`);
                    }
                }
            }

            // AI Score & Feedback
            if (data.IsAIScored || data.AIScore > 0) {
                setRating(document.querySelector('.rating-elem-ai'), Number(data.AIScore), 1);

                // Strengths
                if (data.AIStrengths) {
                    $("#AIStrengths").html(data.AIStrengths);
                } else {
                    $("#AIStrengths").html('None identified');
                }

                // Weaknesses / Improvement Areas
                if (data.AIWeaknesses) {
                    $("#AIWeaknesses").html(data.AIWeaknesses);
                } else {
                    $("#AIWeaknesses").html('No answer was provided');
                }

                // Suggestions
                if (data.AISuggestions) {
                    $("#AISuggestions").html(data.AISuggestions);
                    $(".ai-suggestions").show();
                } else {
                    $(".ai-suggestions").hide();
                }

                $("#divAI").removeClass('xh-hidden');
            } else {
                $("#divAI").addClass('xh-hidden');
            }
        }

        const playPauseIcon = document.getElementById('play-pause-icon');
        if (playPauseIcon) playPauseIcon.classList.remove('playing');
    }

    // --- Helper Functions ---

    function setRating(container, score, max) {
        if (!container) return;
        let html = '';
        // Assuming 5 star rating
        for (let i = 1; i <= 5; i++) {
            // Use score directly as star count
            let activeClass = i <= score ? 'active' : '';
            // Use gold color for active stars
            let colorStyle = i <= score ? 'color: #ffad0f;' : 'color: #d1d5db;';
            html += `<i class="ki-duotone ki-star ${activeClass}" style="${colorStyle} font-size:20px;"><span class="path1"></span><span class="path2"></span></i>`;
        }
        container.innerHTML = html;
    }

    function blankStringIfNull(str) {
        return str === null || str === undefined ? '' : str;
    }

    // Video Player Helpers
    function formatTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    function updateProgress() {
        const video = document.getElementById('VideoURL');
        const progressBar = document.getElementById('progress');
        const timeDisplay = document.getElementById('time-display');
        if (!video) return;

        const manualDuration = parseFloat(video.getAttribute('data-duration')) || video.duration;
        const currentTime = video.currentTime;
        const progress = (currentTime / manualDuration) * 100;
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (timeDisplay) timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(manualDuration)}`;
    }

    function togglePlayPause() {
        const video = document.getElementById('VideoURL');
        const playPauseIcon = document.getElementById('play-pause-icon');
        if (!video) return;

        if (video.paused) {
            video.play();
            if (playPauseIcon) {
                playPauseIcon.innerHTML = '<i class="dashicons dashicons-controls-pause"></i>';
                playPauseIcon.classList.add('playing');
            }
        } else {
            video.pause();
            if (playPauseIcon) {
                playPauseIcon.innerHTML = '<i class="dashicons dashicons-controls-play"></i>';
                playPauseIcon.classList.remove('playing');
            }
        }
    }

    function rewind() {
        const video = document.getElementById('VideoURL');
        if (video) {
            video.currentTime = Math.max(0, video.currentTime - 5);
            updateProgress();
        }
    }

    function forward() {
        const video = document.getElementById('VideoURL');
        if (video) {
            const manualDuration = parseFloat(video.getAttribute('data-duration')) || video.duration;
            video.currentTime = Math.min(manualDuration, video.currentTime + 5);
            updateProgress();
        }
    }

    function seek(event) {
        const video = document.getElementById('VideoURL');
        const customProgressBar = document.getElementById('custom-progress-bar');
        if (!video || !customProgressBar) return;

        const manualDuration = parseFloat(video.getAttribute('data-duration')) || video.duration;
        const rect = customProgressBar.getBoundingClientRect();
        const offsetX = event.clientX - rect.left;
        const seekTime = (offsetX / customProgressBar.clientWidth) * manualDuration;
        video.currentTime = seekTime;
        updateProgress();
    }

    function updateVolume() {
        const video = document.getElementById('VideoURL');
        const volumeSlider = document.getElementById('volume-slider');
        const volumeIcon = document.getElementById('volume-icon');
        if (!video || !volumeSlider) return;

        video.volume = volumeSlider.value;
        if (volumeIcon) {
            if (video.volume === 0) { volumeIcon.className = 'dashicons dashicons-controls-volumeoff'; }
            else if (video.volume <= 0.5) { volumeIcon.className = 'dashicons dashicons-controls-volumeon'; } // No down icon in dashicons easily
            else { volumeIcon.className = 'dashicons dashicons-controls-volumeon'; }
        }
    }

    function updateBlurredBackground() {
        const video = document.getElementById('VideoURL');
        const blurredBackground = document.getElementById('blurred-background');
        if (!video || !blurredBackground) return;

        const blurCtx = blurredBackground.getContext('2d');
        const videoAspect = video.videoWidth / video.videoHeight;
        const canvasAspect = blurredBackground.width / blurredBackground.height;
        let sx, sy, sWidth, sHeight;

        if (videoAspect > canvasAspect) {
            sHeight = video.videoHeight;
            sWidth = sHeight * canvasAspect;
            sx = (video.videoWidth - sWidth) / 2;
            sy = 0;
        } else {
            sWidth = video.videoWidth;
            sHeight = sWidth / canvasAspect;
            sx = 0;
            sy = (video.videoHeight - sHeight) / 2;
        }

        if (sWidth > 0 && sHeight > 0) {
            blurCtx.drawImage(video, sx, sy, sWidth, sHeight, 0, 0, blurredBackground.width, blurredBackground.height);
            blurCtx.filter = 'blur(5px)';
            blurCtx.drawImage(blurredBackground, 0, 0);
            blurCtx.filter = 'none';
        }
    }

    function renderExperience(data) {
        let html = '';
        if (data && data.length > 0) {
            data.forEach(item => {
                const company = item.CompanyName || item.Employer || 'Unknown Company';
                const role = item.JobRole || item.Designation || '';

                // Date Logic
                let startDateStr = '';
                let endDateStr = '';
                let durationStr = '';

                // Helper to format date "01 Jan 1999"
                const formatDate = (y, m) => {
                    if (!y) return '';
                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    // If month is number string '1' or '01', convert to index
                    let mIndex = parseInt(m) - 1;
                    if (isNaN(mIndex) || mIndex < 0 || mIndex > 11) mIndex = 0; // Default Jan if missing/invalid
                    return `01 ${monthNames[mIndex]} ${y}`;
                };

                if (item.StartYear) {
                    startDateStr = formatDate(item.StartYear, item.StartMonth);

                    if (item.IsCurrent) {
                        endDateStr = 'Present';
                    } else if (item.EndYear) {
                        endDateStr = formatDate(item.EndYear, item.EndMonth);
                    } else {
                        endDateStr = 'Present'; // Fallback if no end date and not marked current? Or just empty.
                    }
                } else if (item.StartDate) {
                    // Fallback for full date string if API changes
                    startDateStr = item.StartDate;
                    endDateStr = item.EndDate || 'Present';
                }

                // Calculate Duration
                if (item.StartYear) {
                    let start = new Date(item.StartYear, (parseInt(item.StartMonth) || 1) - 1);
                    let end = new Date();
                    if (!item.IsCurrent && item.EndYear) {
                        end = new Date(item.EndYear, (parseInt(item.EndMonth) || 1) - 1);
                    }

                    let diffMonths = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
                    if (diffMonths > 0) {
                        const years = Math.floor(diffMonths / 12);
                        const months = diffMonths % 12;

                        if (years > 0) durationStr += `${years} year${years > 1 ? 's' : ''}`;
                        if (months > 0) durationStr += ` ${months} month${months > 1 ? 's' : ''}`;
                    }
                }

                const dateRange = startDateStr ? `${startDateStr} - ${endDateStr}` : '';
                const fullMeta = [dateRange, durationStr].filter(Boolean).join(' &middot; ');

                html += `
                    <div class="xh-cd-exp-item" style="border-bottom:1px solid #f3f4f6; padding: 15px 0;">
                        <div class="xh-cd-exp-header" style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px;">
                             <div style="font-weight:600; color:#0e101a; font-size:15px;">
                                <span style="font-weight:700;">${role}</span>, ${company}
                            </div>
                            <div style="color:#64748b; font-size:13px; text-align:right;">
                                ${fullMeta}
                            </div>
                        </div>
                        <div style="font-size:14px; color:#475569; line-height:1.5;">${item.JobDescription || item.Description || ''}</div>
                    </div>
                `;
            });
            $('#xh-experience-list').html(html);
        } else {
            $('#xh-experience-list').html('<div class="xh-cd-empty-state">No experience specified</div>');
        }
    }

    function renderEducation(data) {
        let html = '';
        if (data && data.length > 0) {
            data.forEach(item => {
                // Use Date from API directly or fallback to constructed dates
                let dates = item.Dates || '';
                if (!dates) {
                    if (item.StartYear) dates += item.StartYear;
                    if (item.EndYear) dates += ' to ' + item.EndYear;
                }

                const degree = item.Qualification || item.Degree || '';
                const institution = item.Institute || item.University || item.School || 'Unknown Institute';
                // Specialization might be part of Qualification or separate, keeping fallback
                const specialization = item.Specialization ? ` (${item.Specialization})` : '';

                html += `
                    <div class="xh-cd-edu-item">
                        <div style="font-weight:500; color:#071437; font-size:13.97px; margin-bottom:4px;">
                            ${degree}${specialization}
                        </div>
                        <div style="color: #78829d; font-size: 13.97px; margin-bottom: 2px; font-weight: normal;">
                            ${institution}
                        </div>
                        <div style="color: #78829d; font-size: 13.97px; margin-bottom: 2px; font-weight: normal;">${dates}</div>
                    </div>
                `;
            });
            $('#xh-education-list').html(html);
        } else {
            $('#xh-education-list').html('<div class="xh-cd-empty-state">No education specified</div>');
        }
    }

    function renderExtraInfo(data) {
        if (!data) return;

        let html = '';
        let hasData = false;

        // Iterate through potential ExtraData fields (scanning 1 to 5 for now)
        for (let i = 1; i <= 5; i++) {
            const labelKey = `ExtraData${i}Label`;
            const valKey = `ExtraData${i}Val`;

            let label = data[labelKey];
            let val = data[valKey];

            // Special case for ExtraData1 if label is missing (Observation from screenshot)
            if (i === 1 && !label && val) {
                label = 'Notice Period';
            }

            if (label && val) {
                // Determine if we need to parse date? 
                // The screenshot showed "2026-01-16" which is already formatted well enough.
                // But just in case generic handling:
                if (typeof val === 'string' && val.includes('T') && val.length > 10 && !val.includes(' ')) {
                    // Simple check for ISO date if needed, but Screenshot data looks clean "2026-01-16"
                    val = val.split('T')[0];
                }

                html += `
                    <div style="margin-bottom:8px; font-size:13.97px; color:#78829d; font-weight:400;">
                        <span style="font-weight:600; color:#071437; font-size:13.97px;">${label} :</span> ${val}
                    </div>
                `;
                hasData = true;
            }
        }

        if (hasData) {
            $('#xh-extra-info-content').html(html);
            $('#xh-extra-info-card').show();
        } else {
            $('#xh-extra-info-card').hide();
        }
    }

    function renderMatchScore(data) {
        if (!data) return;

        // Data mapping based on user request:
        // MatchName -> Header
        // MatchComment -> Description (Remarks)
        // MatchStrengths -> Green List
        // MatchWeaknesses -> Red List
        // MatchSkillsYes -> Green Tags
        // MatchSkillsNo -> Red Tags

        let html = '';
        const matchName = data.MatchName || 'Match Score';
        const remarks = data.MatchComment || data.Remarks || ''; // Fallback to Remarks if match comment empty

        // helper to process list (newlines or HTML)
        const processList = (text, className) => {
            if (!text) return '';
            // Split by newline or bullet points if raw text
            let items = text.split(/\\n|•|- /).map(s => s.trim()).filter(s => s.length > 0);
            if (items.length === 0) return '';

            return `<ul class="${className}" style="list-style-type: disc; padding-left: 20px; margin-top:5px; margin-bottom:10px;">
                ${items.map(item => `<li style="margin-bottom:4px;">${item}</li>`).join('')}
            </ul>`;
        };

        // helper to process tags (comma separated)
        const processTags = (text, bgColor, color) => {
            if (!text) return '';
            let tags = text.split(',').map(s => s.trim()).filter(s => s.length > 0);
            if (tags.length === 0) return '';

            return `<div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:5px; margin-bottom:15px;">
                ${tags.map(tag => `<span class="xh-match-score-imparea-tag" style="background-color:${bgColor}75;">${tag}</span>`).join('')}
             </div>`;
        };

        html += `<div style="font-weight:700; font-size:16px; color:#1e293b; margin-bottom:8px;">${matchName}</div>`;
        if (remarks) {
            html += `<div style="font-size:14px; color:#475569; margin-bottom:20px; line-height:1.5;">${remarks}</div>`;
        }

        // Strengths
        if (data.MatchStrengths) {
            html += `<div style="font-weight:600; font-size:14px; color:#1e293b;">Strengths</div>`;
            html += `<div style="color:#22c55e; font-size:14px;">${processList(data.MatchStrengths, '')}</div>`;
        }

        // Skills Yes
        if (data.MatchSkillsYes) {
            html += processTags(data.MatchSkillsYes, '#dcfce7', '#166534');
        } else {
            // Screenshot shows "None" tag in green if empty? Or maybe explicitly passed "None". 
            // If user wants to show "None" when empty, we can add logic. Assuming data contains "None" string if applicable.
        }

        // Weaknesses / Improvement Areas
        if (data.MatchWeaknesses) {
            html += `<div style="font-weight:600; font-size:14px; color:#1e293b; margin-top:10px;">Improvement Areas</div>`;
            html += `<div style="color:#ef4444; font-size:14px;">${processList(data.MatchWeaknesses, '')}</div>`;
        }

        // Skills No
        if (data.MatchSkillsNo) {
            html += processTags(data.MatchSkillsNo, '#fee2e2', '#991b1b');
        }

        $('#xh-match-score-details').html(html);

        // Ensure parent is visible if it was hidden/inactive? 
        // Logic for checking if tab is active belongs usually in event handlers, 
        // but we can ensure content is ready.
    }

    function resizeCanvas() {
        const video = document.getElementById('VideoURL');
        const blurredBackground = document.getElementById('blurred-background');
        if (video && blurredBackground) {
            blurredBackground.width = video.clientWidth;
            blurredBackground.height = video.clientHeight;
            updateBlurredBackground();
        }
    }

});



