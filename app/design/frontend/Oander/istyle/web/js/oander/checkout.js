/**
 * @author Zoltan Belicza
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.08.23
 */

define([
    'jquery'
], function ($) {
    'use strict';

    function elementLoaded(el, cb) {
        if ($(el).length) {
            cb($(el));
        } else {
            setTimeout(function() {
                elementLoaded(el, cb)
            }, 500);
        }
    }

    elementLoaded('.field.street .control .form-control', function(el) {
        var form_elem = document.querySelector('.field.street .control .form-control'),
            label = document.querySelector('.field legend span').innerHTML;
        form_elem.setAttribute('placeholder', label);
    });

});
