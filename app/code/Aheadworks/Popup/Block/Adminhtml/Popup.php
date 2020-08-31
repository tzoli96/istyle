<?php
namespace Aheadworks\Popup\Block\Adminhtml;

/**
 * Class Popup
 * @package Aheadworks\Popup\Block\Adminhtml
 */
class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'popup_admin';
        $this->_blockGroup = 'Aheadworks_Popup';
        $this->_headerText = __('Manage Popups');
        $this->_addButtonLabel = __('Create New Popup');
        parent::_construct();
    }
}
