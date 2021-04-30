<?php

namespace Oander\Minicalculator\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Registry;
use Oander\Minicalculator\Api\Data\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection as BaremCollection;

class CalculatorBarems extends AbstractSource
{
    /**
     * @var BaremCollection
     */
    private $baremCollection;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var null
     */
    private $calculatorType = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * CalculatorBarems constructor.
     * @param BaremCollection $baremCollection
     * @param Registry $registry
     */
    public function __construct(
        BaremCollection $baremCollection,
        Registry $registry
    )
    {
        $this->baremCollection = $baremCollection;
        $this->registry = $registry;
    }


    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId()) {
                return false;
            }
        }

        return $this->product;
    }

    /**
     * @return false|\Magento\Framework\Api\AttributeInterface|null
     */
    private function getCalculatorType()
    {
        if ($this->getProduct()) {
            return $this->getProduct()->getCustomAttribute(CalculatorInterface::CALCULATOR_TYPE);
        } else {
            return false;
        }

    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->getCalculatorType()) {
            if ($this->getCalculatorType()->getValue() === "hellobank") {
                $this->calculatorType = "hellobank";
            }
        }

        switch ($this->calculatorType) {
            case "hellobank":
                $this->getOptions($this->baremCollection->getItems());
                break;
            default:
                $this->getOptions($this->baremCollection->getItems());
        }

        return $this->options;
    }

    /**
     * @param $collection
     * @return void
     */
    private function getOptions($collection)
    {
        foreach ($collection as $item) {
            $this->options[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
    }
}