<?php
/**
 * Shortcode handler class.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode class.
 */
class FMP_Advanced_Charts_Shortcode {

    /**
     * Chart renderer instance.
     */
    private $renderer;

    /**
     * Constructor.
     */
    public function __construct() {
        require_once FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'includes/class-chart-renderer.php';
        $this->renderer = new FMP_Advanced_Charts_Chart_Renderer();

        // Register shortcodes
        add_shortcode('fmp_chart', array($this, 'chart_shortcode'));
        add_shortcode('fmp_chart_grid', array($this, 'chart_grid_shortcode'));

        // Add Gutenberg block support (future enhancement)
        add_action('init', array($this, 'register_blocks'));
    }

    /**
     * Handle [fmp_chart] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string HTML output.
     */
    public function chart_shortcode($atts, $content = null) {
        // Check if API key is configured
        if (empty(get_option('fmp_api_key'))) {
            if (current_user_can('manage_options')) {
                return '<div class="fmp-chart-error">' .
                       __('FMP Advanced Charts: Please configure your API key in', 'fmp-advanced-charts') .
                       ' <a href="' . admin_url('options-general.php?page=fmp-advanced-charts') . '">' .
                       __('Settings', 'fmp-advanced-charts') . '</a></div>';
            }
            return '<div class="fmp-chart-error">' . __('Chart unavailable', 'fmp-advanced-charts') . '</div>';
        }

        return $this->renderer->render_chart($atts);
    }

    /**
     * Handle [fmp_chart_grid] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @param string $content Shortcode content.
     * @return string HTML output.
     */
    public function chart_grid_shortcode($atts, $content = null) {
        // Check if API key is configured
        if (empty(get_option('fmp_api_key'))) {
            if (current_user_can('manage_options')) {
                return '<div class="fmp-chart-error">' .
                       __('FMP Advanced Charts: Please configure your API key in', 'fmp-advanced-charts') .
                       ' <a href="' . admin_url('options-general.php?page=fmp-advanced-charts') . '">' .
                       __('Settings', 'fmp-advanced-charts') . '</a></div>';
            }
            return '<div class="fmp-chart-error">' . __('Charts unavailable', 'fmp-advanced-charts') . '</div>';
        }

        return $this->renderer->render_chart_grid($atts);
    }

    /**
     * Register Gutenberg blocks (future enhancement).
     */
    public function register_blocks() {
        // This is a placeholder for future Gutenberg block support
        // Will be implemented in a future version
    }
}
