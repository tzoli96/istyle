<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Store\Model\ScopeInterface;

class Edit extends \Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute
{
    const ADMIN_RESOURCE = "Oander_AddressFieldsProperties::addressFieldsAttribute_update";

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
