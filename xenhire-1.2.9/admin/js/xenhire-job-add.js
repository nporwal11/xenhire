jQuery(document).ready(function ($) {

    // Global state
    var currentJobId = (typeof xenhireJobAddData !== 'undefined') ? xenhireJobAddData.jobId : 0;
    var isNew = (typeof xenhireJobAddData !== 'undefined') ? xenhireJobAddData.isNewJob : true;
    var loadedQuestions = [];
    var isRestricted = false;

    // Video Recording Variables
    var videoModal = $('#xj_video_modal');
    var mediaRecorder;
    var recordedChunks = [];
    var stream;
    var recordingTimer;
    var recordingTime = 0;
    var maxRecordingTime = 180; // 3 minutes

    // Initialize
    init();

    window.ckEditors = {};

    function initCKEditorFor(selector, key) {
        var element = document.querySelector(selector);
        if (element) {
            ClassicEditor
                .create(element, {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
                })
                .then(editor => {
                    window.ckEditors[key] = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        }
    }

    // Apply CKEditor to both JobDescription and JobRole
    initCKEditorFor('#JobDescription', 'JobDescription');
    initCKEditorFor('#JobRole', 'JobRole');

    function init() {
        loadAllCBOs();

        if (!isNew && currentJobId > 0) {
            loadJobDetails(currentJobId);
            loadInterviewQuestions(currentJobId);
            checkApplicationCount(currentJobId);
        } else {
            // Set defaults for new job
            $('#IsShowApplicationsCount').prop('checked', true);
            $('#AutoStartInSeconds').val(0);
        }

        setupEventHandlers();
        setupSortable();
        initTagifyForExtraData();
    }

    /**
     * Common CBO Loader Function
     * @param {string} key - CBO Key (e.g., 'Employer', 'Currency')
     * @param {string} containerId - Container element ID
     * @param {string} selectId - Select element ID
     * @param {string} cssClass - CSS class for select element (default: 'xj-select')
     * @param {function} callback - Optional callback function after loading
     */
    function loadCBO(key, containerId, selectId, cssClass, callback) {
        cssClass = cssClass || 'xj-select';

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_cbo_items',
                nonce: xenhireAjax.nonce,
                key: key
            },
            beforeSend: function () {
                $('#' + containerId).html(
                    '<select id="' + selectId + '" class="' + cssClass + '" disabled>' +
                    '<option>Loading ' + key + '...</option>' +
                    '</select>'
                );
            },
            success: function (response) {
                if (response.success && Array.isArray(response.data)) {
                    var options = '<select id="' + selectId + '" class="' + cssClass + '">';
                    options += '<option value="">Select ' + key + '</option>';

                    response.data.forEach(function (item) {
                        // Handle various property naming conventions
                        var value = item.Value || item.ID || item.id || item.Key || item.value || '';
                        var text = item.Text || item.Name || item.DisplayText || item.name || item.text || value;

                        if (value) {
                            options += '<option value="' + value + '">' + text + '</option>';
                        }
                    });

                    options += '</select>';
                    $('#' + containerId).html(options);

                    // Execute callback if provided
                    if (typeof callback === 'function') {
                        callback(response.data, selectId);
                    }
                } else {
                    console.error('Invalid CBO response for ' + key, response);
                    if (response.data) {
                        console.warn('Debug Info:', response.data);
                    }

                    // Check for auth required in success:false response
                    if (response.data && response.data.auth_required) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Session Expired',
                            text: 'Your session has expired. Please login again.',
                            confirmButtonColor: '#667eea',
                            allowOutsideClick: false
                        }).then(function () {
                            window.location.href = xenhireAjax.login_url || admin_url + 'admin.php?page=xenhire';
                        });
                        return;
                    }

                    $('#' + containerId).html(
                        '<select id="' + selectId + '" class="' + cssClass + '" disabled>' +
                        '<option>Error loading ' + key + '</option>' +
                        '</select>'
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error loading ' + key, error);

                // Check if authentication is required
                var response = xhr.responseJSON;
                if (response && response.data && response.data.auth_required) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Expired',
                        text: 'Your session has expired. Please login again.',
                        confirmButtonColor: '#667eea'
                    }).then(function () {
                        window.location.href = xenhireAjax.login_url;
                    });
                } else {
                    $('#' + containerId).html(
                        '<select id="' + selectId + '" class="' + cssClass + '" disabled>' +
                        '<option>Error loading ' + key + '</option>' +
                        '</select>'
                    );
                }
            }
        });
    }

    /**
     * Load all required CBOs
     */
    function loadAllCBOs() {
        // Load Employers
        loadCBO('Employer', 'ct-EmployerID', 'EmployerID', 'xj-select');

        // Load Currencies
        loadCBO('Currency', 'ct-CurrencyID', 'CurrencyID', 'xj-select', function (data, selectId) {
            var select = $('#' + selectId);
            if (!select.val()) {
                // Try to find USD option
                var usdOption = select.find('option').filter(function () {
                    return $(this).text() === 'USD' || $(this).val() === 'USD';
                });
                if (usdOption.length > 0) {
                    select.val(usdOption.val());
                }
            }
        });

        // Load Employment Types
        loadCBO('EmploymentType', 'ct-EmploymentTypeID', 'EmploymentTypeID', 'xj-select');
    }

    function initTagifyForExtraData() {
        if (typeof Tagify === 'undefined') {
            console.warn('Tagify library not loaded.');
            return;
        }

        for (var i = 1; i <= 4; i++) {
            var input = document.querySelector("#ExtraData" + i + "Options");
            if (input && !input.tagify) {
                try {
                    input.tagify = new Tagify(input, {
                        delimiters: ", ", // add new tags when a comma or a space character is entered
                        keepInvalidTags: true, // do not remove invalid tags
                        dropdown: {
                            enabled: 0 // disable suggestions dropdown
                        }
                    });
                } catch (e) {
                    console.error('Tagify init failed for ExtraData' + i, e);
                }
            }
        }
    }

    function setupEventHandlers() {
        // Tab switching
        $('.xj-tab-btn').click(function () {
            var tab = $(this).data('tab');

            // Validate before switching to questions
            if (tab === 'questions' && currentJobId <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Save Job First',
                    text: 'Please save the job details before adding interview questions.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            $('.xj-tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.xj-pane').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });

        // Accordions
        $('.xj-acc-hd').off('click').click(function (e) {
            e.preventDefault();
            var target = $(this).data('toggle');
            $(this).toggleClass('active');
            $('#' + target).slideToggle(200);
        });

        // Extra Data Type Change
        $('.extra-type').change(function () {
            var type = $(this).val();
            var id = $(this).attr('id').replace('Type', '');

            if (type == '3') { // Single Select
                $('#' + id + 'OptionsContainer').show();
            } else {
                $('#' + id + 'OptionsContainer').hide();
            }
        });

        // Save Job
        $('#xj_job_form').off('submit').submit(function (e) {
            e.preventDefault();
            saveJob();
        });

        // AI Description
        $('#xj-ai-desc').click(function () {
            generateAIDescription();
        });

        // AI Questions
        $('#xj-ai-ques, #xj-ai-ques-empty').click(function () {
            generateAIQuestions();
        });

        // Publish
        $('#xj_publish').off('click').click(function () {
            publishJob();
        });

        // Add Question Manually
        $('#xj-add-ques').off('click').click(function () {
            openQuestionModal();
        });

        // Question Modal Submit
        $('#kt_modal_ques_submit').off('click').click(function () {
            saveQuestion();
        });

        // Edit Question
        $(document).on('click', '.btn-edit-ques', function () {
            var id = $(this).data('id');
            editQuestion(id);
        });

        // Delete Question
        $(document).on('click', '.btn-del-ques', function () {
            var id = $(this).data('id');
            deleteQuestion(id);
        });

        // Salary Toggle
        $('#IsSalaryHidden').change(function () {
            toggleSalaryFields();
        });

        // City Toggle
        $('#IsCityHidden').change(function () {
            toggleCityFields();
        });

        // Question Type Change
        $(document).on('change', '#QuestionTypeID', function () {
            toggleQuestionFields();
        });

        // Question Type Change
        $(document).on('change', '#QuestionTypeID', function () {
            toggleQuestionFields();
        });

        // Invite Modal Tabs
        $(document).on('click', '#kt_modal_invite .nav-link', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');

            // Toggle Tabs
            $('#kt_modal_invite .nav-link').removeClass('active');
            $(this).addClass('active');

            // Toggle Content
            $('#kt_modal_invite .tab-pane').removeClass('show active');
            $(target).addClass('show active');

            // Toggle Submit Button
            if (target === '#kt_tab_invite_email') {
                $('#btnInviteSubmit').removeClass('d-none');
            } else {
                $('#btnInviteSubmit').addClass('d-none');
            }
        });

        // Modal Close Handlers
        $('.btn-close, [data-bs-dismiss="modal"]').click(function () {
            $('#kt_modal_ques').removeClass('show').hide();
        });

        // Close on overlay click
        $(window).click(function (e) {
            if ($(e.target).is('#kt_modal_ques')) {
                $('#kt_modal_ques').removeClass('show').hide();
            }
        });

        // Video Upload Actions
        $('#xj_upload_video').click(function () {
            resetVideoModal();
            videoModal.css('display', 'flex').show();
        });

        $('.xb-close').click(function () {
            videoModal.hide();
            stopStream();
        });

        // Close on outside click
        $(window).click(function (e) {
            if ($(e.target).is(videoModal)) {
                videoModal.hide();
                stopStream();
            }
            if ($(e.target).is($('#xj_playback_modal'))) {
                $('#xj_playback_modal').hide();
                var video = document.getElementById('xj_playback_video');
                if (video) video.pause();
            }
        });

        $('#xj_video_file').change(function () {
            var file = this.files[0];
            if (file) {
                if (file.size > 40 * 1024 * 1024) {
                    Swal.fire("Error", "File size exceeds 40MB limit.", "error");
                    this.value = '';
                    $('#xj_file_name').text('No file chosen');
                    $('#xj_save_uploaded_video').prop('disabled', true);
                    return;
                }
                $('#xj_file_name').text(file.name);
                $('#xj_save_uploaded_video').prop('disabled', false);
            }
        });

        $('#xj_save_uploaded_video').click(function () {
            var file = $('#xj_video_file')[0].files[0];
            if (file) {
                uploadVideo(file);
            }
        });

        // Recording
        $('#xj_start_recording').click(function () {
            startRecording();
        });

        $('#xj_stop_recording').click(function () {
            stopRecording();
        });

        $('#xj_save_recorded_video').click(function () {
            if (recordedChunks.length > 0) {
                var blob = new Blob(recordedChunks, { type: 'video/webm' });
                uploadVideo(blob);
            }
        });

        // Play Video
        $('#xj_play_video').click(function () {
            var url = $('#JobIntroVideoURL').val();
            if (url) {
                $('#xj_playback_video').attr('src', url);
                $('#xj_playback_modal').show();
                // var video = document.getElementById('xj_playback_video');
                // if (video) video.play();
            }
        });

        $('.xb-close-playback').click(function () {
            $('#xj_playback_modal').hide();
            // var video = document.getElementById('xj_playback_video');
            // if (video) video.pause();
            var video = $('#xj_playback_video').get(0);
            if (video) video.pause();
        });

        // Media Uploader (Ported from Branding)
        $('.xb-upload-btn').click(function (e) {
            e.preventDefault();
            var btn = $(this);
            var targetInput = $('#' + btn.data('target'));
            var previewDiv = $('#' + btn.data('preview'));

            var frame = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                targetInput.val(attachment.url);
                previewDiv.css('background-image', 'url(' + attachment.url + ')');
                btn.closest('.xb-upload-area').addClass('has-image');
            });

            frame.open();
        });

        // Remove Image
        $('.xb-remove-btn').click(function (e) {
            e.preventDefault();
            var btn = $(this);
            var targetInput = $('#' + btn.data('target'));
            var previewDiv = $('#' + btn.data('preview'));

            targetInput.val('');
            previewDiv.css('background-image', 'none');
            btn.closest('.xb-upload-area').removeClass('has-image');
        });
        // Delete Video
        $('#xj_delete_video').click(function () {
            Swal.fire({
                title: 'Delete Video?',
                text: "Are you sure you want to remove the intro video?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#JobIntroVideoURL').val('');
                    updateVideoUI();
                    Swal.fire('Deleted!', 'Video has been removed.', 'success');
                }
            });
        });
    }

    function toggleSalaryFields() {
        if ($('#IsSalaryHidden').is(':checked')) {
            $('.xj-salary').hide();
            $('#SalaryText').show();
        } else {
            $('.xj-salary').show();
            $('#SalaryText').hide();
        }
    }

    function toggleCityFields() {
        if ($('#IsCityHidden').is(':checked')) {
            // Remote work allowed: Hide City input, Show CityText (Remote)
            $('.show-city').eq(0).addClass('d-none');
            $('.show-city').eq(1).removeClass('d-none');
        } else {
            // Remote work NOT allowed: Show City input, Hide CityText
            $('.show-city').eq(0).removeClass('d-none');
            $('.show-city').eq(1).addClass('d-none');
        }
    }

    function setupSortable() {
        if (!$.fn.sortable) {
            console.warn('jQuery UI Sortable is not loaded');
            return;
        }
        $("#tBody").sortable({
            handle: ".drag-handle",
            update: function (event, ui) {
                var ids = [];
                $('#tBody tr').each(function (index) {
                    ids.push($(this).data('id'));
                    // Update position number (2nd column)
                    $(this).find('td:eq(1)').text(index + 1);
                });

                // Save order
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_move_interview_questions',
                        nonce: xenhireAjax.nonce,
                        requirement_id: currentJobId,
                        question_ids: ids.join('|')
                    },
                    success: function () {
                        toastr.options = {
                            "closeButton": false,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": false,
                            "positionClass": "toast-top-center",
                            "preventDuplicates": false,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "timeOut": "5000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                        };
                        toastr.success("Position Changed");
                    }
                });
            }
        });
    }

    // Helper to set image and update UI
    function setImage(inputID, imageURL, previewDivID) {
        $('#' + inputID).val(imageURL);
        if (imageURL) {
            $('#' + previewDivID).css('background-image', 'url(' + imageURL + ')');
            $('#' + previewDivID).closest('.xb-upload-area').addClass('has-image');
        } else {
            $('#' + previewDivID).css('background-image', 'none');
            $('#' + previewDivID).closest('.xb-upload-area').removeClass('has-image');
        }
    }

    // --- Data Loading ---
    function loadJobDetails(id) {
        $('.xj-form').addClass('loading');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_job_details',
                nonce: xenhireAjax.nonce,
                job_id: id
            },
            success: function (res) {
                $('.xj-form').removeClass('loading');

                if (res.success && res.data) {
                    var job = res.data;

                    // Populate fields - Use setTimeout to ensure CBOs are loaded first
                    setTimeout(function () {
                        // Set select values after CBOs are loaded
                        $('#EmployerID').val(job.EmployerID);
                        $('#CurrencyID').val(job.CurrencyID);
                        $('#EmploymentTypeID').val(job.EmploymentTypeID);
                    }, 800);

                    $('#JobTitle').val(job.JobTitle);
                    $('#WorkExMin').val(job.WorkExMin);
                    $('#WorkExMax').val(job.WorkExMax);
                    $('#Keywords').val(job.Keywords);
                    $('#SalaryFrom').val(job.SalaryFrom);
                    $('#SalaryTo').val(job.SalaryTo);
                    $('#SalaryType').val(job.SalaryType);
                    $('#SalaryText').val(job.SalaryText || 'Negotiable');
                    $('#FunctionalArea').val(job.FunctionalArea);
                    $('#City').val(job.City);

                    // CKEditor fields - Retry mechanism to ensure editors are loaded
                    var attempts = 0;
                    var maxAttempts = 50; // 5 seconds
                    var ckInterval = setInterval(function () {
                        attempts++;
                        var descEditor = window.ckEditors && window.ckEditors['JobDescription'];
                        var roleEditor = window.ckEditors && window.ckEditors['JobRole'];

                        if (descEditor && roleEditor) {
                            descEditor.setData(decodeHtml(job.JobDescription || ''));
                            roleEditor.setData(decodeHtml(job.JobRole || ''));
                            clearInterval(ckInterval);
                        } else if (attempts >= maxAttempts) {
                            clearInterval(ckInterval);
                            console.error('CKEditors failed to load in time for data population');
                            // Fallback: try to set one last time if at least one exists
                            if (descEditor) descEditor.setData(decodeHtml(job.JobDescription || ''));
                            if (roleEditor) roleEditor.setData(decodeHtml(job.JobRole || ''));
                        }
                    }, 100);

                    // Update Header Title
                    if (job.JobTitle) {
                        $('#xj-header-job-title').text('(' + job.JobTitle + ')');
                    }

                    // Helper to decode HTML entities
                    function decodeHtml(html) {
                        var txt = document.createElement("textarea");
                        txt.innerHTML = html;
                        return txt.value;
                    }

                    // Checkboxes
                    $('#IsSalaryHidden').prop('checked', job.IsSalaryHidden == '1');
                    toggleSalaryFields();
                    $('#IsCityHidden').prop('checked', job.IsCityHidden == '1');
                    toggleCityFields();
                    $('#IsShowApplicationsCount').prop('checked', job.IsShowApplicationsCount == '1');
                    $('#IsInterview').prop('checked', job.IsInterview == '1');
                    $('#IsInterviewRealtime').prop('checked', job.IsInterviewRealtime == '1');
                    $('#IsEnableAIScoring').prop('checked', job.IsEnableAIScoring == '1');
                    $('#IsInterviewVideoRedoAllowed').prop('checked', job.IsInterviewVideoRedoAllowed == '1');
                    $('#IsUploadResume').prop('checked', job.IsUploadResume == '1');
                    $('#JobIntroVideoURL').val(job.IntroVideoURL);
                    updateVideoUI();

                    // Social Media Preview Image
                    setImage('SocialPreviewURL', job.OGImage, 'xb_social_preview');

                    // Additional Settings
                    $('#DeadlineDatestamp').val(job.DeadlineDatestamp ? job.DeadlineDatestamp.split('T')[0] : '');
                    $('#EmailMain').val(job.EmailMain);
                    $('#EmailCC').val(job.EmailCC);
                    $('#PhoneMain').val(job.PhoneMain);
                    $('#AutoStartInSeconds').val(job.AutoStartInSeconds);


                    // Extra Data
                    for (var i = 1; i <= 4; i++) {
                        $('#ExtraData' + i + 'Label').val(job['ExtraData' + i + 'Label']);
                        $('#ExtraData' + i + 'Type').val(job['ExtraData' + i + 'Type']);

                        var optsVal = job['ExtraData' + i + 'Options'];
                        // Decode HTML entities just in case (e.g. &quot; -> ")
                        if (optsVal) {
                            optsVal = decodeHtml(optsVal);
                        }

                        var input = document.querySelector('#ExtraData' + i + 'Options');
                        if (input && input.tagify) {
                            input.tagify.removeAllTags();
                            try {
                                // If stored as JSON string, Tagify might handle it, or we parse it
                                // Tagify expects array of strings or objects
                                var parsed = optsVal;
                                try {
                                    parsed = JSON.parse(optsVal);
                                } catch (e) {
                                    // If not JSON, try splitting by pipe or comma
                                    if (optsVal.indexOf('|') > -1) {
                                        parsed = optsVal.split('|');
                                    } else if (optsVal.indexOf(',') > -1) {
                                        parsed = optsVal.split(',');
                                    } else {
                                        parsed = [optsVal];
                                    }
                                }

                                if (parsed) input.tagify.addTags(parsed);
                            } catch (e) {
                                console.error('Error adding tags', e);
                            }
                        } else {
                            $('#ExtraData' + i + 'Options').val(optsVal);
                        }
                        $('#ExtraData' + i + 'Mandatory').prop('checked', job['ExtraData' + i + 'Mandatory'] == '1');

                        if (job['ExtraData' + i + 'Type'] == '3') {
                            $('#ExtraData' + i + 'OptionsContainer').show();
                        }
                    }
                }
            }
        });
    }

    function loadInterviewQuestions(id) {
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_list_interview_questions',
                nonce: xenhireAjax.nonce,
                requirement_id: id
            },
            success: function (res) {
                if (res.success && res.data && res.data.length > 0) {
                    $('.xj-empty').hide();
                    $('.xj-q-wrap').show();
                    renderQuestions(res.data);
                } else {
                    $('.xj-empty').show();
                    $('.xj-q-wrap').hide();
                }
            }
        });
    }

    function renderQuestions(questions) {
        loadedQuestions = questions;
        var html = '';

        // Toggle Actions Header Visibility
        if (typeof isRestricted !== 'undefined' && isRestricted) {
            $('#tHead th:eq(4)').hide();
        } else {
            $('#tHead th:eq(4)').show();
        }

        questions.forEach(function (q, index) {
            html += '<tr data-id="' + q.ID + '">';
            html += '<td style="width: 30px;"><i title="Drag up/down to reorder" class="ki-duotone ki-arrow-up-down ki-fs-3 drag-handle" style="cursor: move;"><span class="path1"></span><span class="path2"></span></i></td>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + (q.QuestionType || 'Video') + '</td>';
            html += '<td>' + (q.Name || q.Question) + '</td>';

            if (typeof isRestricted !== 'undefined' && isRestricted) {
                // Do not render actions cell if restricted
            } else {
                html += '<td style="width: 120px; text-align:right;">' +
                    '<button type="button" class="btn-action edit btn-edit-ques" data-id="' + q.ID + '">Edit</button>' +
                    '<button type="button" class="btn-action delete btn-del-ques" data-id="' + q.ID + '">Delete</button>' +
                    '</td>';
            }
            html += '</tr>';
        });
        $('#tBody').html(html);
    }

    function checkApplicationCount(jobId) {
        if (!jobId || jobId <= 0) return;

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_list_applications',
                nonce: xenhireAjax.nonce,
                job_id: jobId,
                page_size: 1
            },
            success: function (res) {
                if (res.success && res.data && res.data.metadata) {
                    var count = parseInt(res.data.metadata.TotalRecordCount) || 0;
                    if (count > 0) {
                        isRestricted = true;
                        // Show warning
                        $('#xj-ques-warning').show();

                        // Disable add buttons
                        $('#xj-add-ques').hide();
                        $('#xj-ai-ques').hide();

                        // Re-render questions if they are already loaded
                        if (loadedQuestions.length > 0) {
                            renderQuestions(loadedQuestions);
                        }

                        // Disable drag sort
                        // if ($("#tBody").sortable("instance")) {
                        //     $("#tBody").sortable("disable");
                        //     $(".drag-handle").css('cursor', 'default').css('opacity', '0.5');
                        // }
                    }
                }
            }
        });
    }

    // --- Actions ---
    function validateJobDetails() {
        // 1. Employer
        var employer = $('#EmployerID').val();
        if (!employer) {
            Swal.fire('Required', 'Please select an Employer', 'warning');
            return false;
        }

        // 2. Job Title
        var title = $('#JobTitle').val();
        if (!title || title.trim() === '') {
            Swal.fire('Required', 'Please enter a Job Title', 'warning');
            $('#JobTitle').focus();
            return false;
        }

        // 3. Job Description (CKEditor)
        var desc = (window.ckEditors && window.ckEditors['JobDescription']) ? window.ckEditors['JobDescription'].getData() : '';
        if (!desc || desc.trim() === '') {
            Swal.fire('Required', 'Please enter Job Description', 'warning');
            return false;
        }

        // 4. Job Responsibilities (CKEditor)
        var role = (window.ckEditors && window.ckEditors['JobRole']) ? window.ckEditors['JobRole'].getData() : '';
        if (!role || role.trim() === '') {
            Swal.fire('Required', 'Please enter Job Responsibilities', 'warning');
            return false;
        }

        // 5. Required Skills
        var skills = $('#Keywords').val();
        if (!skills || skills.trim() === '') {
            Swal.fire('Required', 'Please enter Required Skills', 'warning');
            $('#Keywords').focus();
            return false;
        }

        // 6. Employment Type
        var employmentType = $('#EmploymentTypeID').val();
        if (!employmentType || employmentType == 0) {
            Swal.fire('Required', 'Please select an Employment Type', 'warning');
            return false;
        }

        return true;
    }

    function saveJob(isPublish) {
        var btn = isPublish ? $('#xj_publish') : $('#xj_save');
        isPublish = isPublish || false;

        // Validate Mandatory Fields
        if (!validateJobDetails()) {
            return;
        }

        btn.prop('disabled', true);

        // Get CKEditor content
        var desc = (window.ckEditors && window.ckEditors['JobDescription']) ? window.ckEditors['JobDescription'].getData() : '';
        var role = (window.ckEditors && window.ckEditors['JobRole']) ? window.ckEditors['JobRole'].getData() : '';

        var data = {
            action: 'xenhire_set_requirement',
            nonce: xenhireAjax.nonce,
            ID: currentJobId,
            EmployerID: $('#EmployerID').val(),
            JobTitle: $('#JobTitle').val(),
            WorkExMin: $('#WorkExMin').val(),
            WorkExMax: $('#WorkExMax').val(),
            JobDescription: desc,
            JobRole: role,
            Keywords: $('#Keywords').val(),
            CurrencyID: $('#CurrencyID').val(),
            SalaryFrom: $('#IsSalaryHidden').is(':checked') ? 0 : $('#SalaryFrom').val(),
            SalaryTo: $('#IsSalaryHidden').is(':checked') ? 0 : $('#SalaryTo').val(),
            SalaryType: $('#SalaryType').val(),
            SalaryText: $('#SalaryText').val(),
            IsSalaryHidden: $('#IsSalaryHidden').is(':checked') ? 1 : 0,
            FunctionalArea: $('#FunctionalArea').val(),
            EmploymentTypeID: $('#EmploymentTypeID').val(),
            City: $('#City').val(),
            IsCityHidden: $('#IsCityHidden').is(':checked') ? 1 : 0,
            IsActive: isPublish ? 1 : 0
        };

        // Add Extra Data
        for (var i = 1; i <= 4; i++) {
            data['ExtraData' + i + 'Label'] = $('#ExtraData' + i + 'Label').val();
            data['ExtraData' + i + 'Type'] = $('#ExtraData' + i + 'Type').val();
            data['ExtraData' + i + 'Options'] = $('#ExtraData' + i + 'Options').val();
            data['ExtraData' + i + 'Mandatory'] = $('#ExtraData' + i + 'Mandatory').is(':checked') ? 1 : 0;
        }

        // Add Additional Settings
        data.DeadlineDatestamp = $('#DeadlineDatestamp').val();
        data.EmailMain = $('#EmailMain').val();
        data.EmailCC = $('#EmailCC').val();
        data.PhoneMain = $('#PhoneMain').val();
        data.IsShowApplicationsCount = $('#IsShowApplicationsCount').is(':checked') ? 1 : 0;
        data.IsUploadResume = $('#IsUploadResume').is(':checked') ? 1 : 0;
        data.IntroVideoURL = $('#JobIntroVideoURL').val();
        data.OGImage = $('#SocialPreviewURL').val(); // Save Social Preview Image
        data.IsInterview = $('#IsInterview').is(':checked') ? 1 : 0;
        data.IsInterviewRealtime = $('#IsInterviewRealtime').is(':checked') ? 1 : 0;
        data.IsEnableAIScoring = $('#IsEnableAIScoring').is(':checked') ? 1 : 0;
        data.IsInterviewVideoRedoAllowed = $('#IsInterviewVideoRedoAllowed').is(':checked') ? 1 : 0;
        data.AutoStartInSeconds = $('#AutoStartInSeconds').val();
        data.NoOfQuestions = $('#NoOfQuestions').val() || 0;

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: data,
            success: function (res) {
                btn.prop('disabled', false);

                if (res.success) {
                    currentJobId = res.data.job_id || res.data.jobid || res.data.RequirementID || res.data.ID;

                    if (!currentJobId || currentJobId <= 0) {
                        console.error('Save Job: ID not found in response', res);
                        // Show debug info to user
                        var debugInfo = res.data.debug_raw ? JSON.stringify(res.data.debug_raw) : 'No debug info';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Saving Job',
                            text: 'Could not retrieve Job ID. Response: ' + debugInfo,
                            footer: 'Please share this error with support.'
                        });
                        return;
                    }

                    $('#RequirementID').val(currentJobId);

                    if (isPublish) {
                        // Show Instruction Modal
                        $('#kt_modal_instruction').modal('show');

                        // Bind Preview Button
                        var localPreviewUrl = xenhireAjax.site_url + '/jobs/?job_id=' + currentJobId;

                        $('#btnPreview').off('click').click(function () {
                            window.open(localPreviewUrl, '_blank');
                        });

                        // Keep the original PreviewURL for the invite link if needed, or use local?
                        // The invite link usually points to the actual application page (external)
                        // But if we want the invite to point to our local page, we should use localPreviewUrl.
                        // However, the invite modal logic uses the passed URL for the "Apply Link".
                        // Let's use the API provided URL for invites (as it likely points to the actual interview app)
                        // and the local URL for "Preview Job" button.

                        // UPDATE: User request implies "Preview Job" button specifically.
                        // "Invite Candidates" modal uses the URL passed to openInviteModal.
                        // If we want the invite link to also be the local page, we should change it here too.
                        // But usually invite links go to the interview platform.
                        // Let's stick to changing ONLY the Preview Job button behavior as requested.

                        $('#btnInvite').data('PreviewURL', res.data.PreviewURL);

                        $('#btnInvite').off('click').click(function () {
                            openInviteModal(res.data.PreviewURL);
                        });

                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Job details saved successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () {
                            // Switch to questions tab
                            $('.xj-tab-btn[data-tab="questions"]').click();
                        });
                    }
                } else {
                    console.error('Save Job Failed:', res);
                    if (res.data.message && res.data.message.indexOf('Subscription') !== -1) {
                        Swal.fire('Subscription Required', res.data.message, 'warning');
                    } else {
                        Swal.fire('Error', res.data.message || 'Failed to save job', 'error');
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Save Job AJAX Error:', error, xhr);
                btn.prop('disabled', false);
                Swal.fire('Error', 'Network error occurred', 'error');
            }
        });
    }

    function generateAIDescription() {
        var title = $('#JobTitle').val();
        if (!title) {
            Swal.fire('Required', 'Please enter a Job Title first', 'warning');
            return;
        }

        var btn = $('#xj-ai-desc');
        btn.prop('disabled', true);

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_generate_ai_description',
                nonce: xenhireAjax.nonce,
                jobtitle: title
            },
            success: function (res) {
                btn.prop('disabled', false);

                if (res.success && res.data.description) {
                    if (window.ckEditors && window.ckEditors['JobDescription']) {
                        window.ckEditors['JobDescription'].setData(res.data.description);
                    }
                } else {
                    Swal.fire('Error', 'Failed to generate description', 'error');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                Swal.fire('Error', 'Network error', 'error');
            }
        });
    }

    function generateAIQuestions() {
        if (currentJobId <= 0) {
            Swal.fire('Required', 'Please save the job first', 'warning');
            return;
        }

        var btn = $('#xj-ai-ques');
        var btnEmpty = $('#xj-ai-ques-empty');
        btn.prop('disabled', true);
        btnEmpty.prop('disabled', true);

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_suggest_ai_questions',
                nonce: xenhireAjax.nonce,
                jobid: currentJobId
            },
            success: function (res) {
                btn.prop('disabled', false);
                btnEmpty.prop('disabled', false);

                if (res.success) {
                    loadInterviewQuestions(currentJobId);
                    Swal.fire('Success', 'Questions generated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to generate questions', 'error');
                }
            }
        });
    }

    function deleteQuestion(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete it',
            cancelButtonText: 'No, Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_delete_interview_question',
                        nonce: xenhireAjax.nonce,
                        question_id: id
                    },
                    success: function (res) {
                        if (res.success) {
                            loadInterviewQuestions(currentJobId);
                        } else {
                            Swal.fire('Error', 'Failed to delete question', 'error');
                        }
                    }
                });
            }
        });
    }

    function publishJob() {
        // Publish is essentially saving with IsActive = 1
        // We can reuse saveJob logic but override IsActive
        saveJob(true);
    }

    // --- Modal Logic ---

    function openQuestionModal() {
        // Check if interview attended and get default data
        if (currentJobId > 0) {
            $.ajax({
                url: xenhireAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'xenhire_check_interview_attended',
                    nonce: xenhireAjax.nonce,
                    RequirementID: currentJobId
                },
                success: function (res) {
                    if (res.success) {
                        var data = res.data.data;

                        // Check if attended
                        if (data && data.IsInterviewAttended) {
                            Swal.fire('Restricted', 'Interviews have already been attended for this job. You cannot add new questions.', 'warning');
                            return;
                        }

                        // Populate fields if data exists
                        // Note: We don't know exact field names from API, assuming standard ones or based on user request
                        // If the user wants to populate specific fields, they should match the form IDs
                        // For now, we pass the data to showQuestionModal to handle population
                        showQuestionModal(data);
                    } else {
                        // If error, just show modal empty? Or show error?
                        // User said "populate data", implies success path. 
                        // If check fails, maybe just open empty?
                        showQuestionModal();
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Failed to verify interview status.', 'error');
                }
            });
        } else {
            showQuestionModal();
        }
    }

    function showQuestionModal(data) {
        // Reset form
        $('#kt_modal_ques_form')[0].reset();
        $('#kt_modal_ques_form #ID').val('-1');
        $('#headTitle').text('Add Interview Question');

        // Populate defaults from API data if available
        if (data) {
            if (data.MaxSeconds) $('#MaxSeconds').val(data.MaxSeconds);
            if (data.IsNotAIScore !== undefined) $('#IsNotAIScore').prop('checked', data.IsNotAIScore == 1 || data.IsNotAIScore == true);
            // Add other fields here if API returns them
        }

        // Helper to set default video
        var setDefaultVideo = function () {
            var videoVal = '';
            $('#QuestionTypeID option').each(function () {
                if ($(this).text().toLowerCase().indexOf('video') !== -1) {
                    videoVal = $(this).val();
                    return false;
                }
            });
            if (videoVal) {
                $('#QuestionTypeID').val(videoVal).trigger('change');
            } else {
                $('#QuestionTypeID').val('').trigger('change');
            }
        };

        // Load Question Types if empty
        if ($('#QuestionTypeID').length === 0) {
            loadCBO('QuestionType', 'ct-QuestionTypeID', 'QuestionTypeID', 'form-select form-select-lg form-select-solid', function () {
                setDefaultVideo();
            });
        } else {
            setDefaultVideo();
        }

        // Show modal
        $('#kt_modal_ques').addClass('show').show();
    }

    function editQuestion(id) {
        // Find question data from loadedQuestions
        // Ensure ID comparison handles string/number mismatch
        var q = loadedQuestions.find(function (item) { return item.ID == id; });

        if (!q) {
            console.error('Question data not found for ID: ' + id);
            return;
        }

        $('#headTitle').text('Edit Question');
        $('#kt_modal_ques_form #ID').val(q.ID);
        $('#kt_modal_ques_form #Name').val(q.Name || q.Question);
        $('#kt_modal_ques_form #Description').val(q.Description || '');

        // Handle MaxSeconds (e.g., "2 mins" -> 120)
        var seconds = q.MaxSeconds;
        if (typeof seconds === 'string' && seconds.indexOf('min') !== -1) {
            var mins = parseInt(seconds);
            if (!isNaN(mins)) {
                seconds = mins * 60;
            }
        }
        $('#kt_modal_ques_form #MaxSeconds').val(String(seconds)).trigger('change');

        $('#kt_modal_ques_form #IsNotAIScore').prop('checked', q.IsNotAIScore == '1' || q.IsNotAIScore === true);

        // Load types if needed, then set value
        var setType = function () {
            var typeVal = q.QuestionTypeID;

            // If ID is missing but we have text (e.g. "Short Text"), find option by text
            if (!typeVal && q.QuestionType) {
                $('#QuestionTypeID option').each(function () {
                    if ($(this).text().trim() === q.QuestionType) {
                        typeVal = $(this).val();
                        return false; // break
                    }
                });
            }

            if (typeVal) {
                $('#QuestionTypeID').val(String(typeVal)).trigger('change');
            }
        };

        if ($('#QuestionTypeID').length === 0) {
            loadCBO('QuestionType', 'ct-QuestionTypeID', 'QuestionTypeID', 'form-select form-select-lg form-select-solid', function () {
                setType();
            });
        } else {
            setType();
        }

        $('#kt_modal_ques').addClass('show').show();
    }

    function saveQuestion() {
        var btn = $('#kt_modal_ques_submit');
        var form = $('#kt_modal_ques_form');

        // Validation
        var type = $('#QuestionTypeID').val();
        var question = $('#Name').val();

        if (!type) {
            Swal.fire('Required', 'Please select Question Type', 'warning');
            return;
        }
        if (!question) {
            Swal.fire('Required', 'Please enter Question', 'warning');
            return;
        }

        btn.prop('disabled', true);

        var data = {
            action: 'xenhire_save_interview_question',
            nonce: xenhireAjax.nonce,
            RequirementID: currentJobId,
            ID: $('#kt_modal_ques_form #ID').val(),
            QuestionTypeID: type,
            Question: question,
            Description: $('#Description').val(),
            MaxSeconds: $('#MaxSeconds').val(),
            IsNotAIScore: $('#IsNotAIScore').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: data,
            success: function (res) {
                btn.prop('disabled', false);

                if (res.success) {
                    $('#kt_modal_ques').removeClass('show').hide();
                    loadInterviewQuestions(currentJobId);

                    Swal.fire({
                        title: 'Interview Question Saved',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Add More',
                        cancelButtonText: 'Close',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-light'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Add More: Open modal again
                            openQuestionModal();
                        } else {
                            // Close: Do nothing (modal already closed)
                        }
                    });
                } else {
                    Swal.fire('Error', res.data.message || 'Failed to save question', 'error');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                Swal.fire('Error', 'Network error', 'error');
            }
        });
    }


    function toggleQuestionFields() {
        var typeId = $('#QuestionTypeID').val();
        // Logic for showing/hiding fields based on type
        // Assuming:
        // 1 = Video (Default)
        // 2 = Text
        // 3 = Multiple Choice (needs Options)
        // 4 = Code (needs Description)
        // You might need to adjust these IDs based on actual CBO values

        // Reset
        $('#divDescription').addClass('d-none');
        $('#divAnswerTime').addClass('d-none');

        // TODO: Update these IDs based on actual QuestionType CBO values
        // For now, let's assume if text contains "Choice" show options, etc.
        var typeText = $('#QuestionTypeID option:selected').text().toLowerCase();

        if (typeText.indexOf('code') !== -1 || typeText.indexOf('programming') !== -1) {
            $('#divDescription').removeClass('d-none');
        }

        // Show Answer Time only if Video
        if (typeText.indexOf('video') !== -1) {
            $('#divAnswerTime').removeClass('d-none');
        }
    }

    function openInviteModal(url) {
        // Close the instruction modal if open
        $('#kt_modal_instruction').modal('hide');

        // Use WordPress Jobs page URL instead of API PreviewURL
        // Assuming url passed might be the API one, we construct local one
        // But wait, openInviteModal is called with res.data.PreviewURL in saveJob
        // We should override it here or in saveJob.
        // Let's override it here to be safe and consistent.
        var localUrl = xenhireAjax.site_url + '/jobs/?job_id=' + currentJobId;

        $('#InviteJobLink').val(localUrl);
        $('#kt_modal_invite').modal('show');

        // Setup social share links
        $('.share-item.linkedin').attr('href', 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(localUrl));
        $('.share-item.facebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(localUrl));
        $('.share-item.whatsapp').attr('href', 'https://api.whatsapp.com/send?text=Check out this job opportunity: ' + encodeURIComponent(localUrl));
        $('.share-item.twitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(localUrl) + '&text=Check out this job opportunity!');

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

        // Initialize CKEditor for InviteText if not already done
        if (!window.ckEditors['InviteText']) {
            ClassicEditor
                .create(document.querySelector('#InviteText'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    removePlugins: ['MediaEmbed']
                })
                .then(editor => {
                    window.ckEditors['InviteText'] = editor;
                    setInviteText(editor, localUrl);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            setInviteText(window.ckEditors['InviteText'], localUrl);
        }

        // Initialize CKEditor for InviteEmailBody if not already done
        if (!window.ckEditors['InviteEmailBody']) {
            ClassicEditor
                .create(document.querySelector('#InviteEmailBody'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                    removePlugins: ['MediaEmbed']
                })
                .then(editor => {
                    window.ckEditors['InviteEmailBody'] = editor;
                    setInviteEmail(editor, localUrl);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            setInviteEmail(window.ckEditors['InviteEmailBody'], localUrl);
        }

        // Copy Link
        $('#btnCopyLink').off('click').click(function () {
            var copyText = document.getElementById("InviteJobLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            navigator.clipboard.writeText(copyText.value).then(function () {
                toastr.success('Copied', '', { positionClass: 'toast-top-center' });
            });
        });

        // Share Link Dropdown is handled by Bootstrap data-bs-toggle="dropdown"
    }

    function getRandomColor() {
        function c() {
            var hex = Math.floor(Math.random() * 256).toString(16);
            return ("0" + String(hex)).substr(-2); // pad with zero
        }
        return "#" + c() + c() + c();
    }

    function setInviteText(editor, url) {
        var jobTitle = $('#JobTitle').val();
        var employer = $('#EmployerID option:selected').text();

        var content = `
            <p>🚀 <strong>We're Hiring! Become a ${jobTitle} at ${employer}!</strong> ☕</p>
            <p>Are you looking for a new opportunity? We want <strong>YOU</strong> to join our team!</p>
            <p><strong>How to Apply:</strong></p>
            <ol>
                <li>Visit <a href="${url}">this link</a></li>
                <li>Log in with your email and verify</li>
                <li>Record your responses to our questions</li>
            </ol>
            <p>Don't miss out on this chance to be a part of an amazing team! Complete your video interview at your convenience. If you have any questions, our team is here to help.</p>
            <p>We can't wait to meet you! 😊</p>
            <p>#Hiring #JobOpportunity #JoinOurTeam #CareerOpportunity #NowHiring</p>
        `;
        editor.setData(content);
    }

    function setInviteEmail(editor, url) {
        var jobTitle = $('#JobTitle').val();
        var employer = $('#EmployerID option:selected').text();

        // Set Subject
        $('#InviteSubject').val(`Invitation for Interview for ${jobTitle} at ${employer}`);

        var content = `
            <p>Dear Candidate,</p>
            <p>Congratulations! You have been chosen to interview for the <strong>${jobTitle}</strong> position at <strong>${employer}</strong>. We believe you could be a great fit for our team.</p>
            <p>To move forward, we would like to invite you to participate in the interview of our selection process.</p>
            <p>Instructions to appear for the interview:</p>
            <p>Steps to Complete:</p>
            <ol>
                <li>Click on the link <a href="${url}">${url}</a> to start</li>
                <li>Login with your email and verify</li>
                <li>Record your responses to our questions</li>
            </ol>
            <p>Please complete the interview at your earliest convenience. If you have any questions or need assistance, our team is here to help.</p>
            <p>We are excited to learn more about you and look forward to your participation.</p>
            <p>Best regards,</p>
            <p>HR Team<br>${employer}</p>
        `;
        editor.setData(content);
    }

    // Submit Email Invite
    $(document).on('click', '#btnInviteSubmit', function () {
        var btn = $(this);
        var emailsVal = $('#InviteCCEmails').val();
        var subject = $('#InviteSubject').val();
        var body = window.ckEditors['InviteEmailBody'].getData();

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

        // Basic Validation
        if (!subject || !body) {
            Swal.fire('Required', 'Subject and Body are required', 'warning');
            return;
        }

        btn.prop('disabled', true).text('Sending...');

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
            success: function (res) {
                btn.prop('disabled', false).text('Submit');

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sent!',
                        text: 'Invitations sent successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () {
                        $('#kt_modal_invite').modal('hide');
                    });
                } else {
                    Swal.fire('Error', res.data.message || 'Failed to send email', 'error');
                }
            },
            error: function (xhr, status, error) {
                btn.prop('disabled', false).text('Submit');
                console.error('Send Email Error:', error, xhr);
                Swal.fire('Error', 'Network error occurred', 'error');
            }
        });
    });


    // --- Video Logic (Ported from Branding) ---

    function resetVideoModal() {
        $('#xj_video_file').val('');
        $('#xj_file_name').text('No file chosen');
        $('#xj_save_uploaded_video').prop('disabled', true);

        // Reset recorder
        stopStream();
        $('#xj_recorder_preview').hide();
        $('#xj_recorder_placeholder').show();
        $('#xj_start_recording').show();
        $('#xj_stop_recording').hide();
        $('#xj_save_recorded_video').hide();
        $('#xj_timer').hide().text('00:00 / 03:00');
        recordedChunks = [];
    }

    async function startRecording() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            var videoElement = document.getElementById('xj_recorder_preview');
            videoElement.srcObject = stream;

            $('#xj_recorder_placeholder').hide();
            $(videoElement).show();

            mediaRecorder = new MediaRecorder(stream);
            recordedChunks = [];

            mediaRecorder.ondataavailable = function (e) {
                if (e.data.size > 0) {
                    recordedChunks.push(e.data);
                }
            };

            mediaRecorder.start();
            $('#xj_start_recording').hide();

            var stopBtn = $('#xj_stop_recording');
            stopBtn.show().prop('disabled', true).html('<span class="dashicons dashicons-controls-pause"></span> Wait 10s...');

            $('#xj_timer').show();

            // Timer
            recordingTime = 0;
            recordingTimer = setInterval(function () {
                recordingTime++;
                var mins = Math.floor(recordingTime / 60);
                var secs = recordingTime % 60;
                $('#xj_timer').text((mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs + ' / 03:00');

                if (recordingTime < 10) {
                    stopBtn.html('<span class="dashicons dashicons-controls-pause"></span> Wait ' + (10 - recordingTime) + 's...');
                } else if (recordingTime === 10) {
                    stopBtn.prop('disabled', false).html('<span class="dashicons dashicons-controls-pause"></span> Stop Recording');
                }

                if (recordingTime >= maxRecordingTime) {
                    stopRecording();
                }
            }, 1000);

        } catch (err) {
            console.error("Error accessing media devices.", err);
            Swal.fire("Error", "Could not access camera/microphone. Please allow permissions.", "error");
        }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            clearInterval(recordingTimer);

            stopStream();

            $('#xj_stop_recording').hide();
            $('#xj_save_recorded_video').show();
        }
    }

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function uploadVideo(file) {
        var formData = new FormData();
        formData.append('action', 'xenhire_upload_video');
        formData.append('nonce', xenhireAjax.nonce);
        if (file.name) {
            formData.append('video_file', file);
        } else {
            formData.append('video_file', file, 'recorded-video.webm');
        }

        var btn = $('#xj_save_uploaded_video');
        var btnRec = $('#xj_save_recorded_video');

        btn.prop('disabled', true).text('Uploading...');
        btnRec.prop('disabled', true).text('Uploading...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                btn.prop('disabled', false).text('Save Video');
                btnRec.prop('disabled', false).text('Save Video');

                if (res.success) {
                    var url = res.data.url;
                    $('#JobIntroVideoURL').val(url);
                    updateVideoUI();
                    videoModal.hide();
                    Swal.fire("Success", "Video uploaded successfully!", "success");
                    resetVideoModal();
                } else {
                    Swal.fire("Error", res.data.message || "Failed to upload video", "error");
                }
            },
            error: function () {
                btn.prop('disabled', false).text('Save Video');
                btnRec.prop('disabled', false).text('Save Video');
                Swal.fire("Error", "Network error occurred", "error");
            }
        });
    }

    function updateVideoUI() {
        var url = $('#JobIntroVideoURL').val();
        if (url) {
            $('#xj_upload_video').text('Change Video');
            $('#xj_play_video').show();
            // $('#xj_delete_video').show(); // Hidden per user request
        } else {
            $('#xj_upload_video').text('Upload Video');
            $('#xj_play_video').hide();
            $('#xj_delete_video').hide();
        }
    }


});
