<?php
namespace Oander\HelloBankPayment\Model\ResourceModel\Barems;

use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\Barems;
use Oander\HelloBankPayment\Model\ResourceModel\Barems as BaremsResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = BaremInterface::ID;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Barems::class, BaremsResourceModel::class);
    }

    /**
     * @return Collection
     */
    public function getAvailableBarems(): Collection
    {
        return $this->addFieldToFilter(BaremInterface::STATUS, ['eq' => BaremInterface::STATUS_ENABLED]);
    }

    /**
     * @param $disAllowedBarems
     * @param $grandTotal
     * @return Collection
     */
    public function getDissAllowed($disAllowedBarems=false, $grandTotal=false): Collection
    {
        $query = $this->addFieldToFilter(BaremInterface::ID, ['nin' => $disAllowedBarems]);

        if($grandTotal)
        {
            $query->addFieldToFilter(BaremInterface::MAXIMUM_PRICE, ['gt' => $grandTotal])
                ->addFieldToFilter(BaremInterface::MINIMUM_PRICE, ['lt' => $grandTotal]);
        }

        return $query;
    }
}
