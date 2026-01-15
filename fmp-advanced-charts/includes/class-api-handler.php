<?php
/**
 * API Handler for Financial Modeling Prep integration.
 *
 * @package FMP_Advanced_Charts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FMP API Handler class.
 */
class FMP_Advanced_Charts_API_Handler {

    /**
     * API base URL.
     */
    private $api_base_url = 'https://financialmodelingprep.com/api/v3/';

    /**
     * API key.
     */
    private $api_key;

    /**
     * Rate limit tracking.
     */
    private $rate_limit_option = 'fmp_api_rate_limit';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->api_key = get_option('fmp_api_key', '');
    }

    /**
     * Check if API key is configured.
     *
     * @return bool
     */
    public function has_api_key() {
        return !empty($this->api_key);
    }

    /**
     * Test API connection.
     *
     * @return array|WP_Error
     */
    public function test_connection() {
        if (!$this->has_api_key()) {
            return new WP_Error('no_api_key', __('API key not configured', 'fmp-advanced-charts'));
        }

        $response = $this->make_request('quote/AAPL');

        if (is_wp_error($response)) {
            return $response;
        }

        return array(
            'success' => true,
            'message' => __('API connection successful', 'fmp-advanced-charts')
        );
    }

    /**
     * Get chart data for a symbol.
     *
     * @param string $symbol Stock ticker symbol.
     * @param string $timeframe Timeframe (1min, 5min, 15min, 30min, 1hour, 4hour, 1D, 1W, 1M).
     * @param string $from Start date (YYYY-MM-DD).
     * @param string $to End date (YYYY-MM-DD).
     * @return array|WP_Error
     */
    public function get_chart_data($symbol, $timeframe = '1D', $from = '', $to = '') {
        $symbol = strtoupper(sanitize_text_field($symbol));

        // Check cache first
        $cache_key = $this->get_cache_key('chart', $symbol, $timeframe, $from, $to);
        $cached_data = $this->get_from_cache($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        // Determine endpoint based on timeframe
        $endpoint = $this->get_chart_endpoint($symbol, $timeframe, $from, $to);

        $response = $this->make_request($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        // Process and format data
        $formatted_data = $this->format_chart_data($response, $timeframe);

        // Cache the data
        $cache_duration = get_option('fmp_cache_duration_historical', 86400);
        $this->save_to_cache($cache_key, $formatted_data, $cache_duration);

        return $formatted_data;
    }

    /**
     * Get chart endpoint based on timeframe.
     *
     * @param string $symbol
     * @param string $timeframe
     * @param string $from
     * @param string $to
     * @return string
     */
    private function get_chart_endpoint($symbol, $timeframe, $from = '', $to = '') {
        $intraday_timeframes = array('1min', '5min', '15min', '30min', '1hour', '4hour');

        if (in_array($timeframe, $intraday_timeframes)) {
            // Intraday data - limit to current/previous trading day
            $endpoint = "historical-chart/{$timeframe}/{$symbol}";

            // If no dates specified, use current/previous day
            if (empty($from) || empty($to)) {
                $now = new DateTime('now', new DateTimeZone('America/New_York'));
                $current_hour = (int)$now->format('H');

                // If before 9:30 AM ET or on weekend, use previous trading day
                if ($current_hour < 9 || $now->format('N') >= 6) {
                    // Get previous trading day
                    $to_date = clone $now;
                    if ($now->format('N') == 6) {
                        // Saturday - go back to Friday
                        $to_date->modify('-1 day');
                    } elseif ($now->format('N') == 7) {
                        // Sunday - go back to Friday
                        $to_date->modify('-2 days');
                    } elseif ($current_hour < 9) {
                        // Before market open - use previous day
                        $to_date->modify('-1 day');
                        // Check if that was weekend
                        if ($to_date->format('N') >= 6) {
                            $to_date->modify('last Friday');
                        }
                    }
                    $to = $to_date->format('Y-m-d');
                } else {
                    // Use today
                    $to = $now->format('Y-m-d');
                }

                $from = $to; // Same day for intraday
            }

            $endpoint .= "?from={$from}&to={$to}";
        } else {
            // Daily or longer timeframes
            $endpoint = "historical-price-full/{$symbol}";
            if ($from && $to) {
                $endpoint .= "?from={$from}&to={$to}";
            }
        }

        return $endpoint;
    }

    /**
     * Format chart data for frontend consumption.
     *
     * @param array $data Raw API data.
     * @param string $timeframe
     * @return array
     */
    private function format_chart_data($data, $timeframe) {
        $formatted = array();

        // Handle different response formats
        if (isset($data['historical'])) {
            $historical = $data['historical'];
        } elseif (is_array($data) && isset($data[0])) {
            $historical = $data;
        } else {
            return array();
        }

        // Reverse array if needed (FMP returns newest first)
        $historical = array_reverse($historical);

        foreach ($historical as $bar) {
            $formatted[] = array(
                'time' => isset($bar['date']) ? strtotime($bar['date']) : time(),
                'open' => floatval($bar['open'] ?? 0),
                'high' => floatval($bar['high'] ?? 0),
                'low' => floatval($bar['low'] ?? 0),
                'close' => floatval($bar['close'] ?? 0),
                'volume' => intval($bar['volume'] ?? 0)
            );
        }

        return $formatted;
    }

    /**
     * Get real-time quote for a symbol.
     *
     * @param string $symbol Stock ticker symbol.
     * @return array|WP_Error
     */
    public function get_real_time_quote($symbol) {
        $symbol = strtoupper(sanitize_text_field($symbol));

        // Check cache first
        $cache_key = $this->get_cache_key('quote', $symbol);
        $cached_data = $this->get_from_cache($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $endpoint = "quote/{$symbol}";
        $response = $this->make_request($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        $quote_data = isset($response[0]) ? $response[0] : array();

        // Cache for shorter duration
        $cache_duration = get_option('fmp_cache_duration_quote', 300);
        $this->save_to_cache($cache_key, $quote_data, $cache_duration);

        return $quote_data;
    }

    /**
     * Get company profile.
     *
     * @param string $symbol Stock ticker symbol.
     * @return array|WP_Error
     */
    public function get_company_profile($symbol) {
        $symbol = strtoupper(sanitize_text_field($symbol));

        // Check cache first
        $cache_key = $this->get_cache_key('profile', $symbol);
        $cached_data = $this->get_from_cache($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $endpoint = "profile/{$symbol}";
        $response = $this->make_request($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        $profile_data = isset($response[0]) ? $response[0] : array();

        // Cache for longer duration
        $cache_duration = get_option('fmp_cache_duration_profile', 604800);
        $this->save_to_cache($cache_key, $profile_data, $cache_duration);

        return $profile_data;
    }

    /**
     * Search for symbols.
     *
     * @param string $query Search query.
     * @param int $limit Maximum results.
     * @return array|WP_Error
     */
    public function search_symbols($query, $limit = 10) {
        $query = sanitize_text_field($query);

        // Check cache first
        $cache_key = $this->get_cache_key('search', $query);
        $cached_data = $this->get_from_cache($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $endpoint = "search?query={$query}&limit={$limit}";
        $response = $this->make_request($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        // Cache search results for 1 hour
        $this->save_to_cache($cache_key, $response, 3600);

        return $response;
    }

    /**
     * Get available stock list.
     *
     * @return array|WP_Error
     */
    public function get_stock_list() {
        // Check cache first
        $cache_key = $this->get_cache_key('stock_list');
        $cached_data = $this->get_from_cache($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $endpoint = 'stock/list';
        $response = $this->make_request($endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        // Cache for 24 hours
        $this->save_to_cache($cache_key, $response, 86400);

        return $response;
    }

    /**
     * Make API request with rate limiting and error handling.
     *
     * @param string $endpoint API endpoint.
     * @param array $args Additional arguments.
     * @return array|WP_Error
     */
    private function make_request($endpoint, $args = array()) {
        if (!$this->has_api_key()) {
            return new WP_Error('no_api_key', __('API key not configured', 'fmp-advanced-charts'));
        }

        // Check rate limit
        if (!$this->check_rate_limit()) {
            return new WP_Error('rate_limit', __('API rate limit exceeded. Please try again later.', 'fmp-advanced-charts'));
        }

        // Build URL
        $separator = (strpos($endpoint, '?') !== false) ? '&' : '?';
        $url = $this->api_base_url . $endpoint . $separator . 'apikey=' . $this->api_key;

        // Make request with retry logic
        $max_retries = 3;
        $retry_count = 0;
        $response = null;

        while ($retry_count < $max_retries) {
            $response = wp_remote_get($url, array(
                'timeout' => 15,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            ));

            if (!is_wp_error($response)) {
                break;
            }

            $retry_count++;
            if ($retry_count < $max_retries) {
                sleep(pow(2, $retry_count)); // Exponential backoff
            }
        }

        if (is_wp_error($response)) {
            $this->log_error('API Request Failed', $response->get_error_message());
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code !== 200) {
            $error_message = sprintf(__('API returned error code: %d', 'fmp-advanced-charts'), $response_code);
            $this->log_error('API Error', $error_message);
            return new WP_Error('api_error', $error_message);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_error('JSON Parse Error', json_last_error_msg());
            return new WP_Error('json_error', __('Failed to parse API response', 'fmp-advanced-charts'));
        }

        // Check for API error messages
        if (isset($data['Error Message'])) {
            return new WP_Error('api_error', $data['Error Message']);
        }

        // Increment rate limit counter
        $this->increment_rate_limit();

        return $data;
    }

    /**
     * Check rate limit.
     *
     * @return bool
     */
    private function check_rate_limit() {
        $rate_data = get_transient($this->rate_limit_option);

        if ($rate_data === false) {
            return true;
        }

        $max_requests = get_option('fmp_rate_limit_per_minute', 300);

        return $rate_data['count'] < $max_requests;
    }

    /**
     * Increment rate limit counter.
     */
    private function increment_rate_limit() {
        $rate_data = get_transient($this->rate_limit_option);

        if ($rate_data === false) {
            $rate_data = array('count' => 0, 'reset_time' => time() + 60);
        }

        $rate_data['count']++;

        set_transient($this->rate_limit_option, $rate_data, 60);
    }

    /**
     * Generate cache key.
     *
     * @param string $type Cache type.
     * @param mixed ...$params Additional parameters.
     * @return string
     */
    private function get_cache_key($type, ...$params) {
        return 'fmp_' . $type . '_' . md5(serialize($params));
    }

    /**
     * Get data from cache.
     *
     * @param string $cache_key Cache key.
     * @return mixed|false
     */
    private function get_from_cache($cache_key) {
        global $wpdb;
        $table = $wpdb->prefix . 'fmp_chart_cache';

        $cached = $wpdb->get_row($wpdb->prepare(
            "SELECT data, expires_at FROM $table WHERE cache_key = %s AND expires_at > NOW()",
            $cache_key
        ));

        if ($cached) {
            return json_decode($cached->data, true);
        }

        return false;
    }

    /**
     * Save data to cache.
     *
     * @param string $cache_key Cache key.
     * @param mixed $data Data to cache.
     * @param int $duration Cache duration in seconds.
     */
    private function save_to_cache($cache_key, $data, $duration) {
        global $wpdb;
        $table = $wpdb->prefix . 'fmp_chart_cache';

        $parts = explode('_', $cache_key);
        $type = $parts[1] ?? 'unknown';
        $symbol = '';
        $timeframe = '';

        // Extract symbol and timeframe if available
        if ($type === 'chart' || $type === 'quote' || $type === 'profile') {
            $symbol = isset($data[0]['symbol']) ? $data[0]['symbol'] : '';
        }

        $wpdb->replace($table, array(
            'cache_key' => $cache_key,
            'symbol' => $symbol,
            'timeframe' => $timeframe,
            'data_type' => $type,
            'data' => wp_json_encode($data),
            'created_at' => current_time('mysql'),
            'expires_at' => date('Y-m-d H:i:s', time() + $duration)
        ), array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));
    }

    /**
     * Clean expired cache entries.
     */
    public function cleanup_cache() {
        global $wpdb;
        $table = $wpdb->prefix . 'fmp_chart_cache';

        $wpdb->query("DELETE FROM $table WHERE expires_at < NOW()");
    }

    /**
     * Log error for debugging.
     *
     * @param string $title Error title.
     * @param string $message Error message.
     */
    private function log_error($title, $message) {
        if (get_option('fmp_debug_mode', false)) {
            error_log(sprintf('[FMP Advanced Charts] %s: %s', $title, $message));
        }
    }

    /**
     * Get API usage statistics.
     *
     * @return array
     */
    public function get_usage_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'fmp_chart_cache';

        $total_cached = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $expired_cached = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE expires_at < NOW()");

        $rate_data = get_transient($this->rate_limit_option);
        $current_requests = $rate_data ? $rate_data['count'] : 0;

        return array(
            'total_cached_items' => intval($total_cached),
            'expired_cached_items' => intval($expired_cached),
            'current_minute_requests' => $current_requests,
            'rate_limit' => get_option('fmp_rate_limit_per_minute', 300)
        );
    }
}
