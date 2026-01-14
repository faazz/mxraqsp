<?php
/**
 * Plugin Name: FMP Advanced Charts
 * Plugin URI: https://github.com/faazz/mxraqsp
 * Description: Advanced TradingView-style financial charting with Financial Modeling Prep API integration
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://github.com/faazz
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fmp-advanced-charts
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('FMP_ADVANCED_CHARTS_VERSION', '1.0.0');
define('FMP_ADVANCED_CHARTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FMP_ADVANCED_CHARTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FMP_ADVANCED_CHARTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Plugin activation hook.
 */
function fmp_advanced_charts_activate() {
    // Create custom database tables
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-activator.php';
    FMP_Advanced_Charts_Activator::activate();
}
register_activation_hook(__FILE__, 'fmp_advanced_charts_activate');

/**
 * Plugin deactivation hook.
 */
function fmp_advanced_charts_deactivate() {
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-deactivator.php';
    FMP_Advanced_Charts_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'fmp_advanced_charts_deactivate');

/**
 * Plugin uninstall hook.
 */
function fmp_advanced_charts_uninstall() {
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-uninstaller.php';
    FMP_Advanced_Charts_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'fmp_advanced_charts_uninstall');

/**
 * Initialize the plugin.
 */
function fmp_advanced_charts_init() {
    // Load plugin text domain for translations
    load_plugin_textdomain('fmp-advanced-charts', false, dirname(FMP_ADVANCED_CHARTS_PLUGIN_BASENAME) . '/languages');

    // Include core classes
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-api-handler.php';
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-chart-renderer.php';
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-settings.php';
    require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-shortcode.php';

    // Initialize settings page
    if (is_admin()) {
        $settings = new FMP_Advanced_Charts_Settings();
    }

    // Initialize shortcode
    $shortcode = new FMP_Advanced_Charts_Shortcode();

    // Enqueue scripts and styles
    add_action('wp_enqueue_scripts', 'fmp_advanced_charts_enqueue_assets');
    add_action('admin_enqueue_scripts', 'fmp_advanced_charts_admin_enqueue_assets');
}
add_action('plugins_loaded', 'fmp_advanced_charts_init');

/**
 * Enqueue frontend assets.
 */
function fmp_advanced_charts_enqueue_assets() {
    // Only enqueue if shortcode is present on the page
    global $post;
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'fmp_chart') || has_shortcode($post->post_content, 'fmp_chart_grid'))) {
        // Enqueue Lightweight Charts library
        wp_enqueue_script(
            'lightweight-charts',
            'https://unpkg.com/lightweight-charts@4.1.0/dist/lightweight-charts.standalone.production.js',
            array(),
            '4.1.0',
            true
        );

        // Enqueue custom chart engine
        wp_enqueue_script(
            'fmp-chart-engine',
            FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/js/chart-engine.js',
            array('jquery', 'lightweight-charts'),
            FMP_ADVANCED_CHARTS_VERSION,
            true
        );

        // Enqueue indicators
        wp_enqueue_script(
            'fmp-indicators',
            FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/js/indicators.js',
            array('fmp-chart-engine'),
            FMP_ADVANCED_CHARTS_VERSION,
            true
        );

        // Enqueue drawing tools
        wp_enqueue_script(
            'fmp-drawing-tools',
            FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/js/drawing-tools.js',
            array('fmp-chart-engine'),
            FMP_ADVANCED_CHARTS_VERSION,
            true
        );

        // Enqueue styles
        wp_enqueue_style(
            'fmp-chart-styles',
            FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/css/chart-styles.css',
            array(),
            FMP_ADVANCED_CHARTS_VERSION
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script('fmp-chart-engine', 'fmpChartsConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fmp_charts_nonce'),
            'pluginUrl' => FMP_ADVANCED_CHARTS_PLUGIN_URL
        ));
    }
}

/**
 * Enqueue admin assets.
 */
function fmp_advanced_charts_admin_enqueue_assets($hook) {
    // Only load on plugin settings page
    if ($hook !== 'settings_page_fmp-advanced-charts') {
        return;
    }

    wp_enqueue_style(
        'fmp-admin-styles',
        FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/css/admin-styles.css',
        array(),
        FMP_ADVANCED_CHARTS_VERSION
    );

    wp_enqueue_script(
        'fmp-admin-scripts',
        FMP_ADVANCED_CHARTS_PLUGIN_URL . 'assets/js/admin-scripts.js',
        array('jquery'),
        FMP_ADVANCED_CHARTS_VERSION,
        true
    );
}

/**
 * AJAX handler for chart data requests.
 */
function fmp_charts_get_data() {
    check_ajax_referer('fmp_charts_nonce', 'nonce');

    $symbol = sanitize_text_field($_POST['symbol'] ?? '');
    $timeframe = sanitize_text_field($_POST['timeframe'] ?? '1D');
    $from = sanitize_text_field($_POST['from'] ?? '');
    $to = sanitize_text_field($_POST['to'] ?? '');

    if (empty($symbol)) {
        wp_send_json_error(array('message' => __('Symbol is required', 'fmp-advanced-charts')));
        return;
    }

    $api_handler = new FMP_Advanced_Charts_API_Handler();
    $data = $api_handler->get_chart_data($symbol, $timeframe, $from, $to);

    if (is_wp_error($data)) {
        wp_send_json_error(array('message' => $data->get_error_message()));
    } else {
        wp_send_json_success($data);
    }
}
add_action('wp_ajax_fmp_get_chart_data', 'fmp_charts_get_data');
add_action('wp_ajax_nopriv_fmp_get_chart_data', 'fmp_charts_get_data');

/**
 * AJAX handler for symbol search.
 */
function fmp_charts_search_symbols() {
    check_ajax_referer('fmp_charts_nonce', 'nonce');

    $query = sanitize_text_field($_POST['query'] ?? '');

    if (strlen($query) < 1) {
        wp_send_json_error(array('message' => __('Query too short', 'fmp-advanced-charts')));
        return;
    }

    $api_handler = new FMP_Advanced_Charts_API_Handler();
    $results = $api_handler->search_symbols($query);

    if (is_wp_error($results)) {
        wp_send_json_error(array('message' => $results->get_error_message()));
    } else {
        wp_send_json_success($results);
    }
}
add_action('wp_ajax_fmp_search_symbols', 'fmp_charts_search_symbols');
add_action('wp_ajax_nopriv_fmp_search_symbols', 'fmp_charts_search_symbols');

/**
 * AJAX handler for real-time quote.
 */
function fmp_charts_get_quote() {
    check_ajax_referer('fmp_charts_nonce', 'nonce');

    $symbol = sanitize_text_field($_POST['symbol'] ?? '');

    if (empty($symbol)) {
        wp_send_json_error(array('message' => __('Symbol is required', 'fmp-advanced-charts')));
        return;
    }

    $api_handler = new FMP_Advanced_Charts_API_Handler();
    $quote = $api_handler->get_real_time_quote($symbol);

    if (is_wp_error($quote)) {
        wp_send_json_error(array('message' => $quote->get_error_message()));
    } else {
        wp_send_json_success($quote);
    }
}
add_action('wp_ajax_fmp_get_quote', 'fmp_charts_get_quote');
add_action('wp_ajax_nopriv_fmp_get_quote', 'fmp_charts_get_quote');
