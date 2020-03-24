<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Service;

/**
 * CMS block edit form container
 */
class Connection extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Nostress_Koongo';
        $this->_controller = 'adminhtml_service_connection';        

        parent::_construct();        
        $this->addButton( 'help', $this->_getHelpButtonData());
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Activate Service');
    }
    
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    // public function getBackUrl()
    // {
    //     return $this->getUrl('*/channel_profile/');
    // }
    
    /**
     * Get form save URL
     *
     * @see getFormActionUrl()
     * @return string
     */
    // public function getSaveUrl()
    // {
    //     return $this->getUrl('*/*/step1');
    // }

    protected function _getHelpButtonData() {
         
        $helpButtonProps = [
                'id' => 'help_dialog',
                'label' => __('Get Support'),
                'class' => 'primary',
        ];
        return $helpButtonProps;
        
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $numStep = $this->_coreRegistry->registry(\Nostress\Koongo\Helper\Data::REGISTRY_KEY_KOONGO_SERVIVE_CONNECTION_STEP_NUMBER);
        if($numStep == 2) //Show permissions tab only in step 2
            $html .= "<div style=\"padding-left:25%\">".$this->getChildHtml('activate_permissions')."</div>";
        return $html;
    }    
}
