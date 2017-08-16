<?php

namespace Oander\IstyleBase\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {
        return [
            'checkout_info_box' => $this->_layout
                                        ->createBlock('Magento\Cms\Block\Block')
                                        ->setBlockId('checkout_info_box')
                                        ->toHtml()
        ];
    }
}