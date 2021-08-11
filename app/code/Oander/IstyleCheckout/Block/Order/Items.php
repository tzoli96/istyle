<?php


namespace Oander\IstyleCheckout\Block\Order;


class Items extends \Magento\Sales\Block\Order\Items
{
  public function getRendererTemplate()
  {
    return 'Oander_IstyleCheckout::checkout/success/order/items/renderer/default.phtml';
  }

  public function getOverriddenTemplates()
  {
    return [
      'default' => 'Oander_IstyleCheckout::checkout/success/order/items/renderer/default.phtml', //Orig: Magento_Sales::order/items/renderer/default.phtml
      'bundle' => 'Oander_IstyleCheckout::checkout/success/order/items/renderer/bundle.phtml', //Orig: Magento_Bundle::sales/order/items/renderer.phtml
      'downloadable' => 'Oander_IstyleCheckout::checkout/success/order/items/renderer/downloadable.phtml', //Orig: Magento_Downloadable::sales/order/items/renderer/downloadable.phtml
    ];
  }
}