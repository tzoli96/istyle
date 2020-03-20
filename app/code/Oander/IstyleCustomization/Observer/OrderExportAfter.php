<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderExportAfter
 * @package Oander\IstyleCustomization\Observer
 */
class OrderExportAfter implements ObserverInterface
{
    const PFPJ_REG_NO_ATTRIBUTE_CODE = 'pfpj_reg_no';
    const COMPANY_REGISTRATION_NUMBER_ATTRIBUTE_CODE = 'company_registration_number';

    const PFPJ_REG_NO_STORE_CODES = ['cz_cz', 'sk_sk'];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OrderAddressRepositoryInterface
     */
    protected $orderAddressRepository;

    /**
     * OrderExportAfter constructor.
     *
     * @param StoreManagerInterface           $storeManager
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        OrderAddressRepositoryInterface $orderAddressRepository
    ) {
        $this->storeManager = $storeManager;
        $this->orderAddressRepository = $orderAddressRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getEvent()->getData('result');

        if (!empty($result)) {

            $companyRegistrationNumber = null;
            $storeCode = $this->storeManager->getStore($result[OrderInterface::STORE_ID])->getCode();

            if (isset($result[OrderInterface::BILLING_ADDRESS_ID])
                && is_numeric($result[OrderInterface::BILLING_ADDRESS_ID])
                && in_array($storeCode, self::PFPJ_REG_NO_STORE_CODES)
            ) {
                /** @var OrderAddressInterface $billingAddress */
                $billingAddress = $this->orderAddressRepository->get((int)$result[OrderInterface::BILLING_ADDRESS_ID]);
                if ($billingAddress) {
                    $companyRegistrationNumber = $billingAddress->getData(self::PFPJ_REG_NO_ATTRIBUTE_CODE);
                }
            }

            $result[self::COMPANY_REGISTRATION_NUMBER_ATTRIBUTE_CODE] = $companyRegistrationNumber;
        }

        $observer->getEvent()->setData('result', $result);
    }
}