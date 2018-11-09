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
 *                            ,-~.
 *                          :  .o \
 *                          `.   _/`.
 *                            `.  `. `.
 *                              `.  ` .`.
 *                                `.  ``.`.
 *                        _._.-. -._`.  `.``.
 *                    _.'            .`.  `. `.
 *                 _.'            )     \   '
 *               .'             _.          "
 *             .'.-.'._     _.-'            "
 *           ;'       _'-.-'              "
 *          ; _._.-.-;  `.,,_;  ,..,,,.:"
 *         %-'      `._.-'   \_/   :;;
 *                           | |
 *                           : :
 *                           | |
 *                           { }
 *                            \|
 *                            ||
 *                            ||
 *                            ||
 *                          _ ;; _
 *                         "-' ` -"
 *
 * Oander_IstyleBase
 *
 * @author  Gabor Kuti <gabor.kuti@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */


/*global define*/
define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';
        return function(swatchRenderer) {
            return $.widget('mage.SwatchRenderer', swatchRenderer, {
                _UpdateConfigPrice: function () {
                    var $widget = this,
                        options = _.object(_.keys($widget.optionsMap), {});
                    var priceContainer = $('.price-container');
                    var productInfoMain = $('.product-info-right');
                    var priceHtml = priceContainer.find('.price');
                    var $product = $widget.element.parents($widget.options.selectorProduct),
                        $productPrice = $product.find(this.options.selectorProductPrice);

                    $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                        var attributeId = $(this).attr('attribute-id');

                        options[attributeId] = $(this).attr('option-selected');
                    });
                    _.each(options, function(value, key, obj){
                        if(!value)
                        {
                            delete options[key];
                        }
                    });

                    var tempproductids = _.where($widget.options.jsonConfig.index,options);
                    var displayprice;
                    var isBiggerFinalPrice = false;
                    var isBiggerOlderPrice = false;
                    var oldprices = [];

                    _.each(tempproductids,function(value, key, obj) {
                        var tempresult = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, value)];
                        if(!displayprice)
                            displayprice = tempresult;
                        if(parseFloat(tempresult["finalPrice"].amount) < parseFloat(displayprice["finalPrice"].amount))
                        {
                            isBiggerFinalPrice = true;
                            displayprice = tempresult;
                        }
                        else if(parseFloat(tempresult["finalPrice"].amount) == parseFloat(displayprice["finalPrice"].amount) && tempresult["oldPrice"].amount)
                        {
                            if(displayprice["oldPrice"].amount)
                            {
                                if(parseFloat(tempresult["oldPrice"].amount) < parseFloat(displayprice["oldPrice"].amount))
                                {
                                    displayprice = tempresult;
                                }
                            }
                            else
                            {
                                displayprice = tempresult;
                            }
                        }
                        else if((parseFloat(tempresult["finalPrice"].amount) - 1E-5) > parseFloat(displayprice["finalPrice"].amount))
                        {
                            isBiggerFinalPrice = true;
                        }
                        oldprices.push(parseFloat(tempresult["oldPrice"].amount));
                    });
                    if(displayprice["oldPrice"].amount)
                    {
                        if(Math.min.apply(this,oldprices) !== Math.max.apply(this,oldprices) && Math.min.apply(this,oldprices) == parseFloat(displayprice["oldPrice"].amount))
                        {
                            isBiggerOlderPrice = true;
                        }
                    }
                    $productPrice.trigger(
                        'updatePrice',
                        {
                            'prices': $widget._getPrices(displayprice, $productPrice.priceBox('option').prices)
                        }
                    );

                    try {
                        if (displayprice['oldPrice'].amount !== displayprice['finalPrice'].amount) {
                            $(this.options.slyOldPriceSelector).show();
                        } else {
                            $(this.options.slyOldPriceSelector).hide();
                        }
                    } catch (e) { }
                    if (parseFloat(displayprice['finalPrice'].amount) < parseFloat(displayprice['oldPrice'].amount)) {
                        productInfoMain.find('.price-box > .regular-price').removeClass('regular-price').addClass('special-price');
                    } else {
                        productInfoMain.find('.price-box > .special-price').removeClass('special-price').addClass('regular-price');
                    }

                    var finalpricespan = productInfoMain.find('span[data-price-type="finalPrice"]');
                    var oldpricespan = productInfoMain.find('span[data-price-type="oldPrice"]');
                    if ($(finalpricespan).find('span').length > 0 && isBiggerFinalPrice) {
                        $(finalpricespan).find('span').each(function (_, element) {
                            var priceHtmltoUpdate = $(element).html();

                            if ($(element).find('span').length === 0) {
                                $(element).html($.mage.__('Price from: <span>%1</span>').replace('%1', priceHtmltoUpdate));
                            }
                        });
                    }
                    if ($(oldpricespan).find('span').length > 0 && isBiggerOlderPrice) {
                        $(oldpricespan).find('span').each(function (_, element) {
                            var priceHtmltoUpdate = $(element).html();

                            if ($(element).find('span').length === 0) {
                                $(element).html($.mage.__('Price from: <span>%1</span>').replace('%1', priceHtmltoUpdate));
                            }
                        });
                    }

                }
            });
        };
    }
);