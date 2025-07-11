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
