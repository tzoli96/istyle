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

define([], function () {
  'use strict';
  return {

    /**
     * Initialize Cleave input formatter
     * @param {HTMLElement} e The form element (input, select, textarea)
     * @param {Object} additionalClasses The classes what the element has
     * @returns {Void}
     */
    cleaveInit: function(e, additionalClasses) {
      if (!e.classList.contains('cleave-executed')) {
        var hasNoAdditionalClasses = typeof additionalClasses === 'undefined' ;

        if (!additionalClasses) {
          additionalClasses = {}; 
          for (var i = 0; i < e.classList.length; i++) {
            additionalClasses[e.classList[i]] = true;
          }
        }
        
        var cleaveParams = this.getSettings(additionalClasses);
        this.removeClasses(additionalClasses);

        if (cleaveParams) {
          var cleave = new Cleave('#' + e.id,  cleaveParams);
          if (cleaveParams.hasOwnProperty('prefix') && cleaveParams.prefix !== '') {
            document.querySelector('#' + e.id).addEventListener('focusout', function(e) {
              
              function clearInput() {
                e.target.value = '';
                e.target.dispatchEvent(new Event('change'));
              }

              if (e.target.value === cleaveParams.prefix) {
                clearInput();
              }

              if (cleaveParams.hasOwnProperty('blocks')) {
                if (cleaveParams.hasOwnProperty('delimiter')) {
                  if (e.target.value === cleaveParams.prefix + cleaveParams.delimiter) {
                    clearInput();
                  }
                } else if (cleaveParams.hasOwnProperty('delimiters')) {
                  if (e.target.value === cleaveParams.prefix + cleaveParams.delimiters[0]) {
                    clearInput();
                  }
                } else {
                  if (e.target.value === cleaveParams.prefix + ' ') {
                    clearInput();
                  }
                }
              }
            }); 
          }
          
          if (hasNoAdditionalClasses) {
            var classesToRemove = [];

            for (var i = 0; i < e.classList.length; i++) {
              if (!additionalClasses.hasOwnProperty(e.classList[i])) {
                classesToRemove.push(e.classList[i]);
              }
            }

            for (var i = 0; i < classesToRemove.length; i++) {
              e.classList.remove(classesToRemove[i]);
            }
          }
        }
        e.classList.add('cleave-executed');
      }
    },

    /**
     * Get the settings of Cleave element
     * @param {Object} classes The classes what the element has
     * @returns {Object|Boolean} Returns the Cleave settings or false if no cleave classes
     */
    getSettings: function(classes) {
      if (classes.hasOwnProperty('cleave-pattern')) {
        var cleaveParams = {},
            delimiters = [];

        for (var key in classes) {
          if (Object.hasOwnProperty.call(classes, key)) {
            if (classes[key] === true && key.indexOf('cleave-pattern--') > -1) {
              var cleaveParam = key.split('--').pop().split('-').shift(),
                  cleaveParamValue = key.split('--').pop().split('-{').pop().slice(0, -1),
                  delimiterPosition = 0;

              if (cleaveParam === 'blocks') {
                if (cleaveParamValue === '0') cleaveParamValue = false;
                if (cleaveParamValue.length === 1) cleaveParamValue = [cleaveParamValue * 1];

                if (cleaveParamValue.length > 1) {
                  cleaveParamValue = cleaveParamValue.split(',');
                  for (var i = 0; i < cleaveParamValue.length; i++) {
                    cleaveParamValue[i] = cleaveParamValue[i] * 1;
                  }
                }
              }

              if (cleaveParam === 'delimiters') {
                delimiterPosition = key.split('--').pop().split('-{').pop().split('}-').pop() * 1 - 1;
                cleaveParamValue = key.split('--').pop().split('-{').pop().split('}-').shift().slice(1, -1);
                delimiters[delimiterPosition] = cleaveParamValue;
              }

              if (cleaveParam !== 'delimiters') {
                cleaveParams[cleaveParam] = cleaveParamValue;
              }
            }
          }
        }

        if (delimiters.length > 1) {
          cleaveParams.delimiters = delimiters;
        }

        return cleaveParams;
      } else {
        return false;
      }
    },

    /**
     * Remove cleave classes at the end
     * @param {Object} classes The classes what the element has
     * @returns {Void}
     */
    removeClasses: function(classes) {
      if (classes.hasOwnProperty('cleave-pattern')) {
        delete classes['cleave-pattern'];
        for (var key in classes) {
          if (Object.hasOwnProperty.call(classes, key)) {
            if (classes[key] === true && key.indexOf('cleave-pattern--') > -1) {
              delete classes[key];
            }
          }
        }
      }
    }
  }
});
