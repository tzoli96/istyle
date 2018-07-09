## Old Price display

27116 issue

Needed to add new price attribute for display on product page and product list pages, attribute name is **old_price**

#### Modifications:
 * Pricing\OldPrice - this is the price model
 * view/base/templates/product/price/old_price.phtml - overriden template
 * view/base/templates/configurable_product/price/final_price.phtml - overridden template
 * see catalog_product_prices.xml layout xml for modified renderers
 * Magento\ConfigurableProduct\Block\Product\View\Type\Configurable - overridden 
 * updated Discount Badge module for version 1.0.9.1
 * DiscountBadge\Model\DiscountCalculator - for old price handling
 