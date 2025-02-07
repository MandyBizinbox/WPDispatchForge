<?php
/**
 * Platform Utility Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

// Token generation for secure actions
if (!function_exists('wpdf_generate_token')) {
    function wpdf_generate_token($action) {
        return hash_hmac('sha256', $action, AUTH_SALT);
    }
}

// Token verification for secure actions
if (!function_exists('wpdf_verify_token')) {
    function wpdf_verify_token($token, $action) {
        return hash_equals($token, wpdf_generate_token($action));
    }
}

// Additional platform-related utility functions will be added here
