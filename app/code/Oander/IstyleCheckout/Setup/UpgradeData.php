<?php

namespace Oander\IstyleCheckout\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\CheckoutAgreements\Model\Agreement;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AgreementCollectionFactory
     */
    private $agreementCollection;
    /**
     * @var Agreement
     */
    private $agreementModel;

    public function __construct(
        Agreement                  $agreementModel,
        AgreementCollectionFactory $agreementCollection
    )
    {
        $this->agreementModel = $agreementModel;
        $this->agreementCollection = $agreementCollection;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $agreementCollection = $this->agreementCollection->create();

            foreach ($agreementCollection as $agreementItem) {
                $agreement = $this->agreementModel->load($agreementItem->getId());
                $agreement->setAgreementType("all");
                $agreement->save();
            }
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->changeBillingAddressDisplay($setup);
        }
    }

    private function changeBillingAddressDisplay(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->delete(
            $setup->getConnection()->getTableName("core_config_data"),
            "path = 'checkout/options/display_billing_address_on' and scope<>'default'"
        );
        $setup->getConnection()->insertOnDuplicate(
            $setup->getConnection()->getTableName("core_config_data"),
            ["scope" =>"default", "scope_id" => 0, "path" => "checkout/options/display_billing_address_on", "value" => 1],
            ["value"]
        );
    }
}