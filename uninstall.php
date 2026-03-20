<?php if (!defined('ABSPATH')) exit; ?>
<?php
/**
 * Uninstall XenHire Plugin
 * Fired when the plugin is uninstalled
 */

// Exit if accessed directly or not during uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete Jobs Page
$xenhire_job_page_id = get_option('xenhire_jobs_page_id');
if ($xenhire_job_page_id) {
    wp_delete_post($xenhire_job_page_id, true);
}

// Delete all plugin options
delete_option('xenhire_api_key');
delete_option('xenhire_token_expiry');
delete_option('xenhire_logged_email');
delete_option('xenhire_jobs_page_id');
delete_option('xenhire_activation_redirect');

// Delete transients
delete_transient('xenhire_access_token');
