/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander Address Fields Properties
 *
 * @author  János Pinczés <janos.pinczes@oander.hu>
 * @author  László Krammer <laszlo.krammer@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

define([
    'jquery'
], function($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'oandervalidate-length',
            function (v, elm) {
                var requiredLength = new RegExp(/^oandervalidate-length-[0-9]+$/),
                    validator = this,
                    result = true,
                    length = 0;
                    
                $.each(elm.className.split(' '), function (index, name) {
                    if (name.match(requiredLength) && result && !$.mage.isEmpty(v)) {
                        length = name.split('-')[2];
                        validator.attrLength = length;
                        result = (v.length == length);
                    }
                });
                
                return result;
            },
            function (v, elm) {
                return $(elm).attr('data-errormessage');
            }
        );

        $.validator.addMethod(
            'oandervalidate-regex',
            function (value, element) {
                var classes = element.classList,
                    counter = 0,
                    regexes = [],
                    regexesAreValid = false;

                for (var i = 0; i < classes.length; i++) {
                    if (classes[i].indexOf('oandervalidate-regex-') > -1) {
                        var rawRegex = classes[i].split('oandervalidate-regex-' + counter + '-')[1].replace(String.fromCharCode(160), ' '),
                            regex = new RegExp('(' + rawRegex  + ')', 'gm');

                        regexes.push(regex);
                        counter++;
                    }
                }

                for (var i = 0; i < regexes.length; i++) {
                    if (value.match(regexes[i]) !== null) {
                        regexesAreValid = true;
                        break;
                    }
                }

                return regexesAreValid;
            },
            function (v, elm) {
                return $(elm).attr('data-errormessage');
            }
        );
    }
});
