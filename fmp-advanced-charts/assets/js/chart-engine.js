/**
 * FMP Advanced Charts - Chart Engine
 *
 * @package FMP_Advanced_Charts
 */

(function($) {
    'use strict';

    /**
     * Chart Manager Class
     */
    class FMPChartManager {
        constructor(containerId, config) {
            this.containerId = containerId;
            this.container = document.getElementById(containerId);
            this.config = config;
            this.chart = null;
            this.series = null;
            this.volumeSeries = null;
            this.indicators = [];
            this.data = [];
            this.currentTimeframe = config.timeframe;
            this.currentType = config.type;
            this.theme = config.theme;

            this.init();
        }

        /**
         * Initialize chart
         */
        init() {
            this.createChart();
            this.bindEvents();
            this.loadData();
        }

        /**
         * Create chart instance
         */
        createChart() {
            const isDark = this.theme === 'dark';

            const chartOptions = {
                width: this.container.offsetWidth,
                height: this.container.offsetHeight,
                layout: {
                    background: {
                        color: isDark ? '#1E222D' : '#FFFFFF'
                    },
                    textColor: isDark ? '#D9D9D9' : '#191919'
                },
                grid: {
                    vertLines: {
                        color: isDark ? '#2B2B43' : '#E1E3EB'
                    },
                    horzLines: {
                        color: isDark ? '#2B2B43' : '#E1E3EB'
                    }
                },
                crosshair: {
                    mode: LightweightCharts.CrosshairMode.Normal,
                    vertLine: {
                        color: isDark ? '#758696' : '#9598A1',
                        labelBackgroundColor: isDark ? '#363C4E' : '#F8F8F8'
                    },
                    horzLine: {
                        color: isDark ? '#758696' : '#9598A1',
                        labelBackgroundColor: isDark ? '#363C4E' : '#F8F8F8'
                    }
                },
                rightPriceScale: {
                    borderColor: isDark ? '#2B2B43' : '#E1E3EB'
                },
                timeScale: {
                    borderColor: isDark ? '#2B2B43' : '#E1E3EB',
                    timeVisible: true,
                    secondsVisible: false
                },
                handleScroll: {
                    mouseWheel: true,
                    pressedMouseMove: true,
                    horzTouchDrag: true,
                    vertTouchDrag: true
                },
                handleScale: {
                    axisPressedMouseMove: true,
                    mouseWheel: true,
                    pinch: true
                }
            };

            this.chart = LightweightCharts.createChart(this.container, chartOptions);
            this.createSeries();

            // Handle window resize
            window.addEventListener('resize', () => this.handleResize());
        }

        /**
         * Create chart series based on type
         */
        createSeries() {
            const seriesOptions = {
                upColor: '#26a69a',
                downColor: '#ef5350',
                borderVisible: false,
                wickUpColor: '#26a69a',
                wickDownColor: '#ef5350'
            };

            switch(this.currentType) {
                case 'candlestick':
                    this.series = this.chart.addCandlestickSeries(seriesOptions);
                    break;
                case 'bar':
                    this.series = this.chart.addBarSeries(seriesOptions);
                    break;
                case 'line':
                    this.series = this.chart.addLineSeries({
                        color: '#2962FF',
                        lineWidth: 2
                    });
                    break;
                case 'area':
                    this.series = this.chart.addAreaSeries({
                        topColor: 'rgba(41, 98, 255, 0.4)',
                        bottomColor: 'rgba(41, 98, 255, 0.0)',
                        lineColor: 'rgba(41, 98, 255, 1)',
                        lineWidth: 2
                    });
                    break;
                default:
                    this.series = this.chart.addCandlestickSeries(seriesOptions);
            }

            // Add crosshair move handler
            this.chart.subscribeCrosshairMove(param => this.handleCrosshairMove(param));
        }

        /**
         * Load chart data from API
         */
        loadData() {
            const symbol = this.config.symbol;
            const timeframe = this.currentTimeframe;

            // Show loading indicator
            this.showLoading();

            $.ajax({
                url: fmpChartsConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmp_get_chart_data',
                    nonce: fmpChartsConfig.nonce,
                    symbol: symbol,
                    timeframe: timeframe
                },
                success: (response) => {
                    this.hideLoading();

                    if (response.success && response.data) {
                        this.data = response.data;
                        this.updateChart();
                        this.loadQuote();
                    } else {
                        this.showError(response.data?.message || 'Failed to load chart data');
                    }
                },
                error: (xhr, status, error) => {
                    this.hideLoading();
                    this.showError('Network error: ' + error);
                }
            });
        }

        /**
         * Load real-time quote
         */
        loadQuote() {
            $.ajax({
                url: fmpChartsConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'fmp_get_quote',
                    nonce: fmpChartsConfig.nonce,
                    symbol: this.config.symbol
                },
                success: (response) => {
                    if (response.success && response.data) {
                        this.updateQuoteDisplay(response.data);
                    }
                }
            });
        }

        /**
         * Update chart with data
         */
        updateChart() {
            if (!this.data || this.data.length === 0) {
                this.showError('No data available');
                return;
            }

            // Convert data format for different chart types
            let chartData = this.data;

            if (this.currentType === 'line' || this.currentType === 'area') {
                chartData = this.data.map(item => ({
                    time: item.time,
                    value: item.close
                }));
            }

            this.series.setData(chartData);

            // Add volume series if not exists
            if (!this.volumeSeries && this.data[0].volume !== undefined) {
                this.createVolumeSeries();
            }

            // Fit content
            this.chart.timeScale().fitContent();

            // Apply indicators if configured
            if (this.config.indicators && this.config.indicators.length > 0) {
                this.applyIndicators(this.config.indicators);
            }
        }

        /**
         * Create volume series
         */
        createVolumeSeries() {
            this.volumeSeries = this.chart.addHistogramSeries({
                color: '#26a69a',
                priceFormat: {
                    type: 'volume',
                },
                priceScaleId: '',
                scaleMargins: {
                    top: 0.8,
                    bottom: 0,
                }
            });

            const volumeData = this.data.map(item => ({
                time: item.time,
                value: item.volume,
                color: item.close >= item.open ? 'rgba(38, 166, 154, 0.5)' : 'rgba(239, 83, 80, 0.5)'
            }));

            this.volumeSeries.setData(volumeData);
        }

        /**
         * Apply technical indicators
         */
        applyIndicators(indicators) {
            indicators.forEach(indicator => {
                switch(indicator) {
                    case 'SMA':
                        this.addSMA(20);
                        break;
                    case 'EMA':
                        this.addEMA(20);
                        break;
                    case 'RSI':
                        this.addRSI(14);
                        break;
                    case 'MACD':
                        this.addMACD();
                        break;
                    case 'BB':
                        this.addBollingerBands(20, 2);
                        break;
                }
            });
        }

        /**
         * Add Simple Moving Average
         */
        addSMA(period) {
            const smaData = this.calculateSMA(this.data, period);
            const smaSeries = this.chart.addLineSeries({
                color: '#2196F3',
                lineWidth: 2,
                title: `SMA(${period})`
            });
            smaSeries.setData(smaData);
            this.indicators.push({ type: 'SMA', period, series: smaSeries });
        }

        /**
         * Add Exponential Moving Average
         */
        addEMA(period) {
            const emaData = this.calculateEMA(this.data, period);
            const emaSeries = this.chart.addLineSeries({
                color: '#FF6D00',
                lineWidth: 2,
                title: `EMA(${period})`
            });
            emaSeries.setData(emaData);
            this.indicators.push({ type: 'EMA', period, series: emaSeries });
        }

        /**
         * Add Bollinger Bands
         */
        addBollingerBands(period, stdDev) {
            const bbData = this.calculateBollingerBands(this.data, period, stdDev);

            const upperSeries = this.chart.addLineSeries({
                color: '#9C27B0',
                lineWidth: 1,
                title: `BB Upper(${period})`
            });

            const middleSeries = this.chart.addLineSeries({
                color: '#9C27B0',
                lineWidth: 1,
                lineStyle: 2,
                title: `BB Middle(${period})`
            });

            const lowerSeries = this.chart.addLineSeries({
                color: '#9C27B0',
                lineWidth: 1,
                title: `BB Lower(${period})`
            });

            upperSeries.setData(bbData.upper);
            middleSeries.setData(bbData.middle);
            lowerSeries.setData(bbData.lower);

            this.indicators.push({
                type: 'BB',
                period,
                stdDev,
                series: [upperSeries, middleSeries, lowerSeries]
            });
        }

        /**
         * Calculate Simple Moving Average
         */
        calculateSMA(data, period) {
            const sma = [];
            for (let i = period - 1; i < data.length; i++) {
                let sum = 0;
                for (let j = 0; j < period; j++) {
                    sum += data[i - j].close;
                }
                sma.push({
                    time: data[i].time,
                    value: sum / period
                });
            }
            return sma;
        }

        /**
         * Calculate Exponential Moving Average
         */
        calculateEMA(data, period) {
            const ema = [];
            const multiplier = 2 / (period + 1);
            let emaValue = data[0].close;

            for (let i = 0; i < data.length; i++) {
                emaValue = (data[i].close - emaValue) * multiplier + emaValue;
                ema.push({
                    time: data[i].time,
                    value: emaValue
                });
            }
            return ema;
        }

        /**
         * Calculate Bollinger Bands
         */
        calculateBollingerBands(data, period, stdDev) {
            const sma = this.calculateSMA(data, period);
            const bands = { upper: [], middle: [], lower: [] };

            for (let i = 0; i < sma.length; i++) {
                const dataIndex = i + period - 1;
                let sum = 0;

                for (let j = 0; j < period; j++) {
                    sum += Math.pow(data[dataIndex - j].close - sma[i].value, 2);
                }

                const standardDeviation = Math.sqrt(sum / period);

                bands.middle.push(sma[i]);
                bands.upper.push({
                    time: sma[i].time,
                    value: sma[i].value + (stdDev * standardDeviation)
                });
                bands.lower.push({
                    time: sma[i].time,
                    value: sma[i].value - (stdDev * standardDeviation)
                });
            }

            return bands;
        }

        /**
         * Handle crosshair move
         */
        handleCrosshairMove(param) {
            if (!param.time || !param.point) {
                return;
            }

            const data = param.seriesData.get(this.series);
            if (data) {
                this.updateOHLC(data);
            }
        }

        /**
         * Update OHLC display
         */
        updateOHLC(data) {
            const wrapper = $(this.container).closest('.fmp-chart-wrapper');
            const ohlcElement = wrapper.find('.fmp-ohlc-data');

            if (data.open !== undefined) {
                const ohlcText = `O: ${data.open.toFixed(2)} H: ${data.high.toFixed(2)} L: ${data.low.toFixed(2)} C: ${data.close.toFixed(2)}`;
                ohlcElement.text(ohlcText);
            }
        }

        /**
         * Update quote display
         */
        updateQuoteDisplay(quote) {
            const wrapper = $(this.container).closest('.fmp-chart-wrapper');
            const priceElement = wrapper.find('.fmp-current-price');
            const changeElement = wrapper.find('.fmp-price-change');
            const changePercentElement = wrapper.find('.fmp-price-change-percent');
            const companyNameElement = wrapper.find('.fmp-company-name');

            if (quote.price) {
                priceElement.text('$' + parseFloat(quote.price).toFixed(2));
            }

            if (quote.change) {
                const change = parseFloat(quote.change);
                const changeClass = change >= 0 ? 'positive' : 'negative';
                changeElement.text((change >= 0 ? '+' : '') + change.toFixed(2))
                    .removeClass('positive negative')
                    .addClass(changeClass);
            }

            if (quote.changesPercentage) {
                const changePercent = parseFloat(quote.changesPercentage);
                const changeClass = changePercent >= 0 ? 'positive' : 'negative';
                changePercentElement.text('(' + (changePercent >= 0 ? '+' : '') + changePercent.toFixed(2) + '%)')
                    .removeClass('positive negative')
                    .addClass(changeClass);
            }

            if (quote.name) {
                companyNameElement.text(quote.name);
            }
        }

        /**
         * Change chart type
         */
        changeType(newType) {
            if (this.currentType === newType) return;

            // Remove current series
            this.chart.removeSeries(this.series);

            // Update type and create new series
            this.currentType = newType;
            this.createSeries();

            // Update chart with existing data
            this.updateChart();
        }

        /**
         * Change timeframe
         */
        changeTimeframe(newTimeframe) {
            if (this.currentTimeframe === newTimeframe) return;

            this.currentTimeframe = newTimeframe;
            this.loadData();
        }

        /**
         * Handle window resize
         */
        handleResize() {
            if (this.chart) {
                this.chart.applyOptions({
                    width: this.container.offsetWidth,
                    height: this.container.offsetHeight
                });
            }
        }

        /**
         * Bind UI events
         */
        bindEvents() {
            const wrapper = $(this.container).closest('.fmp-chart-wrapper');

            // Timeframe buttons
            wrapper.find('.fmp-tf-btn').on('click', (e) => {
                const btn = $(e.currentTarget);
                const timeframe = btn.data('timeframe');
                wrapper.find('.fmp-tf-btn').removeClass('active');
                btn.addClass('active');
                this.changeTimeframe(timeframe);
            });

            // Chart type buttons
            wrapper.find('.fmp-chart-type-btn').on('click', (e) => {
                const btn = $(e.currentTarget);
                const type = btn.data('type');
                wrapper.find('.fmp-chart-type-btn').removeClass('active');
                btn.addClass('active');
                this.changeType(type);
            });

            // Indicators button
            wrapper.find('.fmp-indicators-btn').on('click', () => {
                wrapper.find('.fmp-indicator-panel').toggle();
            });

            // Drawing tools button
            wrapper.find('.fmp-drawings-btn').on('click', () => {
                wrapper.find('.fmp-drawing-panel').toggle();
            });

            // Panel close buttons
            wrapper.find('.fmp-panel-close').on('click', (e) => {
                $(e.currentTarget).closest('.fmp-indicator-panel, .fmp-drawing-panel').hide();
            });

            // Fullscreen button
            wrapper.find('.fmp-fullscreen-btn').on('click', () => {
                this.toggleFullscreen();
            });

            // Screenshot button
            wrapper.find('.fmp-screenshot-btn').on('click', () => {
                this.takeScreenshot();
            });
        }

        /**
         * Toggle fullscreen mode
         */
        toggleFullscreen() {
            const wrapper = $(this.container).closest('.fmp-chart-wrapper')[0];

            if (!document.fullscreenElement) {
                wrapper.requestFullscreen().then(() => {
                    setTimeout(() => this.handleResize(), 100);
                });
            } else {
                document.exitFullscreen().then(() => {
                    setTimeout(() => this.handleResize(), 100);
                });
            }
        }

        /**
         * Take screenshot of chart
         */
        takeScreenshot() {
            // Implementation would use html2canvas or similar library
            alert('Screenshot functionality requires additional library. Coming soon!');
        }

        /**
         * Show loading indicator
         */
        showLoading() {
            $(this.container).find('.fmp-chart-loading').show();
        }

        /**
         * Hide loading indicator
         */
        hideLoading() {
            $(this.container).find('.fmp-chart-loading').hide();
        }

        /**
         * Show error message
         */
        showError(message) {
            $(this.container).html(`<div class="fmp-chart-error">${message}</div>`);
        }

        /**
         * Destroy chart instance
         */
        destroy() {
            if (this.chart) {
                this.chart.remove();
                this.chart = null;
            }
        }
    }

    /**
     * Initialize all charts on page load
     */
    $(document).ready(function() {
        $('.fmp-chart-container').each(function() {
            const containerId = $(this).attr('id');
            const config = {
                symbol: $(this).data('symbol'),
                type: $(this).data('type'),
                timeframe: $(this).data('timeframe'),
                theme: $(this).data('theme'),
                indicators: $(this).data('indicators') || [],
                saveState: $(this).data('save-state') || false
            };

            // Store chart instance
            $(this).data('chartInstance', new FMPChartManager(containerId, config));
        });
    });

    // Expose to global scope
    window.FMPChartManager = FMPChartManager;

})(jQuery);
