=== FMP Advanced Charts ===
Contributors: yourusername
Tags: charts, financial, stocks, trading, tradingview, candlestick, technical analysis
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional TradingView-style financial charts with advanced technical indicators, powered by Financial Modeling Prep API.

== Description ==

FMP Advanced Charts brings professional-grade financial charting capabilities to your WordPress site. Create interactive, beautiful charts with candlestick patterns, technical indicators, and drawing tools - just like TradingView!

= Key Features =

**Chart Types**
* Candlestick charts
* Line charts
* Area charts
* Bar charts (OHLC)
* Heikin Ashi
* Hollow candles
* Baseline charts

**Technical Indicators**
* Moving Averages (SMA, EMA, WMA)
* MACD (Moving Average Convergence Divergence)
* RSI (Relative Strength Index)
* Bollinger Bands
* Stochastic Oscillator
* CCI (Commodity Channel Index)
* ATR (Average True Range)
* On-Balance Volume (OBV)
* And many more!

**Drawing Tools**
* Trend lines
* Horizontal and vertical lines
* Fibonacci retracement
* Fibonacci extension
* Rectangles and shapes
* Text annotations
* Parallel channels

**Interactive Features**
* Zoom in/out with mouse wheel
* Pan left/right by dragging
* Crosshair with price/time info
* Real-time quote updates
* Fullscreen mode
* Screenshot capability
* Multiple timeframes (1min to Monthly)

**Performance & Reliability**
* Smart caching system
* Rate limiting management
* Retry logic for API calls
* Responsive design
* Mobile-friendly
* Light and dark themes

= API Integration =

This plugin requires a free API key from [Financial Modeling Prep](https://financialmodelingprep.com/). FMP provides comprehensive financial data for stocks, forex, crypto, and more.

= Usage =

After installing and activating the plugin:

1. Go to Settings > FMP Charts
2. Enter your Financial Modeling Prep API key
3. Customize your default settings
4. Add charts to any post or page using shortcodes

**Basic Shortcode:**
`[fmp_chart symbol="AAPL"]`

**Advanced Shortcode:**
`[fmp_chart symbol="AAPL" type="candlestick" timeframe="1D" height="600px" theme="dark" indicators="SMA,RSI,MACD"]`

**Chart Grid:**
`[fmp_chart_grid symbols="AAPL,GOOGL,MSFT,TSLA" columns="2"]`

= Shortcode Parameters =

* **symbol** (required): Stock ticker symbol (e.g., AAPL, GOOGL, TSLA)
* **type**: Chart type (candlestick, line, area, bar, heikin-ashi, hollow, baseline)
* **timeframe**: Time interval (1min, 5min, 15min, 30min, 1hour, 4hour, 1D, 1W, 1M)
* **height**: Chart height (e.g., 600px, 500px)
* **width**: Chart width (e.g., 100%, 800px)
* **theme**: Color theme (light, dark)
* **indicators**: Comma-separated list of indicators (SMA, EMA, RSI, MACD, BB)
* **toolbar**: Show/hide toolbar (true, false)
* **drawings**: Enable drawing tools (true, false)

= Developer Friendly =

* Clean, well-documented code
* WordPress coding standards
* Extensive hooks and filters
* Custom CSS support
* Modular architecture
* Translation ready

== Installation ==

1. Upload the `fmp-advanced-charts` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > FMP Charts
4. Enter your Financial Modeling Prep API key
5. Configure your preferences
6. Add charts using shortcodes

= Getting an API Key =

1. Visit [Financial Modeling Prep](https://financialmodelingprep.com/developer/docs/)
2. Sign up for a free account
3. Copy your API key from the dashboard
4. Paste it in Settings > FMP Charts

== Frequently Asked Questions ==

= Do I need an API key? =

Yes, you need a free API key from Financial Modeling Prep to use this plugin.

= Is the API free? =

Financial Modeling Prep offers a free tier with 250 requests per day. Paid plans are available for higher limits.

= What markets are supported? =

The plugin supports stocks, ETFs, forex, cryptocurrencies, and commodities - any symbol available through Financial Modeling Prep.

= Can I customize the chart colors? =

Yes! The plugin includes light and dark themes, and you can add custom CSS in the Advanced Settings tab.

= Does it work on mobile? =

Absolutely! The charts are fully responsive and include touch gestures for mobile devices.

= Can I display multiple charts? =

Yes, use the `[fmp_chart_grid]` shortcode to display multiple charts in a grid layout.

= How is the data cached? =

Chart data is cached in a custom database table. Cache durations are configurable in settings.

= Can I save my drawings? =

Drawing save functionality is included and uses browser localStorage.

= Is it compatible with page builders? =

Yes, the shortcodes work with all major page builders including Gutenberg, Elementor, and others.

= How do I get support? =

For support, please visit the [plugin support forum](https://github.com/faazz/mxraqsp) or contact us directly.

== Screenshots ==

1. Candlestick chart with indicators and toolbar
2. Settings page - General tab
3. Settings page - Display options
4. Chart grid with multiple symbols
5. Indicator panel with technical indicators
6. Drawing tools panel
7. Dark theme chart
8. Mobile responsive view

== Changelog ==

= 1.0.0 =
* Initial release
* Candlestick, line, area, and bar charts
* 20+ technical indicators
* Drawing tools
* Real-time quotes
* Smart caching
* Light and dark themes
* Responsive design
* Multiple timeframes
* Chart grid support

== Upgrade Notice ==

= 1.0.0 =
Initial release of FMP Advanced Charts.

== Credits ==

* Chart rendering powered by [Lightweight Charts](https://tradingview.github.io/lightweight-charts/)
* Financial data provided by [Financial Modeling Prep](https://financialmodelingprep.com/)

== Privacy Policy ==

This plugin makes API requests to Financial Modeling Prep (financialmodelingprep.com) to retrieve financial data. Please review their [privacy policy](https://financialmodelingprep.com/privacy-policy) for information on how they handle data.

The plugin stores chart data in your WordPress database for caching purposes. No personal user data is collected or transmitted.

== Support ==

For support, feature requests, or bug reports, please visit:
* GitHub: https://github.com/faazz/mxraqsp
* Documentation: Coming soon
* Email: support@example.com
