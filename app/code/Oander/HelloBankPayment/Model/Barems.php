<?php
namespace Oander\HelloBankPayment\Model;

use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Magento\Framework\Model\AbstractModel;
use Oander\HelloBankPayment\Model\ResourceModel\Barems as BaremsResourceModel;
use Magento\Framework\DataObject\IdentityInterface;

class Barems extends AbstractModel implements BaremInterface,IdentityInterface
{
    const CACHE_TAG = 'hellobank_barems';

    /**
     * @var string
     */
    protected $_cacheTag = 'oander_hellobank_barems';

    /**
     * @var string
     */
    protected $_eventPrefix = 'oander_hello_bank_barems';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BaremsResourceModel::class);
    }


    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    public function getBaremName()
    {
        return $this->getData(self::BAREM_NAME);
    }

    public function setBaremName($baremName)
    {
        return $this->setData(self::BAREM_NAME, $baremName);
    }

    public function getBaremId()
    {
        return $this->getData(self::BAREM_ID);
    }

    public function setBaremId($baremId)
    {
        return $this->setData(self::BAREM_ID, $baremId);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    public function getMaxPrice()
    {
        return $this->getData(self::MAXIMUM_PRICE);
    }

    public function setMaxPrice($price)
    {
        return $this->setData(self::MAXIMUM_PRICE, $price);
    }

    public function getMinPrice()
    {
        return $this->getData(self::MINIMUM_PRICE);
    }

    public function setMinPrice($price)
    {
        return $this->setData(self::MINIMUM_PRICE, $price);
    }

    public function getInstallmentsType()
    {
        return $this->getData(self::INSTALLMENTS_TYPE);
    }

    public function setInstallmentsType($type)
    {
        return $this->setData(self::INSTALLMENTS_TYPE, $type);
    }

    public function getInstallments()
    {
        return $this->getData(self::INSTALLMENTS);
    }

    public function setInstallments($installments)
    {
        return $this->setData(self::INSTALLMENTS, $installments);
    }

    public function getDefaultInstallment()
    {
        return $this->getData(self::DEFAULT_INSTALLMENT);
    }

    public function setDefaultInstallment($installment)
    {
        return $this->setData(self::DEFAULT_INSTALLMENT, $installment);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * @return array
     */
    public function getAvailableInstallmentsTypes()
    {
        return [self::INSTALLMENTS_TYPE_FIXED => __('Fixed'), self::INSTALLMENTS_TYPE_RANGE => __('Range')];
    }
}