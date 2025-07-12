<?php
/**
 * =================================================================
 * BANDLAND SHOES - GLOBAL HELPER FUNCTIONS
 * =================================================================
 * This file contains globally accessible helper functions.
 * =================================================================
 */

if (!function_exists('generate_slug')) {
    /**
     * Generate a URL-friendly slug from a string.
     *
     * @param string $text The input string.
     * @return string The generated slug.
     */
    function generate_slug($text) {
        // Replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Trim
        $text = trim($text, '-');

        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // Lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}


if (!function_exists('generate_uuid')) {
    /**
     * Generate a version 4 (random) UUID.
     *
     * @return string The UUID.
     */
    function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('set_flash_message')) {
    /**
     * Set a flash message in the session.
     *
     * @param string $type The message type (e.g., 'success', 'error').
     * @param string $message The message content.
     */
    function set_flash_message($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flash_message')) {
    /**
     * Get and clear a flash message from the session.
     *
     * @return array|null The flash message or null if not set.
     */
    function get_flash_message() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * Generate and store a CSRF token in the session.
     *
     * @return string The CSRF token.
     */
    function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Verify a CSRF token.
     *
     * @param string $token The token to verify.
     * @return bool True if the token is valid, false otherwise.
     */
    function verify_csrf_token($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
