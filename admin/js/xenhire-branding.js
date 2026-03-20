/**
 * XenHire Branding Page JS
 */
jQuery(document).ready(function ($) {
    var industryData = [];
    var langTagify;

    var languageList = [
        { code: "af", value: "Afrikaans" }, { code: "sq", value: "Albanian" }, { code: "am", value: "Amharic" }, { code: "ar", value: "Arabic" },
        { code: "hy", value: "Armenian" }, { code: "as", value: "Assamese" }, { code: "ay", value: "Aymara" }, { code: "az", value: "Azerbaijani" },
        { code: "bm", value: "Bambara" }, { code: "eu", value: "Basque" }, { code: "be", value: "Belarusian" }, { code: "bn", value: "Bengali" },
        { code: "bho", value: "Bhojpuri" }, { code: "bs", value: "Bosnian" }, { code: "bg", value: "Bulgarian" }, { code: "ca", value: "Catalan" },
        { code: "ceb", value: "Cebuano" }, { code: "zh-CN", value: "Chinese (Simplified)" }, { code: "zh-TW", value: "Chinese (Traditional)" },
        { code: "co", value: "Corsican" }, { code: "hr", value: "Croatian" }, { code: "cs", value: "Czech" }, { code: "da", value: "Danish" },
        { code: "dv", value: "Dhivehi" }, { code: "doi", value: "Dogri" }, { code: "nl", value: "Dutch" }, { code: "eo", value: "Esperanto" },
        { code: "et", value: "Estonian" }, { code: "ee", value: "Ewe" }, { code: "fil", value: "Filipino (Tagalog)" }, { code: "fi", value: "Finnish" },
        { code: "fr", value: "French" }, { code: "fy", value: "Frisian" }, { code: "gl", value: "Galician" }, { code: "ka", value: "Georgian" },
        { code: "de", value: "German" }, { code: "el", value: "Greek" }, { code: "gn", value: "Guarani" }, { code: "gu", value: "Gujarati" },
        { code: "ht", value: "Haitian Creole" }, { code: "ha", value: "Hausa" }, { code: "haw", value: "Hawaiian" }, { code: "he", value: "Hebrew" },
        { code: "hi", value: "Hindi" }, { code: "hmn", value: "Hmong" }, { code: "hu", value: "Hungarian" }, { code: "is", value: "Icelandic" },
        { code: "ig", value: "Igbo" }, { code: "ilo", value: "Ilocano" }, { code: "id", value: "Indonesian" }, { code: "ga", value: "Irish" },
        { code: "it", value: "Italian" }, { code: "ja", value: "Japanese" }, { code: "jv", value: "Javanese" }, { code: "kn", value: "Kannada" },
        { code: "kk", value: "Kazakh" }, { code: "km", value: "Khmer" }, { code: "rw", value: "Kinyarwanda" }, { code: "gom", value: "Konkani" },
        { code: "ko", value: "Korean" }, { code: "kri", value: "Krio" }, { code: "ku", value: "Kurdish" }, { code: "ckb", value: "Kurdish (Sorani)" },
        { code: "ky", value: "Kyrgyz" }, { code: "lo", value: "Lao" }, { code: "la", value: "Latin" }, { code: "lv", value: "Latvian" },
        { code: "ln", value: "Lingala" }, { code: "lt", value: "Lithuanian" }, { code: "lg", value: "Luganda" }, { code: "lb", value: "Luxembourgish" },
        { code: "mk", value: "Macedonian" }, { code: "mai", value: "Maithili" }, { code: "mg", value: "Malagasy" }, { code: "ms", value: "Malay" },
        { code: "ml", value: "Malayalam" }, { code: "mt", value: "Maltese" }, { code: "mi", value: "Maori" }, { code: "mr", value: "Marathi" },
        { code: "mni-Mtei", value: "Meiteilon (Manipuri)" }, { code: "lus", value: "Mizo" }, { code: "mn", value: "Mongolian" },
        { code: "my", value: "Myanmar (Burmese)" }, { code: "ne", value: "Nepali" }, { code: "no", value: "Norwegian" }, { code: "ny", value: "Nyanja (Chichewa)" },
        { code: "or", value: "Odia (Oriya)" }, { code: "om", value: "Oromo" }, { code: "ps", value: "Pashto" }, { code: "fa", value: "Persian" },
        { code: "pl", value: "Polish" }, { code: "pt", value: "Portuguese (Portugal, Brazil)" }, { code: "pa", value: "Punjabi" }, { code: "qu", value: "Quechua" },
        { code: "ro", value: "Romanian" }, { code: "ru", value: "Russian" }, { code: "sm", value: "Samoan" }, { code: "sa", value: "Sanskrit" },
        { code: "gd", value: "Scots Gaelic" }, { code: "nso", value: "Sepedi" }, { code: "sr", value: "Serbian" }, { code: "st", value: "Sesotho" },
        { code: "sn", value: "Shona" }, { code: "sd", value: "Sindhi" }, { code: "si", value: "Sinhala (Sinhalese)" }, { code: "sk", value: "Slovak" },
        { code: "sl", value: "Slovenian" }, { code: "so", value: "Somali" }, { code: "es", value: "Spanish" }, { code: "su", value: "Sundanese" },
        { code: "sw", value: "Swahili" }, { code: "sv", value: "Swedish" }, { code: "tl", value: "Tagalog (Filipino)" }, { code: "tg", value: "Tajik" },
        { code: "ta", value: "Tamil" }, { code: "tt", value: "Tatar" }, { code: "te", value: "Telugu" }, { code: "th", value: "Thai" },
        { code: "ti", value: "Tigrinya" }, { code: "ts", value: "Tsonga" }, { code: "tr", value: "Turkish" }, { code: "tk", value: "Turkmen" },
        { code: "ak", value: "Twi (Akan)" }, { code: "uk", value: "Ukrainian" }, { code: "ur", value: "Urdu" }, { code: "ug", value: "Uyghur" },
        { code: "uz", value: "Uzbek" }, { code: "vi", value: "Vietnamese" }, { code: "cy", value: "Welsh" }, { code: "xh", value: "Xhosa" },
        { code: "yi", value: "Yiddish" }, { code: "yo", value: "Yoruba" }, { code: "zu", value: "Zulu" }
    ];

    // Initialize Tagify
    var input = document.querySelector('#OtherLanguages');
    if (input) {
        langTagify = new Tagify(input, {
            whitelist: languageList,
            enforceWhitelist: true,
            dropdown: {
                maxItems: 20,           // <- mixumum allowed rendered suggestions
                classname: "tags-look", // <- custom classname for this dropdown, so it could be targeted
                enabled: 0,             // <- show suggestions on focus
                closeOnSelect: false    // <- do not hide the suggestions dropdown once an item has been selected
            }
        });
    }

    // Initialize
    loadBrandingData();
    loadIndustryCBO();

    // ... Event Handlers ...

    function loadIndustryCBO() {
        var input = $('#Industry_Search');
        input.prop('disabled', true).val('Loading...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_cbo_items',
                nonce: xenhireAjax.nonce,
                key: 'Industry'
            },
            success: function (res) {
                if (res.success && res.data) {
                    industryData = [];

                    var items = res.data;

                    // Handle if data is a JSON string
                    if (typeof items === 'string') {
                        try {
                            items = JSON.parse(items);
                        } catch (e) {
                            console.error('Failed to parse Industry data string', e);
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
                        items.forEach(function (item) {
                            var value, text;
                            if (typeof item === 'string') {
                                value = item;
                                text = item;
                            } else if (typeof item === 'object') {
                                value = item.Value || item.Key || item.ID || item.id;
                                text = item.DisplayText || item.Text || item.Name || item.Value || item.description;
                            }
                            if (value && text && value != -1) {
                                industryData.push({ label: text, value: value });
                            }
                        });
                    } else {
                        console.error('Industry data is not an array', items);
                    }

                    // Initialize Autocomplete
                    input.val('').prop('disabled', false).attr('placeholder', 'Type to search industry...');

                    input.autocomplete({
                        source: industryData,
                        minLength: 0,
                        select: function (event, ui) {
                            event.preventDefault();
                            input.val(ui.item.label);
                            $('#Industry').val(ui.item.value);
                        },
                        focus: function (event, ui) {
                            event.preventDefault();
                            input.val(ui.item.label);
                        },
                        change: function (event, ui) {
                            if (!ui.item) {
                                if (input.val() === '') {
                                    $('#Industry').val('');
                                }
                            }
                        }
                    }).focus(function () {
                        $(this).autocomplete("search", "");
                    });

                    // If we have a saved value (ID), try to find label
                    var savedId = $('#Industry').val();
                    if (savedId) {
                        var found = industryData.find(function (i) { return i.value == savedId; });
                        if (found) {
                            input.val(found.label);
                        } else {
                            input.val(savedId);
                        }
                    }

                } else {
                    input.val('').attr('placeholder', 'Error loading industries');
                }
            },
            error: function () {
                input.val('').attr('placeholder', 'Error loading industries');
            }
        });
    }

    // --- Event Handlers ---

    // Save Buttons
    $('#xb_save_top, #xb_save_bottom').click(function (e) {
        e.preventDefault();
        saveBrandingData();
    });

    // Copy URL
    $('#xb_copy_url').click(function (e) {
        e.preventDefault();
        var url = $('#xb_preview_link').attr('href');
        navigator.clipboard.writeText(url).then(function () {
            var btn = $('#xb_copy_url');
            var originalText = btn.text();
            btn.text('Copied!');
            setTimeout(function () {
                btn.text(originalText);
            }, 2000);
        });
    });

    // Career Page URL Input - Update Preview
    $('#CareerPageURL').on('input', function () {
        var slug = $(this).val();
        $('#xb_url_slug').text(slug);
        $('#xb_preview_link').attr('href', '' + slug);
    });

    // Media Uploader
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

    // --- Functions ---

    function loadBrandingData() {
        $('.xb-form').addClass('loading'); // You might want to add a loading spinner style

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_get_branding',
                nonce: xenhireAjax.nonce
            },
            success: function (res) {
                if (res.success && res.data) {
                    var data = res.data;

                    // Populate fields
                    $('#CareerPageURL').val(data.CustomURL || data.CareerPageURL).trigger('input');
                    $('#BrandName').val(data.BrandName);
                    $('#CompanyName').val(data.CompanyName);
                    $('#Website').val(data.Website);

                    // Industry - Handle Autocomplete
                    if (data.Industry) {
                        $('#Industry').val(data.Industry);
                        // If industryData is already loaded, update label
                        if (typeof industryData !== 'undefined' && industryData.length > 0) {
                            var found = industryData.find(function (i) { return i.value == data.Industry; });
                            if (found) {
                                $('#Industry_Search').val(found.label);
                            } else {
                                $('#Industry_Search').val(data.Industry);
                            }
                        }
                    }

                    $('#AboutBrand').val(data.Description || data.AboutBrand);

                    // Colors
                    if (data.PrimaryColor) $('#PrimaryColor').val(data.PrimaryColor);
                    if (data.SecondaryColor) $('#SecondaryColor').val(data.SecondaryColor);
                    if (data.TagLineColor) $('#TagLineColor').val(data.TagLineColor);

                    // Images
                    setImage('FaviconURL', data.BrandLogoIMG || data.FaviconURL, 'xb_favicon_preview');
                    setImage('LogoURL', data.LogoIMG || data.LogoURL, 'xb_logo_preview');
                    setImage('SocialPreviewURL', data.OGImage || data.SocialPreviewURL, 'xb_social_preview');
                    setImage('BannerURL', data.BannerIMG || data.BannerURL, 'xb_banner_preview');

                    // Video
                    $('#IntroVideoURL').val(data.IntroVideoURL);
                    updateVideoUI();

                    // Toggles
                    $('#IsHiringMultipleBrands').prop('checked', (data.IsMultiBrand == '1' || data.IsHiringMultipleBrands == '1'));
                    $('#IsHideCityFilter').prop('checked', (data.IsHideSearchByCity == '1' || data.IsHideCityFilter == '1'));

                    // Languages
                    var savedLangs = data.TranslationLangs || data.OtherLanguages;
                    if (savedLangs && langTagify) {
                        var codes = savedLangs.split(',');
                        var tags = [];
                        codes.forEach(function (code) {
                            code = code.trim();
                            if (code) {
                                var found = languageList.find(function (l) { return l.code == code; });
                                if (found) {
                                    tags.push(found);
                                }
                            }
                        });
                        langTagify.removeAllTags();
                        langTagify.addTags(tags);
                    } else if (savedLangs) {
                        $('#OtherLanguages').val(savedLangs);
                    }
                }
            },
            error: function (err) {
                console.error('Failed to load branding data', err);
            },
            complete: function () {
                $('.xb-form').removeClass('loading');
            }
        });
    }

    function saveBrandingData() {
        var btn = $('#xb_save_bottom');
        btn.prop('disabled', true).text('Saving...');

        var otherLanguagesVal = $('#OtherLanguages').val();
        var otherLanguagesCodes = '';
        if (otherLanguagesVal) {
            try {
                var tags = JSON.parse(otherLanguagesVal);
                if (Array.isArray(tags)) {
                    otherLanguagesCodes = tags.map(function (t) { return t.code; }).join(',');
                }
            } catch (e) {
                otherLanguagesCodes = otherLanguagesVal;
            }
        }

        var data = {
            action: 'xenhire_save_branding',
            nonce: xenhireAjax.nonce,
            CareerPageURL: $('#CareerPageURL').val(),
            BrandName: $('#BrandName').val(),
            CompanyName: $('#CompanyName').val(),
            Website: $('#Website').val(),
            Industry: $('#Industry').val(),
            AboutBrand: $('#AboutBrand').val(),
            PrimaryColor: $('#PrimaryColor').val(),
            SecondaryColor: $('#SecondaryColor').val(),
            TagLineColor: $('#TagLineColor').val(),
            FaviconURL: $('#FaviconURL').val(),
            LogoURL: $('#LogoURL').val(),
            SocialPreviewURL: $('#SocialPreviewURL').val(),
            BannerURL: $('#BannerURL').val(),
            IntroVideoURL: $('#IntroVideoURL').val(),
            IsHiringMultipleBrands: $('#IsHiringMultipleBrands').is(':checked') ? 1 : 0,
            IsHideCityFilter: $('#IsHideCityFilter').is(':checked') ? 1 : 0,
            OtherLanguages: otherLanguagesCodes
        };

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: data,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Career page branding saved',
                        icon: 'success',
                        confirmButtonText: 'Okay'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: res.data.message || 'Unknown error',
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                }
            },
            error: function () {
                alert('Network error occurred.');
            },
            complete: function () {
                btn.prop('disabled', false).text('Save');
            }
        });
    }

    function setImage(inputId, url, previewId) {
        if (url) {
            $('#' + inputId).val(url);
            $('#' + previewId).css('background-image', 'url(' + url + ')');
            $('#' + previewId).closest('.xb-upload-area').addClass('has-image');
        }
    }

    // --- Video Upload & Recording Logic ---

    var videoModal = $('#xb_video_modal');
    var mediaRecorder;
    var recordedChunks = [];
    var stream;
    var recordingTimer;
    var recordingTime = 0;
    var maxRecordingTime = 180; // 3 minutes
    // Open Modal
    $('#xb_upload_video').click(function (e) {
        e.preventDefault();

        if (videoModal.length === 0) {
            videoModal = $('#xb_video_modal');
        }

        if (videoModal.length === 0) {
            console.error('Video modal not found');
            return;
        }

        videoModal.show();
        videoModal.css('display', 'flex');
        resetVideoModal();
    });

    // Close Modal
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
    });

    // File Selection
    $('#xb_video_file').change(function () {
        var file = this.files[0];
        if (file) {
            if (file.size > 40 * 1024 * 1024) { // 40MB
                alert('File size exceeds 40MB limit.');
                this.value = '';
                $('#xb_file_name').text('No file chosen');
                $('#xb_save_uploaded_video').prop('disabled', true);
            } else {
                $('#xb_file_name').text(file.name);
                $('#xb_save_uploaded_video').prop('disabled', false);
            }
        }
    });

    // Save Uploaded Video
    $('#xb_save_uploaded_video').click(function () {
        var file = $('#xb_video_file')[0].files[0];
        if (file) {
            uploadVideo(file);
        }
    });

    // Start Recording
    $('#xb_start_recording').click(function () {
        startRecording();
    });

    // Stop Recording
    $('#xb_stop_recording').click(function () {
        stopRecording();
    });

    // Save Recorded Video
    $('#xb_save_recorded_video').click(function () {
        var blob = new Blob(recordedChunks, { type: 'video/webm' });
        // Create a file from blob
        var file = new File([blob], "recorded-video.webm", { type: "video/webm" });
        uploadVideo(file);
    });

    function resetVideoModal() {
        $('#xb_video_file').val('');
        $('#xb_file_name').text('No file chosen');
        $('#xb_save_uploaded_video').prop('disabled', true);

        stopStream();

        var videoElement = $('#xb_recorder_preview')[0];
        if (videoElement.src) {
            window.URL.revokeObjectURL(videoElement.src);
        }
        videoElement.src = '';
        videoElement.srcObject = null;
        videoElement.controls = false;
        videoElement.muted = true;

        $('#xb_recorder_preview').hide();
        $('#xb_recorder_placeholder').show();
        $('#xb_recorder_placeholder').show();
        $('#xb_start_recording').show();
        $('#xb_stop_recording').hide().prop('disabled', false).html('<span class="dashicons dashicons-controls-stop"></span> Stop Recording');
        $('#xb_save_recorded_video').hide();
        $('#xb_timer').hide().text('00:00 / 03:00');
        recordedChunks = [];
    }

    async function startRecording() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            var videoElement = $('#xb_recorder_preview')[0];
            videoElement.srcObject = stream;
            videoElement.muted = true; // Mute during recording to prevent feedback
            videoElement.controls = false;
            $(videoElement).show();

            $('#xb_recorder_placeholder').hide();

            mediaRecorder = new MediaRecorder(stream);

            mediaRecorder.ondataavailable = function (e) {
                if (e.data.size > 0) {
                    recordedChunks.push(e.data);
                }
            };

            mediaRecorder.onstop = function () {
                stopStream();
                $('#xb_stop_recording').hide();
                $('#xb_save_recorded_video').show();
                clearInterval(recordingTimer);

                // Create Blob and set as src for preview
                var blob = new Blob(recordedChunks, { type: 'video/webm' });
                var videoURL = window.URL.createObjectURL(blob);
                var videoElement = $('#xb_recorder_preview')[0];

                videoElement.srcObject = null;
                videoElement.src = videoURL;
                videoElement.controls = true;
                videoElement.muted = false;
                videoElement.autoplay = false;
                // videoElement.play(); // Optional: Auto-play after stop
            };

            mediaRecorder.start();
            $('#xb_start_recording').hide();
            $('#xb_stop_recording').show().prop('disabled', true).html('<span class="dashicons dashicons-controls-stop"></span> Wait 10s...');

            // Timer
            recordingTime = 0;
            $('#xb_timer').show().text('00:00 / 03:00');
            recordingTimer = setInterval(function () {
                recordingTime++;
                var minutes = Math.floor(recordingTime / 60);
                var seconds = recordingTime % 60;
                $('#xb_timer').text(
                    (minutes < 10 ? '0' : '') + minutes + ':' +
                    (seconds < 10 ? '0' : '') + seconds + ' / 03:00'
                );

                if (recordingTime >= 10) {
                    $('#xb_stop_recording').prop('disabled', false).html('<span class="dashicons dashicons-controls-stop"></span> Stop Recording');
                }

                if (recordingTime >= maxRecordingTime) {
                    stopRecording();
                }
            }, 1000);

        } catch (err) {
            console.error("Error accessing media devices.", err);
            alert("Could not access camera/microphone. Please allow permissions.");
        }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
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
        formData.append('video_file', file);

        var btn = file.name === 'recorded-video.webm' ? $('#xb_save_recorded_video') : $('#xb_save_uploaded_video');
        var originalText = btn.text();
        btn.prop('disabled', true).text('Uploading...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    $('#IntroVideoURL').val(res.data.url);

                    // Update UI
                    updateVideoUI();

                    videoModal.hide();
                    stopStream();

                    Swal.fire({
                        title: 'Video Uploaded',
                        text: 'Don\'t forget to save the branding settings.',
                        icon: 'success',
                        confirmButtonText: 'Okay'
                    });
                } else {
                    Swal.fire({
                        title: 'Upload Failed',
                        text: res.data.message || 'Unknown error',
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                }
            },
            error: function () {
                alert('Network error during upload.');
            },
            complete: function () {
                btn.prop('disabled', false).text(originalText);
            }
        });
    }

    // --- Video UI & Playback Logic ---

    function updateVideoUI() {
        var videoUrl = $('#IntroVideoURL').val();
        if (videoUrl) {
            $('#xb_upload_video').text('Upload Video'); // Or 'Change Video'
            $('#xb_play_video').css('display', 'flex');
            $('#xb_delete_video').css('display', 'flex');
        } else {
            $('#xb_upload_video').text('Upload Video');
            $('#xb_play_video').hide();
            $('#xb_delete_video').hide();
        }
    }

    // Play Video
    $('#xb_play_video').click(function (e) {
        e.preventDefault();
        var videoUrl = $('#IntroVideoURL').val();
        if (videoUrl) {
            $('#xb_playback_video').attr('src', videoUrl);
            $('#xb_playback_modal').show().css('display', 'flex');
            $('#xb_playback_video')[0].play();
        }
    });

    // Delete Video
    $('#xb_delete_video').click(function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Video?',
            text: "Are you sure you want to remove the introduction video?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#IntroVideoURL').val('');
                updateVideoUI();
                Swal.fire(
                    'Deleted!',
                    'The video has been removed.',
                    'success'
                );
            }
        });
    });

    // Close Playback Modal
    $('.xb-close-playback').click(function () {
        $('#xb_playback_modal').hide();
        $('#xb_playback_video')[0].pause();
        $('#xb_playback_video').attr('src', '');
    });

    // Close Playback Modal on outside click
    $(window).click(function (e) {
        if ($(e.target).is('#xb_playback_modal')) {
            $('#xb_playback_modal').hide();
            $('#xb_playback_video')[0].pause();
            $('#xb_playback_video').attr('src', '');
        }
    });

    // Initial Load
    updateVideoUI();

});
