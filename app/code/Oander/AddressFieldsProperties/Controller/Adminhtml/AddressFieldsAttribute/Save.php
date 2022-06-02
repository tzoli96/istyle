<?php

namespace Oander\AddressFieldsProperties\Controller\Adminhtml\AddressFieldsAttribute;

use Magento\Backend\App\Action\Context as Context;
use Magento\Backend\Model\View\Result\Redirect as Redirect;
use Magento\Customer\Api\AddressMetadataInterface as AddressMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface as AttributeInterface;
use Magento\Eav\Model\Config as Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as Collection;
use Magento\Framework\App\Request\DataPersistorInterface as DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface as ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Oander\AddressFieldsProperties\Helper\ConfigWriter as ConfigWriter;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = "Oander_AddressFieldsProperties::addressFieldsAttribute_update";

    protected $dataPersistor;
    /**
     * @var Collection
     */
    private $eavCollection;
    /**
     * @var ConfigWriter
     */
    private $configWriter;
    /**
     * @var Config
     */
    private $_eavConfig;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param Collection $eavCollection
     * @param Config $eavConfig
     * @param ConfigWriter $configWriter
     */
    public function __construct(
        Context                                             $context,
        DataPersistorInterface                              $dataPersistor,
        Collection                                          $eavCollection,
        Config                                              $eavConfig,
        ConfigWriter $configWriter
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
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('attribute_id');

            $requestParams = ['attribute_id' => $id];
            if($this->getRequest()->getParam('store_id'))
                $requestParams["store"] = $this->getRequest()->getParam('store_id');
            elseif($this->getRequest()->getParam('website_id'))
                $requestParams["website"] = $this->getRequest()->getParam('website_id');

            $entityTypeId = $this->_eavConfig->getEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)->getId();
            $this->eavCollection
                ->addFieldToSelect(AttributeInterface::ATTRIBUTE_ID)
                ->addFieldToSelect(AttributeInterface::FRONTEND_LABEL)
                ->addFieldToFilter(AttributeInterface::ATTRIBUTE_ID, $id)
                ->addFieldToFilter(AttributeInterface::ENTITY_TYPE_ID, $entityTypeId);
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
