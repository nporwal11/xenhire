
$jid = isset($_GET['jid']) ? absint($_GET['jid']) : 0;

if (
    !isset($_GET['_wpnonce']) ||
    !wp_verify_nonce($_GET['_wpnonce'], 'xenhire_view')
) {
    wp_die('Invalid request');
}

if (!is_user_logged_in()) {
    wp_die('Login required');
}

$post = get_post($jid);

if (!$post || $post->post_author != get_current_user_id()) {
    wp_die('Unauthorized access');
}

<?php if (!defined('ABSPATH')) exit; ?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Interview Completion Page
 */


$xenhire_brand_name = get_option('xenhire_brand_name', 'XenHire');
$xenhire_primary_color = get_option('xenhire_primary_color', '#9777fa');
$xenhire_secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
$xenhire_candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';

// Sanitize and validate job ID using absint for better WordPress compatibility
// phpcs:ignore WordPress.Security.NonceVerification.Recommended

if (
    !isset(sanitize_text_field($_GET['_wpnonce'])) ||
    !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'xenhire_view_app')
) {
    wp_die('Invalid request');
}

$xenhire_app_id = isset(sanitize_text_field(absint($_GET['jid']))) ? absint(sanitize_text_field(absint($_GET['jid']))) : 0;


// Check if logged in
if (!isset($_COOKIE['xenhire_candidate_id'])) {
    $xenhire_login_url = home_url('/candidate-login/');
    wp_safe_redirect($xenhire_login_url);
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You - <?php echo esc_html($xenhire_brand_name); ?></title>
    <?php wp_head(); ?>

    
</head>
<body class="xh-page-thanks">

<div class="xh-navbar">
    <div class="xh-brand">
        <?php 
        $xenhire_brand_logo = get_option('xenhire_brand_logo');
        $xenhire_nav_brand_name = get_option('xenhire_brand_name'); 

        if (!empty($xenhire_brand_logo)) {
            $xenhire_alt_text = !empty($xenhire_nav_brand_name) ? $xenhire_nav_brand_name : 'Brand Logo';
            echo '<img src="' . esc_url($xenhire_brand_logo) . '" alt="' . esc_attr($xenhire_alt_text) . '" style="max-width:180px;max-height: 40px;">';
        } elseif (!empty($xenhire_nav_brand_name)) {
            echo esc_html($xenhire_nav_brand_name); 
        }
        ?>
    </div>
    <div class="xh-nav-right">
        <span class="xh-user-email"><?php echo esc_html($xenhire_candidate_email); ?></span>
        <a href="#" id="xh-logout-btn">Logout</a>
    </div>
</div>

<div class="xh-container">
    <div class="xh-question-card">
        <div class="xh-thanks-icon">
            <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/thanks-icon.png'); ?>" alt="thanks" />
        </div>
        <h1>Hey, your job application is complete.<br/><span>Thank you!</span></h1>
        
        <div class="xh-step-wrap">
            <!-- Line -->
            <div class="xh-baseline"></div>
            
            <!-- Step 1 -->
            <div class="xh-steps">
                <div class="xh-icon xh-submitted">
                    <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/application-submitted.png'); ?>" alt="submitted" />
                </div>
                <p>Application Submitted</p>
                <span>Your application has been received.</span>
                <small>We'll review it shortly.</small>
            </div>
            <!-- Step 2 -->
            <div class="xh-steps">
                <div class="xh-icon xh-under-review">
                    <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/under-review.png'); ?>" alt="under-review" />
                </div>
                <p>Under Review</p>
                <span>We're evaluating your profile.</span>
                <small>This may take a few days.</small>
            </div>
            <!-- Step 3 -->
            <div class="xh-steps">
                <div class="xh-icon xh-decision-made">
                    <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/decision-made.png'); ?>" alt="decision-made" />
                </div>
                <p>Decision Made</p>
                <span>We'll inform you of the outcome.</span>
                <small>Check your email regularly.</small>
            </div>
        </div>
        
        <a href="/web/jobs" class="xh-btn-back-jobs">BACK TO JOBS</a>
    </div>
</div>



<?php wp_footer(); ?>
</body>
</html>
