<?php

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Magento\Store\Model\ScopeInterface;

class RemoveBlock implements ObserverInterface
{

    protected $_scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {

        /** @var Layout $layout */
        $layout = $observer->getLayout();
        $block  = $layout->getBlock('breadcrumbs');

        if ($block) {
            $show = $this->_scopeConfig->getValue('catalog/frontend/show_breadcrumbs', ScopeInterface::SCOPE_STORE);
            if (!$show) {
                $layout->unsetElement('breadcrumbs');
            }
        }
    }
}