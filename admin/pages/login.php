if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php if (!defined('ABSPATH')) exit; ?>

<div class="xenhire-login-wrapper">
    <div class="xenhire-login-box">
        <!-- Logo -->
        <div class="xenhire-logo">
            <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/xenhire-logo.png'); ?>" alt="XenHire Logo">
        </div>

        <!-- Heading -->
        <div class="xenhire-heading">
            <h1 id="xenhire-form-title">Login</h1>
            <p id="xenhire-form-subtitle">Log in to XenHire to continue to the magic.</p>
        </div>

        <!-- Login Form -->
        <form id="xenhire-login-form" class="xenhire-form">

            <!-- Error/Success Message -->
            <div id="xenhire-message" class="xenhire-message" style="display:none;"></div>

            <!-- Email Field -->
            <div class="xenhire-form-group">
                <input type="email" id="xenhire-email" name="email" class="xenhire-input" placeholder=" " required autocomplete="off" />
                <label for="xenhire-email" class="xenhire-label">Email address</label>
            </div>

            <!-- Password Field -->
            <div class="xenhire-form-group">
                <div class="xenhire-password-wrapper">
                    <input type="password" id="xenhire-password" name="password" class="xenhire-input" placeholder=" " required autocomplete="off" />
                    <label for="xenhire-password" class="xenhire-label">Password</label>
                    <button type="button" class="xenhire-toggle-password" data-target="xenhire-password">
                        <span class="dashicons dashicons-visibility"></span>
                    </button>
                </div>
            </div>

            <!-- OTP Field (Hidden by default) -->
            <div class="xenhire-form-group" id="xenhire-otp-group" style="display:none;">
                <input type="text" id="xenhire-otp" name="otp" class="xenhire-input" placeholder=" " autocomplete="off" />
                <label for="xenhire-otp" class="xenhire-label">Verification Code</label>
                <div style="text-align: right; margin-top: 5px;">
                    <a href="#" id="xenhire-resend-otp" style="font-size: 12px; color: #667eea; text-decoration: none;">Resend Code</a>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="xenhire-form-group">
                <button type="submit" id="xenhire-submit-btn" class="xenhire-btn xenhire-btn-primary">
                    <span class="xenhire-btn-text">Login</span>
                    <span class="xenhire-btn-loader" style="display:none;">
                        <span class="spinner is-active"></span> Please wait...
                    </span>
                </button>
            </div>

            <!-- Toggle Sign Up Link -->
            <div class="xenhire-toggle-form">
                <span id="xenhire-toggle-text">Don't have an account?</span>
                <a href="#" id="xenhire-toggle-link">Sign up</a>
            </div>

        </form>
    </div>
</div>
