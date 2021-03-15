<?php
namespace Oander\HelloBankPayment\Block\Barem;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Oander\HelloBankPayment\Enum\Attribute;

class Display extends Template
{
    /**
     * @var CollectionFactory
     */
    private $baremCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepsitory;


    public function __construct(
        ProductRepositoryInterface $productRepsitory,
        Registry $registry,
        CollectionFactory $baremCollection,
        Template\Context $context,
        array $data = []
    ) {
        $this->productRepsitory = $productRepsitory;
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

        if($this->product()->getTypeId() == "configurable")
        {
            return $this->getChildProductBarem($this->product()->getTypeInstance()->getUsedProductIds($this->product()));
        }

        $availableBarems = $this->getAvailableBarems($this->getDisallowedBarems());

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

    /**
     * @param $productIds
     * @return array
     * @throws NoSuchEntityException
     */
    private function getChildProductBarem($productIds)
    {
        $response = [];
        foreach($productIds as $productId)
        {
            $product = $this->productRepsitory->getById($productId);
            $disAllowedBarems = ($this->productRepsitory->getById($productId)->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)) ? $product->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)->getValue() : false;

                $collection = $this->getAvailableBarems($disAllowedBarems);
                foreach($collection as $item){
                    $response[$productId][] = $item->getData();
                }
        }

        return $response;
    }

    /**
     * @param $params
     * @return Collection
     */
    private function getAvailableBarems($params)
    {
        return $this->baremCollection->create()->AddFillterAvailableBarems()
            ->addFieldToFilter(BaremInterface::ID, ['nin' => $params]);
    }

}