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
 *
 * @author  János Pinczés <janos.pinczes@oander.hu>
 * @author  László Krammer <laszlo.krammer@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

define([
  'ko',
], function (ko,) {
  'use strict';

  var mixin = {
    initialize: function () {
      var self = this;
      self.filled = ko.observable(false),
      this._super();
      var value = this.value();

      if (typeof value !== 'undefined') self.filled(value.length > 0);

      return this;
    },

    /**
     * Validates itself by it's validation rules using validator object.
     * If validation of a rule did not pass, writes it's message to
     * 'error' observable property.
     *
     * @returns {Object} Validate information.
     */
    validate: function() {
      var self = this,
          returnValue = this._super(),
          value = this.value();

      if (typeof value !== 'undefined') self.filled(returnValue.valid && value.length > 0);

      return returnValue;
    },

    /**
     * Extends 'additionalClasses' object.
     *
     * @returns {Abstract} Chainable.
     */
    _setClasses: function () {
      var self = this;
      this._super();

      _.extend(self.additionalClasses, {
        _filled: self.filled
      });

      return this;
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
