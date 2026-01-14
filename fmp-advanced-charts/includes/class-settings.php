<?php
/**
 * Settings page handler.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class.
 */
class FMP_Advanced_Charts_Settings {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_fmp_test_connection', array($this, 'test_api_connection'));
        add_action('admin_post_fmp_clear_cache', array($this, 'clear_cache'));
    }

    /**
     * Add settings page to WordPress admin.
     */
    public function add_settings_page() {
        add_options_page(
            __('FMP Advanced Charts Settings', 'fmp-advanced-charts'),
            __('FMP Charts', 'fmp-advanced-charts'),
            'manage_options',
            'fmp-advanced-charts',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // General Settings
        register_setting('fmp_general_settings', 'fmp_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));

        register_setting('fmp_general_settings', 'fmp_rate_limit_per_minute', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 300
        ));

        register_setting('fmp_general_settings', 'fmp_debug_mode', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ));

        // Display Settings
        register_setting('fmp_display_settings', 'fmp_default_chart_type', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'candlestick'
        ));

        register_setting('fmp_display_settings', 'fmp_default_timeframe', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1D'
        ));

        register_setting('fmp_display_settings', 'fmp_default_theme', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'dark'
        ));

        register_setting('fmp_display_settings', 'fmp_chart_height', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '600'
        ));

        register_setting('fmp_display_settings', 'fmp_chart_width', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '100%'
        ));

        register_setting('fmp_display_settings', 'fmp_enable_toolbar', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true
        ));

        register_setting('fmp_display_settings', 'fmp_enable_drawings', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true
        ));

        register_setting('fmp_display_settings', 'fmp_enable_indicators', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true
        ));

        // Cache Settings
        register_setting('fmp_cache_settings', 'fmp_cache_duration_historical', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 86400
        ));

        register_setting('fmp_cache_settings', 'fmp_cache_duration_profile', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 604800
        ));

        register_setting('fmp_cache_settings', 'fmp_cache_duration_quote', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 300
        ));
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors(); ?>

            <nav class="nav-tab-wrapper">
                <a href="?page=fmp-advanced-charts&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'fmp-advanced-charts'); ?>
                </a>
                <a href="?page=fmp-advanced-charts&tab=display" class="nav-tab <?php echo $current_tab === 'display' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Display', 'fmp-advanced-charts'); ?>
                </a>
                <a href="?page=fmp-advanced-charts&tab=cache" class="nav-tab <?php echo $current_tab === 'cache' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Cache', 'fmp-advanced-charts'); ?>
                </a>
                <a href="?page=fmp-advanced-charts&tab=stats" class="nav-tab <?php echo $current_tab === 'stats' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Statistics', 'fmp-advanced-charts'); ?>
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ($current_tab) {
                    case 'general':
                        $this->render_general_tab();
                        break;
                    case 'display':
                        $this->render_display_tab();
                        break;
                    case 'cache':
                        $this->render_cache_tab();
                        break;
                    case 'stats':
                        $this->render_stats_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render general settings tab.
     */
    private function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('fmp_general_settings');
            ?>

            <h2><?php _e('API Configuration', 'fmp-advanced-charts'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="fmp_api_key"><?php _e('API Key', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="fmp_api_key"
                               name="fmp_api_key"
                               value="<?php echo esc_attr(get_option('fmp_api_key')); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('Enter your Financial Modeling Prep API key. Get one at', 'fmp-advanced-charts'); ?>
                            <a href="https://financialmodelingprep.com/developer/docs/" target="_blank">financialmodelingprep.com</a>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_rate_limit_per_minute"><?php _e('Rate Limit (per minute)', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="number"
                               id="fmp_rate_limit_per_minute"
                               name="fmp_rate_limit_per_minute"
                               value="<?php echo esc_attr(get_option('fmp_rate_limit_per_minute', 300)); ?>"
                               class="small-text">
                        <p class="description">
                            <?php _e('Maximum API requests per minute (default: 300)', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_debug_mode"><?php _e('Debug Mode', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox"
                               id="fmp_debug_mode"
                               name="fmp_debug_mode"
                               value="1"
                               <?php checked(get_option('fmp_debug_mode'), true); ?>>
                        <label for="fmp_debug_mode"><?php _e('Enable debug logging', 'fmp-advanced-charts'); ?></label>
                        <p class="description">
                            <?php _e('Log API errors and debug information to error log', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>

        <h3><?php _e('API Connection Test', 'fmp-advanced-charts'); ?></h3>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="fmp_test_connection">
            <?php wp_nonce_field('fmp_test_connection', 'fmp_test_nonce'); ?>
            <?php submit_button(__('Test Connection', 'fmp-advanced-charts'), 'secondary'); ?>
        </form>
        <?php
    }

    /**
     * Render display settings tab.
     */
    private function render_display_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('fmp_display_settings');
            ?>

            <h2><?php _e('Default Chart Settings', 'fmp-advanced-charts'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="fmp_default_chart_type"><?php _e('Default Chart Type', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <select id="fmp_default_chart_type" name="fmp_default_chart_type">
                            <?php
                            $chart_types = array(
                                'candlestick' => __('Candlestick', 'fmp-advanced-charts'),
                                'line' => __('Line', 'fmp-advanced-charts'),
                                'area' => __('Area', 'fmp-advanced-charts'),
                                'bar' => __('Bar (OHLC)', 'fmp-advanced-charts'),
                                'heikin-ashi' => __('Heikin Ashi', 'fmp-advanced-charts'),
                                'hollow' => __('Hollow Candles', 'fmp-advanced-charts'),
                                'baseline' => __('Baseline', 'fmp-advanced-charts')
                            );
                            $current_type = get_option('fmp_default_chart_type', 'candlestick');
                            foreach ($chart_types as $value => $label) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr($value),
                                    selected($current_type, $value, false),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_default_timeframe"><?php _e('Default Timeframe', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <select id="fmp_default_timeframe" name="fmp_default_timeframe">
                            <?php
                            $timeframes = array(
                                '1min' => __('1 Minute', 'fmp-advanced-charts'),
                                '5min' => __('5 Minutes', 'fmp-advanced-charts'),
                                '15min' => __('15 Minutes', 'fmp-advanced-charts'),
                                '30min' => __('30 Minutes', 'fmp-advanced-charts'),
                                '1hour' => __('1 Hour', 'fmp-advanced-charts'),
                                '4hour' => __('4 Hours', 'fmp-advanced-charts'),
                                '1D' => __('Daily', 'fmp-advanced-charts'),
                                '1W' => __('Weekly', 'fmp-advanced-charts'),
                                '1M' => __('Monthly', 'fmp-advanced-charts')
                            );
                            $current_timeframe = get_option('fmp_default_timeframe', '1D');
                            foreach ($timeframes as $value => $label) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr($value),
                                    selected($current_timeframe, $value, false),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_default_theme"><?php _e('Default Theme', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <select id="fmp_default_theme" name="fmp_default_theme">
                            <option value="light" <?php selected(get_option('fmp_default_theme'), 'light'); ?>><?php _e('Light', 'fmp-advanced-charts'); ?></option>
                            <option value="dark" <?php selected(get_option('fmp_default_theme'), 'dark'); ?>><?php _e('Dark', 'fmp-advanced-charts'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_chart_height"><?php _e('Default Chart Height', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="fmp_chart_height"
                               name="fmp_chart_height"
                               value="<?php echo esc_attr(get_option('fmp_chart_height', '600')); ?>"
                               class="small-text">
                        <span>px</span>
                        <p class="description">
                            <?php _e('Default height in pixels', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_chart_width"><?php _e('Default Chart Width', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="fmp_chart_width"
                               name="fmp_chart_width"
                               value="<?php echo esc_attr(get_option('fmp_chart_width', '100%')); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php _e('Can be percentage (100%) or pixels (800px)', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Features', 'fmp-advanced-charts'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                       name="fmp_enable_toolbar"
                                       value="1"
                                       <?php checked(get_option('fmp_enable_toolbar'), true); ?>>
                                <?php _e('Enable toolbar', 'fmp-advanced-charts'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       name="fmp_enable_drawings"
                                       value="1"
                                       <?php checked(get_option('fmp_enable_drawings'), true); ?>>
                                <?php _e('Enable drawing tools', 'fmp-advanced-charts'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox"
                                       name="fmp_enable_indicators"
                                       value="1"
                                       <?php checked(get_option('fmp_enable_indicators'), true); ?>>
                                <?php _e('Enable technical indicators', 'fmp-advanced-charts'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Render cache settings tab.
     */
    private function render_cache_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('fmp_cache_settings');
            ?>

            <h2><?php _e('Cache Duration Settings', 'fmp-advanced-charts'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="fmp_cache_duration_historical"><?php _e('Historical Data Cache', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="number"
                               id="fmp_cache_duration_historical"
                               name="fmp_cache_duration_historical"
                               value="<?php echo esc_attr(get_option('fmp_cache_duration_historical', 86400)); ?>"
                               class="small-text">
                        <span><?php _e('seconds', 'fmp-advanced-charts'); ?></span>
                        <p class="description">
                            <?php _e('Default: 86400 (24 hours)', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_cache_duration_profile"><?php _e('Company Profile Cache', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="number"
                               id="fmp_cache_duration_profile"
                               name="fmp_cache_duration_profile"
                               value="<?php echo esc_attr(get_option('fmp_cache_duration_profile', 604800)); ?>"
                               class="small-text">
                        <span><?php _e('seconds', 'fmp-advanced-charts'); ?></span>
                        <p class="description">
                            <?php _e('Default: 604800 (7 days)', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="fmp_cache_duration_quote"><?php _e('Real-time Quote Cache', 'fmp-advanced-charts'); ?></label>
                    </th>
                    <td>
                        <input type="number"
                               id="fmp_cache_duration_quote"
                               name="fmp_cache_duration_quote"
                               value="<?php echo esc_attr(get_option('fmp_cache_duration_quote', 300)); ?>"
                               class="small-text">
                        <span><?php _e('seconds', 'fmp-advanced-charts'); ?></span>
                        <p class="description">
                            <?php _e('Default: 300 (5 minutes)', 'fmp-advanced-charts'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>

        <h3><?php _e('Cache Management', 'fmp-advanced-charts'); ?></h3>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="fmp_clear_cache">
            <?php wp_nonce_field('fmp_clear_cache', 'fmp_clear_cache_nonce'); ?>
            <p><?php _e('Clear all cached data to force fresh API calls.', 'fmp-advanced-charts'); ?></p>
            <?php submit_button(__('Clear Cache', 'fmp-advanced-charts'), 'secondary'); ?>
        </form>
        <?php
    }

    /**
     * Render statistics tab.
     */
    private function render_stats_tab() {
        $api_handler = new FMP_Advanced_Charts_API_Handler();
        $stats = $api_handler->get_usage_stats();

        ?>
        <h2><?php _e('Usage Statistics', 'fmp-advanced-charts'); ?></h2>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Metric', 'fmp-advanced-charts'); ?></th>
                    <th><?php _e('Value', 'fmp-advanced-charts'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Total Cached Items', 'fmp-advanced-charts'); ?></td>
                    <td><?php echo esc_html($stats['total_cached_items']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Expired Cached Items', 'fmp-advanced-charts'); ?></td>
                    <td><?php echo esc_html($stats['expired_cached_items']); ?></td>
                </tr>
                <tr>
                    <td><?php _e('API Requests (Current Minute)', 'fmp-advanced-charts'); ?></td>
                    <td><?php echo esc_html($stats['current_minute_requests']); ?> / <?php echo esc_html($stats['rate_limit']); ?></td>
                </tr>
            </tbody>
        </table>

        <h3><?php _e('Shortcode Usage Examples', 'fmp-advanced-charts'); ?></h3>
        <div class="fmp-code-examples">
            <h4><?php _e('Basic Chart', 'fmp-advanced-charts'); ?></h4>
            <code>[fmp_chart symbol="AAPL"]</code>

            <h4><?php _e('Customized Chart', 'fmp-advanced-charts'); ?></h4>
            <code>[fmp_chart symbol="AAPL" type="candlestick" timeframe="1D" height="600px" theme="dark" indicators="SMA,RSI"]</code>

            <h4><?php _e('Chart Grid', 'fmp-advanced-charts'); ?></h4>
            <code>[fmp_chart_grid symbols="AAPL,GOOGL,MSFT,TSLA" columns="2"]</code>
        </div>
        <?php
    }

    /**
     * Test API connection.
     */
    public function test_api_connection() {
        check_admin_referer('fmp_test_connection', 'fmp_test_nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'fmp-advanced-charts'));
        }

        $api_handler = new FMP_Advanced_Charts_API_Handler();
        $result = $api_handler->test_connection();

        if (is_wp_error($result)) {
            add_settings_error(
                'fmp_messages',
                'fmp_test_error',
                $result->get_error_message(),
                'error'
            );
        } else {
            add_settings_error(
                'fmp_messages',
                'fmp_test_success',
                __('API connection successful!', 'fmp-advanced-charts'),
                'success'
            );
        }

        set_transient('settings_errors', get_settings_errors(), 30);

        wp_redirect(admin_url('options-general.php?page=fmp-advanced-charts&tab=general'));
        exit;
    }

    /**
     * Clear cache.
     */
    public function clear_cache() {
        check_admin_referer('fmp_clear_cache', 'fmp_clear_cache_nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'fmp-advanced-charts'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fmp_chart_cache';
        $wpdb->query("TRUNCATE TABLE $table");

        add_settings_error(
            'fmp_messages',
            'fmp_cache_cleared',
            __('Cache cleared successfully!', 'fmp-advanced-charts'),
            'success'
        );

        set_transient('settings_errors', get_settings_errors(), 30);

        wp_redirect(admin_url('options-general.php?page=fmp-advanced-charts&tab=cache'));
        exit;
    }
}
