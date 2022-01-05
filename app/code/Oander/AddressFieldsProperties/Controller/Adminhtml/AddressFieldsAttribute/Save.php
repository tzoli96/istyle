<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = "Oander_AddressFieldsProperties::addressFieldsAttribute_update";

    protected $dataPersistor;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $eavCollection;
    /**
     * @var \Oander\AddressFieldsProperties\Helper\ConfigWriter
     */
    private $configWriter;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Oander\AddressFieldsProperties\Helper\ConfigWriter $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection,
        \Magento\Eav\Model\Config $eavConfig,
        \Oander\AddressFieldsProperties\Helper\ConfigWriter $configWriter
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->eavCollection = $eavCollection;
        $this->configWriter = $configWriter;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('attribute_id');

            $requestParams = ['attribute_id' => $id];
            if($this->getRequest()->getParam('store_id'))
                $requestParams["store"] = $this->getRequest()->getParam('store_id');
            elseif($this->getRequest()->getParam('website_id'))
                $requestParams["website"] = $this->getRequest()->getParam('website_id');

            $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS)->getId();
            $this->eavCollection
                ->addFieldToSelect(\Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_ID)
                ->addFieldToSelect(\Magento\Eav\Api\Data\AttributeInterface::FRONTEND_LABEL)
                ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_ID, $id)
                ->addFieldToFilter(\Magento\Eav\Api\Data\AttributeInterface::ENTITY_TYPE_ID, $entityTypeId);
            if ($this->eavCollection->getSize()!==1) {
                $this->messageManager->addErrorMessage(__('This Addressfieldsattribute no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            try {
                if ($this->getRequest()->getParam('store_id')) {
                    $this->configWriter->writeByAttribute($id, $data, ScopeInterface::SCOPE_STORE, $this->getRequest()->getParam('store_id'));
                } elseif ($this->getRequest()->getParam('website_id')) {
                    $this->configWriter->writeByAttribute($id, $data, ScopeInterface::SCOPE_WEBSITE, $this->getRequest()->getParam('website_id'));
                } else {
                    $this->configWriter->writeByAttribute($id, $data);
                }
                $this->messageManager->addSuccessMessage(__('You saved the Address Field Properties.'));
                $this->dataPersistor->clear('oander_addressfieldsproperties_addressfieldsattribute');

                return $resultRedirect->setPath('*/*/edit', $requestParams);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Address Field Properties.'));
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
        
            $this->dataPersistor->set('oander_addressfieldsproperties_addressfieldsattribute', $data);
            return $resultRedirect->setPath('*/*/edit', $requestParams);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
