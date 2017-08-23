/**
 * Oander_WarehouseManager
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

define(['jquery', 'uiComponent', 'oanderWarehouseManager'], function ($, Component, oanderWarehouseManager) {
  'use strict';
  var oanderWarehouseView = {

    init: function () {
      oanderWarehouseView.initConfigurableProductOnChange();
      oanderWarehouseView.initWarehouseListClickEvents();
    },

    initConfigurableProductOnChange: function () {
      var product_id = $('#product_addtocart_form').find('[name="product"]').val();

      $(document).on('click','.swatch-attribute', function() {
        $('.swatch-opt').children().each(function () {

          if (oanderWarehouseManager.productAttributes[product_id] == undefined) {
            oanderWarehouseManager.productAttributes[product_id] = [];
          }
          oanderWarehouseManager.productAttributes[product_id][$(this).attr('attribute-id')] = $(this).attr('option-selected');
        });
        oanderWarehouseManager.loadProductSumStockStatus(oanderWarehouseManager.productBlockTypeView,product_id);

        if ($('.warehouse-info').is(":visible")) {
          oanderWarehouseManager.loadWarehouseListBox(product_id);
        }
      });
    },

    initWarehouseListClickEvents: function () {
      $('.warehouse-open-btn').on('click', function () {
        var warehouseTop = $(this).offset().top;
        var product_id = $('#product_addtocart_form').find('[name="product"]').val();

        $(this).addClass('active');
        oanderWarehouseManager.loadWarehouseListBox(product_id);
        $('html,body').animate({ scrollTop: warehouseTop });
      });

      $('.warehouse-close-btn').on('click', function () {
        $('.warehouse-open-btn').removeClass('active');
        $('.warehouse-info').hide();
        $('html,body').animate({ scrollTop: '0' });
      });
    }

  };

  oanderWarehouseView.init();
  return oanderWarehouseView;

});
