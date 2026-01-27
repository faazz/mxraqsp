<?php
/**
 * Chart renderer class.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chart renderer class.
 */
class FMP_Advanced_Charts_Chart_Renderer {

    /**
     * Render a single chart.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_chart($atts) {
        $defaults = array(
            'symbol' => '',
            'type' => get_option('fmp_default_chart_type', 'candlestick'),
            'timeframe' => get_option('fmp_default_timeframe', '1D'),
            'height' => get_option('fmp_chart_height', '600') . 'px',
            'width' => get_option('fmp_chart_width', '100%'),
            'theme' => get_option('fmp_default_theme', 'dark'),
            'indicators' => '',
            'toolbar' => get_option('fmp_enable_toolbar', true),
            'drawings' => get_option('fmp_enable_drawings', true),
            'save_state' => false,
            'class' => ''
        );

        $atts = shortcode_atts($defaults, $atts);

        // Validate symbol
        if (empty($atts['symbol'])) {
            return '<div class="fmp-chart-error">' . __('Error: Symbol is required', 'fmp-advanced-charts') . '</div>';
        }

        // Generate unique chart ID
        $chart_id = 'fmp-chart-' . uniqid();

        // Parse indicators
        $indicators_array = array();
        if (!empty($atts['indicators'])) {
            $indicators_array = array_map('trim', explode(',', $atts['indicators']));
        }

        // Prepare chart configuration
        $config = array(
            'symbol' => strtoupper($atts['symbol']),
            'type' => $atts['type'],
            'timeframe' => $atts['timeframe'],
            'theme' => $atts['theme'],
            'indicators' => $indicators_array,
            'toolbar' => filter_var($atts['toolbar'], FILTER_VALIDATE_BOOLEAN),
            'drawings' => filter_var($atts['drawings'], FILTER_VALIDATE_BOOLEAN),
            'saveState' => filter_var($atts['save_state'], FILTER_VALIDATE_BOOLEAN)
        );

        // Start output buffering
        ob_start();

        // Load template
        include FMP_ADVANCED_CHARTS_PLUGIN_DIR . 'templates/chart-container.php';

        return ob_get_clean();
    }

    /**
     * Render chart grid.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_chart_grid($atts) {
        $defaults = array(
            'symbols' => '',
            'columns' => '2',
            'type' => get_option('fmp_default_chart_type', 'candlestick'),
            'timeframe' => get_option('fmp_default_timeframe', '1D'),
            'height' => '400px',
            'theme' => get_option('fmp_default_theme', 'dark'),
            'sync_crosshair' => false
        );

        $atts = shortcode_atts($defaults, $atts);

        // Validate symbols
        if (empty($atts['symbols'])) {
            return '<div class="fmp-chart-error">' . __('Error: Symbols are required', 'fmp-advanced-charts') . '</div>';
        }

        // Parse symbols
        $symbols = array_map('trim', explode(',', $atts['symbols']));
        $symbols = array_filter($symbols);

        if (empty($symbols)) {
            return '<div class="fmp-chart-error">' . __('Error: No valid symbols provided', 'fmp-advanced-charts') . '</div>';
        }

        // Generate grid ID
        $grid_id = 'fmp-chart-grid-' . uniqid();

        // Build grid HTML
        $columns = absint($atts['columns']);
        $columns = max(1, min(4, $columns)); // Limit between 1 and 4 columns

        $output = '<div id="' . esc_attr($grid_id) . '" class="fmp-chart-grid fmp-grid-columns-' . $columns . '">';
        $output .= '<style>.fmp-grid-columns-' . $columns . ' .fmp-grid-item { width: calc(' . (100 / $columns) . '% - 10px); }</style>';

        foreach ($symbols as $symbol) {
            $chart_atts = array(
                'symbol' => $symbol,
                'type' => $atts['type'],
                'timeframe' => $atts['timeframe'],
                'height' => $atts['height'],
                'theme' => $atts['theme'],
                'toolbar' => false,
                'class' => 'fmp-grid-item'
            );

            $output .= '<div class="fmp-grid-item">';
            $output .= $this->render_chart($chart_atts);
            $output .= '</div>';
        }

        $output .= '</div>';

        // Add sync crosshair data attribute
        if (filter_var($atts['sync_crosshair'], FILTER_VALIDATE_BOOLEAN)) {
            $output = str_replace('<div id="' . esc_attr($grid_id) . '"', '<div id="' . esc_attr($grid_id) . '" data-sync-crosshair="true"', $output);
        }

        return $output;
    }

    /**
     * Get available chart types.
     *
     * @return array
     */
    public function get_chart_types() {
        return array(
            'candlestick' => __('Candlestick', 'fmp-advanced-charts'),
            'line' => __('Line', 'fmp-advanced-charts'),
            'area' => __('Area', 'fmp-advanced-charts'),
            'bar' => __('Bar (OHLC)', 'fmp-advanced-charts'),
            'heikin-ashi' => __('Heikin Ashi', 'fmp-advanced-charts'),
            'hollow' => __('Hollow Candles', 'fmp-advanced-charts'),
            'baseline' => __('Baseline', 'fmp-advanced-charts')
        );
    }

    /**
     * Get available timeframes.
     *
     * @return array
     */
    public function get_timeframes() {
        return array(
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
    }

    /**
     * Get available indicators.
     *
     * @return array
     */
    public function get_indicators() {
        return array(
            'trend' => array(
                'SMA' => __('Simple Moving Average', 'fmp-advanced-charts'),
                'EMA' => __('Exponential Moving Average', 'fmp-advanced-charts'),
                'WMA' => __('Weighted Moving Average', 'fmp-advanced-charts'),
                'MACD' => __('MACD', 'fmp-advanced-charts'),
                'BB' => __('Bollinger Bands', 'fmp-advanced-charts'),
                'ICHIMOKU' => __('Ichimoku Cloud', 'fmp-advanced-charts'),
                'SAR' => __('Parabolic SAR', 'fmp-advanced-charts')
            ),
            'momentum' => array(
                'RSI' => __('RSI', 'fmp-advanced-charts'),
                'STOCH' => __('Stochastic Oscillator', 'fmp-advanced-charts'),
                'CCI' => __('CCI', 'fmp-advanced-charts'),
                'WILLR' => __('Williams %R', 'fmp-advanced-charts'),
                'MOM' => __('Momentum', 'fmp-advanced-charts')
            ),
            'volume' => array(
                'VOL' => __('Volume', 'fmp-advanced-charts'),
                'VOLMA' => __('Volume MA', 'fmp-advanced-charts'),
                'OBV' => __('On-Balance Volume', 'fmp-advanced-charts')
            ),
            'volatility' => array(
                'ATR' => __('Average True Range', 'fmp-advanced-charts'),
                'STDDEV' => __('Standard Deviation', 'fmp-advanced-charts'),
                'KC' => __('Keltner Channels', 'fmp-advanced-charts')
            )
        );
    }
}
