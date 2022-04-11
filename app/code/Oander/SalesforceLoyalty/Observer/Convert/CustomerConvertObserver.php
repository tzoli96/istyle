<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_SalesforceLoyalty
 *
 * @author  Péter Vass <peter.vass@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Observer\Convert;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Oander\EventBasedExporter\Observer\Convert\AbstractConvertObserver;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;

class CustomerConvertObserver extends AbstractConvertObserver
{
    /**
     * @inheritDoc
     */
    protected function accept($inputData): bool
    {
        return $inputData instanceof CustomerInterface;
    }

    /**
     * @param Customer $customer
     * @param array    $convertedData
     *
     * @return void
     */
    protected function convert($customer, array &$convertedData)
    {
        $loyaltyStatus = $customer->getCustomAttribute(CustomerAttribute::LOYALTY_STATUS);
        $convertedData[CustomerAttribute::LOYALTY_STATUS] = $this->cast($loyaltyStatus, 'int', false);
    }
}
