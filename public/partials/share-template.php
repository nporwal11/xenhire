<?php if (!defined('ABSPATH')) exit; ?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template for Share Page
 */
$xenhire_share_key = get_query_var('share_key');
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if (empty($xenhire_share_key) && isset(sanitize_text_field($_GET['key']))) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $xenhire_share_key = sanitize_text_field(wp_unslash(sanitize_text_field($_GET['key'])));
}
$xenhire_brand_name = get_option('xenhire_brand_name', 'XenHire'); // Or "JoyBrand22" as per screenshot?
// Actually screenshot says "JoyBrand22". I'll use that as a placeholder if brand name is default.
if ($xenhire_brand_name === 'XenHire') $xenhire_brand_name = 'JoyBrand22';

$xenhire_primary_color = get_option('xenhire_primary_color', '#9777fa');
$xenhire_secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');

// Enqueue styles manually if needed, or rely on what's enqueued.
// Since it's standalone, we might need to inline some styles or rely on 'xenhire-public-css'.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Candidate Profile - <?php echo esc_html($xenhire_brand_name); ?></title>
    <?php wp_head(); ?>
</head>
<body class="xh-page-share">
<div class="xh-container">    
    <!-- Sidebar -->
    <aside class="xh-profile-card">
        <div id="xh-profile-skeleton" class="xh-skeleton-loader">
             <!-- Placeholder -->
            <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/placeholder.png'); ?>" class="xh-profile-img" alt="Profile" id="xh-img">
            <h2 class="xh-profile-name" id="xh-name">Loading...</h2>
            <div class="xh-profile-job">
                Applied for <br> 
                <span class="xh-job-profile">
                    <span id="xh-job-title">...</span>
                    <span id="xh-sidebar-company"></span>
                </span>
            </div>
        </div>

        <div class="xh-contact-info">
            <div class="xh-contact-item">
                <i class="las la-phone"></i>
                <span id="xh-phone">...</span>
                <i class="ki-outline ki-copy" title="Copy Mobile" onclick="copyText('xh-phone')"></i>
            </div>
            <div class="xh-contact-item">
                <i class="las la-envelope"></i>
                <span id="xh-email">...</span>
                <i class="ki-outline ki-copy" title="Copy Email" onclick="copyText('xh-email')"></i>
            </div>
            <div class="xh-contact-item" id="xh-linkedin-box" style="display:none;">
                <i class="lab la-linkedin"></i>
                <!-- Wrap in span to get flex-grow:1 from CSS -->
                <span>
                    <a href="#" target="_blank" id="xh-linkedin" style="color:inherit; text-decoration:none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; display: inline-block; vertical-align: middle;">...</a>
                </span>
                <i class="ki-outline ki-copy" title="Copy URL" onclick="copyText('xh-linkedin-href')"></i>
            </div>
        </div>

        <div class="xh-divider"></div>

        <div class="xh-meta-list">
            <div class="xh-meta-row">
                <i class="las la-map-marker"></i>
                <span id="xh-location">Not Specified</span>
            </div>
            <div class="xh-meta-row">
                <i class="las la-briefcase"></i>
                <span id="xh-exp-label">Fresher</span>
            </div>
            <div class="xh-meta-row">
                <i class="las la-wallet"></i>
                <span id="xh-salary">$0</span>
            </div>
            <div class="xh-meta-row">
                <i class="las la-calendar"></i>
                <span id="xh-status">Not Specified</span>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="xh-content-area">
        
        <!-- Resume Accordion with Tabs -->
        <div class="xh-card" id="xh-resume-card" style="display:none;">
            <div class="xh-card-header" onclick="toggleAccordion(this)" aria-expanded="false">
                <div class="xh-card-title">
                    Resume 
                </div>
                 <div style="margin-left:auto; margin-right:15px;">
                      <span class="xh-resume-match" id="xh-match-badge" style="display:none;">0% Resume Match</span>
                 </div>
                <i class="ki-outline ki-down"></i>
            </div>
            <div class="xh-card-body" style="display:none;"> 
                
                <!-- Tabs -->
                <div class="xh-tabs">
                    <div class="xh-tab active" onclick="switchTab('resume')">Resume</div>
                    <div class="xh-tab" onclick="switchTab('match')">Match Score</div>
                </div>

                <!-- Resume Content -->
                <div id="tab-resume" class="xh-tab-content active">
                    <div id="xh-resume-container">
                        <p class="xh-no-data">No resume uploaded.</p>
                    </div>
                </div>

                <!-- Match Score Content -->
                <div id="tab-match" class="xh-tab-content">
                    <div id="xh-match-score-details" style="padding: 5px;">
                        <div style="color:#6b7280;">Loading match details...</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Interview -->
        <div class="xh-card" id="xh-interview-card" style="display:none;">
            <div class="xh-card-header" onclick="toggleAccordion(this)" aria-expanded="true">
                <div class="xh-card-title xh-interview-status"> <!-- Orange color from screenshot -->
                    Interview
                </div>
                
                <!-- Controls -->
                <div class="xh-interview-controls" id="xh-int-controls" onclick="event.stopPropagation()">
                    <i class="ki-outline ki-left xh-nav-btn" id="xh-prev-q" onclick="prevQuestion(event)"></i>
                    <span id="xh-q-counter">Question 1 of 3</span>
                    <i class="ki-outline ki-right xh-nav-btn" id="xh-next-q" onclick="nextQuestion(event)"></i>
                </div>

                <i class="ki-outline ki-down"></i>
            </div>
            <div class="xh-card-body" style="display:block;">
                <div id="xh-interview-container">
                </div>
            </div>
        </div>

        <!-- Skills -->
        <div class="xh-card">
            <div class="xh-card-header" onclick="toggleAccordion(this)" aria-expanded="true">
                <div class="xh-card-title">Skills</div>
                <i class="ki-outline ki-down"></i>
            </div>
            <div class="xh-card-body" style="display:block;">
                <div id="xh-skills-list">
                    <p class="xh-no-data">No skills specified</p>
                </div>
            </div>
        </div>

        <!-- Experience -->
        <div class="xh-card">
            <div class="xh-card-header" onclick="toggleAccordion(this)" aria-expanded="true">
                <div class="xh-card-title">Experience</div>
                <i class="ki-outline ki-down"></i>
            </div>
            <div class="xh-card-body" style="display:block;">
                 <!-- To be populated as List Items -->
                <div id="xh-experience-list">
                    <p class="xh-no-data">No experience specified</p>
                </div>
            </div>
        </div>

        <!-- Education -->
        <div class="xh-card">
            <div class="xh-card-header" onclick="toggleAccordion(this)" aria-expanded="true">
                <div class="xh-card-title">Education</div>
                <i class="ki-outline ki-down"></i>
            </div>
            <div class="xh-card-body" style="display:block;">
                 <!-- To be populated as List Items -->
                <div id="xh-education-list">
                    <p class="xh-no-data">No education specified</p>
                </div>
            </div>
        </div>

    </main>

</div>




<?php wp_footer(); ?>
</body>
</html>
