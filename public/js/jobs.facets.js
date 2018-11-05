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

    $(function() {
        var $form = $('#jobs-list-filter');
        $(document).on('click', '.facet-checkbox', function () {
            var $checkbox = $(this),
                $form = $('#jobs-list-filter'),
                name = $checkbox.attr('name');
            $form.find('input[name="' + name + '"]').remove();
            if ($checkbox.prop('checked')) {
                $form.append('<input type="hidden" class="facet-param" name="' + name + '">');
            }
            $form.trigger('submit', {forceAjax: true});
        }).on('click', '.facet-active', function () {
            $('#jobs-list-filter').find('input[name="' + $(this).data('name') + '"]').remove()
                .end().trigger('submit', {forceAjax: true});
        }).on('click', '.facet-reset', function () {

            $form.find('.facet-param').remove()
                .end().trigger('submit', {forceAjax: true});
        });

        $form
            .on('reset.facets', function() { $form.find('.facet-param').remove();})
            .on('submit.facets', function(e, flags) {
                if (!flags || !flags.forceAjax) {
                    $form.find('.facet-param').remove();
                }
            })
        ;

    });

})(jQuery); 
 
