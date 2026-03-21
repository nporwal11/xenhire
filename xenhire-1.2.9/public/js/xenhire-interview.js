var jobId = (typeof xenhireInterviewData !== 'undefined') ? xenhireInterviewData.jobId : 0;
var appId = (typeof xenhireInterviewData !== 'undefined') ? xenhireInterviewData.appId : 0;
var thanksUrl = (typeof xenhireInterviewData !== 'undefined') ? xenhireInterviewData.thanksUrl : '';
var questions = [];
var currentQuestionIndex = 0;

jQuery(document).ready(function ($) {
    // Fetch Questions
    $.ajax({
        url: xenhireAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'xenhire_public_get_interview_questions',
            nonce: xenhireAjax.nonce,
            job_id: jobId,
            application_id: appId
        },
        success: function (res) {
            if (res.success && res.data) {
                // Handle array response
                var data = res.data.data || res.data; // Handle both nested and direct (fallback)
                if (Array.isArray(data) && data.length > 0) {
                    // Check for nested array [[{...}]] or [{...}]
                    var qData = Array.isArray(data[0]) ? data[0][0] : data[0];

                    // Check if Finished
                    if (qData && qData.Message === "Finished") {
                        renderThankYou();
                        return;
                    }

                    if (qData) {
                        renderQuestion(qData);
                    } else {
                        renderThankYou();
                    }
                } else {
                    renderThankYou();
                }
            } else {
                var debugInfo = JSON.stringify(res.data && res.data.debug ? res.data.debug : {}, null, 2);
                $('#xh-interview-content').html('<div class="xh-loading">Failed to load questions: ' + (res.data.message || 'Unknown error') + '<br><pre style="text-align:left;font-size:12px;background:#f5f5f5;padding:10px;">Debug: ' + debugInfo + '</pre></div>');
            }
        },
        error: function () {
            $('#xh-interview-content').html('<div class="xh-loading">Network error. Please try again.</div>');
        }
    });

    var mediaRecorder;
    var recordedChunks = [];
    var stream;
    var timerInterval;
    var timeLeft = 60;
    var totalDuration = 60;
    var recordedTime = 0;

    function renderQuestion(q) {
        var qNum = q.SNo || 1;
        var qText = q.Name || q.QuestionText || 'Question';
        var qDesc = q.Description || '';
        var qType = q.QuestionTypeID;

        // Parse Duration from API (e.g. "01:00" or integer seconds)
        totalDuration = 60; // Default

        if (q.MaxSeconds && !isNaN(q.MaxSeconds) && parseInt(q.MaxSeconds) > 0) {
            totalDuration = parseInt(q.MaxSeconds);
        } else if (q.Duration) {
            if (typeof q.Duration === 'string' && q.Duration.includes(':')) {
                var parts = q.Duration.split(':');
                totalDuration = (parseInt(parts[0]) * 60) + parseInt(parts[1]);
            } else if (!isNaN(q.Duration)) {
                totalDuration = parseInt(q.Duration);
            }
        }
        timeLeft = totalDuration;

        // Cleanup previous stream if any
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        clearInterval(timerInterval);

        var inputHtml = '';

        // Type 1: Video
        if (qType === 1) {
            var minStr = Math.floor(totalDuration / 60);
            var secStr = totalDuration % 60;
            var timeStr = (minStr < 10 ? '0' + minStr : minStr) + ':' + (secStr < 10 ? '0' + secStr : secStr);

            inputHtml = `
                <div class="xh-video-container" style="position: relative; background: #000; border-radius: 12px; overflow: hidden; aspect-ratio: 16/9;">
                    <!-- Header Overlay -->
                    <div style="position: absolute; top: 20px; left: 20px; right: 20px; display: flex; justify-content: space-between; z-index: 10; color: #fff; font-size: 14px; font-weight: 500;">
                        <div style="background: rgba(0,0,0,0.5); padding: 4px 12px; border-radius: 20px;">Answer time <span id="xh-timer">${timeStr}</span></div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div id="xh-status-dot" style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                            <span id="xh-status-text">Ready</span>
                        </div>
                    </div>

                    <!-- Video Element -->
                    <video id="xh-video-preview" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>

                    <!-- Countdown Overlay -->
                    <div id="xh-countdown-overlay" style="display:none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; align-items: center; justify-content: center; background: rgba(0,0,0,0.6); z-index: 20;">
                        <div style="color: #fff; font-size: 18px; font-weight: 600; background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 30px;">
                            Interview starts in (<span id="xh-countdown-timer">10</span>) secs
                        </div>
                    </div>

                    <!-- Start Overlay -->
                    <div id="xh-video-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3);">
                        <div style="color: #fff; font-size: 16px; font-weight: 500;">Press start to record</div>
                    </div>
                </div>
                
                <div style="margin-top: 15px;text-align:left;">
                    <!-- Validation Message -->
                    <div id="xh-validation-msg" style="display:none; background: #ffe4e6; color: #be123c; padding: 10px 15px; border-radius: 6px; margin-bottom: 10px; font-size: 12px; width:210px; font-weight: 500;">
                        Please record video of atleast 10 secs.
                    </div>

                    <button id="xh-btn-record" class="xh-btn-record">
                        START RECORDING
                    </button>
                    <button id="xh-btn-stop" class="xh-btn-record" style="display:none;">
                        STOP RECORDING
                    </button>
                </div>
            `;
        }
        // Type 2: Short Text
        else if (qType === 2) {
            inputHtml = `<input type="text" class="xh-answer-input" id="xh-answer-input" placeholder="Type your answer here..." />`;
        }
        // Type 3: Long Text
        else if (qType === 3) {
            inputHtml = `<textarea class="xh-answer-input" id="xh-answer-input" placeholder="Type your answer here..." rows="1"></textarea>
                         <p class="xh-hint"><strong>Shift ⇧</strong> + <strong>Enter ↵</strong> for line break</p>`;
        }
        // Type 4: Number
        else if (qType === 4) {
            inputHtml = `<input type="number" class="xh-answer-input" id="xh-answer-input" placeholder="Type number here..." />`;
        }
        // Type 5: Single Select
        else if (qType === 5) {
            var options = q.Options ? q.Options.split('|') : [];
            inputHtml = '<div class="xh-options">';
            options.forEach(function (opt, idx) {
                inputHtml += `
                    <label style="display:block; margin: 10px 0; cursor:pointer;">
                        <input type="radio" name="xh-answer-opt" value="${opt}" ${idx === 0 ? 'checked' : ''}> 
                        <span style="margin-left:8px;">${String.fromCharCode(65 + idx)}. ${opt}</span>
                    </label>`;
            });
            inputHtml += '</div>';
        }
        // Type 6: Multi Select
        else if (qType === 6) {
            var options = q.Options ? q.Options.split('|') : [];
            inputHtml = '<div class="xh-options"><small>Choose as many as you like</small>';
            options.forEach(function (opt, idx) {
                inputHtml += `
                    <label style="display:block; margin: 10px 0; cursor:pointer;">
                        <input type="checkbox" name="xh-answer-chk" value="${opt}"> 
                        <span style="margin-left:8px;">${String.fromCharCode(65 + idx)}. ${opt}</span>
                    </label>`;
            });
            inputHtml += '</div>';
        }
        // Type 7: Date
        else if (qType === 7) {
            inputHtml = `<input type="date" class="xh-answer-input" id="xh-answer-input" />`;
        }
        // Type 8: Code
        else if (qType === 8) {
            inputHtml = `<textarea class="xh-answer-input" id="xh-answer-input" placeholder="Write your code here..." rows="10" style="font-family:monospace; font-size:14px; background:#1f2937; color:#fff; padding:15px;"></textarea>`;
        }

        var html = `
            <div class="xh-question-card">
                <div class="xh-q-header">
                    <div class="xh-q-number">${qNum}</div>
                    <div class="xh-q-text">
                        ${qText}
                        ${qDesc ? `<div style="font-size:14px; color:#6b7280; margin-top:5px;">${qDesc}</div>` : ''}
                    </div>
                    ${q.AudioURL ? `<div class="xh-q-audio">🔊</div>` : ''}
                </div>
                
                <div class="xh-answer-area">
                    ${inputHtml}
                </div>
                
                ${qType !== 1 ? `
                <div class="xh-actions">
                    <button class="xh-btn-save" id="xh-btn-save">Save</button>
                    ${(qType === 2 || qType === 3 || qType === 4) ? '<span class="xh-hint">press <strong>Enter ↵</strong></span>' : ''}
                </div>` : ''}
                
                <div class="xh-progress-container">
                    <div class="xh-progress-bar">
                        <div class="xh-progress-fill" style="width: ${q.ProgressPercent || 0}%"></div>
                    </div>
                    <div class="xh-progress-text">${q.ProgressMessage || (q.ProgressPercent + '% Completed')}</div>
                </div>
            </div>
        `;

        $('#xh-interview-content').html(html);

        // Video Logic
        if (qType === 1) {
            $('#xh-btn-record').click(function () {
                var $btn = $(this);
                $btn.prop('disabled', true).text('INITIALIZING...');
                startCamera(function () {
                    initiateRecordingSequence();
                }, function () {
                    $btn.prop('disabled', false).text('START RECORDING');
                });
            });

            $('#xh-btn-stop').click(function () {
                stopRecording(q);
            });
        }

        // Auto-focus logic
        if (qType === 2 || qType === 3 || qType === 4 || qType === 8) {
            var $input = $('#xh-answer-input');
            $input.focus();

            // Auto-resize for textarea
            if (qType === 3) {
                $input.on('input', function () {
                    this.style.height = 'auto';
                });
            }

            // Handle Enter key
            $input.on('keydown', function (e) {
                if (e.key === 'Enter') {
                    if (qType === 3 && e.shiftKey) return; // Allow line break
                    e.preventDefault();
                    saveAnswer(q);
                }
            });
        }

        $('#xh-btn-save').click(function () { saveAnswer(q); });
    }

    function startCamera(onSuccess, onError) {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(function (s) {
                stream = s;
                var video = document.getElementById('xh-video-preview');
                video.srcObject = stream;
                if (onSuccess) onSuccess();
            })
            .catch(function (err) {
                console.error('Camera error:', err);
                alert('Could not access camera. Please allow permissions.');
                if (onError) onError();
            });
    }

    function initiateRecordingSequence() {
        startRecording();
    }

    function startRecording() {
        recordedChunks = [];
        try {
            mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
        } catch (e) {
            mediaRecorder = new MediaRecorder(stream); // Fallback
        }

        mediaRecorder.ondataavailable = function (e) {
            if (e.data.size > 0) {
                recordedChunks.push(e.data);
            }
        };

        mediaRecorder.start();

        // UI Updates
        $('#xh-btn-record').hide();
        $('#xh-btn-stop').show();
        $('#xh-video-overlay').hide();
        $('#xh-validation-msg').hide(); // Hide any previous validation msg
        $('#xh-status-dot').css('background', '#ef4444'); // Red for recording
        $('#xh-status-text').text('Recording...');

        // Timer
        timeLeft = totalDuration;
        recordedTime = 0;
        updateTimerDisplay();
        timerInterval = setInterval(function () {
            timeLeft--;
            recordedTime++;
            updateTimerDisplay();
            if (timeLeft <= 0) {
                stopRecording(questions[currentQuestionIndex], true);
            }
        }, 1000);
    }

    function stopRecording(q, force = false) {
        // Check Minimum Duration (10s) unless forced
        if (!force && recordedTime < 10) {
            // Show inline validation error for 2 seconds
            $('#xh-validation-msg').stop(true, true).show().delay(2000).fadeOut();
            return;
        }

        mediaRecorder.stop();
        clearInterval(timerInterval);

        // Turn off camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        var video = document.getElementById('xh-video-preview');
        if (video) {
            video.srcObject = null;
        }

        mediaRecorder.onstop = function () {
            var blob = new Blob(recordedChunks, { type: 'video/webm' });
            uploadVideo(blob, q);
        };

        $('#xh-btn-stop').prop('disabled', true).text('Uploading...');
        $('#xh-status-text').text('Processing...');
        $('#xh-validation-msg').hide();
    }

    function updateTimerDisplay() {
        $('#xh-timer').text(formatTime(timeLeft));
    }

    function formatTime(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
    }

    function uploadVideo(blob, q) {
        var formData = new FormData();
        formData.append('action', 'xenhire_public_upload_video');
        formData.append('nonce', xenhireAjax.nonce);
        formData.append('video', blob, 'interview.webm');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    var videoUrl = res.data.url;
                    saveAnswer(q, videoUrl);
                } else {
                    alert('Upload failed: ' + (res.data.message || 'Unknown error'));
                    $('#xh-btn-stop').prop('disabled', false).text('STOP RECORDING');
                }
            },
            error: function () {
                alert('Upload network error');
                $('#xh-btn-stop').prop('disabled', false).text('STOP RECORDING');
            }
        });
    }

    function saveAnswer(q, videoUrl = null) {
        var answer = '';
        var qType = q.QuestionTypeID;

        if (qType === 5) {
            answer = $('input[name="xh-answer-opt"]:checked').val();
        } else if (qType === 6) {
            var checked = [];
            $('input[name="xh-answer-chk"]:checked').each(function () { checked.push($(this).val()); });
            answer = checked.join('|');
        } else if (qType === 1) {
            if (videoUrl) {
                answer = videoUrl;
            } else {
                alert('Please record and upload a video first.');
                return;
            }
        } else {
            answer = $('#xh-answer-input').val();
        }

        if (!answer && qType !== 1) {
            alert('Please enter an answer.');
            return;
        }

        // Disable button
        $('#xh-btn-save').prop('disabled', true).text('Saving...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_public_save_interview_answer',
                nonce: xenhireAjax.nonce,
                application_id: appId,
                question_id: q.ID,
                question_type: qType,
                answer: answer
            },
            success: function (res) {
                if (res.success) {
                    // Answer saved, reload to get next question
                    location.reload();
                } else {
                    alert('Failed to save answer: ' + (res.data.message || 'Unknown error'));
                    $('#xh-btn-save').prop('disabled', false).text('Save');
                }
            },
            error: function () {
                alert('Network error. Please try again.');
                $('#xh-btn-save').prop('disabled', false).text('Save');
            }
        });
    }

    function renderThankYou() {
        window.location.href = thanksUrl + '?jid=' + appId;
    }

    // Logout Logic
    $('#xh-logout-btn').click(function (e) {
        e.preventDefault();
        // Clear Cookies
        document.cookie = "xenhire_candidate_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "xenhire_candidate_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "xenhire_candidate_otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        // Redirect
        window.location.href = xenhireAjax.login_url;
    });
});
