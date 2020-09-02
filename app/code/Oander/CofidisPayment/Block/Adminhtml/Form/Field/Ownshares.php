<?php
namespace Oander\CofidisPayment\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Oander\CofidisPayment\Enum\Ownshare;

/**
 * Class Ranges
 */
class Ownshares extends AbstractFieldArray
{

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn(Ownshare::CONSTRUCTION_GROUP, ['label' => __('Construction Group'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::NAME, ['label' => __('Name'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::PRIORITY, ['label' => __('Priority'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::INSTALMENTS, ['label' => __('Instalments'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::MINIMUM_LOAN, ['label' => __('Minimum loan'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::MAXIMUM_LOAN, ['label' => __('Maximum loan'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::OWNSHARE_PRICE_LIMIT, ['label' => __('Ownshare price limit'), 'class' => 'required-entry']);
        $this->addColumn(Ownshare::OWNSHARE_PERCENTAGE, ['label' => __('Ownshare percentage'), 'class' => 'required-entry ownshare-percentage']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}