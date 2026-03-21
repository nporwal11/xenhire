<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Before You Begin Page
 */
$xenhire_job_id = get_query_var('job_id');
$xenhire_candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';
$xenhire_brand_name = get_option('xenhire_brand_name', 'XenHire');
$xenhire_primary_color = get_option('xenhire_primary_color', '#9777fa');
$xenhire_secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Before You Begin - <?php echo esc_html($xenhire_brand_name); ?></title>
    <?php wp_head(); ?>
    
</head>
<body class="xh-page-intro">

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
        <!-- <select class="xh-lang-select">
            <option>Select Language</option>
            <option>English</option>
        </select> -->
        <span class="xh-user-email"><?php echo esc_html($xenhire_candidate_email); ?></span>
        <button class="xh-btn-logout" onclick="logout()">Logout</button>
    </div>
</div>

<div class="xh-container">
    <h1 class="xh-title">Tips before you begin</h1>
    <p class="xh-subtitle">Get ready with key tips to boost your confidence and make a great first impression.</p>
    
    <div class="xh-card">
        <div class="xh-tip-item">
            <div class="xh-tip-icon"><i class="las la-check-circle"></i></div>
            <div class="xh-tip-content">
                <h4>You are in complete control</h4>
                <p>You can complete the interview in your own time, on any device, anywhere.</p>
            </div>
        </div>
        
        <div class="xh-tip-item">
            <div class="xh-tip-icon"><i class="las la-wifi"></i></div>
            <div class="xh-tip-content">
                <h4>Remove any distractions and noise</h4>
                <p>Ensure you've got strong Wi-Fi signal and power. Close all other tabs and applications to remove the risk of distractions, like a notification popping up.</p>
            </div>
        </div>
        
        <div class="xh-tip-item">
            <div class="xh-tip-icon"><i class="las la-lightbulb"></i></div>
            <div class="xh-tip-content">
                <h4>Check your surroundings</h4>
                <p>Find a space with natural lighting so you are visible, sitting by a window helps. If you can't do that, place a small light in front of you.</p>
            </div>
        </div>
        
        <div class="xh-tip-item">
            <div class="xh-tip-icon"><i class="las la-eye"></i></div>
            <div class="xh-tip-content">
                <h4>Don't worry about making eye contact with the camera</h4>
                <p>Just act natural, read each question fully and relax.</p>
            </div>
        </div>
        
        <div class="xh-tip-item">
            <div class="xh-tip-icon"><i class="las la-clock"></i></div>
            <div class="xh-tip-content">
                <h4>Give yourself enough time to answer all questions</h4>
                <p>If now isn't a good time for you, come back later - that's the benefit; you have total control.</p>
            </div>
        </div>
        
        <div class="xh-mic-test">
            <div class="xh-mic-title"><i class="las la-microphone"></i> Test your microphone</div>
            <div class="xh-mic-controls">
                <button class="xh-btn-mic xh-btn-start-mic" id="start-mic">Start</button>
                <button class="xh-btn-mic xh-btn-stop-mic" id="stop-mic">Stop</button>
                <div class="xh-mic-visualizer">
                    <div class="xh-mic-bar" id="mic-bar"></div>
                </div>
            </div>
            <div class="xh-mic-status" id="mic-status">Microphone access denied or not available.</div>
        </div>
        
        <div class="xh-action-area">
            <button class="xh-btn-start-interview" onclick="startInterview()">Start Interview</button>
            <p class="xh-terms">By clicking the button above, I accept the <a href="#">terms and conditions</a>.</p>
        </div>
    </div>
</div>



<?php wp_footer(); ?>
</body>
</html>
