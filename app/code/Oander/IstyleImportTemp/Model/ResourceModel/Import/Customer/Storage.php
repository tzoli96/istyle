<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleImportTemp\Model\ResourceModel\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage as MagentoStorage;

/**
 * Class Storage
 *
 * @package Oander\IstyleImportTemp\Model\ResourceModel\Import\Customer
 */
class Storage extends MagentoStorage
{
    /**
     * Add customer to array
     *
     * @param \Magento\Framework\DataObject|\Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function addCustomer(\Magento\Framework\DataObject $customer)
    {
        if ($customer->getWebsiteId() == 19) {
            $email = strtolower(trim($customer->getEmail()));
            if (!isset($this->_customerIds[$email])) {
                $this->_customerIds[$email] = [];
            }

            $this->_customerIds[$email][$customer->getWebsiteId()] = $customer->getId();
        }
        return $this;
    }

}
