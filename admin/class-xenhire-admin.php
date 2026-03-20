
if (
    !isset($_POST['xenhire_nonce']) ||
    !wp_verify_nonce($_POST['xenhire_nonce'], 'xenhire_action')
) {
    wp_die('Security check failed');
}
<?php
if (!defined('ABSPATH')) exit;

class XenHire_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_xenhire_login', array($this, 'ajax_login'));
        add_action('wp_ajax_xenhire_register', array($this, 'ajax_register'));
        add_action('wp_ajax_xenhire_logout', array($this, 'ajax_logout'));
        add_action('wp_ajax_xenhire_send_otp', array($this, 'ajax_send_otp'));
        add_action('wp_ajax_xenhire_verify_otp', array($this, 'ajax_verify_otp'));
        
        // Stages AJAX handlers
        add_action('wp_ajax_xenhire_save_stage', array($this, 'ajax_save_stage'));
        add_action('wp_ajax_xenhire_delete_stage', array($this, 'ajax_delete_stage'));
        add_action('wp_ajax_xenhire_list_stages', array($this, 'ajax_list_stages'));
        
        // Email Templates AJAX handlers
        add_action('wp_ajax_xenhire_get_email_templates', array($this, 'ajax_get_email_templates'));
        add_action('wp_ajax_xenhire_save_email_template', array($this, 'ajax_save_email_template'));
        add_action('wp_ajax_xenhire_delete_email_template', array($this, 'ajax_delete_email_template'));
        add_action('wp_ajax_xenhire_test_smtp', array($this, 'ajax_test_smtp'));
        add_action('wp_ajax_xenhire_save_smtp', array($this, 'ajax_save_smtp'));
        add_action('wp_ajax_xenhire_get_email_templates_cbo', array($this, 'ajax_get_email_templates_cbo'));


        // Analytics AJAX handler
        add_action('wp_ajax_xenhire_save_analytics', array($this, 'ajax_save_analytics'));

        // Jobs AJAX handlers
        add_action('wp_ajax_xenhire_get_employers', array($this, 'ajax_get_employers'));
        add_action('wp_ajax_xenhire_list_jobs', array($this, 'ajax_list_jobs'));
        add_action('wp_ajax_xenhire_toggle_job_status', array($this, 'ajax_toggle_job_status'));
        // Invite Email AJAX handler
        add_action('wp_ajax_xenhire_send_invite_email', array($this, 'ajax_send_invite_email'));

        add_action('wp_ajax_xenhire_get_cbo_items', [$this,'ajax_get_cbo_items']);
        add_action('wp_ajax_xenhire_get_job_details', [$this,'ajax_get_job_details']);
        add_action('wp_ajax_xenhire_set_requirement', [$this,'ajax_set_requirement']);
        add_action('wp_ajax_xenhire_list_interview_questions', [$this,'ajax_list_interview_questions']);
        add_action('wp_ajax_xenhire_move_interview_questions', [$this,'ajax_move_interview_questions']);
        add_action('wp_ajax_xenhire_delete_interview_question', [$this,'ajax_delete_interview_question']);
        add_action('wp_ajax_xenhire_generate_ai_description', [$this,'ajax_generate_ai_description']);
        add_action('wp_ajax_xenhire_suggest_ai_questions', [$this,'ajax_suggest_ai_questions']);
        add_action('wp_ajax_xenhire_publish_job', [$this,'ajax_publish_job']);
        add_action('wp_ajax_xenhire_save_interview_question', [$this,'ajax_save_interview_question']);
        add_action('wp_ajax_xenhire_check_interview_attended', [$this,'ajax_check_interview_attended']);
        add_action('wp_ajax_xenhire_send_mail_admin', [$this,'ajax_send_mail_admin']);

        // Public jobs listing (front-end)
        add_action('wp_ajax_xenhire_public_list_jobs', [$this, 'ajax_public_list_jobs']);
        add_action('wp_ajax_nopriv_xenhire_public_list_jobs', [$this, 'ajax_public_list_jobs']);
        add_action('wp_ajax_xenhire_get_cbo_items', array($this, 'ajax_get_cbo_items'));

        add_action('wp_ajax_xenhire_get_job_details', array($this, 'ajax_get_job_details'));
        add_action('wp_ajax_xenhire_set_requirement', array($this, 'ajax_set_requirement'));
        add_action('wp_ajax_xenhire_get_job_details', array($this, 'ajax_get_job_details'));
        add_action('wp_ajax_xenhire_set_requirement', array($this, 'ajax_set_requirement'));

        // Branding AJAX handlers
        add_action('wp_ajax_xenhire_get_branding', array($this, 'ajax_get_branding'));
        add_action('wp_ajax_xenhire_save_branding', array($this, 'ajax_save_branding'));
        add_action('wp_ajax_xenhire_upload_video', array($this, 'ajax_upload_video'));

        // Applications AJAX handler
        add_action('wp_ajax_xenhire_list_applications', array($this, 'ajax_list_applications'));
        add_action('wp_ajax_xenhire_delete_application', array($this, 'ajax_delete_application'));
        add_action('wp_ajax_xenhire_download_applications_csv', array($this, 'ajax_download_applications_csv'));

        // Employers AJAX handler
        add_action('wp_ajax_xenhire_list_employers', array($this, 'ajax_list_employers'));
        add_action('wp_ajax_xenhire_toggle_employer_status', array($this, 'ajax_toggle_employer_status'));
        add_action('wp_ajax_xenhire_save_employer', array($this, 'ajax_save_employer'));
        add_action('wp_ajax_xenhire_get_employer_details', array($this, 'ajax_get_employer_details'));
        add_action('wp_ajax_xenhire_get_job_application_details', array($this, 'ajax_get_job_application_details'));
        add_action('wp_ajax_xenhire_get_stages', array($this, 'ajax_get_stages'));
        add_action('wp_ajax_xenhire_set_job_application_stage', array($this, 'ajax_set_job_application_stage'));
        add_action('wp_ajax_xenhire_get_email_template', array($this, 'ajax_get_email_template'));
        add_action('wp_ajax_xenhire_get_feedback', array($this, 'ajax_get_feedback'));
        add_action('wp_ajax_xenhire_save_feedback', array($this, 'ajax_save_feedback'));
        add_action('wp_ajax_xenhire_get_match_score', array($this, 'ajax_get_match_score'));

    }
    
    /**
     * AJAX: Set Job Application Stage
     */
    public function ajax_set_job_application_stage() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;
        $stage_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) : 0;
        
        if ($app_id <= 0 || $stage_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Application ID or Stage ID'));
        }
        
        $args = array(
            array('Key' => 'ID', 'Value' => $app_id),
            array('Key' => 'StageID', 'Value' => (string)$stage_id)
        );
        
        $result = XenHire_API::call('Set_JobApplication_Stage', $args);
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Stage updated successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to update stage'));
        }
    }

    public function ajax_get_email_template() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;
        $stage_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) : 0;

        if (!$app_id || !$stage_id) {
            wp_send_json_error(['message' => 'Missing parameters']);
        }

        $args = [
            ['Key' => 'JobApplicationID', 'Value' => $app_id],
            ['Key' => 'EmailTemplateID', 'Value' => $stage_id]
        ];

        $response = XenHire_API::call('Get_Vendor_EmailTemplate', $args);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success($response);
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
    // Main menu
    add_menu_page(
        'XenHire',
        'XenHire',
        'manage_options',
        'xenhire',
        array($this, 'render_main_page'),
        XENHIRE_PLUGIN_URL . 'public/images/xh.png',
        30
    );

    add_action('admin_enqueue_scripts', function() {
        wp_add_inline_style('xenhire-admin-menu', "
            #toplevel_page_xenhire .wp-menu-image img {
                padding: 0;
                max-height: 16px;
                opacity: 1;
                margin-top:9px;
            }
        ");
    });
    
    add_submenu_page(
        null,
        'Add New Job',
        'Add New Job',
        'manage_options',
        'xenhire-job-add',
        array($this, 'render_job_add_page')
    );
        
    // Only show submenu if logged in
    if (XenHire_Auth::is_logged_in()) {
        add_submenu_page(
            'xenhire',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'xenhire',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'xenhire',
            'Jobs',
            'Jobs',
            'manage_options',
            'xenhire-jobs',
            array($this, 'render_jobs_page')
        );
        
        add_submenu_page(
            'xenhire',
            'Applications',
            'Applications',
            'manage_options',
            'xenhire-applications',
            array($this, 'render_applications_page')
        );
        
        add_submenu_page(
            'xenhire',
            'Employer',
            'Employer',
            'manage_options',
            'xenhire-employers',
            array($this, 'render_employers_page')
        );
        
        add_submenu_page(
            'xenhire',
            'Settings',
            'Settings',
            'manage_options',
            'xenhire-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'xenhire',
            'Branding',
            'Branding',
            'manage_options',
            'xenhire-branding',
            array($this, 'render_branding_page')
        );
    }

    // Hidden Pages - Register ALWAYS so they can handle auth redirection internally
        
        // Stages submenu (hidden from main menu, accessed from Settings)
        add_submenu_page(
            null,
            'Stages',
            'Stages',
            'manage_options',
            'xenhire-stages',
            array($this, 'render_stages_page')
        );
        
        // Email Templates submenu (hidden from main menu, accessed from Settings)
        add_submenu_page(
            null,
            'Email Templates',
            'Email Templates',
            'manage_options',
            'xenhire-email-templates',
            array($this, 'render_email_templates_page')
        );
        
        // Analytics submenu (hidden from main menu, accessed from Settings)
        add_submenu_page(
            null,
            'Analytics',
            'Analytics',
            'manage_options',
            'xenhire-analytics',
            array($this, 'render_analytics_page')
        );

        // Candidate Details (Hidden)
        add_submenu_page(
            null,
            'Candidate Details',
            'Candidate Details',
            'manage_options',
            'xenhire-candidate-details',
            array($this, 'render_candidate_details_page')
        );

        // Packages Page (Hidden)
        add_submenu_page(
            null,
            'Packages',
            'Packages',
            'manage_options',
            'xenhire-packages',
            array($this, 'render_packages_page')
        );
    
}

    /**
     * AJAX: Send OTP for Admin Signup
     */
    public function ajax_send_otp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email'])))) ? sanitize_email(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email']))))) : '';
        
        // Use Fallback API Key for unauthenticated signup
        $api_key = 'CFD99E5B-25CE-402F-A007-EE682C0E8D63';

        if (empty($email)) {
            wp_send_json_error(['message' => 'Email is required']);
        }

        $response = wp_remote_post('', [
            'body' => [
                'APIKey' => $api_key,
                'Email' => $email,
                'IsMobileLogin' => 'false'
            ],
            'headers' => [
                'Referer' => '',
                'Origin' => ''
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Network error: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        
        // Check for "true" string OR JSON Result="OK"
        if (
            ($body === 'true') || 
            (strpos($body, 'true') !== false) ||
            (isset($json['Result']) && $json['Result'] === 'OK')
        ) {
             wp_send_json_success(['message' => 'OTP sent successfully']);
        } else {
             $msg = isset($json['Message']) ? $json['Message'] : 'Failed to send OTP';
             wp_send_json_error(['message' => $msg]);
        }
    }

    /**
     * AJAX: Verify OTP for Admin Signup
     */
    public function ajax_verify_otp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email'])))) ? sanitize_email(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email']))))) : '';
        $otp = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['otp'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['otp']))))) : '';
        
        // Use Fallback API Key
        $api_key = 'CFD99E5B-25CE-402F-A007-EE682C0E8D63';

        if (empty($email) || empty($otp)) {
            wp_send_json_error(['message' => 'Email and OTP are required']);
        }

        $response = wp_remote_post('', [
            'body' => [
                'APIKey' => $api_key,
                'Email' => $email,
                'OTP' => $otp,
                'IsMobileLogin' => 'false'
            ],
            'headers' => [
                'Referer' => '',
                'Origin' => ''
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Network error: ' . $response->get_error_message()]);
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        // Debug Logging
        // error_log('XenHire VerifyOTP Request: Email=' . $email . ', OTP=' . $otp);
        // error_log('XenHire VerifyOTP Response Code: ' . $code);
        // error_log('XenHire VerifyOTP Response Body: ' . $body);

        $json = json_decode($body, true);

        if (
            ($body === 'true') || 
            (strpos($body, 'true') !== false) ||
            (isset($json['Result']) && $json['Result'] === 'OK')
        ) {
             wp_send_json_success(['message' => 'OTP Verified']);
        } else {
             $msg = isset($json['Message']) ? $json['Message'] : 'Invalid OTP';
             // Return debug info in error response for immediate feedback
             wp_send_json_error([
                 'message' => $msg,
                 'debug_body' => $body,
                 'status_code' => $code
             ]);
        }
    }

    /**
     * AJAX: Send Mail Admin
     */
    public function ajax_send_mail_admin() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $emails = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emails'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emails']))))) : '';
        $cc = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['cc'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['cc']))))) : '';
        $subject = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['subject'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['subject']))))) : '';
        $body = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['body'])))) ? wp_kses_post(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['body']))))) : '';
        
        // Basic validation
        if (empty($emails) || empty($subject) || empty($body)) {
            wp_send_json_error(array('message' => 'Email, Subject and Body are required'));
        }
        
        $access_token = XenHire_Auth::get_access_token();
        
        if (!$access_token) {
            wp_send_json_error(array('message' => 'Not authenticated'));
        }

        $url = XENHIRE_API_BASE_URL . '/Api/SendMailAdmin';
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
            ),
            'body' => array(
                'CandidateEmail' => $emails,
                'CC' => $cc,
                'HTMLBody' => $body,
                'Subject' => $subject
            ),
            'timeout' => 30
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        
        if (isset($result['Result']) && $result['Result'] === 'OK') {
            wp_send_json_success(array('message' => 'Email sent successfully', 'data' => $result));
        } else {
            wp_send_json_error(array('message' => isset($result['Message']) ? $result['Message'] : 'Failed to send email', 'debug' => $result));
        }
    }

    
    /**
     * Enqueue admin assets
     */
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'xenhire') === false) return;
        
        // Enqueue admin menu styling (for proper logo display)
        wp_enqueue_style('xenhire-admin-menu', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-admin-menu.css', array(), XENHIRE_VERSION);
        
        // --- Register Shared Assets ---
        
        // Common Admin
        wp_register_style('xenhire-admin', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-admin.css', array(), XENHIRE_VERSION);
        wp_register_script('xenhire-admin', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-admin.js', array('jquery'), XENHIRE_VERSION, true);

        // Splash
        wp_register_style('xenhire-splash', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-splash.css', array(), XENHIRE_VERSION);

        // SweetAlert2
        wp_register_style('xenhire-sweetalert2', XENHIRE_PLUGIN_URL . 'admin/css/sweetalert2.min.css', array(), '11.16.1');
        wp_register_script('xenhire-sweetalert2', XENHIRE_PLUGIN_URL . 'admin/js/sweetalert2.all.min.js', array('jquery'), '11.16.1', true);

        // Toastr
        wp_register_style('xenhire-toastr', XENHIRE_PLUGIN_URL . 'admin/css/toastr.min.css', array(), '2.1.4');
        wp_register_script('xenhire-toastr', XENHIRE_PLUGIN_URL . 'admin/js/toastr.min.js', array('jquery'), '2.1.4', true);

        // CKEditor
        wp_register_script('xenhire-ckeditor', XENHIRE_PLUGIN_URL . 'admin/js/ckeditor.js', array(), '40.0.0', true);

        // Tagify
        wp_register_style('xenhire-tagify', XENHIRE_PLUGIN_URL . 'admin/css/tagify.css', array(), '4.17.9');
        wp_register_script('xenhire-tagify', XENHIRE_PLUGIN_URL . 'admin/js/tagify.js', array('jquery'), '4.17.9', true);

        // Bootstrap
        wp_register_script('xenhire-bootstrap', XENHIRE_PLUGIN_URL . 'admin/js/bootstrap.bundle.min.js', array('jquery'), '5.3.8', true);

        // PDF.js
        wp_register_script('xenhire-pdf-js', XENHIRE_PLUGIN_URL . 'admin/js/pdf.min.js', array(), '2.10.377', true);

        // Chart.js
        wp_register_script('xenhire-chart-js', XENHIRE_PLUGIN_URL . 'admin/js/chart.js', array(), '4.4.0', true);

        
        // --- Enqueue for Pages ---

        // Always enqueue common admin assets
        wp_enqueue_style('xenhire-admin');
        wp_enqueue_script('xenhire-admin');
        
        // XenHire Settings
        if ($hook === 'xenhire_page_xenhire-settings') {
            wp_enqueue_style('xenhire-settings', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-settings.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
            wp_enqueue_script('xenhire-settings', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-settings.js', array('jquery'), XENHIRE_VERSION, true);
        }
        
        // Dashboard / Splash / Login
        if ($hook === 'toplevel_page_xenhire' || $hook === 'xenhire_page_xenhire') {
            if (XenHire_Auth::is_logged_in()) {
                 // Fetch dashboard data here or localize it? The view handles logic, but assets belong here.
                 wp_enqueue_style('xenhire-dashboard-css', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-dashboard.css', array(), XENHIRE_VERSION);
                 wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
                 wp_enqueue_script('xenhire-dashboard-js', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-dashboard.js', array('jquery', 'xenhire-chart-js'), XENHIRE_VERSION, true);
                 
                 // Data (localize)
                 $dashboard_data = XenHire_API::call('Get_Dashboard', array());
                 wp_localize_script('xenhire-dashboard-js', 'xenhireDashboardData', $dashboard_data);
            } else {
                // Splash
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $action = isset(sanitize_text_field($_GET['action'])) ? sanitize_key(wp_unslash(sanitize_text_field($_GET['action']))) : '';
                if ($action !== 'login') {
                    wp_enqueue_style('xenhire-splash');
                    wp_enqueue_script('xenhire-splash', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-splash.js', array('jquery'), XENHIRE_VERSION, true);
                }
            }
        }
        
        // Stages & Email Templates (Shared Styles)
        if ($hook === 'admin_page_xenhire-stages' || $hook === 'admin_page_xenhire-email-templates') {
            wp_enqueue_style('xenhire-stages', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-stages.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');
        }
        if ($hook === 'admin_page_xenhire-stages') {
            wp_enqueue_script('xenhire-stages', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-stages.js', array('jquery'), XENHIRE_VERSION, true);
        }

        // Email Templates Specific
        if ($hook === 'admin_page_xenhire-email-templates') {
             wp_enqueue_script('xenhire-ckeditor');
             wp_enqueue_script('xenhire-email-templates', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-email-templates.js', array('jquery', 'xenhire-admin'), XENHIRE_VERSION, true);
        }

        // Analytics
        if ($hook === 'admin_page_xenhire-analytics') {
            wp_enqueue_style('xenhire-analytics', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-analytics.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-analytics', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-analytics.js', array('jquery', 'xenhire-admin'), XENHIRE_VERSION, true);
        }

        // Candidate Details
        if ($hook === 'admin_page_xenhire-candidate-details') {
            wp_enqueue_style('xenhire-candidate-details', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-candidate-details.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_style('line-awesome', XENHIRE_PLUGIN_URL . 'public/css/line-awesome.min.css', array(), '1.3.0');
            wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
            
            wp_enqueue_script('xenhire-ckeditor');
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');
            wp_enqueue_style('xenhire-toastr');
            wp_enqueue_script('xenhire-toastr');

            wp_enqueue_script('xenhire-candidate-details', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-candidate-details.js', array('jquery', 'xenhire-admin', 'xenhire-sweetalert2'), XENHIRE_VERSION, true);

            // PDF.js
            wp_enqueue_script('xenhire-pdf-js');
            $worker_url = XENHIRE_PLUGIN_URL . 'admin/js/pdf.worker.min.js';
            wp_add_inline_script('xenhire-pdf-js', "pdfjsLib.GlobalWorkerOptions.workerSrc = " . wp_json_encode($worker_url) . ";", 'after');
        }

        // Packages Page
        if ($hook === 'admin_page_xenhire-packages') {
            wp_enqueue_style('xenhire-packages', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-packages.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_script('xenhire-packages', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-packages.js', array('jquery'), XENHIRE_VERSION, true);
        }

        // Employers Page
        if ($hook === 'xenhire_page_xenhire-employers') {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('xenhire-ckeditor');
            
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');

            wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');

            wp_enqueue_style('xenhire-employers', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-employers.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_script('xenhire-employers', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-employers.js', array('jquery', 'xenhire-admin', 'jquery-ui-autocomplete', 'xenhire-sweetalert2'), XENHIRE_VERSION, true);
        }

        // Branding Page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ($hook === 'xenhire_page_xenhire-branding' || (isset(sanitize_text_field($_GET['page'])) && sanitize_text_field($_GET['page']) == 'xenhire-branding')) {
            wp_enqueue_media();
            wp_enqueue_style('xenhire-branding', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-branding.css', array('xenhire-admin'), XENHIRE_VERSION);
            
            wp_enqueue_style('xenhire-tagify');
            wp_enqueue_script('xenhire-tagify');
            
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');

            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('xenhire-branding', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-branding.js', array('jquery', 'xenhire-admin', 'jquery-ui-autocomplete', 'xenhire-tagify', 'xenhire-sweetalert2'), XENHIRE_VERSION, true);
        }

        // Job Add Page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ($hook == 'admin_page_xenhire-job-add' || (isset(sanitize_text_field($_GET['page'])) && sanitize_text_field($_GET['page']) == 'xenhire-job-add')) {
            wp_enqueue_media();
            wp_enqueue_style('xenhire-job-add', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-job-add.css', array('xenhire-admin'), XENHIRE_VERSION);
            
            wp_enqueue_script('xenhire-ckeditor');
            wp_enqueue_style('xenhire-tagify');
            wp_enqueue_script('xenhire-bootstrap');
            wp_enqueue_script('xenhire-tagify');
            wp_enqueue_script('jquery-ui-sortable');
            
             wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');

            wp_enqueue_script('xenhire-job-add', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-job-add.js', array('jquery', 'xenhire-admin', 'xenhire-bootstrap', 'xenhire-tagify', 'jquery-ui-sortable'), XENHIRE_VERSION, true);
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $xenhire_job_id = isset(sanitize_text_field($_GET['id'])) ? intval(sanitize_text_field($_GET['id'])) : -1;
            wp_localize_script('xenhire-job-add', 'xenhireJobAddData', array(
                'jobId' => $xenhire_job_id,
                'isNewJob' => ($xenhire_job_id === -1)
            ));

            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');
            wp_enqueue_style('xenhire-toastr');
            wp_enqueue_script('xenhire-toastr');
            
            wp_enqueue_script('jquery-ui-autocomplete');
        }

        // Jobs Page (List)
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (strpos($hook, 'xenhire-jobs') !== false || (isset(sanitize_text_field($_GET['page'])) && sanitize_text_field($_GET['page']) == 'xenhire-jobs')) {
            wp_enqueue_style('xenhire-jobs', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-jobs.css', array('xenhire-admin'), XENHIRE_VERSION);
            wp_enqueue_script('xenhire-jobs', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-jobs.js', array('jquery'), XENHIRE_VERSION, true);
            wp_enqueue_script('xenhire-ckeditor');
            
            wp_enqueue_style('xenhire-tagify');
            wp_enqueue_script('xenhire-tagify');

            wp_enqueue_style('xenhire-toastr');
            wp_enqueue_script('xenhire-toastr');
            
            wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
            
            wp_enqueue_style('xenhire-sweetalert2');
            wp_enqueue_script('xenhire-sweetalert2');
        }

        // Applications Page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (strpos($hook, 'xenhire-applications') !== false || (isset(sanitize_text_field($_GET['page'])) && sanitize_text_field($_GET['page']) == 'xenhire-applications')) {
             wp_enqueue_style('xenhire-applications', XENHIRE_PLUGIN_URL . 'admin/css/xenhire-applications.css', array('xenhire-admin'), XENHIRE_VERSION);
             wp_enqueue_style('keen-icons', XENHIRE_PLUGIN_URL . 'public/css/keen-icons.css', array(), '1.4.3');
             wp_enqueue_style('line-awesome', XENHIRE_PLUGIN_URL . 'public/css/line-awesome.min.css', array(), '1.3.0');
             wp_enqueue_style('font-awesome', XENHIRE_PLUGIN_URL . 'public/css/all.min.css', array(), '6.0.0');
             wp_enqueue_style('xenhire-sweetalert2');
             wp_enqueue_script('xenhire-sweetalert2');
             wp_enqueue_script('xenhire-applications', XENHIRE_PLUGIN_URL . 'admin/js/xenhire-applications.js', array('jquery', 'xenhire-admin'), XENHIRE_VERSION, true);
        }
        
        // Localize script for AJAX
        wp_localize_script('xenhire-admin', 'xenhireAjax', array(
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('xenhire_nonce'),
            'jobs_url'    => admin_url('admin.php?page=xenhire-jobs'),
            'site_url'    => site_url(),
            'home_url'    => home_url(),
            'login_url'   => admin_url('admin.php?page=xenhire'),
            'job_add_url' => admin_url('admin.php?page=xenhire-job-add'),
            'plugin_url'  => XENHIRE_PLUGIN_URL,
            's3_base_url' => ''
        ));
    }

    public function ajax_get_feedback() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;
        
        if (!$app_id) {
            wp_send_json_error(array('message' => 'Application ID is required'));
        }

        $result = XenHire_API::call('List_JobApplicationVendorUserMap', array(
            array('Key' => 'JobApplicationID', 'Value' => $app_id)
        ));

        if ($result['success']) {
            // Decode the inner data string
            $data = json_decode($result['data'], true);
            
            // The API returns [[...], [FeedbackItems]]
            $owner_feedback = null;
            $other_feedbacks = [];
            
            if (is_array($data) && count($data) > 1 && is_array($data[1])) {
                foreach ($data[1] as $item) {
                    // Check if it's the current user's feedback ("IsOwner": 1)
                    // Note: API returns IsOwner as integer 1 or 0 usually, but let's be safe
                    if (isset($item['IsOwner']) && $item['IsOwner'] == 1) {
                        $owner_feedback = $item;
                    } else {
                        // Any other feedback goes to others list
                        // Use VendorUserName or Username for display
                        $other_feedbacks[] = $item;
                    }
                }
            }
            
            wp_send_json_success(array(
                'owner' => $owner_feedback,
                'others' => $other_feedbacks
            ));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load feedback'));
        }
    }

    public function ajax_save_feedback() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;
        $rating = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['rating'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['rating'])))) : 0;
        $remarks = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['remarks'])))) ? sanitize_textarea_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['remarks']))))) : '';
        $map_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['map_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['map_id'])))) : 0;
        
        if (!$app_id) {
            wp_send_json_error(array('message' => 'Application ID is required'));
        }

        // Get Vendor User ID
        if (!class_exists('XenHire_Auth')) {
             require_once XENHIRE_PLUGIN_DIR . 'includes/class-xenhire-auth.php';
        }
        // Pass app_id to help find the user via mapping if needed
        $vendor_user_id = XenHire_Auth::get_vendor_user_id($app_id);

        // If still 0, we proceed with 0 (backend might handle it or it's a new mapping)
        // Recovery Mechanism: If VendorUserID is missing, try to find it via API
        // Always verify VendorUserID via API to ensure we have the correct XenHire User ID, not WP ID
        if ($app_id) {
            $recovery_result = XenHire_API::call('List_JobApplicationVendorUserMap', array(
                array('Key' => 'JobApplicationID', 'Value' => $app_id)
            ));

            if ($recovery_result['success']) {
                $rec_data = $recovery_result['data'];
                if (is_string($rec_data)) {
                    $rec_data = json_decode($rec_data, true);
                }
                
                if (is_array($rec_data) && !empty($rec_data)) {
                    // Data structure might be directly an array of items or array of tables
                    // Based on logs: [0]=>empty, [1]=>Array of items. 
                    // Let's search recursively or check likely locations
                    $items = isset($rec_data[1]) ? $rec_data[1] : (isset($rec_data[0]) ? $rec_data[0] : $rec_data);

                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (isset($item['IsOwner']) && $item['IsOwner'] == 1 && !empty($item['VendorUserID'])) {
                                $correct_id = $item['VendorUserID'];
                                
                                // Update if different or missing
                                if ($vendor_user_id != $correct_id) {
                                    $vendor_user_id = $correct_id;
                                    update_option('xenhire_vendor_user_id', $vendor_user_id);
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!$vendor_user_id) {
            // Include debug info
            wp_send_json_error(array(
                'message' => 'Session invalid: VendorUser ID is missing and could not be recovered. Please Logout and Login again.',
                'debug_app_id' => $app_id
            ));
        }

        // If map_id is 0, send -1 for new record
        $api_map_id = ($map_id > 0) ? $map_id : -1;

        $args = array(
            array('Key' => 'ID', 'Value' => (string)$api_map_id),
            array('Key' => 'JobApplicationID', 'Value' => (string)$app_id),
            array('Key' => 'RatingID', 'Value' => (string)$rating),
            array('Key' => 'Remarks', 'Value' => $remarks),
            array('Key' => 'VendorUserID', 'Value' => (string)$vendor_user_id),
            array('Key' => 'IsInterviewAttended', 'Value' => '0'),
            array('Key' => 'IsShortlisted', 'Value' => '0'),
            array('Key' => 'IsRejected', 'Value' => '0'),
            array('Key' => 'IsOnHold', 'Value' => '0'),
            array('Key' => 'IsOwner', 'Value' => '1'),
            array('Key' => 'IsActive', 'Value' => '1')
        );

        $result = XenHire_API::call('Set_JobApplicationVendorUserMap', $args);


        if ($result['success']) {
            wp_send_json_success(array('message' => 'Feedback saved successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save feedback'));
        }
    }

    public function ajax_get_match_score() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;
        
        if (!$app_id) {
            wp_send_json_error(array('message' => 'Application ID is required'));
        }

        $access_token = XenHire_Auth::get_access_token();
        if (!$access_token) {
             wp_send_json_error(array('message' => 'Not authenticated'));
        }

        $url = '';
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With' => 'XMLHttpRequest'
            ),
            'body' => array(
                'JobApplicationID' => $app_id
            ),
            'timeout' => 45
        ));

        if (is_wp_error($response)) {
             wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        
        // Check for success (The API seems to return the object directly or a wrapped response)
        // Based on curl, it might return the match object directly.
        $success = false;
        if (isset($result['MatchName']) || isset($result['MatchScore']) || isset($result['Analysis'])) {
             $success = true;
        } elseif (isset($result['Result']) && $result['Result'] === 'OK') {
             $success = true;
        }

        if ($success) {
             // Fetch latest application details immediately for chaining
             $details_result = $this->get_application_details_data($app_id);
             
             if ($details_result['success']) {
                 wp_send_json_success($details_result['data']);
             } else {
                 // Fallback if details fetch fails, though unlikely
                 wp_send_json_success($result['Data'] ?? $result);
             }
        } else {
             $msg = isset($result['Message']) ? $result['Message'] : 'Failed to get match score';
             wp_send_json_error(array('message' => $msg, 'debug' => $result));
        }
    }
    /**
     * Render main page (Login or Dashboard)
     */
    public function render_main_page() {
        if (!XenHire_Auth::is_logged_in()) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $action = isset(sanitize_text_field($_GET['action'])) ? sanitize_key(wp_unslash(sanitize_text_field($_GET['action']))) : '';
            if ($action === 'login') {
                include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
            } else {
                include XENHIRE_PLUGIN_DIR . 'admin/pages/splash.php';
            }
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/dashboard.php';
        }
    }
    
    /**
     * Render Jobs page
     */
    public function render_jobs_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/jobs.php';
        }
    }
    
    /**
     * Render Applications page
     */
    public function render_applications_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/applications.php';
        }
    }
    
    /**
     * Render Employers page
     */
    public function render_employers_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/employers.php';
        }
    }

    /**
     * Render Candidate Details page
     */
    public function render_candidate_details_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/candidate-details.php';
        }
    }

    /**
     * Render Packages page
     */
    public function render_packages_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/packages.php';
        }
    }
    
    /**
     * Render Settings page
     */
    public function render_settings_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/settings.php';
        }
    }
    
    /**
     * Render Stages page
     */
    public function render_stages_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/stages.php';
        }
    }
    
    /**
     * Render Email Templates page
     */
    public function render_email_templates_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/email-templates.php';
        }
    }
    
    /**
     * Render Branding page
     */
    public function render_branding_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/branding.php';
        }
    }

    /**
     * Render Analytics page
     */ 
    public function render_analytics_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/analytics.php';
        }
    }
    
    /**
     * AJAX: Handle login
     */
    public function ajax_login() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email'])))) ? sanitize_email(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email']))))) : '';
        $password = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['password'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['password']))))) : '';
        
        if (empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Email and password are required'));
        }
        
        $result = XenHire_Auth::login($email, $password);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Handle registration
     */
    public function ajax_register() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $email = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email'])))) ? sanitize_email(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email']))))) : '';
        $password = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['password'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['password']))))) : '';
        
        if (empty($email) || empty($password)) {
            wp_send_json_error(array('message' => 'Email and password are required'));
        }
        
        $result = XenHire_Auth::register($email, $password);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Handle logout
     */
    public function ajax_logout() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        XenHire_Auth::logout();
        wp_send_json_success(array('message' => 'Logged out successfully'));
    }
    
    /**
     * AJAX: Save Stage (Create or Update)
     */
    public function ajax_save_stage() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $stage = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage'])))) : array();
        
        if (empty($stage)) {
            wp_send_json_error(array('message' => 'Stage data is required'));
        }
        
        // Validate required fields
        if (empty($stage['Name'])) {
            wp_send_json_error(array('message' => 'Stage name is required'));
        }
        
        if (empty($stage['Color'])) {
            wp_send_json_error(array('message' => 'Stage color is required'));
        }
        
        if (empty($stage['EmailTemplateID']) || $stage['EmailTemplateID'] <= 0) {
            wp_send_json_error(array('message' => 'Email template is required'));
        }
        
        // Always use Set_Stage proc (handles both create and update)
        // Sanitize all fields before API call
        $stage_id = isset($stage['ID']) ? intval($stage['ID']) : 0;
        $args = array(
            array('Key' => 'ID', 'Value' => $stage_id),  // SECURE: intval() sanitization
            array('Key' => 'Name', 'Value' => sanitize_text_field($stage['Name'])),
            array('Key' => 'Color', 'Value' => sanitize_hex_color($stage['Color'])),
            array('Key' => 'OrdPos', 'Value' => isset($stage['OrdPos']) && $stage['OrdPos'] !== '' ? intval($stage['OrdPos']) : 1),
            array('Key' => 'EmailTemplateID', 'Value' => intval($stage['EmailTemplateID']))
        );
        
        $result = XenHire_API::call('Set_Stage', $args);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Stage saved successfully',
                'data' => $result
            ));
        } else {
            wp_send_json_error(array(
                'message' => isset($result['message']) ? $result['message'] : 'Failed to save stage'
            ));
        }
    }
    
    /**
     * AJAX: Delete Stage
     */
    public function ajax_delete_stage() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $stage_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) : 0;
        
        if ($stage_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid stage ID'));
        }
        
        $result = XenHire_API::call('Delete_Stage', array(
            array('Key' => 'ID', 'Value' => $stage_id)
        ));
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: List Stages
     */
    public function ajax_list_stages() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $search = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search']))))) : '';
        $page_no = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) : 1;
        $page_size = 100;
        
        $stages_data = XenHire_API::call('List_Stage', array(
            array('Key' => 'IsActive', 'Value' => -1),
            array('Key' => 'Search', 'Value' => $search),
            array('Key' => 'PageNo', 'Value' => $page_no),
            array('Key' => 'PageSize', 'Value' => $page_size)
        ));
        
        if ($stages_data['success']) {
            $data = json_decode($stages_data['data'], true);
            $stages = array();
            
            if (isset($data[0]) && is_array($data[0])) {
                $stages = $data[0];
            }
            
            ob_start();
            if (!empty($stages)) {
                foreach ($stages as $index => $stage) {
                    ?>
                    <tr class="<?php echo $index % 2 === 0 ? 'even' : 'odd'; ?>">
                        <td class="column-name">
                            <strong><?php echo esc_html($stage['Name']); ?></strong>
                        </td>
                        <td class="column-color">
                            <div class="xenhire-color-box" style="background-color: <?php echo esc_attr($stage['Color']); ?>"></div>
                        </td>
                        <td class="column-position" style="text-align: left; padding-left: 45px;">
                            <?php echo esc_html($stage['OrdPos']); ?>
                        </td>
                        <td class="column-template">
                            <?php 
                                if (isset($stage['EmailTemplate']) && !empty($stage['EmailTemplate'])) {
                                    $template_display = html_entity_decode($stage['EmailTemplate'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $template_display = wp_strip_all_tags($template_display);
                                    $template_display = trim(preg_replace('/\s+/', ' ', $template_display));
                                    echo esc_html($template_display);
                                } else {
                                    echo '<span style="color: #999;">Not Assigned</span>';
                                }
                            ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="xh-btn xh-secondary xenhire-edit-stage" 
                                    data-id="<?php echo esc_attr($stage['ID']); ?>"
                                    data-name="<?php echo esc_attr($stage['Name']); ?>"
                                    data-color="<?php echo esc_attr($stage['Color']); ?>"
                                    data-ordpos="<?php echo esc_attr($stage['OrdPos']); ?>"
                                    data-emailtemplateid="<?php echo esc_attr($stage['EmailTemplateID'] ?? ''); ?>">
                                Edit
                            </button>
                            <?php if ($stage['OrdPos'] != 1): ?>
                                <button type="button" class="button button-small xenhire-delete-stage" data-id="<?php echo esc_attr($stage['ID']); ?>">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px;">
                        <p style="color: #999; font-size: 16px;">No stages found.</p>
                        <?php if ($search): ?>
                            <p><a href="#" class="xenhire-clear-search-link">Clear search</a></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
            $html = ob_get_clean();
            
            wp_send_json_success(array('html' => $html));
        } else {
            wp_send_json_error(array('message' => isset($stages_data['message']) ? $stages_data['message'] : 'Failed to load stages'));
        }
    }
    
    /**
     * AJAX: Get Email Templates (calls /EmailTemplates/Get endpoint)
     */
    public function ajax_get_email_templates() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $search = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search']))))) : '';
        
        $result = XenHire_API::call_direct('EmailTemplates/Get', array(
            'Search' => $search
        ));
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load templates'));
        }
    }
    
    /**
     * AJAX: Get Email Templates CBO (for Stages dropdown)
     */
    public function ajax_get_email_templates_cbo() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $result = XenHire_API::get_cbo('EmailTemplate');
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array(
                'message' => isset($result['message']) ? $result['message'] : 'Failed to load email templates',
                'debug' => isset($result) ? $result : null
            ));
        }
    }
    
    /**
     * AJAX: Save Email Template (calls /EmailTemplates/Set endpoint)
     */
    public function ajax_save_email_template() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $template = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['template'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['template'])))) : array();
        
        if (empty($template)) {
            wp_send_json_error(array('message' => 'Template data is required'));
        }
        
        // Validate required fields
        if (empty($template['Name'])) {
            wp_send_json_error(array('message' => 'Template name is required'));
        }
        
        if (empty($template['Subject'])) {
            wp_send_json_error(array('message' => 'Subject is required'));
        }
        
        $result = XenHire_API::call_direct('EmailTemplates/Set', array(
            'ID' => isset($template['ID']) ? intval($template['ID']) : -1,
            'Name' => sanitize_text_field($template['Name']),
            'Subject' => sanitize_text_field($template['Subject']),
            'Body' => wp_kses_post($template['Body'])
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Template saved successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save template'));
        }
    }
    
    /**
     * AJAX: Delete Email Template (calls /EmailTemplates/Delete endpoint)
     */
    public function ajax_delete_email_template() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $template_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) : 0;
        
        if ($template_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid template ID'));
        }
        
        $result = XenHire_API::call_direct('EmailTemplates/Delete', array(
            'ID' => $template_id
        ));
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to delete template'));
        }
    }
    
    /**
     * AJAX: Get Email Templates from CBO (for dropdown binding)
     */
    public function ajax_get_cbo_items() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $key = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['key'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['key']))))) : '';
        
        if (empty($key)) {
            wp_send_json_error(array('message' => 'Key is required'));
        }

        // Special handling for 'Stage' to get extra fields (Color, TemplateID)
        if ($key === 'Stage') {
            $result = XenHire_API::call('List_Stage', array(
                array('Key' => 'IsActive', 'Value' => -1),
                array('Key' => 'PageSize', 'Value' => 100),
                array('Key' => 'Search', 'Value' => ''),
                array('Key' => 'PageNo', 'Value' => 1)
            ));

            if ($result['success']) {
                $data = json_decode($result['data'], true);
                $stages = array();
                
                if (isset($data[0]) && is_array($data[0])) {
                    $raw_stages = $data[0];
                    foreach ($raw_stages as $stage) {
                        $stages[] = array(
                            'Value' => $stage['ID'],
                            'DisplayText' => $stage['Name'],
                            'color' => isset($stage['Color']) ? $stage['Color'] : '',
                            'templateid' => isset($stage['EmailTemplateID']) ? $stage['EmailTemplateID'] : 0
                        );
                    }
                }
                
                wp_send_json_success($stages);
                return;
            } else {
                wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load stages'));
                return;
            }
        }

        // Use form-data for Industry as per specific requirement
        $use_form_data = ($key === 'Industry');
        
        $result = XenHire_API::get_cbo($key, $use_form_data);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array(
                'message' => isset($result['message']) ? $result['message'] : 'Failed to load items',
                'debug' => isset($result) ? $result : null
            ));
        }
    }
    
    /**
     * AJAX: Test SMTP Configuration
     */
    public function ajax_test_smtp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
            wp_die();
        }
        
        // Properly sanitize the input array
        if (!isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp']))))) {
            wp_send_json_error(array('message' => 'SMTP data is required'));
        }
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per field
        $smtp_raw = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp'])))) : array();
        
        // Sanitize each field in the array
        $smtp = array();
        if (is_array($smtp_raw)) {
            $smtp['Email_GatewayURL'] = isset($smtp_raw['Email_GatewayURL']) ? sanitize_text_field($smtp_raw['Email_GatewayURL']) : '';
            $smtp['Email_GatewayPortNo'] = isset($smtp_raw['Email_GatewayPortNo']) ? intval($smtp_raw['Email_GatewayPortNo']) : 0;
            $smtp['Email_ClientUserName'] = isset($smtp_raw['Email_ClientUserName']) ? sanitize_text_field($smtp_raw['Email_ClientUserName']) : '';
            $smtp['Email_Password'] = isset($smtp_raw['Email_Password']) ? sanitize_text_field($smtp_raw['Email_Password']) : '';
            $smtp['Email_From'] = isset($smtp_raw['Email_From']) ? sanitize_email($smtp_raw['Email_From']) : '';
            $smtp['Email_EnableSSL'] = isset($smtp_raw['Email_EnableSSL']) ? intval($smtp_raw['Email_EnableSSL']) : 0;
        }
        
        // Validate required fields
        if (empty($smtp['Email_GatewayURL']) || empty($smtp['Email_Password']) || empty($smtp['Email_EnableSSL'])) {
            wp_send_json_error(array('message' => 'Missing required SMTP configuration fields'));
        }
        
        $result = XenHire_API::call('Test_EmailConfig', array(
            array('Key' => 'EmailGatewayURL', 'Value' => $smtp['Email_GatewayURL']),
            array('Key' => 'EmailGatewayPortNo', 'Value' => $smtp['Email_GatewayPortNo']),
            array('Key' => 'EmailUserName', 'Value' => $smtp['Email_ClientUserName']),
            array('Key' => 'EmailPassword', 'Value' => $smtp['Email_Password']),
            array('Key' => 'EmailFrom', 'Value' => $smtp['Email_From']),
            array('Key' => 'EmailEnableSSL', 'Value' => $smtp['Email_EnableSSL'])
        ));
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? sanitize_text_field($result['message']) : 'SMTP test failed'));
        }
    }
    
    /**
     * AJAX: Save SMTP Configuration
     */
    public function ajax_save_smtp() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
            wp_die();
        }
        
        // Properly sanitize the input array
        if (!isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp']))))) {
            wp_send_json_error(array('message' => 'SMTP data is required'));
        }
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per field
        $smtp_raw = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['smtp'])))) : array();
        
        // Sanitize each field in the array
        $smtp = array();
        if (is_array($smtp_raw)) {
            $smtp['Email_GatewayURL'] = isset($smtp_raw['Email_GatewayURL']) ? sanitize_text_field($smtp_raw['Email_GatewayURL']) : '';
            $smtp['Email_GatewayPortNo'] = isset($smtp_raw['Email_GatewayPortNo']) ? intval($smtp_raw['Email_GatewayPortNo']) : 0;
            $smtp['Email_ClientUserName'] = isset($smtp_raw['Email_ClientUserName']) ? sanitize_text_field($smtp_raw['Email_ClientUserName']) : '';
            $smtp['Email_Password'] = isset($smtp_raw['Email_Password']) ? sanitize_text_field($smtp_raw['Email_Password']) : '';
            $smtp['Email_From'] = isset($smtp_raw['Email_From']) ? sanitize_email($smtp_raw['Email_From']) : '';
            $smtp['Email_EnableSSL'] = isset($smtp_raw['Email_EnableSSL']) ? intval($smtp_raw['Email_EnableSSL']) : 0;
        }
        
        // Validate required fields
        if (empty($smtp['Email_GatewayURL']) || empty($smtp['Email_Password']) || empty($smtp['Email_EnableSSL'])) {
            wp_send_json_error(array('message' => 'Missing required SMTP configuration fields'));
        }
        
        $result = XenHire_API::call('Set_Vendor_SMTP', array(
            array('Key' => 'Email_GatewayURL', 'Value' => $smtp['Email_GatewayURL']),
            array('Key' => 'Email_GatewayPortNo', 'Value' => $smtp['Email_GatewayPortNo']),
            array('Key' => 'Email_ClientUserName', 'Value' => $smtp['Email_ClientUserName']),
            array('Key' => 'Email_Password', 'Value' => $smtp['Email_Password']),
            array('Key' => 'Email_From', 'Value' => $smtp['Email_From']),
            array('Key' => 'Email_EnableSSL', 'Value' => $smtp['Email_EnableSSL'])
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'SMTP Configuration Saved'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? sanitize_text_field($result['message']) : 'Failed to save SMTP configuration'));
        }
    }


    /**
     * AJAX: Save Analytics Settings
     */
    public function ajax_save_analytics() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $analytics = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['analytics'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['analytics'])))) : array();
        
        if (empty($analytics)) {
            wp_send_json_error(array('message' => 'Analytics data is required'));
        }
        
        $result = XenHire_API::call('Set_Vendor_Analytics', array(
            array('Key' => 'FacebookAnalyticsCode', 'Value' => sanitize_text_field($analytics['FacebookAnalyticsCode'])),
            array('Key' => 'GoogleAnalyticsCode', 'Value' => sanitize_text_field($analytics['GoogleAnalyticsCode'])),
            array('Key' => 'GoogleTagManager', 'Value' => sanitize_text_field($analytics['GoogleTagManager']))
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Analytics settings saved successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save analytics settings'));
        }
    }

    /**
     * AJAX: Get Employers CBO
     */
    /**
     * AJAX: Get Employers CBO
     */
    public function ajax_get_employers() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $result = XenHire_API::get_cbo('Employer');
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array(
                'message' => isset($result['message']) ? $result['message'] : 'Failed to load employers',
                'debug' => isset($result) ? $result : null
            ));
        }
    }

    /**
     * AJAX: List Jobs
     */
    public function ajax_list_jobs() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $filters = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['filters'])))) ? wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['filters'])))) : array();
        
        // Extract and SANITIZE parameters with defaults
        $city = isset($filters['City']) ? sanitize_text_field($filters['City']) : '';
        $job_title = isset($filters['JobTitle']) ? sanitize_text_field($filters['JobTitle']) : '';
        $candidate_id = isset($filters['CandidateID']) ? intval($filters['CandidateID']) : 8522;
        $offset = isset($filters['Offset']) ? intval($filters['Offset']) : -330;
        $is_active = isset($filters['IsActive']) ? intval($filters['IsActive']) : -1;
        $employer_id = isset($filters['EmployerID']) ? intval($filters['EmployerID']) : -1;
        $search = isset($filters['Search']) ? sanitize_text_field($filters['Search']) : '';
        $page_no = isset($filters['PageNo']) ? max(1, intval($filters['PageNo'])) : 1;
        $page_size = isset($filters['PageSize']) ? min(100, max(1, intval($filters['PageSize']))) : 10;
        
        $result = XenHire_API::call('List_Requirement', array(
            array('Key' => 'City', 'Value' => $city),  // SECURE: sanitize_text_field()
            array('Key' => 'JobTitle', 'Value' => $job_title),  // SECURE: sanitize_text_field()
            array('Key' => 'CandidateID', 'Value' => $candidate_id),
            array('Key' => 'Offset', 'Value' => $offset),
            array('Key' => 'IsActive', 'Value' => $is_active),  // SECURE: intval()
            array('Key' => 'EmployerID', 'Value' => $employer_id),  // SECURE: intval()
            array('Key' => 'Search', 'Value' => $search),  // SECURE: sanitize_text_field()
            array('Key' => 'PageNo', 'Value' => $page_no),  // SECURE: intval() + validation
            array('Key' => 'PageSize', 'Value' => $page_size)  // SECURE: intval() + min/max
        ));
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            if (is_array($data) && count($data) >= 2) {
                $jobs = $data[0]; // First dataset - jobs list
                $metadata = $data[1][0]; // Second dataset - metadata (first row)
                
                wp_send_json_success(array(
                    'jobs' => $jobs,
                    'metadata' => $metadata
                ));
            } else {
                wp_send_json_success(array(
                    'jobs' => array(),
                    'metadata' => array(
                        'TotalRecordCount' => 0,
                        'IsAllowJobAdd' => true,
                        'Message' => ''
                    )
                ));
            }
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load jobs'));
        }
    }

    /**
     * AJAX: Change Job Status
     */
    public function ajax_change_job_status() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        $is_active = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['is_active'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['is_active'])))) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid job ID'));
        }
        
        $result = XenHire_API::call('Update_Requirement_Status', array(
            array('Key' => 'ID', 'Value' => $job_id),
            array('Key' => 'IsActive', 'Value' => $is_active)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Job status updated successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to update job status'));
        }
    }


    /**
     * AJAX: Toggle Job Status (Active/Inactive)
     */
    public function ajax_toggle_job_status() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid job ID'));
        }
        
        $result = XenHire_API::call('Set_Requirement_ToggleActive', array(
            array('Key' => 'ID', 'Value' => $job_id)
        ));
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Extract response data
            if (is_array($data) && count($data) > 0 && isset($data[0][0])) {
                $response = $data[0][0];
                
                $message = isset($response['Message']) ? $response['Message'] : '';
                $isError = isset($response['IsError']) ? $response['IsError'] : false;
                
                wp_send_json_success(array(
                    'Message' => $message,
                    'IsError' => $isError
                ));
            } else {
                wp_send_json_success(array(
                    'Message' => '',
                    'IsError' => false
                ));
            }
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to toggle job status'));
        }
    }

    /**
     * AJAX: Get Branding Data
     */
    public function ajax_get_branding() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $result = XenHire_API::call('Get_Vendor', array());
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            // Assuming data[0][0] structure like other endpoints
            if (is_array($data) && isset($data[0][0])) {
                wp_send_json_success($data[0][0]);
            } else {
                wp_send_json_success(array());
            }
        } else {
            wp_send_json_error(array('message' => 'Failed to load branding data'));
        }
    }

    /**
     * AJAX: Save Branding Data
     */
    public function ajax_save_branding() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        // 1. Fetch existing vendor data to ensure we have all fields
        $get_result = XenHire_API::call('Get_Vendor', array());
        
        $vendor_data = array();
        if ($get_result['success'] && !empty($get_result['data'])) {
            $decoded = json_decode($get_result['data'], true);
            if (isset($decoded[0][0])) {
                $vendor_data = $decoded[0][0];
            }
        }

        // 2. Prepare values from POST, falling back to existing data or defaults
        // 2. Prepare values from POST, falling back to existing data or defaults
        $brand_name = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BrandName'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BrandName']))))) : (isset($vendor_data['BrandName']) ? $vendor_data['BrandName'] : '');
        $company_name = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CompanyName'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CompanyName']))))) : (isset($vendor_data['CompanyName']) ? $vendor_data['CompanyName'] : '');
        $website = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Website'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Website']))))) : (isset($vendor_data['Website']) ? $vendor_data['Website'] : '');
        $industry = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Industry'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Industry']))))) : (isset($vendor_data['Industry']) ? $vendor_data['Industry'] : '');
        $description = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['AboutBrand'])))) ? sanitize_textarea_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['AboutBrand']))))) : (isset($vendor_data['Description']) ? $vendor_data['Description'] : '');
        
        $favicon = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['FaviconURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['FaviconURL']))))) : (isset($vendor_data['BrandLogoIMG']) ? $vendor_data['BrandLogoIMG'] : '');
        $logo = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['LogoURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['LogoURL']))))) : (isset($vendor_data['LogoIMG']) ? $vendor_data['LogoIMG'] : '');
        $banner = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BannerURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BannerURL']))))) : (isset($vendor_data['BannerIMG']) ? $vendor_data['BannerIMG'] : '');
        $custom_url = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CareerPageURL'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CareerPageURL']))))) : (isset($vendor_data['CustomURL']) ? $vendor_data['CustomURL'] : '');
        
        $primary_color = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PrimaryColor'])))) ? sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PrimaryColor']))))) : (isset($vendor_data['PrimaryColor']) ? $vendor_data['PrimaryColor'] : '#000000');
        $secondary_color = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['SecondaryColor'])))) ? sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['SecondaryColor']))))) : (isset($vendor_data['SecondaryColor']) ? $vendor_data['SecondaryColor'] : '#000000');
        $tagline_color = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['TagLineColor'])))) ? sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['TagLineColor']))))) : (isset($vendor_data['TagLineColor']) ? $vendor_data['TagLineColor'] : '#000000');

        // Booleans MUST be strings "0" or "1" based on curl example
        $is_multi_brand = (!empty(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsHiringMultipleBrands'])))) && sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsHiringMultipleBrands']))) !== '0') ? '1' : '0';
        $is_hide_city = (!empty(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsHideCityFilter'])))) && sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsHideCityFilter']))) !== '0') ? '1' : '0';
        
        $og_image = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['SocialPreviewURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['SocialPreviewURL']))))) : (isset($vendor_data['OGImage']) ? $vendor_data['OGImage'] : '');
        $langs = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['OtherLanguages'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['OtherLanguages']))))) : (isset($vendor_data['TranslationLangs']) ? $vendor_data['TranslationLangs'] : '');
        $intro_video = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IntroVideoURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IntroVideoURL']))))) : (isset($vendor_data['IntroVideoURL']) ? $vendor_data['IntroVideoURL'] : '');

        // Construct args exactly matching the working curl payload structure
        $args = array(
            array('Key' => 'BrandName', 'Value' => (string)$brand_name),
            array('Key' => 'CompanyName', 'Value' => (string)$company_name),
            array('Key' => 'Website', 'Value' => (string)$website),
            array('Key' => 'Industry', 'Value' => (string)$industry),
            array('Key' => 'Description', 'Value' => (string)$description),
            array('Key' => 'BrandLogoIMG', 'Value' => (string)$favicon),
            array('Key' => 'LogoIMG', 'Value' => (string)$logo),
            array('Key' => 'BannerIMG', 'Value' => (string)$banner),
            array('Key' => 'CustomURL', 'Value' => (string)$custom_url),
            array('Key' => 'ListColumns', 'Value' => ''),
            array('Key' => 'PrimaryColor', 'Value' => (string)$primary_color),
            array('Key' => 'SecondaryColor', 'Value' => (string)$secondary_color),
            array('Key' => 'IsMultiBrand', 'Value' => (string)$is_multi_brand), // String "0"/"1"
            array('Key' => 'IsHideSearchByCity', 'Value' => (string)$is_hide_city), // String "0"/"1"
            array('Key' => 'OGImage', 'Value' => (string)$og_image),
            array('Key' => 'BackgroundImageURL', 'Value' => ''),
            array('Key' => 'TagLineText', 'Value' => ''),
            array('Key' => 'TagLineColor', 'Value' => (string)$tagline_color),
            array('Key' => 'TranslationLangs', 'Value' => (string)$langs),
            array('Key' => 'IsHideEmployerDetails', 'Value' => '0'),
            array('Key' => 'MaxInterviewsPerCandidate', 'Value' => -1), // Integer
            array('Key' => 'IntroVideoURL', 'Value' => (string)$intro_video),
            array('Key' => 'IsWhitelisted', 'Value' => '0'),
            array('Key' => 'IsShowPreconfiguredOnly', 'Value' => '0')
        );
        
        // error_log('XenHire Save Branding Args (Curl Match): ' . print_r($args, true));
        
        $result = XenHire_API::call('Set_Vendor', $args);
    
        if ($result['success']) {
            // Save public branding info to options for frontend usage
            update_option('xenhire_brand_name', sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BrandName']))))));
            update_option('xenhire_brand_logo', esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['LogoURL']))))));
            update_option('xenhire_primary_color', sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PrimaryColor']))))));
            update_option('xenhire_secondary_color', sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['SecondaryColor']))))));
            update_option('xenhire_tagline_color', sanitize_hex_color(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['TagLineColor']))))));
            
            wp_send_json_success(array('message' => 'Branding saved successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save branding'));
        }
    }

    /**
     * AJAX: Send Invite Email
     */
    public function ajax_send_invite_email() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        $email_to = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email_to'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email_to']))))) : '';
        $subject = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['subject'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['subject']))))) : '';
        $body = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['body'])))) ? wp_kses_post(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['body']))))) : '';
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid job ID'));
        }
        
        if (empty($email_to)) {
            wp_send_json_error(array('message' => 'Email address is required'));
        }
        
        // Call SendMailAdmin API
        $result = XenHire_API::call_custom('SendMailAdmin', array(
            'JobID' => $job_id,
            'EmailTo' => $email_to,
            'Subject' => $subject,
            'Body' => $body
        ));
        
        if ($result['success']) {
            // Parse response
            $response_data = json_decode($result['data'], true);
            
            if (isset($response_data['Result']) && $response_data['Result'] === 'OK') {
                wp_send_json_success(array(
                    'message' => isset($response_data['Message']) ? $response_data['Message'] : 'Invitation emails sent successfully',
                    'Message' => isset($response_data['Message']) ? $response_data['Message'] : ''
                ));
            } else {
                wp_send_json_error(array('message' => isset($response_data['Message']) ? $response_data['Message'] : 'Failed to send emails'));
            }
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to send emails'));
        }
    }

    /**
     * Render Job Add Page
     */
    public function render_job_add_page() {
        if (!XenHire_Auth::is_logged_in()) {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/login.php';
        } else {
            include XENHIRE_PLUGIN_DIR . 'admin/pages/job-add.php';
        }
    }

    /**
     * AJAX: Public List Jobs (front-end, using APIKey)
     */
    public function ajax_public_list_jobs() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        if (!class_exists('XenHire_API')) {
            wp_send_json_error(['message' => 'API not available']);
        }

        // Get all parameters including new ones
        $city        = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['City'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['City']))))) : '';
        $job_title   = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobTitle'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobTitle']))))) : '';
        $candidate_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CandidateID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CandidateID'])))) : 8522;
        $offset      = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Offset'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Offset'])))) : -330;
        $page_no     = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PageNo'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PageNo']))))) : '1';
        $page_size   = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PageSize'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PageSize']))))) : '50';

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

        $decoded = json_decode($result['data'], true);
        if (!is_array($decoded) || count($decoded) < 2) {
            wp_send_json_success([
                'jobs' => [],
                'metadata' => ['TotalRecordCount' => 0, 'IsAllowJobAdd' => false, 'Message' => '']
            ]);
        }

        $jobs     = isset($decoded[0]) ? $decoded[0] : [];
        $metadata = isset($decoded[1][0]) ? $decoded[1][0] : ['TotalRecordCount' => 0, 'IsAllowJobAdd' => false, 'Message' => ''];

        wp_send_json_success(['jobs' => $jobs, 'metadata' => $metadata]);
    }



    /**
     * AJAX: Get Job Details
     */
    public function ajax_get_job_details() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        
        // error_log('XenHire: ajax_get_job_details job_id: ' . $job_id . ' POST: ' . print_r($_POST, true));

        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
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
                    // Check if it was actually encoded (urldecode returns the string if not)
                    // But since we see it is encoded, we just assign it.
                    $job_details['JobDescription'] = $decoded;
                }

                if (!empty($job_details['JobRole'])) {
                    $decoded = urldecode($job_details['JobRole']);
                    $job_details['JobRole'] = $decoded;
                }

                wp_send_json_success($job_details);
            } else {
                wp_send_json_error(array('message' => 'Job not found'));
            }
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load job details'));
        }
    }

    /**
     * AJAX: Save Job (Set_Requirement)
     */
    /**
     * AJAX: Save Job (Set_Requirement)
     */
    public function ajax_set_requirement() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // Map all possible fields based on user snippet
        $fields = array(
            'ID', 'EmployerID', 'JobTitle', 'WorkExMin', 'WorkExMax', 
            'JobDescription', 'JobRole', 'Keywords', 'CurrencyID', 
            'SalaryFrom', 'SalaryTo', 'SalaryType', 'IsSalaryHidden', 'SalaryText',
            'FunctionalArea', 'EmploymentTypeID', 'City', 'CityText', 'IsCityHidden', 
            'IsActive', 'DeadlineDatestamp', 'EmailMain', 'EmailCC', 
            'Phone', 'IsShowApplicationsCount', 'AutoStartInSeconds',
            'IsInterview', 'IsInterviewRealtime', 'IsEnableAIScoring', 'IsInterviewVideoRedoAllowed',
            'IsUploadResume', 'IntroVideoURL', 'OGImage', 'NoOfQuestions', 'JobDescriptionOG',
            'ExtraData1Label', 'ExtraData1Type', 'ExtraData1Options', 'ExtraData1Mandatory',
            'ExtraData2Label', 'ExtraData2Type', 'ExtraData2Options', 'ExtraData2Mandatory',
            'ExtraData3Label', 'ExtraData3Type', 'ExtraData3Options', 'ExtraData3Mandatory',
            'ExtraData4Label', 'ExtraData4Type', 'ExtraData4Options', 'ExtraData4Mandatory'
        );
        
        $args = array();

        // Manual mapping for fields that might have different names or need processing
        if (isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PhoneMain']))))) {
            sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Phone']))) = sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['PhoneMain'])))));
        }
        
        // Generate JobDescriptionOG if not provided
        if (isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobDescription'])))) && !isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobDescriptionOG']))))) {
            sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobDescriptionOG']))) = wp_strip_all_tags(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['JobDescription'])))));
        }

        // Defaults for missing fields
        if (!isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsUploadResume']))))) sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsUploadResume']))) = 1;
        if (!isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['NoOfQuestions']))))) sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['NoOfQuestions']))) = 0;

        // Fix for new jobs: ID must be -1, not 0
        if (!isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID'])))) || intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID'])))) <= 0) {
            sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID']))) = -1;
        }
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                // Special handling for content fields to allow HTML
                if ($field === 'JobDescription' || $field === 'JobRole') {
                    $args[] = array('Key' => $field, 'Value' => wp_kses_post(wp_unslash($_POST[$field])));
                } else {
                    $args[] = array('Key' => $field, 'Value' => sanitize_text_field(wp_unslash($_POST[$field])));
                }
            } else {
                // Ensure required fields are sent even if empty, to match the expected signature
                if ($field === 'Phone') $args[] = array('Key' => 'Phone', 'Value' => '');
                if ($field === 'IntroVideoURL') $args[] = array('Key' => 'IntroVideoURL', 'Value' => '');
                if ($field === 'OGImage') $args[] = array('Key' => 'OGImage', 'Value' => '');
                if ($field === 'JobDescriptionOG') $args[] = array('Key' => 'JobDescriptionOG', 'Value' => '');
                // Add empty values for ExtraData fields if missing
                if (strpos($field, 'ExtraData') === 0) $args[] = array('Key' => $field, 'Value' => '');
            }
        }
        
        $result = XenHire_API::call('Set_Requirement', $args);
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Log for debugging
            // error_log('XenHire Save Job Response: ' . print_r($data, true));
            
            $job = null;
            
            // Try to find the job object in various structures
            if (is_numeric($data) && $data > 0) {
                // Case 0: Direct ID
                $new_job_id = $data;
            } elseif (is_array($data)) {
                // Case 1: Flat object {ID: 1, ...}
                if (isset($data['ID']) || isset($data['RequirementID']) || isset($data['id'])) {
                    $job = $data;
                } 
                // Case 2: Array of objects [{ID: 1, ...}]
                elseif (isset($data[0])) {
                    if (is_array($data[0]) && (isset($data[0]['ID']) || isset($data[0]['RequirementID']) || isset($data[0]['id']))) {
                        $job = $data[0];
                    } 
                    // Case 3: Nested array [[{ID: 1, ...}]]
                    elseif (isset($data[0][0]) && is_array($data[0][0]) && (isset($data[0][0]['ID']) || isset($data[0][0]['RequirementID']) || isset($data[0][0]['id']))) {
                        $job = $data[0][0];
                    }
                }
            }
            
            if ($new_job_id == 0 && $job) {
                if (isset($job['ID'])) $new_job_id = $job['ID'];
                elseif (isset($job['id'])) $new_job_id = $job['id'];
                elseif (isset($job['RequirementID'])) $new_job_id = $job['RequirementID'];
                elseif (isset($job['job_id'])) $new_job_id = $job['job_id'];
                
                if (isset($job['Message']) && !empty($job['Message'])) $message = $job['Message'];
                if (isset($job['PreviewURL'])) $preview_url = $job['PreviewURL'];
            }
            
            // Fallback to POST ID if we couldn't find a new one (e.g. update without ID return)
            if ($new_job_id <= 0 && isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID'])))) && sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID']))) > 0) {
                $new_job_id = intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID']))));
            }

            wp_send_json_success(array(
                'message' => $message,
                'job_id' => $new_job_id,
                'ID' => $new_job_id, // Send both for compatibility
                'PreviewURL' => $preview_url,
                'debug_raw' => $data
            ));
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX: List Interview Questions
     */
    public function ajax_list_interview_questions() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $requirement_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['requirement_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['requirement_id'])))) : 0;
        
        if ($requirement_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Requirement ID'));
        }
        
        $result = XenHire_API::call('List_InterviewQuestion', array(
            array('Key' => 'RequirementID', 'Value' => $requirement_id),
            array('Key' => 'IsActive', 'Value' => '-1')
        ));
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            wp_send_json_success(isset($data[0]) ? $data[0] : array());
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load questions'));
        }
    }

    /**
     * AJAX: Move Interview Questions (Reorder)
     */
    public function ajax_move_interview_questions() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $requirement_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['requirement_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['requirement_id'])))) : 0;
        $question_ids = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['question_ids'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['question_ids']))))) : '';
        
        if ($requirement_id <= 0 || empty($question_ids)) {
            wp_send_json_error(array('message' => 'Invalid parameters'));
        }
        
        $result = XenHire_API::call('Set_InterviewQuestion_Move_v2', array(
            array('Key' => 'RequirementID', 'Value' => $requirement_id),
            array('Key' => 'InterviewQuestionIDs', 'Value' => $question_ids)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Order saved'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save order'));
        }
    }

    /**
     * AJAX: Delete Interview Question
     */
    public function ajax_delete_interview_question() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $question_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['question_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['question_id'])))) : 0;
        
        if ($question_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Question ID'));
        }
        
        $result = XenHire_API::call('Delete_InterviewQuestion', array(
            array('Key' => 'ID', 'Value' => $question_id)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Question deleted'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to delete question'));
        }
    }

    /**
     * AJAX: Generate AI Description
     */
    public function ajax_generate_ai_description() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_title = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_title'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_title']))))) : '';
        
        if (empty($job_title)) {
            wp_send_json_error(array('message' => 'Job Title is required'));
        }
        
        $result = XenHire_API::call('Generate_AI_JobDescription', array(
            array('Key' => 'JobTitle', 'Value' => $job_title)
        ));
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            if (is_array($data) && isset($data[0][0]['Description'])) {
                wp_send_json_success(array('description' => $data[0][0]['Description']));
            } else {
                wp_send_json_error(array('message' => 'AI generation returned unexpected format'));
            }
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'AI generation failed'));
        }
    }

    /**
     * AJAX: Suggest AI Questions
     */
    public function ajax_suggest_ai_questions() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
        $result = XenHire_API::call('Generate_AI_Questions', array(
            array('Key' => 'RequirementID', 'Value' => $job_id)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Questions generated'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to generate questions'));
        }
    }

    /**
     * AJAX: Publish Job
     */
    public function ajax_publish_job() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : 0;
        
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
        $result = XenHire_API::call('Publish_Requirement', array(
            array('Key' => 'ID', 'Value' => $job_id),
            array('Key' => 'IsActive', 'Value' => 1)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Job published successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to publish job'));
        }
    }

    /**
     * AJAX: Save Interview Question
     */
    public function ajax_save_interview_question() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $requirement_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['RequirementID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['RequirementID'])))) : 0;
        
        if ($requirement_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
        $args = array(
            array('Key' => 'ID', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ID'])))) : -1),
            array('Key' => 'RequirementID', 'Value' => $requirement_id),
            array('Key' => 'Name', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Question'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Question']))))) : ''), // API expects 'Name'
            array('Key' => 'Description', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Description'])))) ? sanitize_textarea_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Description']))))) : ''),
            array('Key' => 'MaxSeconds', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['MaxSeconds'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['MaxSeconds'])))) : 120),
            array('Key' => 'QuestionTypeID', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['QuestionTypeID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['QuestionTypeID'])))) : 1),
            array('Key' => 'Options', 'Value' => ''), // Options removed from UI, pass empty
            array('Key' => 'IsActive', 'Value' => 1),
            array('Key' => 'IsNotAIScore', 'Value' => isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsNotAIScore'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsNotAIScore'])))) : 0)
        );
        
        $result = XenHire_API::call('Set_InterviewQuestion', $args);
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Question saved successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save question'));
        }
    }

    /**
     * AJAX: Check if Interview Attended
     */
    public function ajax_check_interview_attended() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $requirement_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['RequirementID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['RequirementID'])))) : 0;
        
        if ($requirement_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Job ID'));
        }
        
        $args = array(
            array('Key' => 'ID', 'Value' => $requirement_id)
        );
        
        $result = XenHire_API::call('Get_Requirement_IsInterviewAttended', $args);
        
        if ($result['success'] && isset($result['data'])) {
            // Parse the Data string if it's a JSON string
            $data = $result['data'];
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }
            
            // Extract IsInterviewAttended and other data
            $row = array();
            if (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0][0])) {
                $row = $data[0][0];
            }
            
            wp_send_json_success(array('data' => $row));
        } else {
            // If call fails, assume not attended or handle error? 
            // Better to return error so UI knows something went wrong.
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to check interview status'));
        }
    }

    /**
     * AJAX: List Applications
     */
    public function ajax_list_applications() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $page_no = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) : 1;
        $page_size = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_size'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_size'])))) : 10;
        $job_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['job_id'])))) : -1;
        $rating_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['rating_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['rating_id'])))) : -1;
        $stage_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['stage_id'])))) : -1;
        
        $interview_status = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['interview_status'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['interview_status'])))) : -1;
        $ai_score = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ai_score'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['ai_score'])))) : -1;
        $search = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search']))))) : '';
        $email = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email'])))) ? sanitize_email(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['email']))))) : '';
        $mobile = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['mobile'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['mobile']))))) : '';
        $exp_from = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['exp_from'])))) ? floatval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['exp_from'])))) : 0;
        $exp_to = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['exp_to'])))) ? floatval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['exp_to'])))) : 0;

        $args = array(
            array('Key' => 'RequirementID', 'Value' => $job_id),
            array('Key' => 'Search', 'Value' => $search),
            array('Key' => 'RatingID', 'Value' => $rating_id),
            array('Key' => 'AIScore', 'Value' => $ai_score),
            array('Key' => 'StageID', 'Value' => $stage_id),
            array('Key' => 'IsInterviewComplete', 'Value' => $interview_status),
            array('Key' => 'Email', 'Value' => $email),
            array('Key' => 'Mobile', 'Value' => $mobile),
            array('Key' => 'ExpInYearsFrom', 'Value' => $exp_from),
            array('Key' => 'ExpInYearsTo', 'Value' => $exp_to),
            array('Key' => 'CurrentSalaryFrom', 'Value' => 0),
            array('Key' => 'CurrentSalaryTo', 'Value' => 0),
            array('Key' => 'PageNo', 'Value' => $page_no),
            array('Key' => 'PageSize', 'Value' => $page_size)
        );
        
        $result = XenHire_API::call('List_JobApplication', $args);
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Expected format: [ [Applications...], [Metadata...] ]
            $applications = isset($data[0]) ? $data[0] : [];
            $metadata = isset($data[1][0]) ? $data[1][0] : ['TotalRecordCount' => 0];
            
            wp_send_json_success(array(
                'applications' => $applications,
                'metadata' => $metadata
            ));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load applications'));
        }
    }

    /**
     * AJAX: Delete Application
     */
    public function ajax_delete_application() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['id'])))) : 0;
        
        if ($app_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid Application ID'));
        }
        
        $result = XenHire_API::call('Delete_JobApplication', array(
            array('Key' => 'ID', 'Value' => $app_id)
        ));
        
        if ($result['success']) {
            wp_send_json_success(array('message' => 'Application deleted successfully'));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to delete application'));
        }
    }

    /**
     * AJAX: Download Applications CSV
     */
    public function ajax_download_applications_csv() {
        // Mandatory nonce check for CSRF protection
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        // Capability check - only admins can download applications
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'), 403);
            wp_die();
        }

        $job_id = isset($_REQUEST['job_id']) ? intval($_REQUEST['job_id']) : -1;
        $rating_id = isset($_REQUEST['rating_id']) ? intval($_REQUEST['rating_id']) : -1;
        $stage_id = isset($_REQUEST['stage_id']) ? intval($_REQUEST['stage_id']) : -1;

        $interview_status = isset($_REQUEST['interview_status']) ? intval($_REQUEST['interview_status']) : -1;
        $ai_score = isset($_REQUEST['ai_score']) ? intval($_REQUEST['ai_score']) : -1;
        $search = isset($_REQUEST['search']) ? sanitize_text_field(wp_unslash($_REQUEST['search'])) : '';
        $email = isset($_REQUEST['email']) ? sanitize_email(wp_unslash($_REQUEST['email'])) : '';
        $mobile = isset($_REQUEST['mobile']) ? sanitize_text_field(wp_unslash($_REQUEST['mobile'])) : '';
        $exp_from = isset($_REQUEST['exp_from']) ? floatval($_REQUEST['exp_from']) : 0;
        $exp_to = isset($_REQUEST['exp_to']) ? floatval($_REQUEST['exp_to']) : 0;

        // Fetch ALL records
        $args = array(
            array('Key' => 'RequirementID', 'Value' => $job_id),
            array('Key' => 'Search', 'Value' => $search),
            array('Key' => 'RatingID', 'Value' => $rating_id),
            array('Key' => 'AIScore', 'Value' => $ai_score),
            array('Key' => 'StageID', 'Value' => $stage_id),
            array('Key' => 'IsInterviewComplete', 'Value' => $interview_status),
            array('Key' => 'Email', 'Value' => $email),
            array('Key' => 'Mobile', 'Value' => $mobile),
            array('Key' => 'ExpInYearsFrom', 'Value' => $exp_from),
            array('Key' => 'ExpInYearsTo', 'Value' => $exp_to),
            array('Key' => 'CurrentSalaryFrom', 'Value' => 0),
            array('Key' => 'CurrentSalaryTo', 'Value' => 0),
            array('Key' => 'PageNo', 'Value' => 1),
            array('Key' => 'PageSize', 'Value' => 99999) // Fetch all
        );
        
        $result = XenHire_API::call('List_JobApplication', $args);
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            $applications = isset($data[0]) ? $data[0] : [];

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=applications_' . gmdate('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($output, array(
                'Candidate Name', 'Job Title', 'Email', 'Mobile', 'Stage', 
                'Rating', 'Experience', 'Current Salary', 'Designation', 
                'Employer', 'Applied On', 'Interview Status'
            ));

            // CSV Rows
            foreach ($applications as $app) {
                // Strip HTML from InterviewStatus
                $interviewStatus = isset($app['InterviewStatus']) ? wp_strip_all_tags($app['InterviewStatus']) : '';
                
                fputcsv($output, array(
                    isset($app['Candidate']) ? $app['Candidate'] : '',
                    isset($app['JobTitle']) ? $app['JobTitle'] : '',
                    isset($app['Email']) ? $app['Email'] : '',
                    isset($app['Mobile']) ? $app['Mobile'] : '',
                    isset($app['Stage']) ? $app['Stage'] : '',
                    isset($app['Rating']) ? $app['Rating'] : '',
                    isset($app['ExpInYears']) ? $app['ExpInYears'] : '',
                    isset($app['CurrentSalary']) ? $app['CurrentSalary'] : '',
                    isset($app['Designation']) ? $app['Designation'] : '',
                    isset($app['Employer']) ? $app['Employer'] : '',
                    isset($app['CreatedOn']) ? $app['CreatedOn'] : '',
                    $interviewStatus
                ));
            }
            
            fclose($output); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
            exit;
        } else {
            // Handle error (maybe redirect or show simple message)
            wp_die('Failed to fetch data for CSV.');
        }
    }






    /**
     * AJAX: List Employers
     */
    public function ajax_list_employers() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $page_no = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_no'])))) : 1;
        $page_size = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_size'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['page_size'])))) : 10;
        $search = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['search']))))) : '';
        $is_active = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['is_active'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['is_active'])))) : -1;

        $args = array(
            array('Key' => 'IsActive', 'Value' => $is_active),
            array('Key' => 'Search', 'Value' => $search),
            array('Key' => 'PageNo', 'Value' => $page_no),
            array('Key' => 'PageSize', 'Value' => $page_size)
        );
        
        $result = XenHire_API::call('List_Employer', $args);
        
        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Expected format: [ [Employers...], [Metadata...] ]
            $employers = isset($data[0]) ? $data[0] : [];
            $metadata = isset($data[1][0]) ? $data[1][0] : ['TotalRecordCount' => 0];
            
            wp_send_json_success(array(
                'employers' => $employers,
                'metadata' => $metadata
            ));
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load employers'));
        }
    }
    
    /**
     * AJAX: Toggle Employer Status
     */
    public function ajax_toggle_employer_status() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $emp_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emp_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emp_id'])))) : 0;
        
        if ($emp_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid employer ID'));
        }
        
        // Assuming API is Set_Employer_ToggleActive similar to jobs
        $result = XenHire_API::call('Set_Employer_ToggleActive', array(
            array('Key' => 'ID', 'Value' => $emp_id)
        ));
        
        if ($result['success']) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to update status'));
        }
    }

    /**
     * AJAX: Save Employer
     */
    public function ajax_save_employer() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        
        $brand_name = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BrandName'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['BrandName']))))) : '';
        $company_name = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CompanyName'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['CompanyName']))))) : '';
        $website = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Website'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Website']))))) : '';
        $industry = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Industry'])))) ? sanitize_text_field(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Industry']))))) : '';
        $description = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Description'])))) ? wp_kses_post(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['Description']))))) : '';
        $logo_url = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['LogoURL'])))) ? esc_url_raw(wp_unslash(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['LogoURL']))))) : '';
        $is_recruiting = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsRecruiting'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['IsRecruiting'])))) : 0;
        $employer_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['EmployerID'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['EmployerID'])))) : -1;
        
        if (empty($brand_name) || empty($company_name)) {
            wp_send_json_error(array('message' => 'Brand Name and Company Name are required'));
        }
        
        $args = array(
            array('Key' => 'ID', 'Value' => (string)$employer_id),
            array('Key' => 'LogoIMG', 'Value' => $logo_url),
            array('Key' => 'CompanyName', 'Value' => $company_name),
            array('Key' => 'BrandName', 'Value' => $brand_name),
            array('Key' => 'Description', 'Value' => $description),
            array('Key' => 'Website', 'Value' => $website),
            array('Key' => 'Industry', 'Value' => $industry),
            array('Key' => 'Remarks', 'Value' => ''),
            array('Key' => 'IsActive', 'Value' => (string)$is_recruiting)
        );
        
        $result = XenHire_API::call('Set_Employer', $args);
        
        if ($result['success']) {
             // Check for API specific error messages in data
             if (!empty($result['data'])) {
                $data = json_decode($result['data'], true);
                if (isset($data[0][0]['IsError']) && $data[0][0]['IsError']) {
                    wp_send_json_error(array('message' => $data[0][0]['Message']));
                    return;
                }
            }
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to save employer'));
        }
    }

/**
 * AJAX: Get Employer Details
 */
public function ajax_get_employer_details() {
    check_ajax_referer('xenhire_nonce', 'nonce');

    $emp_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emp_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['emp_id'])))) : 0;

    if ($emp_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid Employer ID'));
    }

    $args = array(
        array('Key' => 'ID', 'Value' => (string)$emp_id)
    );

    $result = XenHire_API::call('Get_Employer', $args);

    if ($result['success'] && !empty($result['data'])) {
        $data = json_decode($result['data'], true);
        
        // Expected format: [ [EmployerDetails] ]
        if (isset($data[0][0])) {
            wp_send_json_success($data[0][0]);
        } else {
            wp_send_json_error(array('message' => 'Employer not found'));
        }
    } else {
        wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to fetch employer details'));
    }
}

    /**
     * Private helper to get application details data
     */
    private function get_application_details_data($app_id) {
        $args = array(
            array('Key' => 'ID', 'Value' => (string)$app_id)
        );

        $result = XenHire_API::call('Get_JobApplication', $args);

        if ($result['success'] && !empty($result['data'])) {
            $data = json_decode($result['data'], true);
            
            // Return full data to include interview details (likely in second result set)
            if (isset($data[0][0])) {
                
                // --- Share Key Logic ---
                global $wpdb;
                $table_name = $wpdb->prefix . 'xenhire_share_links';
                
                // Ensure table exists (Lazy check to avoid running on every init)
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                    $charset_collate = $wpdb->get_charset_collate();
                    $sql = "CREATE TABLE $table_name (
                        id bigint(20) NOT NULL AUTO_INCREMENT,
                        share_key varchar(36) NOT NULL,
                        application_id bigint(20) NOT NULL,
                        created_at datetime DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY  (id),
                        KEY share_key (share_key),
                        KEY application_id (application_id)
                    ) $charset_collate;";
                    if (!function_exists('dbDelta')) {
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                    }
                    dbDelta($sql);
                }

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $share_key = $wpdb->get_var($wpdb->prepare("SELECT share_key FROM $table_name WHERE application_id = %d", $app_id));

                if (!$share_key) {
                    $share_key = wp_generate_uuid4();
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $wpdb->insert(
                        $table_name,
                        array(
                            'share_key' => $share_key,
                            'application_id' => $app_id
                        )
                    );
                }
                
                // Append key to details
                $data[0][0]['ShareKey'] = $share_key;

                return array(
                    'success' => true,
                    'data' => array(
                        'details' => $data[0][0],
                        'education' => isset($data[1]) ? $data[1] : [],
                        'experience' => isset($data[2]) ? $data[2] : [],
                        'interview_data' => isset($data[3]) ? $data[3] : []
                    )
                );
            } else {
                return array('success' => false, 'message' => 'Application not found');
            }
        } else {
            return array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Failed to fetch application details');
        }
    }

/**
 * AJAX: Get Job Application Details
 */
public function ajax_get_job_application_details() {
    check_ajax_referer('xenhire_nonce', 'nonce');

    $app_id = isset(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) ? intval(sanitize_text_field(sanitize_text_field(sanitize_text_field($_POST['app_id'])))) : 0;

    if ($app_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid Application ID'));
    }

    $result = $this->get_application_details_data($app_id);

    if ($result['success']) {
        wp_send_json_success($result['data']);
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}

/**
 * AJAX: Get Stages
 */
public function ajax_get_stages() {
    check_ajax_referer('xenhire_nonce', 'nonce');

    $args = array(
        array('Key' => 'IsActive', 'Value' => 1), // Only active stages
        array('Key' => 'PageNo', 'Value' => 1),
        array('Key' => 'PageSize', 'Value' => 100)
    );

    $result = XenHire_API::call('List_Stage', $args);

    if ($result['success'] && !empty($result['data'])) {
        $data = json_decode($result['data'], true);
        // Expected format: [ [Stages...], [Metadata...] ]
        $stages = isset($data[0]) ? $data[0] : [];
        wp_send_json_success($stages);
    } else {
        wp_send_json_error(array('message' => isset($result['message']) ? $result['message'] : 'Failed to load stages'));
    }
}

    /**
     * AJAX: Upload Video (File or Recording)
     */
    public function ajax_upload_video() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        // Required for media_handle_sideload() and wp_handle_upload() functions used immediately below
        // Per WordPress.org plugin guidelines, this is acceptable when the function is called right after
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';


        if (!isset($_FILES['video_file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $file = $_FILES['video_file'];

        // Validate file type
        $file_type = wp_check_filetype($file['name']);
        $allowed_types = ['video/webm', 'video/mp4', 'video/quicktime'];
        
        if (!in_array($file_type['type'], $allowed_types) && !in_array($file['type'], $allowed_types)) {
             // Basic check failed, but sometimes mime type detection is tricky. 
             // Let's trust wp_handle_upload to do the heavy lifting but we should at least check extension.
             $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
             if (!in_array($ext, ['webm', 'mp4', 'mov'])) {
                 wp_send_json_error(['message' => 'Invalid file type. Allowed: .webm, .mp4, .mov']);
             }
        }

        // Validate size (40MB)
        if ($file['size'] > 40 * 1024 * 1024) {
            wp_send_json_error(['message' => 'File size exceeds 40MB limit']);
        }

        // Use wp_handle_upload
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $file_path = $movefile['file'];
            $file_url = $movefile['url'];
            $file_type = wp_check_filetype(basename($file_path), null);

            // Prepare attachment data
            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_path)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            // Insert attachment
            $attach_id = wp_insert_attachment($attachment, $file_path);

            // Generate metadata
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);

            wp_send_json_success(['url' => $file_url, 'id' => $attach_id]);
        } else {
            wp_send_json_error(['message' => $movefile['error']]);
        }
    }

}
