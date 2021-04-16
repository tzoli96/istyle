<?php
namespace Oander\HelloBankPayment\Block\Adminhtml\Barem;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context ;
use Oander\HelloBankPayment\Helper\Config;

class AvailableBarems extends Template
{
    /** @var string */
    protected $_template = 'available_barems.phtml';

    /**
     * @var Config
     */
    private $helper;

    private $currentStoreId;

    /**
     * AvailableBarems constructor.
     * @param Config $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Config $helper,
        Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
        $this->currentStoreId = $this->_storeManager->getStore()->getId();
    }

    /**
     * @return false|mixed
     */
    public function getAvailableBaremsUrl()
    {
        return (\Oander\HelloBankPayment\Helper\Config::AVAILABLE_BAREMS_URL . $this->helper->getPaymentMethodSellerId($this->currentStoreId));
    }
}