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
  'Magento_Ui/js/lib/view/utils/dom-observer',
  'Oander_AddressFieldsProperties/js/cleaveHelper'
], function (domObserver, cleaveHelper) {
  'use strict';

  var mixin = {
    initialize: function () {
      var self = this;
      this._super();

      if (self.uid !== '') {
        var additionalClassesClone = JSON.parse(JSON.stringify(self.additionalClasses));
        cleaveHelper.removeClasses(self.additionalClasses);
        domObserver.get('#' + self.uid, function (e) {
          cleaveHelper.cleaveInit(e, additionalClassesClone);
        });
      }

      return this;
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
