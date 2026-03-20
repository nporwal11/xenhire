<?php
/**
 * Plugin Name: XenHire
 * Plugin URI: 
 * Description: Complete job board integration with the XenHire API. Manage jobs, applications, and candidates directly from WordPress.
 * Version: 1.2.9
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: XenHire
 * License: GPLv2 or later
 * License URI: 
 * Text Domain: xenhire
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -------------------------------------------------------------------------
 * CONSTANTS
 * ---------------------------------------------------------------------- */

define( 'XENHIRE_VERSION', '1.2.9' );
define( 'XENHIRE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'XENHIRE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'XENHIRE_API_BASE_URL', '' );

/* -------------------------------------------------------------------------
 * INCLUDES
 * ---------------------------------------------------------------------- */

require_once XENHIRE_PLUGIN_DIR . 'includes/class-xenhire-auth.php';
require_once XENHIRE_PLUGIN_DIR . 'includes/class-xenhire-api.php';
require_once XENHIRE_PLUGIN_DIR . 'admin/class-xenhire-admin.php';
require_once XENHIRE_PLUGIN_DIR . 'public/class-xenhire-public.php';

// Include testing endpoints (moved from direct loading to REST)
// require_once XENHIRE_PLUGIN_DIR . 'test_endpoints.php';


/* -------------------------------------------------------------------------
 * INITIALIZATION
 * ---------------------------------------------------------------------- */

function xenhire_init() {

    // Core services (AJAX + API)
    new XenHire_Auth();
    new XenHire_API();

    // Admin & Public handlers
    if ( is_admin() ) {
        new XenHire_Admin();
    }

    new XenHire_Public();
}
add_action( 'plugins_loaded', 'xenhire_init' );

/* -------------------------------------------------------------------------
 * ACTIVATION
 * ---------------------------------------------------------------------- */

register_activation_hook( __FILE__, 'xenhire_activate' );

function xenhire_activate() {

    // Create Jobs page if not exists
    $existing_page = get_page_by_path( 'jobs' );

    if ( ! $existing_page ) {
        $page_id = wp_insert_post(
            array(
                'post_title'   => 'Jobs',
                'post_content' => '[xenhire_jobs]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            )
        );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_option( 'xenhire_jobs_page_id', absint( $page_id ) );
        }
    }

    add_option( 'xenhire_activation_redirect', true );

    // Register rewrite rules before flushing
    $public = new XenHire_Public();
    $public->add_rewrite_rules();

    flush_rewrite_rules();
}

/* -------------------------------------------------------------------------
 * POST-ACTIVATION REDIRECT
 * ---------------------------------------------------------------------- */

add_action( 'admin_init', 'xenhire_activation_redirect' );

function xenhire_activation_redirect() {

    if ( ! get_option( 'xenhire_activation_redirect', false ) ) {
        return;
    }

    delete_option( 'xenhire_activation_redirect' );

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['activate-multi'] ) ) {
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=xenhire' ) );
    exit;
}

/* -------------------------------------------------------------------------
 * DEACTIVATION
 * ---------------------------------------------------------------------- */

register_deactivation_hook( __FILE__, 'xenhire_deactivate' );

function xenhire_deactivate() {
    delete_option( 'xenhire_activation_redirect' );
    flush_rewrite_rules();
}
