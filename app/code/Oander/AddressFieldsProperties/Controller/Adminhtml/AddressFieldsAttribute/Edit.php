<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Backend\App\Action\Context as Context;
use Magento\Backend\Model\View\Result\Page as Page;
use Magento\Backend\Model\View\Result\Redirect as Redirect;
use Magento\Customer\Api\AddressMetadataInterface as AddressMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface as AttributeInterface;
use Magento\Eav\Model\Config as Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as Collection;
use Magento\Framework\Controller\ResultInterface as ResultInterface;
use Magento\Framework\DataObject as DataObject;
use Magento\Framework\Registry as Registry;
use Magento\Framework\View\Result\PageFactory as PageFactory;
use Magento\Store\Model\ScopeInterface;

class Edit extends \Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute
{
    const ADMIN_RESOURCE = "Oander_AddressFieldsProperties::addressFieldsAttribute_update";

    protected $resultPageFactory;
    /**
     * @var Collection
     */
    private $eavCollection;
    /**
     * @var Config
     */
    private $_eavConfig;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Collection $eavCollection
     * @param Config $eavConfig
     */
    public function __construct(
        Context                   $context,
        Registry                  $coreRegistry,
        PageFactory               $resultPageFactory,
        Collection                $eavCollection,
        Config $eavConfig
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->eavCollection = $eavCollection;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var DataObject $model */
        $model = $this->_objectManager->create(DataObject::class);
        
        // 2. Initial checking
        if ($id) {
            $entityTypeId = $this->_eavConfig->getEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)->getId();
            $this->eavCollection
                ->addFieldToSelect(AttributeInterface::ATTRIBUTE_ID)
                ->addFieldToSelect(AttributeInterface::FRONTEND_LABEL)
                ->addFieldToFilter(AttributeInterface::ATTRIBUTE_ID, $id)
                ->addFieldToFilter(AttributeInterface::ENTITY_TYPE_ID, $entityTypeId);

            if ($this->eavCollection->getSize()===1) {
                $model = $this->eavCollection->getFirstItem();
            }

            if (!$model->getAttributeId()) {
                $this->messageManager->addErrorMessage(__('This Address Field Properties no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('oander_addressfieldsproperties_addressfieldsattribute', $model);
        
        // 3. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(__('Edit Address Field Properties'), __('Edit Address Field Validation'));
        $resultPage->getConfig()->getTitle()->prepend(__('Address Field Properties'));
        $resultPage->getConfig()->getTitle()->prepend($model->getAttributeId() ? __('Edit \'%1\' Address Field Properties', $model->getData(AttributeInterface::FRONTEND_LABEL)) : __('New Addressfieldsattribute'));
        return $resultPage;
    }
}
