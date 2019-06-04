<?php

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemoveBlock implements ObserverInterface
{

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('breadcrumbs');

        if ($block) {
            $show = $this->_scopeConfig->getValue('catalog/frontend/show_breadcrumbs', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!$show) {
                $layout->unsetElement('breadcrumbs');
            }
        }
    }
}