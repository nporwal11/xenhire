<?php
/**
 * XenHire Authentication Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class XenHire_Auth {

    const ACCESS_TOKEN_TRANSIENT = 'xenhire_access_token';
    const TOKEN_EXPIRY_OPTION    = 'xenhire_token_expiry';
    const REFRESH_TOKEN_OPTION  = 'xenhire_refresh_token';
    const API_KEY_OPTION        = 'xenhire_api_key';
    const LOGGED_EMAIL_OPTION   = 'xenhire_logged_email';
    const VENDOR_USER_ID_OPTION = 'xenhire_vendor_user_id';

    public function __construct() {
        add_action('wp_ajax_xenhire_login', [$this, 'ajax_login']);
        add_action('wp_ajax_nopriv_xenhire_login', [$this, 'ajax_login']);

        add_action('wp_ajax_xenhire_register', [$this, 'ajax_register']);
        add_action('wp_ajax_nopriv_xenhire_register', [$this, 'ajax_register']);

        add_action('wp_ajax_xenhire_logout', [$this, 'ajax_logout']);
    }

    /* =========================
     * CORE STATE HELPERS
     * ========================= */

    public static function is_logged_in(): bool {
        $token = get_transient(self::ACCESS_TOKEN_TRANSIENT);
        $expiry = get_option(self::TOKEN_EXPIRY_OPTION, 0);

        return (!empty($token) && time() < intval($expiry));
    }

    public static function get_access_token() {
        return get_transient(self::ACCESS_TOKEN_TRANSIENT);
    }

    public static function get_api_key() {
        return get_option(self::API_KEY_OPTION);
    }

    public static function get_logged_email() {
        return get_option(self::LOGGED_EMAIL_OPTION);
    }

    /**
     * Optional helper used in feedback mapping
     */
    public static function get_vendor_user_id($app_id = 0) {
        return get_option(self::VENDOR_USER_ID_OPTION, 0);
    }

    /* =========================
     * AUTH ACTIONS
     * ========================= */

    public static function login($email, $password) {
        $response = wp_remote_post(
            XENHIRE_API_BASE_URL . '/Account/APILogin',
            [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode([
                    'Email'    => sanitize_email($email),
                    'Password' => sanitize_text_field($password),
                ]),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['Result']) || $body['Result'] !== 'OK') {
            return ['success' => false, 'message' => $body['Message'] ?? 'Login failed'];
        }

        $expires = max(intval($body['ExpiresIn'] ?? 3600) - 300, 300);
        $expiry_time = time() + $expires;

        set_transient(self::ACCESS_TOKEN_TRANSIENT, $body['AccessToken'], $expires);
        update_option(self::TOKEN_EXPIRY_OPTION, $expiry_time);
        update_option(self::API_KEY_OPTION, $body['ApiKey'] ?? '');
        update_option(self::LOGGED_EMAIL_OPTION, $email);
        
        // Try to find User ID
        $user_id = $body['ID'] ?? $body['UserId'] ?? $body['VendorUserID'] ?? 0;
        if ($user_id) {
            update_option(self::VENDOR_USER_ID_OPTION, $user_id);
        }

        if (!empty($body['RefreshToken'])) {
            update_option(self::REFRESH_TOKEN_OPTION, $body['RefreshToken']);
        }

        return ['success' => true, 'message' => $body['Message'] ?? 'Login successful'];
    }

    public static function register($email, $password) {
        return self::login($email, $password);
    }

    public static function logout() {
        delete_transient(self::ACCESS_TOKEN_TRANSIENT);
        delete_option(self::TOKEN_EXPIRY_OPTION);
        delete_option(self::REFRESH_TOKEN_OPTION);
        delete_option(self::API_KEY_OPTION);
        delete_option(self::LOGGED_EMAIL_OPTION);
    }

    /* =========================
     * AJAX HANDLERS
     * ========================= */

    public function ajax_login() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

        $result = self::login($email, $password);
        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }

    public function ajax_register() {
        check_ajax_referer('xenhire_nonce', 'nonce');

        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

        $result = self::register($email, $password);
        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }

    public function ajax_logout() {
        check_ajax_referer('xenhire_nonce', 'nonce');
        self::logout();
        wp_send_json_success(['message' => 'Logged out']);
    }
}
