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
  "use strict";

  return function(validator) {
    validator.addRule(
      'oandervalidate-length',
      function(value, params) {
        return $.mage.isEmpty(value) || value.length === params[1] * 1;
      },
      '{0}'
    );

    validator.addRule(
      'oandervalidate-regex',
      function(value, params) {
        var regexes = [],
            regexesAreValid = false;

        if ($.mage.isEmpty(value)) return true;

        for (var i = 1; i < params.length; i++) {
          var regex = new RegExp('(' + params[i]  + ')', 'gm');
          regexes.push(regex);
        }

        for (var i = 0; i < regexes.length; i++) {
          if (value.match(regexes[i]) !== null) {
            regexesAreValid = true;
            break;
          }
        }
        
        return regexesAreValid;
      },
      '{0}'
    );
    
    return validator;
  }
});
