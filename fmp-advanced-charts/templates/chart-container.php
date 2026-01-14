<?php
/**
 * Chart container template.
 *
 * Available variables:
 * - $chart_id: Unique chart ID
 * - $atts: Chart attributes array
 * - $config: Chart configuration array
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="fmp-chart-wrapper <?php echo esc_attr($atts['class']); ?>"
     data-chart-id="<?php echo esc_attr($chart_id); ?>"
     data-theme="<?php echo esc_attr($config['theme']); ?>">

    <?php if ($config['toolbar']): ?>
    <div class="fmp-chart-toolbar">
        <div class="fmp-toolbar-section fmp-toolbar-left">
            <div class="fmp-symbol-display">
                <span class="fmp-symbol"><?php echo esc_html($config['symbol']); ?></span>
                <span class="fmp-company-name" data-symbol="<?php echo esc_attr($config['symbol']); ?>"></span>
            </div>
            <div class="fmp-price-info">
                <span class="fmp-current-price">--</span>
                <span class="fmp-price-change">--</span>
                <span class="fmp-price-change-percent">--</span>
            </div>
        </div>

        <div class="fmp-toolbar-section fmp-toolbar-center">
            <div class="fmp-timeframe-selector">
                <button class="fmp-tf-btn" data-timeframe="1min">1m</button>
                <button class="fmp-tf-btn" data-timeframe="5min">5m</button>
                <button class="fmp-tf-btn" data-timeframe="15min">15m</button>
                <button class="fmp-tf-btn" data-timeframe="30min">30m</button>
                <button class="fmp-tf-btn" data-timeframe="1hour">1H</button>
                <button class="fmp-tf-btn" data-timeframe="4hour">4H</button>
                <button class="fmp-tf-btn <?php echo $config['timeframe'] === '1D' ? 'active' : ''; ?>" data-timeframe="1D">D</button>
                <button class="fmp-tf-btn" data-timeframe="1W">W</button>
                <button class="fmp-tf-btn" data-timeframe="1M">M</button>
            </div>
        </div>

        <div class="fmp-toolbar-section fmp-toolbar-right">
            <div class="fmp-chart-type-selector">
                <button class="fmp-chart-type-btn" data-type="candlestick" title="<?php esc_attr_e('Candlestick', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><rect x="7" y="2" width="4" height="14" fill="currentColor"/></svg>
                </button>
                <button class="fmp-chart-type-btn" data-type="line" title="<?php esc_attr_e('Line', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><path d="M2 14 L8 8 L12 10 L16 4" stroke="currentColor" fill="none" stroke-width="2"/></svg>
                </button>
                <button class="fmp-chart-type-btn" data-type="area" title="<?php esc_attr_e('Area', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><path d="M2 14 L8 8 L12 10 L16 4 L16 16 L2 16 Z" fill="currentColor" opacity="0.5"/></svg>
                </button>
            </div>

            <div class="fmp-indicator-menu">
                <button class="fmp-toolbar-btn fmp-indicators-btn" title="<?php esc_attr_e('Indicators', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><circle cx="9" cy="9" r="7" stroke="currentColor" fill="none" stroke-width="2"/></svg>
                    <span><?php _e('Indicators', 'fmp-advanced-charts'); ?></span>
                </button>
            </div>

            <?php if ($config['drawings']): ?>
            <div class="fmp-drawing-tools">
                <button class="fmp-toolbar-btn fmp-drawings-btn" title="<?php esc_attr_e('Drawing Tools', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><path d="M2 16 L16 2" stroke="currentColor" stroke-width="2"/></svg>
                    <span><?php _e('Draw', 'fmp-advanced-charts'); ?></span>
                </button>
            </div>
            <?php endif; ?>

            <div class="fmp-chart-controls">
                <button class="fmp-toolbar-btn fmp-fullscreen-btn" title="<?php esc_attr_e('Fullscreen', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><path d="M2 2 L7 2 M2 2 L2 7 M16 2 L11 2 M16 2 L16 7 M2 16 L7 16 M2 16 L2 11 M16 16 L11 16 M16 16 L16 11" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                </button>
                <button class="fmp-toolbar-btn fmp-screenshot-btn" title="<?php esc_attr_e('Take Screenshot', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><rect x="2" y="4" width="14" height="10" stroke="currentColor" fill="none" stroke-width="2"/><circle cx="9" cy="9" r="2" fill="currentColor"/></svg>
                </button>
                <button class="fmp-toolbar-btn fmp-settings-btn" title="<?php esc_attr_e('Settings', 'fmp-advanced-charts'); ?>">
                    <svg width="18" height="18" viewBox="0 0 18 18"><circle cx="9" cy="9" r="2" fill="currentColor"/><path d="M9 1 L9 5 M9 13 L9 17 M1 9 L5 9 M13 9 L17 9" stroke="currentColor" stroke-width="2"/></svg>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="fmp-chart-container"
         id="<?php echo esc_attr($chart_id); ?>"
         style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;"
         data-symbol="<?php echo esc_attr($config['symbol']); ?>"
         data-type="<?php echo esc_attr($config['type']); ?>"
         data-timeframe="<?php echo esc_attr($config['timeframe']); ?>"
         data-theme="<?php echo esc_attr($config['theme']); ?>"
         data-indicators="<?php echo esc_attr(wp_json_encode($config['indicators'])); ?>"
         data-save-state="<?php echo $config['saveState'] ? 'true' : 'false'; ?>">
        <div class="fmp-chart-loading">
            <div class="fmp-spinner"></div>
            <p><?php _e('Loading chart data...', 'fmp-advanced-charts'); ?></p>
        </div>
    </div>

    <?php if ($config['toolbar']): ?>
    <div class="fmp-chart-footer">
        <div class="fmp-volume-info">
            <span class="fmp-volume-label"><?php _e('Volume:', 'fmp-advanced-charts'); ?></span>
            <span class="fmp-volume-value">--</span>
        </div>
        <div class="fmp-crosshair-info">
            <span class="fmp-ohlc-data"></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Indicator Panel (Hidden by default) -->
    <div class="fmp-indicator-panel" style="display: none;">
        <div class="fmp-panel-header">
            <h3><?php _e('Technical Indicators', 'fmp-advanced-charts'); ?></h3>
            <button class="fmp-panel-close">&times;</button>
        </div>
        <div class="fmp-panel-content">
            <div class="fmp-indicator-group">
                <h4><?php _e('Trend', 'fmp-advanced-charts'); ?></h4>
                <label><input type="checkbox" value="SMA" class="fmp-indicator-checkbox"> SMA - Simple Moving Average</label>
                <label><input type="checkbox" value="EMA" class="fmp-indicator-checkbox"> EMA - Exponential Moving Average</label>
                <label><input type="checkbox" value="WMA" class="fmp-indicator-checkbox"> WMA - Weighted Moving Average</label>
                <label><input type="checkbox" value="MACD" class="fmp-indicator-checkbox"> MACD</label>
                <label><input type="checkbox" value="BB" class="fmp-indicator-checkbox"> Bollinger Bands</label>
            </div>
            <div class="fmp-indicator-group">
                <h4><?php _e('Momentum', 'fmp-advanced-charts'); ?></h4>
                <label><input type="checkbox" value="RSI" class="fmp-indicator-checkbox"> RSI - Relative Strength Index</label>
                <label><input type="checkbox" value="STOCH" class="fmp-indicator-checkbox"> Stochastic Oscillator</label>
                <label><input type="checkbox" value="CCI" class="fmp-indicator-checkbox"> CCI - Commodity Channel Index</label>
            </div>
            <div class="fmp-indicator-group">
                <h4><?php _e('Volume', 'fmp-advanced-charts'); ?></h4>
                <label><input type="checkbox" value="VOL" class="fmp-indicator-checkbox" checked> Volume</label>
                <label><input type="checkbox" value="OBV" class="fmp-indicator-checkbox"> On-Balance Volume</label>
            </div>
        </div>
    </div>

    <!-- Drawing Tools Panel (Hidden by default) -->
    <?php if ($config['drawings']): ?>
    <div class="fmp-drawing-panel" style="display: none;">
        <div class="fmp-panel-header">
            <h3><?php _e('Drawing Tools', 'fmp-advanced-charts'); ?></h3>
            <button class="fmp-panel-close">&times;</button>
        </div>
        <div class="fmp-panel-content">
            <div class="fmp-drawing-group">
                <h4><?php _e('Lines', 'fmp-advanced-charts'); ?></h4>
                <button class="fmp-drawing-tool" data-tool="trendline"><?php _e('Trend Line', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="horizontal"><?php _e('Horizontal Line', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="vertical"><?php _e('Vertical Line', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="ray"><?php _e('Ray', 'fmp-advanced-charts'); ?></button>
            </div>
            <div class="fmp-drawing-group">
                <h4><?php _e('Fibonacci', 'fmp-advanced-charts'); ?></h4>
                <button class="fmp-drawing-tool" data-tool="fib-retracement"><?php _e('Retracement', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="fib-extension"><?php _e('Extension', 'fmp-advanced-charts'); ?></button>
            </div>
            <div class="fmp-drawing-group">
                <h4><?php _e('Shapes', 'fmp-advanced-charts'); ?></h4>
                <button class="fmp-drawing-tool" data-tool="rectangle"><?php _e('Rectangle', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="circle"><?php _e('Circle', 'fmp-advanced-charts'); ?></button>
                <button class="fmp-drawing-tool" data-tool="text"><?php _e('Text', 'fmp-advanced-charts'); ?></button>
            </div>
            <button class="fmp-clear-drawings"><?php _e('Clear All Drawings', 'fmp-advanced-charts'); ?></button>
        </div>
    </div>
    <?php endif; ?>
</div>
