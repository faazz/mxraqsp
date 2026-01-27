<?php
/**
 * Fired during plugin deactivation.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin deactivator class.
 */
class FMP_Advanced_Charts_Deactivator {

    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Clear scheduled events if any
        wp_clear_scheduled_hook('fmp_charts_cleanup_cache');

        // Flush rewrite rules
        flush_rewrite_rules();

        // Remove activation flag
        delete_option('fmp_advanced_charts_activated');
    }
}
