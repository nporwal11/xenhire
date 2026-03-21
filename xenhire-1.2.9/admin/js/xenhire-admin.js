jQuery(document).ready(function ($) {

    // Toggle password visibility
    $('.xenhire-toggle-password').on('click', function () {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('.dashicons');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // Toggle between login and register forms
    let isLoginForm = true;

    $('#xenhire-toggle-link').on('click', function (e) {
        e.preventDefault();

        isLoginForm = !isLoginForm;

        if (isLoginForm) {
            $('#xenhire-form-title').text('Login');
            $('#xenhire-form-subtitle').text('Log in to XenHire to continue to the magic.');
            $('#xenhire-submit-btn .xenhire-btn-text').text('Login');
            $('#xenhire-toggle-text').text("Don't have an account?");
            $('#xenhire-toggle-link').text('Sign up');
        } else {
            $('#xenhire-form-title').text('Sign Up');
            $('#xenhire-form-subtitle').text('Create your XenHire account to get started.');
            $('#xenhire-submit-btn .xenhire-btn-text').text('Sign Up');
            $('#xenhire-toggle-text').text('Already have an account?');
            $('#xenhire-toggle-link').text('Login');
        }

        // Clear form and messages
        $('#xenhire-login-form')[0].reset();
        $('#xenhire-message').hide();
        $('#xenhire-otp-group').hide();
    });

    // Handle form submission (login or register)
    $('#xenhire-login-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const btn = $('#xenhire-submit-btn');
        const btnText = btn.find('.xenhire-btn-text');
        const btnLoader = btn.find('.xenhire-btn-loader');
        const message = $('#xenhire-message');

        const email = $('#xenhire-email').val();
        const password = $('#xenhire-password').val();
        const otp = $('#xenhire-otp').val();

        // Basic validation
        if (!email || !password) {
            message.removeClass('success').addClass('error').text('Please fill in all fields').show();
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            message.removeClass('success').addClass('error').text('Please enter a valid email address').show();
            return;
        }

        // Show loader
        btn.prop('disabled', true);
        btnText.hide();
        btnLoader.show();
        message.hide();

        // LOGIN FLOW
        if (isLoginForm) {
            $.ajax({
                url: xenhireAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'xenhire_login',
                    nonce: xenhireAjax.nonce,
                    email: email,
                    password: password
                },
                success: function (response) {
                    if (response.success) {
                        // message.removeClass('error').addClass('success').text(response.data.message).show();
                        location.reload();
                    } else {
                        message.removeClass('success').addClass('error').text(response.data.message).show();
                        resetBtn();
                    }
                },
                error: function () {
                    message.removeClass('success').addClass('error').text('An error occurred. Please try again.').show();
                    resetBtn();
                }
            });
        }
        // SIGNUP FLOW
        else {
            // Step 2: Verify OTP & Register
            if ($('#xenhire-otp-group').is(':visible')) {
                if (!otp) {
                    message.removeClass('success').addClass('error').text('Please enter the verification code').show();
                    resetBtn();
                    return;
                }

                // 1. Verify OTP
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_verify_otp',
                        nonce: xenhireAjax.nonce,
                        email: email,
                        otp: otp
                    },
                    success: function (res) {
                        if (res.success) {
                            // 2. Register
                            registerUser(email, password);
                        } else {
                            message.removeClass('success').addClass('error').text(res.data.message || 'Invalid OTP').show();
                            resetBtn();
                        }
                    },
                    error: function () {
                        message.removeClass('success').addClass('error').text('Verification failed. Please try again.').show();
                        resetBtn();
                    }
                });
            }
            // Step 1: Send OTP
            else {
                $.ajax({
                    url: xenhireAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'xenhire_send_otp',
                        nonce: xenhireAjax.nonce,
                        email: email
                    },
                    success: function (res) {
                        if (res.success) {
                            message.removeClass('error').addClass('success').text('Verification code sent to your email.').show();
                            $('#xenhire-otp-group').slideDown();
                            $('#xenhire-submit-btn .xenhire-btn-text').text('Verify & Sign Up');
                            resetBtn();
                        } else {
                            message.removeClass('success').addClass('error').text(res.data.message || 'Failed to send OTP').show();
                            resetBtn();
                        }
                    },
                    error: function () {
                        message.removeClass('success').addClass('error').text('Failed to send OTP. Please try again.').show();
                        resetBtn();
                    }
                });
            }
        }

        function resetBtn() {
            btn.prop('disabled', false);
            btnText.show();
            btnLoader.hide();
        }

        function registerUser(email, password) {
            $.ajax({
                url: xenhireAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'xenhire_register',
                    nonce: xenhireAjax.nonce,
                    email: email,
                    password: password
                },
                success: function (response) {
                    if (response.success) {
                        message.removeClass('error').addClass('success').text('Registration successful! Redirecting...').show();
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        message.removeClass('success').addClass('error').text(response.data.message).show();
                        resetBtn();
                    }
                },
                error: function () {
                    message.removeClass('success').addClass('error').text('Registration failed. Please try again.').show();
                    resetBtn();
                }
            });
        }
    });

    // Resend OTP Handler
    $('#xenhire-resend-otp').on('click', function (e) {
        e.preventDefault();
        const email = $('#xenhire-email').val();
        const message = $('#xenhire-message');

        if (!email) return;

        $(this).text('Sending...');

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_send_otp',
                nonce: xenhireAjax.nonce,
                email: email
            },
            success: function (res) {
                $('#xenhire-resend-otp').text('Resend Code');
                if (res.success) {
                    message.removeClass('error').addClass('success').text('Verification code resent.').show();
                } else {
                    message.removeClass('success').addClass('error').text(res.data.message).show();
                }
            },
            error: function () {
                $('#xenhire-resend-otp').text('Resend Code');
                message.removeClass('success').addClass('error').text('Failed to resend.').show();
            }
        });
    });

    // Global Logout Logic
    $(document).on('click', '#xenhire-logout-btn', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to logout?')) return;

        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_logout',
                nonce: xenhireAjax.nonce
            },
            success: function () {
                window.location.href = xenhireAjax.login_url;
            }
        });
    });

});
