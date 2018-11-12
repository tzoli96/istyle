<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderExportAfter implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();

        if ($eventName === 'oander_xtento_order_export_after') {
            $result = $observer->getEvent()->getData('result');

            if (!empty($result)) {
                $companyRegistrationNumber = null;

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                /**
                 * @var \Magento\Store\Model\StoreManagerInterface $storeManager
                 */
                $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
                $store = $storeManager->getStore($result['store_id']);
                if (isset($result['billing_address_id'])
                    && isset($result['billing_address_id']['entity_id'])
                    && in_array($store->getCode(), ['cz_cz', 'sk_sk'])
                ) {
                    $billingAddress = $objectManager->create('\Magento\Sales\Model\Order\Address')->load($result['billing_address_id']['entity_id']);
                    if ($billingAddress) {
                        $companyRegistrationNumber = $billingAddress->getData('pfpj_reg_no');
                    }
                }

                $result['company_registration_number'] = $companyRegistrationNumber;
            }


            $observer->getEvent()->setData('result', $result);
        }
    }
}