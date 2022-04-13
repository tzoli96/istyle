define([
], function () {
  'use strict';

  var mixin = {
    initObservable: function () {
      this._super();

      this.value.equalityComparer = function (a, b) {
        return (!a && !b) || (a == b);
      };

      return this;
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
