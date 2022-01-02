<?php
/**
 * Address Fields Properties
 * Copyright (C) 2019 
 * 
 * This file is part of Oander/AddressFieldsProperties.
 * 
 * Oander/AddressFieldsProperties is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Store\Model\ScopeInterface;

class Edit extends \Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute
{

    protected $resultPageFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $eavCollection;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->eavCollection = $eavCollection;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var \Magento\Framework\DataObject $model */
        $model = $this->_objectManager->create(\Magento\Framework\DataObject::class);
        
        // 2. Initial checking
        if ($id) {
            $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS)->getId();
            $this->eavCollection
                ->addFieldToSelect(\Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_ID)
                ->addFieldToSelect(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_LABEL)
                ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_ID, $id)
                ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::ENTITY_TYPE_ID, $entityTypeId);

            if ($this->eavCollection->getSize()===1) {
                $model = $this->eavCollection->getFirstItem();
            }

            if (!$model->getAttributeId()) {
                $this->messageManager->addErrorMessage(__('This Address Field Properties no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('oander_addressfieldsproperties_addressfieldsattribute', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(__('Edit Address Field Properties'), __('Edit Address Field Validation'));
        $resultPage->getConfig()->getTitle()->prepend(__('Address Field Properties'));
        $resultPage->getConfig()->getTitle()->prepend($model->getAttributeId() ? __('Edit \'%1\' Address Field Properties', $model->getData(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_LABEL)) : __('New Addressfieldsattribute'));
        return $resultPage;
    }
}
