<?php
namespace Oander\IstyleCheckout\Block\Adminhtml\Agreement;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_agreement';
        $this->_blockGroup = 'Oander_IstyleCheckout';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Condition'));
        $this->buttonList->update('delete', 'label', __('Delete Condition'));
    }

    /**
     * Get Header text
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkout_agreement')->getId()) {
            return __('Edit Terms and Conditions');
        } else {
            return __('New Terms and Conditions');
        }
    }
}
