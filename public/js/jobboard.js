/**
 * YAWIK
 *
 * License: MIT
 * (c) 2013 - 2017 CROSS Solution <http://cross-solution.de>
 */

/**
 *
 * Author: Mathias Gelhausen <gelhausen@cross-solution.de>
 */
;(function ($) {

    function onPagiantorLoaded()
    {
        $('.featured-image-box').matchHeight({
            byRow: true,
            property: 'height',
            target: null,
            remove: false
        });
    }

    $(function() {
        $('#jobs-list-container').on('yk-paginator-container:loaded.jobboard', onPagiantorLoaded);
        onPagiantorLoaded();
    });

})(jQuery); 
 
