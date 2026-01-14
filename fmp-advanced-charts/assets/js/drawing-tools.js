/**
 * FMP Advanced Charts - Drawing Tools
 *
 * @package FMP_Advanced_Charts
 */

(function($) {
    'use strict';

    /**
     * Drawing Tools Manager
     */
    class FMPDrawingTools {
        constructor(chartInstance) {
            this.chart = chartInstance;
            this.drawings = [];
            this.activeTool = null;
            this.isDrawing = false;
            this.startPoint = null;
            this.currentDrawing = null;
        }

        /**
         * Set active drawing tool
         */
        setActiveTool(toolName) {
            this.activeTool = toolName;
            this.enableDrawingMode();
        }

        /**
         * Enable drawing mode
         */
        enableDrawingMode() {
            // Add drawing mode class to container
            $(this.chart.container).addClass('fmp-drawing-mode');

            // Bind mouse events for drawing
            this.bindDrawingEvents();
        }

        /**
         * Disable drawing mode
         */
        disableDrawingMode() {
            this.activeTool = null;
            $(this.chart.container).removeClass('fmp-drawing-mode');
            this.unbindDrawingEvents();
        }

        /**
         * Bind drawing events
         */
        bindDrawingEvents() {
            $(this.chart.container).on('mousedown.drawing', (e) => this.handleMouseDown(e));
            $(this.chart.container).on('mousemove.drawing', (e) => this.handleMouseMove(e));
            $(this.chart.container).on('mouseup.drawing', (e) => this.handleMouseUp(e));
        }

        /**
         * Unbind drawing events
         */
        unbindDrawingEvents() {
            $(this.chart.container).off('.drawing');
        }

        /**
         * Handle mouse down
         */
        handleMouseDown(e) {
            if (!this.activeTool) return;

            this.isDrawing = true;
            this.startPoint = this.getChartCoordinates(e);

            // Initialize current drawing based on tool
            this.initializeDrawing();
        }

        /**
         * Handle mouse move
         */
        handleMouseMove(e) {
            if (!this.isDrawing || !this.activeTool) return;

            const currentPoint = this.getChartCoordinates(e);
            this.updateCurrentDrawing(currentPoint);
        }

        /**
         * Handle mouse up
         */
        handleMouseUp(e) {
            if (!this.isDrawing || !this.activeTool) return;

            const endPoint = this.getChartCoordinates(e);
            this.finalizeDrawing(endPoint);

            this.isDrawing = false;
            this.startPoint = null;
            this.currentDrawing = null;
        }

        /**
         * Get chart coordinates from mouse event
         */
        getChartCoordinates(e) {
            const rect = this.chart.container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Convert to chart coordinates
            // This is a simplified version - actual implementation would use
            // LightweightCharts coordinate conversion methods
            return { x, y };
        }

        /**
         * Initialize drawing
         */
        initializeDrawing() {
            switch(this.activeTool) {
                case 'trendline':
                    this.currentDrawing = this.createTrendLine();
                    break;
                case 'horizontal':
                    this.currentDrawing = this.createHorizontalLine();
                    break;
                case 'vertical':
                    this.currentDrawing = this.createVerticalLine();
                    break;
                case 'ray':
                    this.currentDrawing = this.createRay();
                    break;
                case 'rectangle':
                    this.currentDrawing = this.createRectangle();
                    break;
                case 'circle':
                    this.currentDrawing = this.createCircle();
                    break;
                case 'fib-retracement':
                    this.currentDrawing = this.createFibonacciRetracement();
                    break;
                case 'fib-extension':
                    this.currentDrawing = this.createFibonacciExtension();
                    break;
                case 'text':
                    this.currentDrawing = this.createTextAnnotation();
                    break;
            }
        }

        /**
         * Update current drawing
         */
        updateCurrentDrawing(point) {
            if (!this.currentDrawing) return;

            // Update drawing based on type
            // This is a placeholder - actual implementation would update
            // the drawing series data
        }

        /**
         * Finalize drawing
         */
        finalizeDrawing(endPoint) {
            if (!this.currentDrawing) return;

            // Save drawing to array
            this.drawings.push({
                type: this.activeTool,
                data: this.currentDrawing,
                startPoint: this.startPoint,
                endPoint: endPoint
            });

            // Reset active tool
            this.disableDrawingMode();
        }

        /**
         * Create trend line
         */
        createTrendLine() {
            // Trend line implementation
            const lineSeries = this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 2,
                priceLineVisible: false,
                lastValueVisible: false
            });

            return lineSeries;
        }

        /**
         * Create horizontal line
         */
        createHorizontalLine() {
            // Horizontal line implementation
            const priceLine = {
                price: 0, // Will be set based on y-coordinate
                color: '#2962FF',
                lineWidth: 2,
                lineStyle: 0,
                axisLabelVisible: true,
                title: 'Horizontal Line'
            };

            return priceLine;
        }

        /**
         * Create vertical line
         */
        createVerticalLine() {
            // Vertical line implementation
            const lineSeries = this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 2,
                priceLineVisible: false
            });

            return lineSeries;
        }

        /**
         * Create ray
         */
        createRay() {
            // Ray implementation (line that extends infinitely in one direction)
            const lineSeries = this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 2,
                priceLineVisible: false
            });

            return lineSeries;
        }

        /**
         * Create rectangle
         */
        createRectangle() {
            // Rectangle implementation
            const series = [];

            // Top line
            series.push(this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 1
            }));

            // Bottom line
            series.push(this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 1
            }));

            // Left line
            series.push(this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 1
            }));

            // Right line
            series.push(this.chart.chart.addLineSeries({
                color: '#2962FF',
                lineWidth: 1
            }));

            return series;
        }

        /**
         * Create circle
         */
        createCircle() {
            // Circle implementation
            // Note: Lightweight Charts doesn't natively support circles
            // This would require custom rendering
            return null;
        }

        /**
         * Create Fibonacci retracement
         */
        createFibonacciRetracement() {
            const levels = [0, 0.236, 0.382, 0.5, 0.618, 0.786, 1];
            const series = [];

            levels.forEach(level => {
                series.push(this.chart.chart.addLineSeries({
                    color: level === 0 || level === 1 ? '#2962FF' : '#B0BEC5',
                    lineWidth: 1,
                    lineStyle: level === 0 || level === 1 ? 0 : 2,
                    title: `Fib ${(level * 100).toFixed(1)}%`
                }));
            });

            return series;
        }

        /**
         * Create Fibonacci extension
         */
        createFibonacciExtension() {
            const levels = [0, 0.618, 1, 1.272, 1.618, 2.618];
            const series = [];

            levels.forEach(level => {
                series.push(this.chart.chart.addLineSeries({
                    color: level === 0 || level === 1 ? '#2962FF' : '#4CAF50',
                    lineWidth: 1,
                    lineStyle: level === 0 || level === 1 ? 0 : 2,
                    title: `Fib Ext ${(level * 100).toFixed(1)}%`
                }));
            });

            return series;
        }

        /**
         * Create text annotation
         */
        createTextAnnotation() {
            // Text annotation implementation
            // This would require custom HTML overlay
            const annotation = {
                type: 'text',
                position: this.startPoint,
                text: prompt('Enter text:') || 'Text'
            };

            return annotation;
        }

        /**
         * Clear all drawings
         */
        clearAllDrawings() {
            this.drawings.forEach(drawing => {
                if (Array.isArray(drawing.data)) {
                    drawing.data.forEach(series => {
                        this.chart.chart.removeSeries(series);
                    });
                } else if (drawing.data && typeof drawing.data.remove === 'function') {
                    this.chart.chart.removeSeries(drawing.data);
                }
            });

            this.drawings = [];
        }

        /**
         * Remove specific drawing
         */
        removeDrawing(index) {
            if (index >= 0 && index < this.drawings.length) {
                const drawing = this.drawings[index];

                if (Array.isArray(drawing.data)) {
                    drawing.data.forEach(series => {
                        this.chart.chart.removeSeries(series);
                    });
                } else if (drawing.data) {
                    this.chart.chart.removeSeries(drawing.data);
                }

                this.drawings.splice(index, 1);
            }
        }

        /**
         * Save drawings to localStorage
         */
        saveDrawings() {
            const savedDrawings = this.drawings.map(drawing => ({
                type: drawing.type,
                startPoint: drawing.startPoint,
                endPoint: drawing.endPoint
            }));

            localStorage.setItem(
                `fmp_drawings_${this.chart.config.symbol}`,
                JSON.stringify(savedDrawings)
            );
        }

        /**
         * Load drawings from localStorage
         */
        loadDrawings() {
            const saved = localStorage.getItem(`fmp_drawings_${this.chart.config.symbol}`);

            if (saved) {
                try {
                    const drawings = JSON.parse(saved);
                    // Restore each drawing
                    // Implementation would recreate drawings based on saved data
                } catch (e) {
                    console.error('Failed to load drawings:', e);
                }
            }
        }
    }

    /**
     * Initialize drawing tools for charts
     */
    $(document).ready(function() {
        $('.fmp-chart-container').each(function() {
            const chartInstance = $(this).data('chartInstance');

            if (chartInstance) {
                const drawingTools = new FMPDrawingTools(chartInstance);
                $(this).data('drawingTools', drawingTools);

                // Bind drawing tool buttons
                const wrapper = $(this).closest('.fmp-chart-wrapper');

                wrapper.find('.fmp-drawing-tool').on('click', function() {
                    const tool = $(this).data('tool');
                    drawingTools.setActiveTool(tool);
                    wrapper.find('.fmp-drawing-panel').hide();
                });

                wrapper.find('.fmp-clear-drawings').on('click', function() {
                    if (confirm('Clear all drawings?')) {
                        drawingTools.clearAllDrawings();
                    }
                });
            }
        });
    });

    // Expose to global scope
    window.FMPDrawingTools = FMPDrawingTools;

})(jQuery);
