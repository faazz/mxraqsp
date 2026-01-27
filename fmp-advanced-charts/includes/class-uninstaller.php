<?php
/**
 * Fired during plugin uninstallation.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin uninstaller class.
 */
class FMP_Advanced_Charts_Uninstaller {

    /**
     * Uninstall the plugin.
     */
    public static function uninstall() {
        global $wpdb;

        // Remove custom tables
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fmp_chart_cache");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fmp_user_layouts");

        // Remove all plugin options
        $options = array(
            'fmp_api_key',
            'fmp_default_chart_type',
            'fmp_default_timeframe',
            'fmp_default_theme',
            'fmp_cache_duration_historical',
            'fmp_cache_duration_profile',
            'fmp_cache_duration_quote',
            'fmp_chart_height',
            'fmp_chart_width',
            'fmp_enable_toolbar',
            'fmp_enable_drawings',
            'fmp_enable_indicators',
            'fmp_rate_limit_per_minute',
            'fmp_debug_mode',
            'fmp_advanced_charts_db_version',
            'fmp_advanced_charts_activated'
        );

        foreach ($options as $option) {
            delete_option($option);
        }

        // Clear all transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_fmp_%' OR option_name LIKE '_transient_timeout_fmp_%'");
    }
}
