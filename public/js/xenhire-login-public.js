jQuery(document).ready(function ($) {
    var timerInterval;

    // Enter key support
    $('#xh-email').keypress(function (e) {
        if (e.which == 13) {
            $('#xh-btn-get-otp').click();
        }
    });

    $('#xh-otp').keypress(function (e) {
        if (e.which == 13) {
            $('#xh-btn-verify').click();
        }
    });

    // Step 1: Get OTP
    $('#xh-btn-get-otp').click(function () {
        var email = $('#xh-email').val();
        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Sending...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_candidate_send_otp',
                nonce: xenhireAjax.nonce,
                email: email
            },
            success: function (res) {
                $btn.prop('disabled', false).text('Get Verification Code');
                if (res.success) {
                    $('#xh-email-display').val(email);
                    $('#xh-step-email').hide();
                    $('#xh-step-otp').fadeIn();
                    $('#xh-otp').focus();
                    startTimer(78); // 1:18
                } else {
                    alert(res.data.message || 'Failed to send OTP');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Get Verification Code');
                alert('Network error. Please try again.');
            }
        });
    });

    // Step 2: Verify OTP
    $('#xh-btn-verify').click(function () {
        var otp = $('#xh-otp').val();
        var email = $('#xh-email-display').val();

        if (!otp || otp.length < 4) {
            alert('Please enter the 4-digit code.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Verifying...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_candidate_verify_otp',
                nonce: xenhireAjax.nonce,
                email: email,
                otp: otp
            },
            success: function (res) {
                if (res.success) {
                    // Redirect logic
                    // Redirect logic
                    var urlParams = new URLSearchParams(window.location.search);
                    var jobId = urlParams.get('job_id');
                    var redirectTo = urlParams.get('redirect_to');

                    if (jobId) {
                        window.location.href = (typeof xenhireAjax !== 'undefined' ? xenhireAjax.jobs_url : '/jobs/') + '?job_id=' + jobId;
                    } else if (redirectTo) {
                        window.location.href = decodeURIComponent(redirectTo);
                    } else {
                        // Default redirect to Jobs page
                        window.location.href = (typeof xenhireAjax !== 'undefined' ? xenhireAjax.jobs_url : '/jobs/');
                    }
                } else {
                    $btn.prop('disabled', false).text('Get Started');
                    alert(res.data.message || 'Invalid OTP');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Get Started');
                alert('Verification failed. Please try again.');
            }
        });
    });

    // Resend Code
    $('#xh-btn-resend').click(function (e) {
        e.preventDefault();
        $('#xh-btn-get-otp').click(); // Re-trigger send
        $(this).hide();
        $('#xh-resend-text').show();
    });

    function startTimer(duration) {
        var timer = duration, minutes, seconds;
        clearInterval(timerInterval);

        $('#xh-resend-text').show();
        $('#xh-btn-resend').hide();

        timerInterval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            $('#xh-timer').text(minutes + ":" + seconds);

            if (--timer < 0) {
                clearInterval(timerInterval);
                $('#xh-resend-text').hide();
                $('#xh-btn-resend').show();
            }
        }, 1000);
    }
});