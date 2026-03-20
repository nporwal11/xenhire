<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Interview Page
 */


$xenhire_job_id = get_query_var('job_id');

// Sanitize and validate application ID using absint for better WordPress compatibility
// phpcs:ignore WordPress.Security.NonceVerification.Recommended

if (
    !isset($_GET['_wpnonce']) ||
    !wp_verify_nonce($_GET['_wpnonce'], 'xenhire_view_app')
) {
    wp_die('Invalid request');
}

$xenhire_app_id = isset($_GET['jid']) ? absint($_GET['jid']) : 0;


$xenhire_brand_name = get_option('xenhire_brand_name', 'XenHire');
$xenhire_primary_color = get_option('xenhire_primary_color', '#9777fa');
$xenhire_secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
$xenhire_candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';

// Check if logged in
if (!isset($_COOKIE['xenhire_candidate_id'])) {
    // Sanitize current URL for redirect to prevent XSS
    $xenhire_current_url = esc_url_raw( home_url( add_query_arg( null, null ) ) );
    
    $xenhire_login_url = home_url('/candidate-login/');
    $xenhire_redirect_url = add_query_arg('redirect_to', urlencode($xenhire_current_url), $xenhire_login_url);
    
    wp_safe_redirect($xenhire_redirect_url);
    exit;
}
$xenhire_thanks_url = home_url('/interview-complete/');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interview - <?php echo esc_html($xenhire_brand_name); ?></title>
    <?php wp_head(); ?>
    
</head>
<body class="xh-page-interview">

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
    <div id="xh-interview-content">
        <div class="xh-loading">Loading questions...</div>
    </div>
</div>



<?php wp_footer(); ?>
</body>
</html>
