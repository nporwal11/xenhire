<?php if (!defined('ABSPATH')) exit; ?>
<?php
/**
 * XenHire API Class
 * Handles all API communications with XenHire backend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class XenHire_API {

    /**
     * ============================
     * CORE API CALL (AUTH TOKEN)
     * ============================
     */
    public static function call( $proc, $args = array() ) {

        $access_token = XenHire_Auth::get_access_token();

        if ( empty( $access_token ) ) {
            return array(
                'success' => false,
                'message' => 'Not authenticated. Please login again.',
            );
        }

        $payload = wp_json_encode( array(
            'Proc' => sanitize_key( $proc ),
            'args' => $args,
        ) );

        $response = wp_remote_post(
            XENHIRE_API_BASE_URL . '/Api/Call',
            array(
                'headers' => array(
                    'Authorization'    => 'Bearer ' . sanitize_text_field( $access_token ),
                    'Content-Type'     => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                ),
                'body'    => $payload,
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $raw_body = wp_remote_retrieve_body( $response );
        $raw_body = preg_replace( '/^\xEF\xBB\xBF/', '', $raw_body );

        $body = json_decode( $raw_body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return array(
                'success' => false,
                'message' => 'Invalid JSON response',
                'debug'   => $raw_body,
            );
        }

        if ( isset( $body['Code'] ) && (int) $body['Code'] === 200 ) {
            return array(
                'success' => true,
                'data'    => $body['Data'], // keep string
                'message' => $body['Message'] ?? 'OK',
            );
        }

        return array(
            'success' => false,
            'message' => $body['Message'] ?? 'API call failed',
            'debug'   => $body,
        );
    }

    /**
     * ============================
     * DIRECT API CALL
     * ============================
     */
    public static function call_direct( $endpoint, $data = array() ) {

        $access_token = XenHire_Auth::get_access_token();

        if ( empty( $access_token ) ) {
            return array(
                'success' => false,
                'message' => 'Not authenticated',
            );
        }

        $response = wp_remote_post(
            esc_url_raw( XENHIRE_API_BASE_URL . '/' . ltrim( $endpoint, '/' ) ),
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . sanitize_text_field( $access_token ),
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode( $data ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['Result'] ) && $body['Result'] === 'OK' ) {
            return array(
                'success' => true,
                'data'    => $body,
            );
        }

        if ( isset( $body['Code'] ) && (int) $body['Code'] === 200 ) {
            return array(
                'success' => true,
                'data'    => $body,
            );
        }

        return array(
            'success' => false,
            'message' => $body['Message'] ?? 'API call failed',
            'data'    => $body,
        );
    }

    /**
     * ============================
     * PUBLIC API CALL (API KEY)
     * ============================
     */
    public static function public_call( $proc, $args = array() ) {

        $api_key = XenHire_Auth::get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'success' => false,
                'message' => 'API Key not found',
            );
        }

        array_unshift( $args, array(
            'Key'   => 'APIKey',
            'Value' => $api_key,
        ) );

        $response = wp_remote_post(
            XENHIRE_API_BASE_URL . '/Api/Call',
            array(
                'headers' => array(
                    'Content-Type'      => 'application/json',
                    'X-Requested-With'  => 'XMLHttpRequest',
                ),
                'body'    => wp_json_encode( array(
                    'Proc' => sanitize_key( $proc ),
                    'args' => $args,
                ) ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['Code'] ) && (int) $body['Code'] === 200 ) {
            return array(
                'success' => true,
                'data'    => $body['Data'],
                'message' => $body['Message'] ?? 'OK',
            );
        }

        return array(
            'success' => false,
            'message' => $body['Message'] ?? 'API call failed',
            'debug'   => $body,
        );
    }

    /**
     * ============================
     * CBO (COMBO BOX OPTIONS)
     * ============================
     */
    public static function get_cbo( $key, $use_form_data = false ) {

        $access_token = XenHire_Auth::get_access_token();

        if ( empty( $access_token ) ) {
            return array(
                'success' => false,
                'message' => 'Not authenticated',
            );
        }

        $headers = array(
            'Authorization' => 'Bearer ' . sanitize_text_field( $access_token ),
            'Accept'        => 'application/json',
        );

        if ( $use_form_data ) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            $body = 'Key=' . rawurlencode( sanitize_text_field( $key ) );
        } else {
            $headers['Content-Type'] = 'application/json';
            $body = wp_json_encode( array(
                'Key' => sanitize_text_field( $key ),
            ) );
        }

        $response = wp_remote_post(
            XENHIRE_API_BASE_URL . '/CBOExpression/GetCBOItems',
            array(
                'headers' => $headers,
                'body'    => $body,
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $data['Result'] ) && $data['Result'] === 'OK' ) {
            return array(
                'success' => true,
                'data'    => $data['Options'] ?? array(),
            );
        }

        return array(
            'success' => false,
            'message' => 'Failed to load CBO items',
            'debug'   => $data,
        );
    }

    /**
     * ============================
     * UPLOAD RESUME
     * ============================
     */
    public static function upload_resume( $file_path, $file_name, $file_type, $job_id ) {
        $access_token = XenHire_Auth::get_access_token();

        if ( empty( $access_token ) ) {
            return array(
                'success' => false,
                'message' => 'Not authenticated',
            );
        }

        $boundary = wp_generate_password( 24 );
        $headers  = array(
            'Authorization' => 'Bearer ' . sanitize_text_field( $access_token ),
            'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
        );

        $payload = '';

        // 1. File Field
        if ( file_exists( $file_path ) ) {
            $payload .= '--' . $boundary . "\r\n";
            $payload .= 'Content-Disposition: form-data; name="file"; filename="' . $file_name . '"' . "\r\n";
            $payload .= 'Content-Type: ' . $file_type . "\r\n\r\n";
            $payload .= file_get_contents( $file_path ) . "\r\n";
        }

        // 2. RequirementID (Job ID)
        if ( $job_id ) {
            $payload .= '--' . $boundary . "\r\n";
            $payload .= 'Content-Disposition: form-data; name="RequirementID"' . "\r\n\r\n";
            $payload .= $job_id . "\r\n";
        }

        // 3. IsParse (Trigger parsing on server side if supported)
        $payload .= '--' . $boundary . "\r\n";
        $payload .= 'Content-Disposition: form-data; name="IsParse"' . "\r\n\r\n";
        $payload .= 'true' . "\r\n";

        $payload .= '--' . $boundary . '--';

        $response = wp_remote_post(
            XENHIRE_API_BASE_URL . '/Candidate/UploadResume',
            array(
                'headers' => $headers,
                'body'    => $payload,
                'timeout' => 60,
            )
        );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        // Success usually means Code 200 or Result OK
        if ( ( isset( $body['Code'] ) && (int) $body['Code'] === 200 ) || ( isset( $body['Result'] ) && $body['Result'] === 'OK' ) ) {
            return array(
                'success' => true,
                'data'    => $body['Data'] ?? $body,
            );
        }

        return array(
            'success' => false,
            'message' => $body['Message'] ?? 'Upload failed',
            'debug'   => $body,
        );
    }
}
