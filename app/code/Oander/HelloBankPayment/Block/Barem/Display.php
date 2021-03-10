<?php
namespace Oander\HelloBankPayment\Block\Barem;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Oander\HelloBankPayment\Enum\Attribute;

class Display extends Template
{
    /**
     * @var Collection
     */
    private $baremCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    private $product;


    public function __construct(
        Registry $registry,
        Collection $baremCollection,
        Template\Context $context,
        array $data = []
    ) {
        $this->baremCollection = $baremCollection;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    private function product()
    {
        return $this->registry->registry('product');
    }

    private function getDisallowedBarems()
    {
        $disAllowedBarems= $this->product()->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);
        return ($disAllowedBarems) ? $disAllowedBarems->getValue() : false;
    }

    /**
     * @return array
     */
    public function getBarems()
    {

        $availableBarems = $this->baremCollection->AddFillterAvailableBarems()
        ->addFieldToFilter(BaremInterface::ID, ['nin' => $this->getDisallowedBarems()]);

        $barems = [];
        foreach ($availableBarems->getItems() as $availableBarem)
        {
            if(!in_array($availableBarem->getData(), $barems))
            {
                if($this->product()->getTypeId() === "simple")
                {
                    if($availableBarem->getData(BaremInterface::MINIMUM_PRICE) <= $this->product()->getPrice() &&
                        $availableBarem->getData(BaremInterface::MAXIMUM_PRICE) >= $this->product()->getPrice() )
                    {
                        $barems[] = $availableBarem->getData();
                    }
                }else {
                    $barems[] = $availableBarem->getData();
                }

            }
        }
        return $barems;
    }

}