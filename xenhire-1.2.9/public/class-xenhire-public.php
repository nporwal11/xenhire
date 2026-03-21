<?php
if (!defined('ABSPATH')) exit;

class XenHire_Public {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'filter_jobs_page_content']);

        // Public AJAX for jobs listing (works without login)
        add_action('wp_ajax_xenhire_public_list_jobs', [$this, 'ajax_public_list_jobs']);
        add_action('wp_ajax_nopriv_xenhire_public_list_jobs', [$this, 'ajax_public_list_jobs']);

        // Public AJAX for job details
        add_action('wp_ajax_xenhire_public_get_job_details', [$this, 'ajax_public_get_job_details']);
        add_action('wp_ajax_nopriv_xenhire_public_get_job_details', [$this, 'ajax_public_get_job_details']);

        // Candidate Login AJAX
        add_action('wp_ajax_xenhire_candidate_send_otp', [$this, 'ajax_candidate_send_otp']);
        add_action('wp_ajax_nopriv_xenhire_candidate_send_otp', [$this, 'ajax_candidate_send_otp']);
        
        add_action('wp_ajax_xenhire_candidate_verify_otp', [$this, 'ajax_candidate_verify_otp']);
        add_action('wp_ajax_nopriv_xenhire_candidate_verify_otp', [$this, 'ajax_candidate_verify_otp']);

        // Application Submission
        add_action('wp_ajax_xenhire_public_submit_application', [$this, 'ajax_public_submit_application']);
        add_action('wp_ajax_nopriv_xenhire_public_submit_application', [$this, 'ajax_public_submit_application']);

        // Resume Parsing
        add_action('wp_ajax_xenhire_parse_resume', [$this, 'ajax_parse_resume']);
        add_action('wp_ajax_nopriv_xenhire_parse_resume', [$this, 'ajax_parse_resume']);

        // Get Interview Questions
        add_action('wp_ajax_xenhire_public_get_interview_questions', [$this, 'ajax_public_get_interview_questions']);
        add_action('wp_ajax_nopriv_xenhire_public_get_interview_questions', [$this, 'ajax_public_get_interview_questions']);

        // Save Interview Answer
        add_action('wp_ajax_xenhire_public_save_interview_answer', [$this, 'ajax_public_save_interview_answer']);
        add_action('wp_ajax_nopriv_xenhire_public_save_interview_answer', [$this, 'ajax_public_save_interview_answer']);

        // Send Mail (Completion)
        add_action('wp_ajax_xenhire_public_send_mail', [$this, 'ajax_public_send_mail']);
        add_action('wp_ajax_nopriv_xenhire_public_send_mail', [$this, 'ajax_public_send_mail']);

        // Upload Video
        add_action('wp_ajax_xenhire_public_upload_video', [$this, 'ajax_public_upload_video']);
        add_action('wp_ajax_nopriv_xenhire_public_upload_video', [$this, 'ajax_public_upload_video']);

        // Get Share Data
        add_action('wp_ajax_xenhire_public_get_share_data', [$this, 'ajax_public_get_share_data']);
        add_action('wp_ajax_nopriv_xenhire_public_get_share_data', [$this, 'ajax_public_get_share_data']);

        // Get Candidate Profile (For pre-filling application)
        add_action('wp_ajax_xenhire_public_get_candidate_profile', [$this, 'ajax_public_get_candidate_profile']);
        add_action('wp_ajax_nopriv_xenhire_public_get_candidate_profile', [$this, 'ajax_public_get_candidate_profile']);

        // Register Shortcodes
        add_shortcode('xenhire_candidate_login', [$this, 'render_candidate_login']);

        // Virtual Login Page
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_filter('template_include', [$this, 'load_login_template']);

        // Standalone Page Logic
        add_action('wp_enqueue_scripts', [$this, 'dequeue_theme_assets'], 9999);
        add_filter('show_admin_bar', [$this, 'hide_admin_bar']);
        add_filter('body_class', [$this, 'filter_body_classes']);
    }

    public function add_rewrite_rules() {
        add_rewrite_rule('^candidate-login/?$', 'index.php?xenhire_candidate_login=1', 'top');
        add_rewrite_rule('^before-you-begin/([0-9]+)/?$', 'index.php?xenhire_before_begin=1&job_id=$matches[1]', 'top');
        add_rewrite_rule('^interview/([0-9]+)/?$', 'index.php?xenhire_interview=1&job_id=$matches[1]', 'top');
        add_rewrite_rule('^interview-complete/?$', 'index.php?xenhire_interview_complete=1', 'top');
add_rewrite_rule('^share/([a-zA-Z0-9-]+)/?$', 'index.php?xenhire_share=1&share_key=$matches[1]', 'top');
        add_rewrite_rule('^share/?$', 'index.php?xenhire_share=1', 'top');
        
        // Temporary flush for development (remove in production)
        flush_rewrite_rules(); 
    }

    public function add_query_vars($vars) {
        $vars[] = 'xenhire_candidate_login';
        $vars[] = 'xenhire_before_begin';
        $vars[] = 'xenhire_interview';
        $vars[] = 'xenhire_interview_complete';
        $vars[] = 'xenhire_share';
        $vars[] = 'share_key';
        $vars[] = 'job_id';
        return $vars;
    }

    public function load_login_template($template) {
        if (get_query_var('xenhire_candidate_login')) {
            return plugin_dir_path(__FILE__) . 'partials/candidate-login-template.php';
        }
        if (get_query_var('xenhire_before_begin')) {
            return plugin_dir_path(__FILE__) . 'partials/before-you-begin-template.php';
        }
        if (get_query_var('xenhire_interview')) {
            return plugin_dir_path(__FILE__) . 'partials/interview-template.php';
        }
        if (get_query_var('xenhire_interview_complete')) {
            return plugin_dir_path(__FILE__) . 'partials/thanks-template.php';
        }
        if (get_query_var('xenhire_share')) {
            return plugin_dir_path(__FILE__) . 'partials/share-template.php';
        }
        return $template;
    }

    public function enqueue_assets() {
        if (is_admin()) return;

        // Check if we are on a standalone page
        $is_standalone = get_query_var('xenhire_candidate_login') || 
                         get_query_var('xenhire_before_begin') || 
                         get_query_var('xenhire_interview') ||
                         get_query_var('xenhire_interview_complete') ||
                         get_query_var('xenhire_share');

        if ($is_standalone) {
            // Enqueue only necessary scripts
            wp_enqueue_script('jquery');
            wp_enqueue_style('dashicons');

            wp_enqueue_style('xenhire-satoshi', plugin_dir_url(__FILE__) . 'css/xenhire-satoshi.css', [], '1.0.0');
            wp_enqueue_style('xenhire-public-css', plugin_dir_url(__FILE__) . 'css/xenhire-public.css', [], '1.0.0');
            wp_enqueue_style('xenhire-templates', plugin_dir_url(__FILE__) . 'css/xenhire-templates.css', [], time());
            
            if (get_query_var('xenhire_share') || get_query_var('xenhire_before_begin')) {
                wp_enqueue_style('line-awesome', plugin_dir_url(__FILE__) . 'css/line-awesome.min.css', [], '1.3.1');
            }

            if (get_query_var('xenhire_share')) {
                wp_enqueue_style('keen-icons', plugin_dir_url(__FILE__) . 'css/keen-icons.css', [], '1.0.1');
                wp_enqueue_style('xenhire-share', plugin_dir_url(__FILE__) . 'css/xenhire-share.css', [], '1.0.0');
                wp_enqueue_script('xenhire-share', plugin_dir_url(__FILE__) . 'js/xenhire-share.js', ['jquery'], '1.0.0', true);
                wp_localize_script('xenhire-share', 'xenhireShareData', [
                    'shareKey' => get_query_var('share_key')
                ]);
            }

            if (get_query_var('xenhire_before_begin')) {
                wp_enqueue_style('xenhire-before', plugin_dir_url(__FILE__) . 'css/xenhire-before.css', [], '1.0.0');
                
                // Add dynamic CSS variables
                $primary_color = get_option('xenhire_primary_color', '#9777fa');
                $secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
                $custom_css = "
                    :root {
                        --xh-primary: " . esc_attr($primary_color) . ";
                        --xh-secondary: " . esc_attr($secondary_color) . ";
                    }
                ";
                wp_add_inline_style('xenhire-before', $custom_css);
                
                wp_enqueue_script('xenhire-before', plugin_dir_url(__FILE__) . 'js/xenhire-before.js', ['jquery'], '1.0.0', true);
                wp_localize_script('xenhire-before', 'xenhireBeforeData', [
                    'jobId' => get_query_var('job_id'),
                    'interviewUrl' => home_url('/interview/')
                ]);
            }

            if (get_query_var('xenhire_interview_complete')) {
                wp_enqueue_script('xenhire-thanks', plugin_dir_url(__FILE__) . 'js/xenhire-thanks.js', ['jquery'], '1.0.0', true);
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                wp_localize_script('xenhire-thanks', 'xenhireThanksData', [
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'appId' => isset($_GET['jid']) ? intval($_GET['jid']) : 0
                ]);
            }

            if (get_query_var('xenhire_interview')) {
                wp_enqueue_script('xenhire-interview', plugin_dir_url(__FILE__) . 'js/xenhire-interview.js', ['jquery'], '1.0.0', true);
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                wp_localize_script('xenhire-interview', 'xenhireInterviewData', [
                    'jobId' => get_query_var('job_id'),
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    'appId' => isset($_GET['jid']) ? intval($_GET['jid']) : 0,
                    'thanksUrl' => home_url('/interview-complete/')
                ]);
            }
        } else {
            // Normal enqueue for other pages (like jobs listing)
            wp_enqueue_script('jquery');
            wp_enqueue_style('dashicons');

            wp_enqueue_style('xenhire-satoshi', plugin_dir_url(__FILE__) . 'css/xenhire-satoshi.css', [], '1.0.0');
            wp_enqueue_style('xenhire-public-css', plugin_dir_url(__FILE__) . 'css/xenhire-public.css', [], '1.0.0');
            wp_enqueue_style('line-awesome', plugin_dir_url(__FILE__) . 'css/line-awesome.min.css', [], '1.3.1');
            wp_enqueue_style('keen-icons', plugin_dir_url(__FILE__) . 'css/keen-icons.css', [], '1.0.1');
            
            // Checks for Jobs Page
            global $post; // Helper to check if we are on the jobs page
            if (is_page() && $post && $post->post_name === 'jobs') {
                 wp_enqueue_style('intl-tel-input', plugin_dir_url(__FILE__) . 'css/intlTelInput.css', [], '24.8.2');
                 wp_enqueue_style('xenhire-jobs-public', plugin_dir_url(__FILE__) . 'css/xenhire-jobs-public.css', [], '1.0.0');
                 
                 // Add dynamic CSS variables
                 $primary_color = get_option('xenhire_primary_color', '#9777fa');
                 $secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
                 $tagline_color = get_option('xenhire_tagline_color', '#000000');
                 $custom_css = "
                     :root {
                         --xh-primary: " . esc_attr($primary_color) . ";
                         --xh-secondary: " . esc_attr($secondary_color) . ";
                         --xh-tagline: " . esc_attr($tagline_color) . ";
                     }
                 ";
                 wp_add_inline_style('xenhire-jobs-public', $custom_css);
                 
                 wp_enqueue_script('xenhire-jobs-public', plugin_dir_url(__FILE__) . 'js/xenhire-jobs-public.js', ['jquery'], '1.0.0', true);
            }
        }
        
        // Get Candidate Login URL
        $login_url = home_url('/candidate-login/');

        // Check for candidate cookie
        $candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';
        $is_logged_in = !empty($candidate_email);

        wp_localize_script('jquery', 'xenhireAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('xenhire_nonce'),
            'is_logged_in' => $is_logged_in ? '1' : '',
            'candidate_email' => $candidate_email,
            'api_key' => XenHire_Auth::get_api_key(),
            'login_url' => $login_url,
            'jobs_url' => home_url('/jobs/'),
            'brand_logo' => get_option('xenhire_brand_logo'),
            'brand_name' => get_option('xenhire_brand_name'),
        ]);
    }

    public function dequeue_theme_assets() {
        $is_standalone = get_query_var('xenhire_candidate_login') || 
                         get_query_var('xenhire_before_begin') || 
                         get_query_var('xenhire_interview') ||
                         get_query_var('xenhire_interview_complete') ||
                         get_query_var('xenhire_share');

        if (!$is_standalone) return;

        global $wp_styles, $wp_scripts;

        // Allowed handles
        $allowed_styles = ['dashicons', 'admin-bar', 'xenhire-public-css', 'open-sans', 'buttons', 'line-awesome', 'xenhire-templates', 'xenhire-satoshi', 'keen-icons', 'xenhire-share', 'xenhire-before']; // Keep some basics if needed
        $allowed_scripts = ['jquery', 'jquery-core', 'jquery-migrate', 'xenhire-public-js', 'xenhire-ajax-script', 'xenhire-share', 'xenhire-before', 'xenhire-thanks', 'xenhire-interview'];

        // Dequeue Styles
        if (isset($wp_styles->queue)) {
            foreach ($wp_styles->queue as $handle) {
                if (!in_array($handle, $allowed_styles)) {
                    wp_dequeue_style($handle);
                    wp_deregister_style($handle);
                }
            }
        }

        // Dequeue Scripts
        if (isset($wp_scripts->queue)) {
            foreach ($wp_scripts->queue as $handle) {
                if (!in_array($handle, $allowed_scripts)) {
                    wp_dequeue_script($handle);
                    wp_deregister_script($handle);
                }
            }
        }
    }

    public function hide_admin_bar($show) {
        $is_standalone = get_query_var('xenhire_candidate_login') || 
                         get_query_var('xenhire_before_begin') || 
                         get_query_var('xenhire_interview') ||
                         get_query_var('xenhire_interview_complete') ||
                         get_query_var('xenhire_share');
        if ($is_standalone) {
            return false;
        }
        return $show;
    }

    public function filter_body_classes($classes) {
        $is_standalone = get_query_var('xenhire_candidate_login') || 
                         get_query_var('xenhire_before_begin') || 
                         get_query_var('xenhire_interview') ||
                         get_query_var('xenhire_interview_complete') ||
                         get_query_var('xenhire_share');
        
        if ($is_standalone) {
            // Return only essential classes, remove theme classes
            return ['xenhire-standalone-page'];
        }
        return $classes;
    }

    /**
     * Auto-replace content on the Jobs page (slug: 'jobs')
     */
    /**
     * Auto-replace content on the Jobs page (slug: 'jobs')
     */
    public function filter_jobs_page_content($content) {
        if (!is_page()) return $content;

        global $post;
        if (!$post || $post->post_name !== 'jobs') {
            return $content;
        }

        // Inject jobs listing markup
        ob_start();
        
        // Navbar Logic
        $candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';
        $is_logged_in = !empty($candidate_email);

        // Branding Colors
        $primary_color = get_option('xenhire_primary_color', '#9777fa');
        $secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
        $tagline_color = get_option('xenhire_tagline_color', '#000000');

        // --- NEW: Server-Side Candidate Data Fetching for Apply Action ---
        $candidate_data_js = null;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['action']) && $_GET['action'] === 'apply' && isset($_GET['job_id'])) {
             // phpcs:ignore WordPress.Security.NonceVerification.Recommended
             $job_id = intval($_GET['job_id']);
             $candidate_id = isset($_COOKIE['xenhire_candidate_id']) ? intval($_COOKIE['xenhire_candidate_id']) : 0;
             $api_key = XenHire_Auth::get_api_key(); // Assuming this helper exists or we get it from options

             if ($candidate_id && $api_key) {
                 // Needs to call 'Get_Candidate' with APIKey, ID, RequirementID
                 // Replicating: ExecSQL(SQLFormat("EXEC api_pb_Get_Candidate @APIKey='{0}', @ID={1}, @RequirementID={2}", ViewBag.APIKey, CandidateID, ViewBag.RequirementID));
                 
                 $args = [
                     ['Key' => 'ID', 'Value' => $candidate_id],
                     ['Key' => 'RequirementID', 'Value' => $job_id]
                     // APIKey is added automatically by public_call
                 ];
                 
                 $response = XenHire_API::public_call('Get_Candidate', $args);
                 
                 if ($response['success']) {
                     $data = $response['data'];
                     
                     // Helper to ensure JSON is decoded if string
                     if (is_string($data)) {
                         $decoded = json_decode($data, true);
                         if (json_last_error() === JSON_ERROR_NONE) {
                             $data = $decoded;
                         }
                     }
                     
                     // Normalize Data Structure (The C# code treats it as a DataSet with multiple tables)
                     // [0] -> Candidate Details
                     // [1] -> Education
                     // [2] -> Employment
                     // [3] -> Requirement / Settings
                     
                     $normalized_data = [];
                     
                     if (is_array($data)) {
                         // Table 0: Personal Details
                         if (isset($data[0]) && is_array($data[0])) {
                             $normalized_data['Personal'] = isset($data[0][0]) ? $data[0][0] : $data[0];
                         }
                         
                         // Table 1: Education
                         if (isset($data[1]) && is_array($data[1])) {
                             $normalized_data['Education'] = $data[1];
                         }
                         
                         // Table 2: Employment
                         if (isset($data[2]) && is_array($data[2])) {
                             $normalized_data['Employment'] = $data[2];
                         }
                         
                         // Table 3: Requirement / Settings
                         if (isset($data[3]) && is_array($data[3])) {
                             $normalized_data['Settings'] = isset($data[3][0]) ? $data[3][0] : $data[3];
                         }
                         
                         $candidate_data_js = $normalized_data;
                     }
                 }
             }
        }
        ?>
        


        <div class="xhj-navbar">
            <div id="xhj-navbar-actions" class="">
                <div class="xhj-nav-actions">
                    <div class="xhj-nav-brand">
                        <?php 
                        $brand_logo = get_option('xenhire_brand_logo');
                        $brand_name = get_option('xenhire_brand_name'); 

                        if (!empty($brand_logo)) {
                            $alt_text = !empty($brand_name) ? $brand_name : 'Brand Logo';
                            echo '<img src="' . esc_url($brand_logo) . '" alt="' . esc_attr($alt_text) . '" style="max-width:180px;max-height: 40px;">';
                        } elseif (!empty($brand_name)) {
                            echo esc_html($brand_name); 
                        }
                        ?>
                    </div>
                    <div class="xhj-nav-user-area">
                        <?php if ($is_logged_in): ?>
                            <span class="xhj-user-email">Welcome, <em><?php echo esc_html($candidate_email); ?></em></span>
                            <button class="xhj-btn-logout">Logout</button>
                        <?php else: ?>
                            <!-- <a href="#" class="xhj-btn xhj-btn-primary xhj-btn-login">Candidate Login</a> -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        
        <div id="xenhire-public-jobs" class="xhj-wrap">
            
            <!-- List View -->
            <div id="xhj-list-view">
                <div class="xhj-head">
                    <h2 class="xhj-title">Join Our Team and Shape Your Future</h2>
                    <div class="xhj-search-wrapper">
                        <input type="text" id="xhj-search-input" class="xhj-search-input" placeholder="JOB TITLE">
                        <button id="xhj-search-btn" class="xhj-btn xhj-btn-success">Search</button>
                    </div>
                </div>
                <div class="xhj-loading">Loading jobs...</div>
                <div class="xhj-empty" style="display:none;">No active jobs found.</div>
                <div class="xhj-jobs-grid" style="display:none;">
                    <!-- Cards will be injected here -->
                </div>
            </div>

            <!-- Details View -->
            <div id="xhj-details-view" style="display:none;">
                <div class="xhj-loading-details">Loading details...</div>
                
                <div class="xhj-details-content" style="display:none;">
                    <div style="margin-bottom: 20px; display:none;">
                        <button id="xhj-back-btn" class="xhj-btn xhj-btn-secondary">&larr; Back to Jobs</button>
                    </div>
                    <div class="xhj-grid">
                        <!-- Sidebar -->
                        <div class="xhj-sidebar">
                            <div class="xhj-card xhj-meta-card">
                                <!-- Experience -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Experience</label>
                                        <span id="xhj-meta-exp"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-briefcase"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Salary -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Salary</label>
                                        <span id="xhj-meta-salary"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-wallet"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- City -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>City</label>
                                        <span id="xhj-meta-city"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-map-marker"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Employment Type -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Employment Type</label>
                                        <span id="xhj-meta-type"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-hands-helping"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Posted Date -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Posted Date</label>
                                        <span id="xhj-meta-date"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-calendar"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Last Date -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Last Date</label>
                                        <span id="xhj-meta-deadline"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las las la-calendar"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Already Applied -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Already Applied</label>
                                        <span id="xhj-meta-applied"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-user-check"></i>
                                    </div>
                                </div>
                                <div class="xhj-divider"></div>

                                <!-- Email -->
                                <div class="xhj-meta-item">
                                    <div class="xhj-meta-content">
                                        <label>Contact Email</label>
                                        <span id="xhj-meta-email"></span>
                                    </div>
                                    <div class="xhj-meta-icon">
                                        <i class="las la-envelope"></i>
                                    </div>
                                </div>

                                <div class="xhj-meta-action">
                                    <button class="xhj-btn xhj-btn-disabled" disabled>Hiring Closed</button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="xhj-main">
                            <div class="xhj-header-card">
                                <div class="xhj-header-top">
                                    <h1 id="xhj-detail-title"></h1>
                                    <button class="xhj-btn xhj-btn-success xhj-btn-share">
                                        <i class="las la-share"></i>
                                        Share
                                    </button>
                                </div>
                                <div class="xhj-header-badges">
                                    <span id="xhj-detail-badge" class="xhj-badge xhj-badge-success"></span>
                                    <span id="xhj-detail-deadline" class="xhj-badge xhj-badge-danger" style="display:none;"></span>
                                    <span id="xhj-detail-applied" class="xhj-badge xhj-badge-info" style="display:none;"></span>
                                </div>
                            </div>

                            <!-- Accordions -->
                            <div class="xhj-accordions">
                                <!-- About Company -->
                                <div class="xhj-card xhj-accordion-item">
                                    <div class="xhj-accordion-header active">
                                        <h3>About Company</h3>
                                        <span class="xhj-accordion-icon"></span>
                                    </div>
                                    <div class="xhj-accordion-body" style="display:block;">
                                        <div id="xhj-detail-company"></div>
                                    </div>
                                </div>

                                <!-- Job Description -->
                                <div class="xhj-card xhj-accordion-item">
                                    <div class="xhj-accordion-header active">
                                        <h3>Job Description</h3>
                                        <span class="xhj-accordion-icon"></span>
                                    </div>
                                    <div class="xhj-accordion-body" style="display:block;">
                                        <div id="xhj-detail-desc"></div>
                                    </div>
                                </div>

                                <!-- Roles and Responsibilities -->
                                <div class="xhj-card xhj-accordion-item">
                                    <div class="xhj-accordion-header active">
                                        <h3>Roles and Responsibilities</h3>
                                        <span class="xhj-accordion-icon"></span>
                                    </div>
                                    <div class="xhj-accordion-body" style="display:block;">
                                        <div id="xhj-detail-roles"></div>
                                    </div>
                                </div>

                                <!-- Required Skills -->
                                <div class="xhj-card xhj-accordion-item">
                                    <div class="xhj-accordion-header active">
                                        <h3>Required Skills</h3>
                                        <span class="xhj-accordion-icon"></span>
                                    </div>
                                    <div class="xhj-accordion-body" style="display:block;">
                                        <div id="xhj-detail-skills"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Application View -->
        <div id="xhj-application-view" style="display:none;">
            <div class="xhj-app-layout">
                <!-- Main Form (Centered and Wide) -->
                <div class="xhj-app-main">
                    <div class="xhj-app-header">
                        <h2 id="xhj-app-job-title">Python</h2>
                        <p>Complete your job application</p>
                        <input type="hidden" id="xhj-app-job-id" value="">
                    </div>

                    <!-- Resume Upload -->
                    <div id="xhj-upload-section" class="xhj-card xhj-upload-card">
                        <h3>Upload Resume</h3>
                        <p>Upload resume to create your profile</p>
                        <div class="xhj-upload-area">
                            <input type="file" id="xhj-resume-upload" style="display:none;" accept=".pdf,.doc,.docx">
                            <div class="xhj-file-preview" style="display:none;">
                                <span class="xhj-file-name"></span>
                                <span class="xhj-file-size"></span>
                                <button class="xhj-btn-remove-file">&times;</button>
                            </div>
                            <div class="xhj-upload-placeholder" onclick="document.getElementById('xhj-resume-upload').click()">
                                <div class="xhj-upload-icon">
                                    <i class="ki-duotone ki-file-up fs-4x text-primary">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                                <span>Drag and drop your resume or click to browse</span>
                            </div>
                        </div>
                        <div class="xhj-skip-container">
                            <a href="javascript:void(0);" id="xhj-skip-resume">I don't have a resume, <strong>skip now</strong></a>
                        </div>
                        <!-- Parsing Loader -->
                        <div id="xhj-parsing-loader" style="display:none;">
                            <div class="xhj-loader-spinner"></div>
                            <p>Hold on, processing your resume. Usually takes 30 - 45 seconds</p>
                        </div>
                    </div>

                    

                    <div id="xhj-application-form-container" style="display:none;">

                    <!-- Personal Details -->
                    <div class="xhj-card xhj-form-card">
                        <div class="xhj-card-header-toggle active">
                            <div>
                                <h3>Add Personal Details</h3>
                                <p class="xhj-section-desc">Include your full name and at least one way for employers to reach you.</p>
                            </div>
                            <span class="xhj-toggle-icon"></span>
                        </div>
                        <div class="xhj-form-body">
                            <div class="xhj-profile-photo-upload" style="margin-bottom: 24px;">
                                <div class="xh-flex-column">
                                    <div class="xb-upload-area" id="xhj_photo_area">
                                        <div class="xb-preview" id="xhj_photo_preview"></div>
                                        <input type="file" id="xhj-photo-upload" style="display:none;" accept="image/png, image/jpeg">
                                        <button type="button" class="xb-upload-btn" onclick="document.getElementById('xhj-photo-upload').click()">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="xb-remove-btn" id="xhj-remove-photo">
                                            <span class="dashicons dashicons-no-alt"></span>
                                        </button>
                                    </div>
                                    <p class="description">Accepted formats: .png, .jpg. Max size: 1 MB.</p>
                                </div>
                            </div>

                            <div class="xhj-form-grid">
                                <div class="xhj-form-group">
                                    <label>First Name<span>*</span></label>
                                    <input type="text" class="xhj-input" id="xhj-first-name" placeholder="Enter First Name">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Last Name<span>*</span></label>
                                    <input type="text" class="xhj-input" id="xhj-last-name" placeholder="Enter Last Name">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Mobile<span>*</span></label>
                                    <div class="xhj-input-group">
                                        <!-- <span class="xhj-input-prefix">+91</span> -->
                                        <input type="text" class="xhj-input" id="xhj-mobile" placeholder="Enter Mobile Number">
                                    </div>
                                </div>
                                <div class="xhj-form-group">
                                    <label>Alternate Mobile</label>
                                    <input type="text" class="xhj-input" id="xhj-alt-mobile" placeholder="Alternate Mobile">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" class="xhj-input" id="xhj-dob">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Email ID</label>
                                    <input type="email" class="xhj-input" id="xhj-email" readonly style="background:#f3f4f6;">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Gender</label>
                                    <select class="xhj-input" id="xhj-gender">
                                        <option value="">Select Gender</option>
                                        <option value="1">Female</option>
                                        <option value="2">Male</option>
                                    </select>
                                </div>
                                <div class="xhj-form-group">
                                    <label>Current Annual Salary</label>
                                    <div class="xhj-input-icon-wrapper">
                                        <span class="xhj-input-icon">₹</span>
                                        <input type="number" class="xhj-input has-icon" id="xhj-salary" placeholder="0" min="0" value="0" step="any" oninput="if(this.value<0)this.value=0;">
                                    </div>
                                </div>
                                <div class="xhj-form-group">
                                    <label>Current City</label>
                                    <input type="text" class="xhj-input" id="xhj-city" placeholder="Enter City">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Preferred City</label>
                                    <input type="text" class="xhj-input" id="xhj-pref-city" placeholder="Preferred City">
                                </div>
                                <div class="xhj-form-group">
                                    <label>Industry</label>
                                    <input type="text" class="xhj-input" id="xhj-industry" placeholder="Enter Industry">
                                </div>
                                <div class="xhj-form-group">
                                    <label>LinkedIn Profile URL</label>
                                    <input type="text" class="xhj-input" id="xhj-linkedin" placeholder="linkedin.com/in/username">
                                </div>
                                <div class="xhj-form-group full-width">
                                    <label>Skills</label>
                                    <textarea class="xhj-input" id="xhj-skills" rows="3" placeholder="Enter skills separated by comma"></textarea>
                                </div>
                                <!-- Extra Fields Container (Participates in Grid) -->
                                <div id="xhj-extra-fields-grid" style="display:contents;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="xhj-card xhj-form-card">
                        <div class="xhj-card-header-toggle active">
                            <div>
                                <h3>Add Employment Details</h3>
                                <p class="xhj-section-desc">Fill in your employment details</p>
                            </div>
                            <span class="xhj-toggle-icon"></span>
                        </div>
                        <div class="xhj-form-body">
                            <div id="xhj-employment-container">
                                <!-- Dynamic Employment Fields will go here -->
                            </div>
                            <button class="xhj-btn xhj-btn-primary xhj-btn-sm" id="xhj-add-employment">+ Add More</button>
                        </div>
                    </div>

                    <!-- Education Details -->
                    <div class="xhj-card xhj-form-card">
                        <div class="xhj-card-header-toggle active">
                            <div>
                                <h3>Add Education Details</h3>
                                <p class="xhj-section-desc">Fill in your education details</p>
                            </div>
                            <span class="xhj-toggle-icon"></span>
                        </div>
                        <div class="xhj-form-body">
                            <div id="xhj-education-container">
                                <!-- Dynamic Education Fields will go here -->
                            </div>
                            <button class="xhj-btn xhj-btn-primary xhj-btn-sm" id="xhj-add-education">+ Add More</button>
                        </div>
                    </div>



                    <!-- Submit Action -->
                    <div class="xhj-app-footer">
                        <button id="xhj-btn-submit-app" class="xhj-btn xhj-btn-primary xhj-btn-lg">Submit & Proceed to Interview</button>
                    </div>
                    </div> <!-- End #xhj-application-form-container -->

                </div>
            </div>
        </div>

        </div>

        
            <!-- Intl Tel Input CSS -->


            



        
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Public List Jobs (same proc as admin, using public_call with APIKey)
     */
    public function ajax_public_list_jobs() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        if (!class_exists('XenHire_API')) {
            wp_send_json_error(['message' => 'API not available']);
        }

        // Get all parameters including new ones
        $city        = isset($_POST['City']) ? sanitize_text_field(wp_unslash($_POST['City'])) : '';
        $job_title   = isset($_POST['JobTitle']) ? sanitize_text_field(wp_unslash($_POST['JobTitle'])) : '';
        // Check cookie for candidate_id if not in POST
        $candidate_id_cookie = isset($_COOKIE['xenhire_candidate_id']) ? intval($_COOKIE['xenhire_candidate_id']) : 0;
        $candidate_id = isset($_POST['CandidateID']) ? intval($_POST['CandidateID']) : $candidate_id_cookie;
        
        // Default to a guest ID or 0 if strictly needed, but 0 is usually fine for "Guest"
        if (!$candidate_id) $candidate_id = 0; 
        
        $offset      = isset($_POST['Offset']) ? intval($_POST['Offset']) : -330;
        $page_no     = isset($_POST['PageNo']) ? sanitize_text_field(wp_unslash($_POST['PageNo'])) : '1';
        $page_size   = isset($_POST['PageSize']) ? sanitize_text_field(wp_unslash($_POST['PageSize'])) : '9';

        // Call List_Requirement using public_call (APIKey auth, no login required)
        // The APIKey is automatically added as the first parameter by public_call method
        $result = XenHire_API::public_call('List_Requirement', [
            ['Key' => 'City',        'Value' => $city],
            ['Key' => 'JobTitle',    'Value' => $job_title],
            ['Key' => 'CandidateID', 'Value' => $candidate_id],
            ['Key' => 'Offset',      'Value' => $offset],
            ['Key' => 'PageNo',      'Value' => $page_no],
            ['Key' => 'PageSize',    'Value' => $page_size],
        ]);

        if (!$result || empty($result['success'])) {
            wp_send_json_error([
                'message' => isset($result['message']) ? $result['message'] : 'Failed to load jobs'
            ]);
        }

        // Decode the same way admin does
        $decoded = json_decode($result['data'], true);
        if (!is_array($decoded) || count($decoded) < 2) {
            wp_send_json_success([
                'jobs' => [],
                'metadata' => [
                    'TotalRecordCount' => 0,
                    'IsAllowJobAdd'    => false,
                    'Message'          => ''
                ]
            ]);
        }

        $jobs     = isset($decoded[0]) ? $decoded[0] : [];
        $metadata = isset($decoded[1][0]) ? $decoded[1][0] : [
            'TotalRecordCount' => 0,
            'IsAllowJobAdd'    => false,
            'Message'          => ''
        ];

        wp_send_json_success([
            'jobs'     => $jobs,
            'metadata' => $metadata
        ]);

    }

    /**
     * Shortcode: Candidate Login [xenhire_candidate_login]
     */
    public function render_candidate_login($atts) {
        $primary_color = get_option('xenhire_primary_color', '#9777fa');
        $secondary_color = get_option('xenhire_secondary_color', '#7c5fd6');
        $tagline_color = get_option('xenhire_tagline_color', '#9777fa'); // Default fallback
        
        // Enqueue the base CSS and JS
        wp_enqueue_style('xenhire-login-public', plugin_dir_url(__FILE__) . 'css/xenhire-login-public.css', [], '1.0.0');
        wp_enqueue_script('xenhire-login-public', plugin_dir_url(__FILE__) . 'js/xenhire-login-public.js', ['jquery'], '1.0.0', true);
        wp_enqueue_script('xenhire-candidate-login', plugin_dir_url(__FILE__) . 'js/xenhire-candidate-login.js', ['jquery'], '1.0.0', true);
        
        // Add dynamic CSS variables and background image
        $bg_image_url = esc_url(plugins_url('images/bg10.jpeg', __FILE__));
        $custom_css = "
            :root {
                --xh-primary: " . esc_attr($primary_color) . ";
                --xh-secondary: " . esc_attr($secondary_color) . ";
                --xh-tagline: " . esc_attr($tagline_color) . ";
            }
            .xh-candidate-login-wrapper {
                background-image: url('{$bg_image_url}');
            }
        ";
        wp_add_inline_style('xenhire-login-public', $custom_css);

        ob_start();
        ?>
        <div class="xh-candidate-login-wrapper">
            <div class="xh-candidate-left">
                <img src="<?php echo esc_url( plugins_url('images/agency.png', __FILE__) ); ?>" alt="candidate-login">
                <h2>Fast, Efficient and Productive</h2>
                <p>Your Interview Coach, whether you’re a fresher entering the job market or a professional aiming for your next big role.</p>
            </div>
            <div class="xh-candidate-card">
            <div class="xh-candidate-header">
                    <h2>Begin Application</h2>
                    <span class="xh-brand">
                        <?php 
                        $brand_logo = get_option('xenhire_brand_logo');
                        $brand_name = get_option('xenhire_brand_name'); // No default 'XenHire'

                        if (!empty($brand_logo)) {
                            // Use brand name for alt text if available, otherwise generic
                            $alt_text = !empty($brand_name) ? $brand_name : 'Brand Logo';
                            echo '<img src="' . esc_url($brand_logo) . '" alt="' . esc_attr($alt_text) . '" style="max-width:180px;max-height: 40px;">';
                        } elseif (!empty($brand_name)) {
                            echo esc_html($brand_name); 
                        }
                        ?>
                    </span>
                </div>

                <div id="xh-step-email" class="xh-step">
                    <div class="xh-form-group">
                        <label>Email Address</label>
                        <input type="email" id="xh-email" class="xh-input" placeholder="Enter email address">
                    </div>
                    <button id="xh-btn-get-otp" class="xh-btn xh-btn-primary xh-btn-block">Get Verification Code</button>
                </div>

                <div id="xh-step-otp" class="xh-step" style="display:none;">
                    <div class="xh-form-group">
                        <label>Email Address</label>
                        <input type="email" id="xh-email-display" class="xh-input" readonly>
                    </div>

                    <div class="xh-form-group">
                        <label>Verification code sent on email</label>
                        <input type="text" id="xh-otp" class="xh-input" placeholder="4 digit code" maxlength="4">
                    </div>

                    <div class="xh-resend-wrapper">
                        <button id="xh-btn-resend" class="xh-btn-resend" style="display:none;">Resend Code</button>
                        <span id="xh-resend-text" class="xh-resend-text">Resend Code in <span id="xh-timer">1:18</span></span>
                    </div>

                    <button id="xh-btn-verify" class="xh-btn xh-btn-primary xh-btn-block">Get Started</button>
                </div>
            </div>
        </div>

        

        
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Candidate Send OTP
     */
    public function ajax_candidate_send_otp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $api_key = XenHire_Auth::get_api_key();

        // Fallback to hardcoded key if option is missing (for immediate fix)
        if (empty($api_key)) {
            $api_key = 'CFD99E5B-25CE-402F-A007-EE682C0E8D63';

        }

        if (empty($email)) {
            wp_send_json_error(['message' => 'Email is required']);
        }

        if (empty($api_key)) {
            wp_send_json_error(['message' => 'API Configuration missing (API Key)']);
        }

        $response = wp_remote_post('https://app.xenhire.com/Home/SendOTP', [
            'body' => [
                'APIKey' => $api_key,
                'Email' => $email,
                'IsMobileLogin' => 'false'
            ],
            'headers' => [
                'Referer' => 'https://app.xenhire.com/x/navtech/signin',
                'Origin' => 'https://app.xenhire.com'
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Network error: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        

        
        $json = json_decode($body, true);
        
        // Check for "true" string OR JSON Result="OK"
        if (
            ($body === 'true') || 
            (strpos($body, 'true') !== false) ||
            (isset($json['Result']) && $json['Result'] === 'OK')
        ) {
             wp_send_json_success(['message' => 'OTP sent successfully']);
        } else {
             $msg = isset($json['Message']) ? $json['Message'] : $body;
             
             wp_send_json_error([
                 'message' => 'Failed to send OTP: ' . $msg,
                 'debug_body' => $body,
                 'status_code' => $code
             ]);
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    /**
     * AJAX: Candidate Verify OTP
     */
    public function ajax_candidate_verify_otp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $otp = isset($_POST['otp']) ? sanitize_text_field(wp_unslash($_POST['otp'])) : '';
        $api_key = XenHire_Auth::get_api_key();

        // Fallback to hardcoded key if option is missing
        if (empty($api_key)) {
            $api_key = 'CFD99E5B-25CE-402F-A007-EE682C0E8D63';
        }

        if (empty($email) || empty($otp)) {
            wp_send_json_error(['message' => 'Email and OTP are required']);
        }

        $response = wp_remote_post('https://app.xenhire.com/Home/VerifyOTP', [
            'body' => [
                'APIKey' => $api_key,
                'Email' => $email,
                'OTP' => $otp,
                'IsMobileLogin' => 'false'
            ],
            'headers' => [
                'Referer' => 'https://app.xenhire.com/x/navtech/signin',
                'Origin' => 'https://app.xenhire.com'
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Network error: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        

        
        $json = json_decode($body, true);
        
        // Check for "true" string OR JSON Result="OK"
        if (
            ($body === 'true') || 
            (strpos($body, 'true') !== false) ||
            (isset($json['Result']) && $json['Result'] === 'OK')
        ) {
             // Success - Set Cookie for 1 day
             setcookie('xenhire_candidate_logged_in', 'true', time() + 86400, '/');
             setcookie('xenhire_candidate_email', $email, time() + 86400, '/');
             setcookie('xenhire_candidate_otp', $otp, time() + 86400, '/'); // Save OTP for API calls
             
             // Extract CandidateID if available
             $candidate_id = 0;
             if (isset($json['CandidateID'])) $candidate_id = $json['CandidateID'];
             elseif (isset($json['ID'])) $candidate_id = $json['ID'];
             elseif (isset($json['Data']) && is_numeric($json['Data'])) $candidate_id = $json['Data']; // Sometimes Data IS the ID
             elseif (isset($json['Data']['CandidateID'])) $candidate_id = $json['Data']['CandidateID'];
             
             if ($candidate_id) {
                 setcookie('xenhire_candidate_id', $candidate_id, time() + 86400, '/');
             }

             wp_send_json_success(['message' => 'Login successful', 'candidate_id' => $candidate_id]);
        } else {
             $msg = isset($json['Message']) ? $json['Message'] : $body;
             
             wp_send_json_error([
                 'message' => 'Verification failed: ' . $msg,
                 'debug_body' => $body,
                 'status_code' => $code
             ]);
        }
    }


    /**
     * AJAX: Public Get Job Details
     */
    public function ajax_public_get_job_details() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
        // Use authenticated call to fetch details (Get_Requirement requires auth)
        // This uses the stored access token from the plugin settings
        $result = XenHire_API::call('Get_Requirement', array(
            array('Key' => 'ID', 'Value' => $job_id)
        ));
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Structure is usually [[{...}]] for single row
            if (is_array($data) && isset($data[0][0])) {
                $job_details = $data[0][0];

                // Decrypt/Decode fields
                if (!empty($job_details['JobDescription'])) {
                    $decoded = urldecode($job_details['JobDescription']);
                    $job_details['JobDescription'] = $decoded;
                }

                if (!empty($job_details['JobRole'])) {
                    $decoded = urldecode($job_details['JobRole']);
                    $job_details['JobRole'] = $decoded;
                }

                // --- Start Enrichment ---
                
                // 1. Employment Type Text
                if (!empty($job_details['EmploymentTypeID'])) {
                    $cbo = XenHire_API::get_cbo('EmploymentType');
                    if ($cbo['success'] && !empty($cbo['data'])) {
                        foreach ($cbo['data'] as $item) {
                            $val = isset($item['Value']) ? $item['Value'] : (isset($item['ID']) ? $item['ID'] : '');
                            if ($val == $job_details['EmploymentTypeID']) {
                                $job_details['EmploymentType'] = isset($item['DisplayText']) ? $item['DisplayText'] : (isset($item['Text']) ? $item['Text'] : '');
                                break;
                            }
                        }
                    }
                }

                // 2. Job Applications Count
                $app_args = [
                    ['Key' => 'RequirementID', 'Value' => $job_id],
                    ['Key' => 'PageSize', 'Value' => 1],
                    ['Key' => 'PageNo', 'Value' => 1],
                    ['Key' => 'Search', 'Value' => ''],
                    ['Key' => 'RatingID', 'Value' => -1],
                    ['Key' => 'AIScore', 'Value' => -1],
                    ['Key' => 'StageID', 'Value' => -1],
                    ['Key' => 'IsInterviewComplete', 'Value' => -1],
                    ['Key' => 'Email', 'Value' => ''],
                    ['Key' => 'Mobile', 'Value' => ''],
                    ['Key' => 'ExpInYearsFrom', 'Value' => 0],
                    ['Key' => 'ExpInYearsTo', 'Value' => 0],
                    ['Key' => 'CurrentSalaryFrom', 'Value' => 0],
                    ['Key' => 'CurrentSalaryTo', 'Value' => 0]
                ];
                $app_res = XenHire_API::call('List_JobApplication', $app_args);
                
                $job_details['JobApplications'] = 0; // Default
                if ($app_res['success'] && !empty($app_res['data'])) {
                    $app_data = json_decode($app_res['data'], true);
                    if (isset($app_data[1][0]['TotalRecordCount'])) {
                        $job_details['JobApplications'] = $app_data[1][0]['TotalRecordCount'];
                    }
                }
                
                // 3. Posted Date Mapping
                if (empty($job_details['PostedDate']) && !empty($job_details['CreatedOn'])) {
                    $job_details['PostedDate'] = $job_details['CreatedOn'];
                }
                // --- End Enrichment ---

                wp_send_json_success($job_details);
            } else {
                // DEBUG: Return raw data to see structure
                wp_send_json_error(array(
                    'message' => 'Job not found',
                    'debug_data' => $data,
                    'raw_result' => $result
                ));
            }
        } else {
            wp_send_json_error(array(
                'message' => isset($result['message']) ? $result['message'] : 'Failed to load job details',
                'raw_result' => $result
            ));
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    /**
     * AJAX: Public Submit Application
     */
    public function ajax_public_submit_application() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            wp_send_json_error(['message' => 'Required fields missing']);
        }

        if ($job_id <= 0) {
            wp_send_json_error(['message' => 'Invalid Job ID']);
        }

        // Handle File Uploads
        $resume_url = '';
        $photo_url = '';

        // Conditionally include media handling functions
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        if (!empty($_FILES['resume']['name'])) {
            $resume_id = media_handle_upload('resume', 0);
            if (!is_wp_error($resume_id)) {
                $resume_url = wp_get_attachment_url($resume_id);

            }
        } elseif (!empty($_POST['resume_file'])) {
             // Use the path from parsing if no new file uploaded
             $resume_url = sanitize_text_field(wp_unslash($_POST['resume_file']));
        }

        if (!empty($_FILES['photo']['name'])) {
            $photo_id = media_handle_upload('photo', 0);
            if (!is_wp_error($photo_id)) {
                $photo_url = wp_get_attachment_url($photo_id);

            }
        }

        // Prepare API Args
        // Get CandidateID from cookie if available, or default to 0
        $candidate_id = isset($_COOKIE['xenhire_candidate_id']) ? intval($_COOKIE['xenhire_candidate_id']) : 0; 
        
        // Map Gender
        $gender_val = isset($_POST['gender']) ? sanitize_text_field(wp_unslash($_POST['gender'])) : '';
        $gender_id = '-1'; // Default to -1 (per curl command) instead of 0
        if (strtolower($gender_val) == 'male') $gender_id = '1';
        if (strtolower($gender_val) == 'female') $gender_id = '2';

        $salary = isset($_POST['salary']) ? sanitize_text_field(wp_unslash($_POST['salary'])) : '';
        if ($salary === '' || $salary < 0) $salary = '0';

        $args = [
            ['Key' => 'FirstName', 'Value' => $first_name],
            ['Key' => 'LastName', 'Value' => $last_name],
            ['Key' => 'Email', 'Value' => $email],
            ['Key' => 'Mobile', 'Value' => isset($_POST['mobile']) ? sanitize_text_field(wp_unslash($_POST['mobile'])) : ''],
            ['Key' => 'AltMobile', 'Value' => isset($_POST['alt_mobile']) ? sanitize_text_field(wp_unslash($_POST['alt_mobile'])) : ''],
            ['Key' => 'LinkedInURL', 'Value' => isset($_POST['linkedin']) ? esc_url_raw(wp_unslash($_POST['linkedin'])) : ''],
            ['Key' => 'GenderID', 'Value' => $gender_id],
            ['Key' => 'CurrentSalary', 'Value' => $salary],
            ['Key' => 'CurrentCity', 'Value' => isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : ''],
            ['Key' => 'PreferredCity', 'Value' => isset($_POST['pref_city']) ? sanitize_text_field(wp_unslash($_POST['pref_city'])) : ''],
            ['Key' => 'ResumeFILE', 'Value' => $resume_url], 
            ['Key' => 'PhotoIMG', 'Value' => $photo_url],
            ['Key' => 'Keywords', 'Value' => isset($_POST['keywords']) ? sanitize_text_field(wp_unslash($_POST['keywords'])) : ''],
            ['Key' => 'Industry', 'Value' => isset($_POST['industry']) ? sanitize_text_field(wp_unslash($_POST['industry'])) : ''],
            ['Key' => 'RequirementID', 'Value' => $job_id],
            ['Key' => 'DOB', 'Value' => isset($_POST['dob']) ? sanitize_text_field(wp_unslash($_POST['dob'])) : ''],
            ['Key' => 'CandidateID', 'Value' => $candidate_id],
            ['Key' => 'OTPCode', 'Value' => isset($_COOKIE['xenhire_candidate_otp']) ? sanitize_text_field(wp_unslash($_COOKIE['xenhire_candidate_otp'])) : '']
        ];

        if (!empty($_POST['employment'])) {
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string decoded and values sanitized later
             $emp_json = wp_unslash($_POST['employment']);
             $emp_arr = json_decode($emp_json, true);
             if (is_array($emp_arr)) {
                 foreach ($emp_arr as $key => $val) {
                     $emp_arr[$key]['company'] = isset($val['company']) ? sanitize_text_field($val['company']) : '';
                     $emp_arr[$key]['designation'] = isset($val['designation']) ? sanitize_text_field($val['designation']) : '';
                     $emp_arr[$key]['start'] = isset($val['start']) ? sanitize_text_field($val['start']) : '';
                     $emp_arr[$key]['end'] = isset($val['end']) ? sanitize_text_field($val['end']) : '';
                     $emp_arr[$key]['description'] = isset($val['description']) ? sanitize_textarea_field($val['description']) : '';
                 }
             }
             if (is_array($emp_arr) && count($emp_arr) > 0) {
                 // Prepare XML (NewDataSet)
                 $xml = '<NewDataSet>';
                 $json_arr = [];
                 
                 foreach ($emp_arr as $row) {
                     // Sanitize Usage
                     $employer = sanitize_text_field($row['company']);
                     $designation = sanitize_text_field($row['designation']);
                     $description = sanitize_textarea_field($row['description']);
                     $start_date_raw = sanitize_text_field($row['start']);
                     $end_date_raw = sanitize_text_field($row['end']);

                     $xml .= '<Employment>';
                     // Add CandidateID to link child row
                     $xml .= '<CandidateID>' . $candidate_id . '</CandidateID>';
                     $xml .= '<Employer>' . htmlspecialchars($employer) . '</Employer>';
                     $xml .= '<Designation>' . htmlspecialchars($designation) . '</Designation>';
                     
                     // Helper for YYYYMMDD
                     $fmt_date = function($d) {
                         if (!$d) return '';
                         $d = str_replace('-', '', $d); 
                         if (strlen($d) == 6) $d .= '01'; 
                         if (strlen($d) == 4) $d .= '0101'; 
                         return $d;
                     };
                     
                     $sd = $fmt_date($start_date_raw);
                     $ed = $fmt_date($end_date_raw);
                     
                     $xml .= '<StartDatestamp>' . $sd . '</StartDatestamp>';
                     $xml .= '<EndDatestamp>' . $ed . '</EndDatestamp>';
                     $xml .= '<Description>' . htmlspecialchars($description) . '</Description>';
                     $xml .= '</Employment>';
                     
                     // Prepare Clean JSON Object
                     $json_arr[] = [
                         'CandidateID' => $candidate_id,
                         'Employer' => $employer,
                         'Designation' => $designation,
                         'StartDatestamp' => $sd,
                         'EndDatestamp' => $ed,
                         'Description' => $description
                     ];
                 }
                 $xml .= '</NewDataSet>';
                 
                 // Shotgun Approach: Send all probable keys
                 $args[] = ['Key' => 'Employment', 'Value' => $xml];
                 $args[] = ['Key' => 'EmploymentXML', 'Value' => $xml];
                 $args[] = ['Key' => 'EmploymentData', 'Value' => $xml]; // Try Data suffix
                 $args[] = ['Key' => 'EmploymentJSON', 'Value' => wp_json_encode($json_arr)];
             }
        }

        if (!empty($_POST['education'])) {
             // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON string decoded and values sanitized later
             $edu_json = wp_unslash($_POST['education']);
             $edu_arr = json_decode($edu_json, true);
             if (is_array($edu_arr)) {
                 foreach ($edu_arr as $key => $val) {
                     $edu_arr[$key]['institute'] = isset($val['institute']) ? sanitize_text_field($val['institute']) : '';
                     $edu_arr[$key]['degree'] = isset($val['degree']) ? sanitize_text_field($val['degree']) : '';
                     $edu_arr[$key]['year'] = isset($val['year']) ? sanitize_text_field($val['year']) : '';
                     if (isset($val['start_year'])) {
                         $edu_arr[$key]['start_year'] = sanitize_text_field($val['start_year']);
                     }
                 }
             }
             if (is_array($edu_arr) && count($edu_arr) > 0) {
                 // Prepare XML
                 $xml = '<NewDataSet>';
                 $json_arr = [];
                 
                 foreach ($edu_arr as $row) {
                     // Sanitize Usage
                     $institute = sanitize_text_field($row['institute']);
                     $degree = sanitize_text_field($row['degree']);
                     $year_raw = sanitize_text_field($row['year']);
                     $start_year_raw = isset($row['start_year']) ? sanitize_text_field($row['start_year']) : '';

                     $xml .= '<Education>';
                     // Add CandidateID
                     $xml .= '<CandidateID>' . $candidate_id . '</CandidateID>';
                     $xml .= '<Institute>' . htmlspecialchars($institute) . '</Institute>';
                     $xml .= '<Qualification>' . htmlspecialchars($degree) . '</Qualification>';
                     
                     // Helper for YYYYMMDD
                     $fmt_date = function($d) {
                         if (!$d) return '';
                         $d = str_replace('-', '', $d); 
                         if (strlen($d) == 6) $d .= '01'; 
                         if (strlen($d) == 4) $d .= '0101'; 
                         return $d;
                     };

                     $passingDate = $fmt_date($year_raw); // Year field now holds date string
                     $admissionDate = !empty($start_year_raw) ? $fmt_date($start_year_raw) : '';
                     
                     $xml .= '<PassingDatestamp>' . $passingDate . '</PassingDatestamp>'; 
                     if (!empty($admissionDate)) {
                         $xml .= '<StartDatestamp>' . $admissionDate . '</StartDatestamp>';
                     }
                     $xml .= '</Education>';
                     
                     $json_arr[] = [
                         'CandidateID' => $candidate_id,
                         'Institute' => $institute,
                         'Qualification' => $degree,
                         'PassingDatestamp' => $passingDate,
                         'StartDatestamp' => $admissionDate
                     ];
                 }
                 $xml .= '</NewDataSet>';
                 
                 // Shotgun Approach
                 $args[] = ['Key' => 'Education', 'Value' => $xml];
                 $args[] = ['Key' => 'EducationXML', 'Value' => $xml];
                 $args[] = ['Key' => 'EducationData', 'Value' => $xml]; // Try Data suffix
                 $args[] = ['Key' => 'EducationJSON', 'Value' => wp_json_encode($json_arr)];
             }
        }

        // Add Extra Data placeholders
        for ($i = 1; $i <= 4; $i++) {
            $val = isset($_POST['extra_data_' . $i . '_val']) ? sanitize_text_field(wp_unslash($_POST['extra_data_' . $i . '_val'])) : '';
            $label = isset($_POST['extra_data_' . $i . '_label']) ? sanitize_text_field(wp_unslash($_POST['extra_data_' . $i . '_label'])) : '';
            
            $args[] = ['Key' => "ExtraData{$i}Label", 'Value' => $label];
            $args[] = ['Key' => "ExtraData{$i}Val", 'Value' => $val];
        }

        // Call Set_Candidate
        $response = XenHire_API::public_call('Set_Candidate', $args);



        if ($response['success']) {
            $data = $response['data'];
            
            // Check if Data is a JSON string and decode it
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }

            $app_id = 0;
            $candidate_id = 0;
            
            // Extract ID logic
            // Priority: JobApplicationID > ID > CandidateID
            
            // Helper function to find key in array
            $find_key = function($arr, $key) {
                if (isset($arr[$key])) return $arr[$key];
                return null;
            };

            // 1. Check top level
            $app_id = $find_key($data, 'JobApplicationID');
            if (!$app_id) $app_id = $find_key($data, 'ID');
            if (!$app_id) $app_id = $find_key($data, 'CandidateID');

            // 2. Check first item if array
            if (!$app_id && isset($data[0])) {
                if (is_array($data[0])) {
                    $app_id = $find_key($data[0], 'JobApplicationID');
                    if (!$app_id) $app_id = $find_key($data[0], 'ID');
                    if (!$app_id) $app_id = $find_key($data[0], 'CandidateID');
                } elseif (is_numeric($data[0])) {
                    $app_id = $data[0];
                }
            }

            // 3. Check nested array [[{...}]]
            if (!$app_id && isset($data[0][0]) && is_array($data[0][0])) {
                $app_id = $find_key($data[0][0], 'JobApplicationID');
                if (!$app_id) $app_id = $find_key($data[0][0], 'ID');
                if (!$app_id) $app_id = $find_key($data[0][0], 'CandidateID');
            }
            
            // Extract CandidateID specifically
            $candidate_id = 0;
            if (isset($data['CandidateID'])) $candidate_id = $data['CandidateID'];
            elseif (isset($data[0]['CandidateID'])) $candidate_id = $data[0]['CandidateID'];
            elseif (isset($data[0][0]['CandidateID'])) $candidate_id = $data[0][0]['CandidateID'];
            
            // If Set_Candidate returns the Candidate object but no explicit CandidateID key in some weird structure
            // we assume the main ID found is the CandidateID if we are in this flow.
            if (!$candidate_id && $app_id) {
                $candidate_id = $app_id;
            }

            // *** FIX: Explicitly Save Child Tables if CandidateID is valid ***
            if ($candidate_id > 0) {
                // Save Employment
                if (!empty($emp_arr)) {
                     foreach ($emp_arr as $emp) {
                         $emp_args = [
                             // ['Key' => 'ID', 'Value' => -1], // Removed ID as per curl
                             ['Key' => 'CandidateID', 'Value' => $candidate_id],
                             ['Key' => 'Employer', 'Value' => $emp['company']],
                             ['Key' => 'Designation', 'Value' => $emp['designation']],
                             ['Key' => 'StartDatestamp', 'Value' => $emp['start']], // Keep YYYY-MM-DD
                             ['Key' => 'EndDatestamp', 'Value' => $emp['end']],     // Keep YYYY-MM-DD
                             ['Key' => 'Description', 'Value' => $emp['description']]
                         ];
                         // Try likely name
                         $res = XenHire_API::public_call('Set_Employment', $emp_args);
                         if (!$res['success']) {
                             XenHire_API::public_call('Set_CandidateEmployment', $emp_args);
                         }
                     }
                }

                // Save Education
                if (!empty($edu_arr)) {
                    foreach ($edu_arr as $edu) {
                        $year = $edu['year'];
                        if (strlen($year) == 4) $year .= '-01-01'; // YYYY-MM-DD
 
                        // User specified curl uses AdmissionDatestamp.
                        $pass_year_int = intval(substr($year, 0, 4));
                        $admission_year_int = $pass_year_int - 3; 
                        if ($admission_year_int < 1900) $admission_year_int = $pass_year_int;
                        
                        $admission_date = $admission_year_int . '-07-01'; // YYYY-MM-DD

                        $edu_args = [
                            // ['Key' => 'ID', 'Value' => -1], // Removed ID as per curl
                            ['Key' => 'CandidateID', 'Value' => $candidate_id],
                            ['Key' => 'Institute', 'Value' => $edu['institute']],
                            ['Key' => 'Qualification', 'Value' => $edu['degree']],
                            ['Key' => 'PassingDatestamp', 'Value' => $year],
                            ['Key' => 'AdmissionDatestamp', 'Value' => $admission_date]
                        ];
                        
                        // User specified Set_Education implies this is preferred
                        $res = XenHire_API::public_call('Set_Education', $edu_args);
                        if (!$res['success']) {
                             XenHire_API::public_call('Set_CandidateEducation', $edu_args);
                        }
                    }
                }
            }


            // Extract OTPCode
            $otp_code = '';
            if (isset($data['OTPCode'])) $otp_code = $data['OTPCode'];
            elseif (isset($data[0]['OTPCode'])) $otp_code = $data[0]['OTPCode'];
            elseif (isset($data[0][0]['OTPCode'])) $otp_code = $data[0][0]['OTPCode'];

            // Fallback if ID is still 0
            if (!$app_id) {

            }

            $redirect_url = home_url('/before-you-begin/' . $job_id . '/?jid=' . $app_id);
            
            wp_send_json_success([
                'message' => 'Application submitted successfully',
                'redirect_url' => $redirect_url,
                'id' => $app_id,
                'candidate_id' => $candidate_id,
                'otp_code' => $otp_code, 
                'debug_data' => $data
            ]);
        } else {
            wp_send_json_error([
                'message' => isset($response['message']) ? $response['message'] : 'Application failed',
                'debug' => $response
            ]);
        }
    }


    /**
     * AJAX: Get Interview Questions
     */
    public function ajax_public_get_interview_questions() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        $app_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
        
        if ($app_id <= 0) {
            wp_send_json_error(['message' => 'Invalid Application ID']);
        }

        // Get Candidate Info from Cookie
        $candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';
        $candidate_id = isset($_COOKIE['xenhire_candidate_id']) ? sanitize_text_field(wp_unslash($_COOKIE['xenhire_candidate_id'])) : '';
        $otp_code = isset($_COOKIE['xenhire_candidate_otp']) ? sanitize_text_field(wp_unslash($_COOKIE['xenhire_candidate_otp'])) : '4588'; // Default to 4588 for fallback
        
        // Prepare API Args
        // Prepare API Args
        $args = [
            ['Key' => 'CandidateEmail', 'Value' => $candidate_email],
            ['Key' => 'CandidateMobile', 'Value' => ''],
            ['Key' => 'CandidateID', 'Value' => $candidate_id],
            ['Key' => 'OTPCode', 'Value' => $otp_code], 
            ['Key' => 'JobApplicationID', 'Value' => $app_id],
            ['Key' => 'ApplicationID', 'Value' => $app_id],
            ['Key' => 'JobID', 'Value' => $job_id]
        ];



        // Call Get_InterviewQuestion_V2
        $response = XenHire_API::public_call('Get_InterviewQuestion_V2', $args);



        if ($response['success']) {
            $data = $response['data'];
            // Parse if string
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            // Fallback to V1 if V2 is empty
            if (empty($data)) {
                $responseV1 = XenHire_API::public_call('Get_InterviewQuestion', $args);
                if ($responseV1['success']) {
                    $dataV1 = $responseV1['data'];
                    if (is_string($dataV1)) {
                        $dataV1 = json_decode($dataV1, true);
                    }
                    if (!empty($dataV1)) {
                        $data = $dataV1;
                        $response['debug_note'] = 'Fetched from V1';
                    }
                }
            }
            
            wp_send_json_success([
                'data' => $data,
                'debug_args' => $args, // Return args for debugging
                'debug_response' => $response // Return raw response for debugging
            ]);
        } else {
            wp_send_json_error([
                'message' => isset($response['message']) ? $response['message'] : 'Failed to fetch questions',
                'debug' => $response
            ]);
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    /**
     * AJAX: Save Interview Answer
     */
    public function ajax_public_save_interview_answer() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $question_type = isset($_POST['question_type']) ? intval($_POST['question_type']) : 0;
        $answer = isset($_POST['answer']) ? sanitize_text_field(wp_unslash($_POST['answer'])) : '';
        
        if ($app_id <= 0 || $question_id <= 0) {
            wp_send_json_error(['message' => 'Invalid Parameters']);
        }

        // Get Candidate Info from Cookie
        $candidate_email = isset($_COOKIE['xenhire_candidate_email']) ? sanitize_email(wp_unslash($_COOKIE['xenhire_candidate_email'])) : '';
        
        if (empty($candidate_email)) {
             wp_send_json_error(['message' => 'Candidate Email missing. Please login again.']);
        }

        // Prepare API Args
        // Note: Mapping answer to 'VideoURL' based on provided curl for text answers
        $args = [
            ['Key' => 'CandidateEmail', 'Value' => $candidate_email],
            ['Key' => 'JobApplicationID', 'Value' => $app_id],
            ['Key' => 'InterviewQuestionID', 'Value' => $question_id],
            ['Key' => 'InterviewQuestion', 'Value' => ''], // Empty as per curl
            ['Key' => 'InterviewQuestionAltLang', 'Value' => ''],
            ['Key' => 'QuestionTypeID', 'Value' => $question_type],
            ['Key' => 'Options', 'Value' => ''],
            ['Key' => 'VideoURL', 'Value' => $answer], // Answer mapped to VideoURL
            ['Key' => 'Width', 'Value' => 0],
            ['Key' => 'Height', 'Value' => 0]
        ];



        // Call Set_InterviewAnswer
        $response = XenHire_API::public_call('Set_InterviewAnswer', $args);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error([
                'message' => isset($response['message']) ? $response['message'] : 'Failed to save answer',
                'debug' => $response
            ]);
        }
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    /**
     * AJAX: Send Mail (Completion)
     */
    public function ajax_public_send_mail() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
        
        if ($app_id <= 0) {
            wp_send_json_error(['message' => 'Invalid Application ID']);
        }

        // Prepare API Args
        $args = [
            ['Key' => 'JobApplicationID', 'Value' => $app_id]
        ];



        // Call SendMail
        $response = XenHire_API::public_call('SendMail', $args);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error([
                'message' => isset($response['message']) ? $response['message'] : 'Failed to send mail',
                'debug' => $response
            ]);
        }
    }


    /**
     * AJAX: Upload Video & Convert
     */
    public function ajax_public_upload_video() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';


        try {
            // Check if file exists
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File processed by wp_handle_upload
            if (empty($_FILES['video'])) {
                throw new Exception('No video file uploaded');
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File processed by wp_handle_upload
            $file = $_FILES['video'];
            
            // Use wp_handle_upload to save locally first
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $filename = $movefile['file']; // Absolute path
                $file_url = $movefile['url'];  // Local URL
                $file_type = $movefile['type'];
                $filename_base = basename($filename);

                // Insert into Media Library
                $attachment = array(
                    'guid'           => $file_url,
                    'post_mime_type' => $file_type,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename_base ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $filename);
                
                // Generate metadata (this handles video metadata if ffmpeg/available)
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // Return local URL (now formally in Media Library)
                // We return 'url' which logic expects.
                wp_send_json_success([
                    'url' => $file_url,
                    'attachment_id' => $attach_id,
                    'message' => 'Uploaded to Media Library successfully.'
                ]);

            } else {
                throw new Exception('Upload failed: ' . $movefile['error']);
            }

        } catch (Exception $e) {

            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }


    /**
     * AJAX: Parse Resume
     */
    public function ajax_parse_resume() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        if (empty($_FILES['resume']['name'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';


        // 1. Upload Locally first (WordPress Standard)
        $resume_id = media_handle_upload('resume', 0);
        if (is_wp_error($resume_id)) {
            wp_send_json_error(['message' => 'Upload failed: ' . $resume_id->get_error_message()]);
        }

        $local_file_path = get_attached_file($resume_id);
        $local_file_url = wp_get_attachment_url($resume_id);
        $file_type = get_post_mime_type($resume_id);
        $file_name = basename($local_file_path);

        // 2. Upload to XenHire API (S3) using Candidate/UploadResume
        // This endpoint might parse the resume directly if IsParse=1 is sent
        $upload_response = XenHire_API::upload_resume($local_file_path, $file_name, $file_type, $job_id);

        $resume_url_for_parsing = '';

        if (!$upload_response['success']) {

            
            // Fallback to local URL even on localhost (user might be using ngrok or similar)
            // We do not block execution here anymore.
            $resume_url_for_parsing = $local_file_url;
        } else {
            $data = $upload_response['data'];
            
            // Debug: Log the upload response structure


            // Check if parsing data is already returned
            if (isset($data['Data']) && (isset($data['Data']['FirstName']) || isset($data['Data']['Email']))) {
                 // Parsing happened during upload!
                 $parsed_data = $data['Data'];
                 
                 // Ensure ResumeURL is set
                 if (isset($data['Url'])) {
                     $parsed_data['ResumeURL'] = $data['Url'];
                 } elseif (isset($data['ResumeURL'])) {
                     $parsed_data['ResumeURL'] = $data['ResumeURL'];
                 }
                 
                 wp_send_json_success($parsed_data);
                 return; // Exit, no need to call ParseResume again
            }

            // Extract S3 URL for fallback parsing
            $s3_url = '';
            if (isset($data['Url'])) {
                 $s3_url = $data['Url'];
            } elseif (isset($data['ResumeURL'])) {
                 $s3_url = $data['ResumeURL'];
            } elseif (isset($data[0]['Url'])) {
                 $s3_url = $data[0]['Url'];
            } elseif (isset($data[0]['ResumeURL'])) {
                 $s3_url = $data[0]['ResumeURL'];
            } elseif (is_string($data) && filter_var($data, FILTER_VALIDATE_URL)) {
                 $s3_url = $data;
            }
            


            $resume_url_for_parsing = $s3_url ? $s3_url : $local_file_url;

            // Fix for Localhost: Replace local URL with Public URL so API can access it
            // (Assumes file exists on public server or is accessible via this mapping)
            if (strpos($resume_url_for_parsing, 'localhost') !== false) {
                $resume_url_for_parsing = str_replace('http://localhost:8080/web', 'https://goldentriangletourexperts.com', $resume_url_for_parsing);
            }


        }

        // 3. Call External Parsing API (Fallback if upload didn't return parsed data)
        $api_url = 'https://app.xenhire.com/Home/ParseResume';
        
        // Get API Key
        $api_key = XenHire_Auth::get_api_key();
        if (empty($api_key)) {
            $api_key = 'CFD99E5B-25CE-402F-A007-EE682C0E8D63'; // Fallback
        }

        // Prepare body for JSON
        $body_args = [
            'fileUrl' => $resume_url_for_parsing,
            'RequirementID' => $job_id,
            'APIKey' => $api_key // APIKey is required in body for this endpoint
        ];

        $args = [
            'body' => wp_json_encode($body_args),
            'timeout' => 60,
            'sslverify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
                'Origin' => 'https://app.xenhire.com',
                'Referer' => 'https://app.xenhire.com/'
            ]
        ];



        $response = wp_remote_post($api_url, $args);

        if (is_wp_error($response)) {

            wp_send_json_error(['message' => 'Parsing request failed: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);





        $data = json_decode($body, true);

        if ($data) {
            // Inject the S3 URL into the response so frontend can use it for submission
            if (is_array($data)) {
                $data['ResumeURL'] = $resume_url_for_parsing;
            }
            
            // Check for API level error
            if (isset($data['Result']) && $data['Result'] === 'ERROR') {
                 wp_send_json_error([
                    'message' => $data['Message'],
                    'debug_url' => $resume_url_for_parsing,
                    'upload_success' => $upload_response['success'] ? 'yes' : 'no',
                    'upload_response_data' => $upload_response['data']
                ]);
            } else {
                // Sometimes Data is nested in 'Data' or 'data'
                if (isset($data['Data'])) {
                    $final_data = $data['Data'];
                    if (is_string($final_data)) {
                        $final_data = json_decode($final_data, true);
                    }
                    if (is_array($final_data)) {
                        $final_data['ResumeURL'] = $resume_url_for_parsing;
                        wp_send_json_success($final_data);
                        return;
                    }
                }
                
                wp_send_json_success($data);
            }
        } else {
            wp_send_json_error([
                'message' => 'Failed to parse response', 
                'debug_body' => substr($body, 0, 500),
                'status_code' => $code,
                'debug_url' => $resume_url_for_parsing
            ]);
        }
    }


    /**
     * AJAX: Get Share Data
     */
    public function ajax_public_get_share_data() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        $share_key = isset($_POST['share_key']) ? sanitize_text_field(wp_unslash($_POST['share_key'])) : '';
        
        if (empty($share_key)) {
            wp_send_json_error(['message' => 'Missing Share Key']);
        }
        
        // Real API Call (Authenticated Proxy via System Token)
if (class_exists('XenHire_API')) {
    
    // --- Lookup Application ID from Share Key ---
    global $wpdb;
    $table_name = $wpdb->prefix . 'xenhire_share_links';
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $app_id = $wpdb->get_var($wpdb->prepare("SELECT application_id FROM $table_name WHERE share_key = %s", $share_key));
    
    // If not found, check if it's a legacy numeric ID (only if we want to support backward compat, otherwise fail)
    if (!$app_id) {
        if (is_numeric($share_key)) {
             // Fallback for old links (Optional - Can remove if strict security is needed)
             $app_id = $share_key;
        } else {
             wp_send_json_error(['message' => 'Invalid Link']);
        }
    }

    // Try Get_JobApplication first (Standard way to get details)
    $result = XenHire_API::call('Get_JobApplication', array(
        array('Key' => 'ID', 'Value' => (string)$app_id)
    ));
    
    // If that fails or returns empty, try List_JobApplication as fallback
    if (!$result['success'] || empty($result['data']) || $result['data'] == '[]') {
         $result = XenHire_API::call('List_JobApplication', array(
            array('Key' => 'ID', 'Value' => (string)$app_id)
        ));
    }
        if ($result['success']) {
            $data = json_decode($result['data'], true);
            
            // Expected Structure: [ [CandidateInfo], [FeedbackInfo] ] or similar
            // Check recursivley for the candidate object
            $candidate = null;

            if (isset($data[0]) && is_array($data[0]) && count($data[0]) > 0) {
                 if (isset($data[0][0])) {
                     $candidate = $data[0][0];
                 } else {
                     $candidate = $data[0];
                 }
            } else if (isset($data['Candidate'])) {
                $candidate = $data;
            }

            if ($candidate) {
                
                // Find Interview Data dynamically (instead of hardcoded index 3)
            $interviewData = [];
            if (isset($data[3]) && is_array($data[3]) && count($data[3]) > 0 && isset($data[3][0]['Question'])) {
                $interviewData = $data[3];
            } else {
                // Scan for it
                foreach ($data as $key => $val) {
                    if (is_array($val) && count($val) > 0 && isset($val[0]['Question'])) {
                        $interviewData = $val;
                        break;
                    }
                }
            }

            // Extract Education (Table 1)
            $educationData = [];
            if (isset($data[1]) && is_array($data[1])) {
                $educationData = $data[1];
            }

            // Extract Employment (Table 2)
            $employmentData = [];
            if (isset($data[2]) && is_array($data[2])) {
                $employmentData = $data[2];
            }

            $response = [
                'Name' => !empty($candidate['Candidate']) ? $candidate['Candidate'] : (!empty($candidate['Name']) ? $candidate['Name'] : 'Unknown'),
                'Email' => !empty($candidate['Email']) ? $candidate['Email'] : '',
                'Mobile' => !empty($candidate['Mobile']) ? $candidate['Mobile'] : '',
                // Fix: Check if Designation is actually populated before using it, otherwise fallback to JobTitle
                'JobTitle' => !empty($candidate['JobTitle']) ? $candidate['JobTitle'] : (!empty($candidate['Designation']) ? $candidate['Designation'] : ''),
                'CurrentCity' => !empty($candidate['CurrentCity']) ? $candidate['CurrentCity'] : '',
                'CurrentSalary' => !empty($candidate['CurrentSalary']) ? $candidate['CurrentSalary'] : '',
                'PhotoURL' => !empty($candidate['PhotoIMG']) ? $candidate['PhotoIMG'] : XENHIRE_PLUGIN_URL . 'public/images/placeholder.png',
                'ResumeURL' => !empty($candidate['ResumeFILE']) ? $candidate['ResumeFILE'] : '',
                'Skills' => !empty($candidate['Keywords']) ? $candidate['Keywords'] : '',
                'MatchScore' => !empty($candidate['MatchScore']) ? $candidate['MatchScore'] : '',
                'MatchName' => !empty($candidate['MatchName']) ? $candidate['MatchName'] : '',
                'MatchDescription' => !empty($candidate['MatchComment']) ? $candidate['MatchComment'] : (!empty($candidate['MatchDescription']) ? $candidate['MatchDescription'] : ''), 
                'MatchStrengths' => !empty($candidate['MatchStrengths']) ? $candidate['MatchStrengths'] : '',
                'MatchWeaknesses' => !empty($candidate['MatchWeaknesses']) ? $candidate['MatchWeaknesses'] : '',
                'MatchSkillsYes' => !empty($candidate['MatchSkillsYes']) ? $candidate['MatchSkillsYes'] : '',
                'MatchSkillsNo' => !empty($candidate['MatchSkillsNo']) ? $candidate['MatchSkillsNo'] : '',
                'Experience' => !empty($candidate['ExpInYears']) ? $candidate['ExpInYears'] : '',
                'Education' => !empty($candidate['Qualification']) ? $candidate['Qualification'] : '', 
                'RawData' => $candidate,
                'InterviewData' => $interviewData,
                'EducationData' => $educationData,
                'EmploymentData' => $employmentData
            ];


                wp_send_json_success($response);
            } else {
                wp_send_json_error(['message' => 'Candidate data not found', 'debug' => $data]);
            }
        } else {
             wp_send_json_error(['message' => isset($result['message']) ? $result['message'] : 'API Error']);
        }
    } else {
        wp_send_json_error(['message' => 'API Class not found']);
    }
        
        // Try to fetch real photo if available? 
        // For now sticking to screenshot.
        // Fix for "No experience specified" logic in JS to handle array or string

    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    public function ajax_public_get_candidate_profile() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        $candidate_id = isset($_COOKIE['xenhire_candidate_id']) ? intval($_COOKIE['xenhire_candidate_id']) : 0;
        $api_key = XenHire_Auth::get_api_key();

        if (!$candidate_id || !$api_key || !$job_id) {
            wp_send_json_error(['message' => 'Missing credentials or job ID']);
        }

        // Call Get_Candidate
        $args = [
            ['Key' => 'ID', 'Value' => $candidate_id],
            ['Key' => 'RequirementID', 'Value' => $job_id]
        ];
        
        $response = XenHire_API::public_call('Get_Candidate', $args);
        
        if ($response['success']) {
            $data = $response['data'];
            
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }
            
            $normalized_data = [];
            
            if (is_array($data)) {
                // Table 0: Personal Details (Always assume first or scan for FirstName)
                if (isset($data[0]) && is_array($data[0])) {
                    $normalized_data['Personal'] = isset($data[0][0]) ? $data[0][0] : $data[0];
                }

                // Smart Scan for Child Tables
                $found_edu = false;
                $found_emp = false;

                foreach ($data as $key => $table) {
                    if ($key === 0) continue; // Skip Personal
                    if (!is_array($table) || empty($table)) continue;
                    
                    // Check first row to identify table type
                    $first_row = isset($table[0]) ? $table[0] : null;
                    if (!$first_row || !is_array($first_row)) continue;

                    // Identify Education
                    if (isset($first_row['Institute']) || isset($first_row['Qualification']) || isset($first_row['Degree']) || isset($first_row['University'])) {
                        $normalized_data['Education'] = $table;
                        $found_edu = true;
                    }

                    // Identify Employment
                    if (isset($first_row['Employer']) || isset($first_row['Designation']) || isset($first_row['Company']) || isset($first_row['JobTitle'])) {
                        $normalized_data['Employment'] = $table;
                        $found_emp = true;
                    }
                }
                
                // Fallback to indices if smart scan failed (and tables exist at those indices)
                if (!$found_edu && isset($data[1]) && is_array($data[1])) {
                    $normalized_data['Education'] = $data[1];
                }
                if (!$found_emp && isset($data[2]) && is_array($data[2])) {
                    $normalized_data['Employment'] = $data[2];
                }
                
                // Table 3: Requirement / Settings (usually last or index 3)
                if (isset($data[3]) && is_array($data[3])) {
                    $normalized_data['Settings'] = isset($data[3][0]) ? $data[3][0] : $data[3];
                }

                // Explicit Fetch if missing (Requirement from User)
                if (empty($normalized_data['Education']) && $candidate_id > 0) {
                     $edu_res = XenHire_API::public_call('Get_Education', [['Key' => 'CandidateID', 'Value' => $candidate_id]]);
                     if ($edu_res['success']) {
                         $edu_data = json_decode($edu_res['data'], true);
                         if (is_array($edu_data)) {
                             // Handle nested response if any
                             if (isset($edu_data[0]) && is_array($edu_data[0])) $normalized_data['Education'] = $edu_data;
                             elseif (isset($edu_data['Education'])) $normalized_data['Education'] = $edu_data['Education']; 
                             else $normalized_data['Education'] = $edu_data;
                         }
                     }
                }

                if (empty($normalized_data['Employment']) && $candidate_id > 0) {
                     $emp_res = XenHire_API::public_call('Get_Employment', [['Key' => 'CandidateID', 'Value' => $candidate_id]]);
                     if ($emp_res['success']) {
                         $emp_data = json_decode($emp_res['data'], true);
                         if (is_array($emp_data)) {
                             // Handle nested response if any
                             if (isset($emp_data[0]) && is_array($emp_data[0])) $normalized_data['Employment'] = $emp_data;
                             elseif (isset($emp_data['Employment'])) $normalized_data['Employment'] = $emp_data['Employment'];
                             else $normalized_data['Employment'] = $emp_data;
                         }
                     }
                }
                
                
                // Debugging: Add raw data to response
                $normalized_data['raw_response'] = $data;

                wp_send_json_success($normalized_data);
            } else {
                wp_send_json_error(['message' => 'Invalid data format from API']);
            }
        } else {
            wp_send_json_error(['message' => $response['message']]);
        }
    }

}

// Bootstrap
new XenHire_Public();
