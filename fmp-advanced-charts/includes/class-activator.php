<?php
/**
 * Fired during plugin activation.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin activator class.
 */
class FMP_Advanced_Charts_Activator {

    /**
     * Activate the plugin.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();

        // Set activation flag
        update_option('fmp_advanced_charts_activated', true);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix;

        // Table for chart cache
        $cache_table = $table_prefix . 'fmp_chart_cache';
        $cache_sql = "CREATE TABLE IF NOT EXISTS $cache_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            cache_key VARCHAR(255) UNIQUE NOT NULL,
            symbol VARCHAR(20) NOT NULL,
            timeframe VARCHAR(10) NOT NULL,
            data_type VARCHAR(50) NOT NULL,
            data LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            INDEX idx_symbol (symbol),
            INDEX idx_expires (expires_at),
            INDEX idx_cache_key (cache_key)
        ) $charset_collate;";

        // Table for user layouts
        $layouts_table = $table_prefix . 'fmp_user_layouts';
        $layouts_sql = "CREATE TABLE IF NOT EXISTS $layouts_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            layout_name VARCHAR(100) NOT NULL,
            symbol VARCHAR(20) NOT NULL,
            settings LONGTEXT,
            indicators LONGTEXT,
            drawings LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_symbol (symbol)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($cache_sql);
        dbDelta($layouts_sql);

        // Store database version
        update_option('fmp_advanced_charts_db_version', FMP_ADVANCED_CHARTS_VERSION);
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $defaults = array(
            'fmp_api_key' => '',
            'fmp_default_chart_type' => 'candlestick',
            'fmp_default_timeframe' => '1D',
            'fmp_default_theme' => 'dark',
            'fmp_cache_duration_historical' => 86400, // 24 hours
            'fmp_cache_duration_profile' => 604800, // 7 days
            'fmp_cache_duration_quote' => 300, // 5 minutes
            'fmp_chart_height' => '600',
            'fmp_chart_width' => '100%',
            'fmp_enable_toolbar' => true,
            'fmp_enable_drawings' => true,
            'fmp_enable_indicators' => true,
            'fmp_rate_limit_per_minute' => 300,
            'fmp_debug_mode' => false
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}
