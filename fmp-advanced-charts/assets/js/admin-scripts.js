/**
 * FMP Advanced Charts - Admin Scripts
 *
 * @package FMP_Advanced_Charts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Admin initialization code can go here

        // Copy code examples on click
        $('.fmp-code-examples code').on('click', function() {
            const code = $(this).text();

            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(function() {
                    alert('Code copied to clipboard!');
                });
            }
        });
    });

})(jQuery);
