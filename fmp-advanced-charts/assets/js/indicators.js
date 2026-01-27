/**
 * FMP Advanced Charts - Technical Indicators
 *
 * @package FMP_Advanced_Charts
 */

(function($) {
    'use strict';

    /**
     * Technical Indicators Calculator
     */
    window.FMPIndicators = {

        /**
         * Calculate RSI (Relative Strength Index)
         */
        calculateRSI: function(data, period = 14) {
            const rsi = [];
            let gains = 0;
            let losses = 0;

            // Calculate initial average gain and loss
            for (let i = 1; i <= period; i++) {
                const change = data[i].close - data[i - 1].close;
                if (change >= 0) {
                    gains += change;
                } else {
                    losses -= change;
                }
            }

            let avgGain = gains / period;
            let avgLoss = losses / period;

            // Calculate RSI for remaining data
            for (let i = period; i < data.length; i++) {
                const change = data[i].close - data[i - 1].close;
                let currentGain = 0;
                let currentLoss = 0;

                if (change >= 0) {
                    currentGain = change;
                } else {
                    currentLoss = -change;
                }

                avgGain = (avgGain * (period - 1) + currentGain) / period;
                avgLoss = (avgLoss * (period - 1) + currentLoss) / period;

                const rs = avgGain / avgLoss;
                const rsiValue = 100 - (100 / (1 + rs));

                rsi.push({
                    time: data[i].time,
                    value: rsiValue
                });
            }

            return rsi;
        },

        /**
         * Calculate MACD (Moving Average Convergence Divergence)
         */
        calculateMACD: function(data, fastPeriod = 12, slowPeriod = 26, signalPeriod = 9) {
            const fastEMA = this.calculateEMA(data, fastPeriod);
            const slowEMA = this.calculateEMA(data, slowPeriod);

            const macdLine = [];
            const startIndex = slowPeriod - 1;

            for (let i = startIndex; i < data.length; i++) {
                const macdValue = fastEMA[i].value - slowEMA[i].value;
                macdLine.push({
                    time: data[i].time,
                    value: macdValue
                });
            }

            // Calculate signal line (EMA of MACD line)
            const signalLine = this.calculateEMAFromValues(macdLine, signalPeriod);

            // Calculate histogram
            const histogram = [];
            for (let i = signalPeriod - 1; i < macdLine.length; i++) {
                histogram.push({
                    time: macdLine[i].time,
                    value: macdLine[i].value - signalLine[i - (signalPeriod - 1)].value
                });
            }

            return {
                macd: macdLine,
                signal: signalLine,
                histogram: histogram
            };
        },

        /**
         * Calculate Stochastic Oscillator
         */
        calculateStochastic: function(data, kPeriod = 14, dPeriod = 3) {
            const stoch = [];

            for (let i = kPeriod - 1; i < data.length; i++) {
                let highestHigh = data[i].high;
                let lowestLow = data[i].low;

                for (let j = 0; j < kPeriod; j++) {
                    highestHigh = Math.max(highestHigh, data[i - j].high);
                    lowestLow = Math.min(lowestLow, data[i - j].low);
                }

                const kValue = ((data[i].close - lowestLow) / (highestHigh - lowestLow)) * 100;

                stoch.push({
                    time: data[i].time,
                    k: kValue
                });
            }

            // Calculate %D (SMA of %K)
            for (let i = dPeriod - 1; i < stoch.length; i++) {
                let sum = 0;
                for (let j = 0; j < dPeriod; j++) {
                    sum += stoch[i - j].k;
                }
                stoch[i].d = sum / dPeriod;
            }

            return stoch;
        },

        /**
         * Calculate CCI (Commodity Channel Index)
         */
        calculateCCI: function(data, period = 20) {
            const cci = [];
            const typicalPrices = data.map(item => ({
                time: item.time,
                value: (item.high + item.low + item.close) / 3
            }));

            for (let i = period - 1; i < data.length; i++) {
                let sum = 0;
                for (let j = 0; j < period; j++) {
                    sum += typicalPrices[i - j].value;
                }
                const sma = sum / period;

                let meanDeviation = 0;
                for (let j = 0; j < period; j++) {
                    meanDeviation += Math.abs(typicalPrices[i - j].value - sma);
                }
                meanDeviation /= period;

                const cciValue = (typicalPrices[i].value - sma) / (0.015 * meanDeviation);

                cci.push({
                    time: data[i].time,
                    value: cciValue
                });
            }

            return cci;
        },

        /**
         * Calculate ATR (Average True Range)
         */
        calculateATR: function(data, period = 14) {
            const tr = [];

            for (let i = 1; i < data.length; i++) {
                const high = data[i].high;
                const low = data[i].low;
                const prevClose = data[i - 1].close;

                const trueRange = Math.max(
                    high - low,
                    Math.abs(high - prevClose),
                    Math.abs(low - prevClose)
                );

                tr.push({
                    time: data[i].time,
                    value: trueRange
                });
            }

            // Calculate ATR as EMA of TR
            const atr = [];
            let atrValue = tr[0].value;

            for (let i = 0; i < tr.length; i++) {
                atrValue = (tr[i].value - atrValue) * (2 / (period + 1)) + atrValue;
                atr.push({
                    time: tr[i].time,
                    value: atrValue
                });
            }

            return atr;
        },

        /**
         * Calculate OBV (On-Balance Volume)
         */
        calculateOBV: function(data) {
            const obv = [];
            let obvValue = 0;

            for (let i = 1; i < data.length; i++) {
                if (data[i].close > data[i - 1].close) {
                    obvValue += data[i].volume;
                } else if (data[i].close < data[i - 1].close) {
                    obvValue -= data[i].volume;
                }

                obv.push({
                    time: data[i].time,
                    value: obvValue
                });
            }

            return obv;
        },

        /**
         * Calculate Williams %R
         */
        calculateWilliamsR: function(data, period = 14) {
            const willR = [];

            for (let i = period - 1; i < data.length; i++) {
                let highestHigh = data[i].high;
                let lowestLow = data[i].low;

                for (let j = 0; j < period; j++) {
                    highestHigh = Math.max(highestHigh, data[i - j].high);
                    lowestLow = Math.min(lowestLow, data[i - j].low);
                }

                const wrValue = ((highestHigh - data[i].close) / (highestHigh - lowestLow)) * -100;

                willR.push({
                    time: data[i].time,
                    value: wrValue
                });
            }

            return willR;
        },

        /**
         * Calculate Momentum
         */
        calculateMomentum: function(data, period = 10) {
            const momentum = [];

            for (let i = period; i < data.length; i++) {
                const momValue = data[i].close - data[i - period].close;

                momentum.push({
                    time: data[i].time,
                    value: momValue
                });
            }

            return momentum;
        },

        /**
         * Calculate EMA (helper function)
         */
        calculateEMA: function(data, period) {
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
        },

        /**
         * Calculate EMA from values (helper function)
         */
        calculateEMAFromValues: function(data, period) {
            const ema = [];
            const multiplier = 2 / (period + 1);
            let emaValue = data[0].value;

            for (let i = 0; i < data.length; i++) {
                emaValue = (data[i].value - emaValue) * multiplier + emaValue;
                ema.push({
                    time: data[i].time,
                    value: emaValue
                });
            }

            return ema;
        },

        /**
         * Calculate Standard Deviation
         */
        calculateStandardDeviation: function(data, period = 20) {
            const stdDev = [];

            for (let i = period - 1; i < data.length; i++) {
                let sum = 0;
                for (let j = 0; j < period; j++) {
                    sum += data[i - j].close;
                }
                const mean = sum / period;

                let variance = 0;
                for (let j = 0; j < period; j++) {
                    variance += Math.pow(data[i - j].close - mean, 2);
                }
                variance /= period;

                stdDev.push({
                    time: data[i].time,
                    value: Math.sqrt(variance)
                });
            }

            return stdDev;
        }
    };

})(jQuery);
