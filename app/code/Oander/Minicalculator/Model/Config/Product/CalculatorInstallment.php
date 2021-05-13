<?php

namespace Oander\Minicalculator\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Oander\HelloBankPayment\Model\BaremsFactory;
use Oander\Minicalculator\Api\Data\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class CalculatorInstallment extends AbstractSource
{
    /**
     * @var BaremsFactory
     */
    private $baremFactory;

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
     * CalculatorInstallment constructor.
     * @param BaremsFactory $baremFactory
     * @param Registry $registry
     */
    public function __construct(
        BaremsFactory $baremFactory,
        Registry $registry
    )
    {
        $this->baremFactory = $baremFactory;
        $this->registry = $registry;
    }


    /**
     * @return Product
     */
    private function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (is_null($this->product) || !$this->product->getId()) {
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
     * @return false|\Magento\Framework\Api\AttributeInterface|null
     */
    private function getBarem()
    {
        if ($this->getProduct()) {
            return $this->getProduct()->getCustomAttribute(CalculatorInterface::CALCULATOR_BAREM);
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
                if ($this->getBarem()) {
                    $this->getOptions($this->baremFactory->create()->load($this->getBarem()->getValue()));
                }

                break;
            default:
                $this->getDefaultOptions();
        }

        return $this->options;
    }

    /**
     * @param $baremModel
     * @return void
     */
    private function getOptions($baremModel)
    {
        if ($baremModel->getData(BaremInterface::INSTALLMENTS_TYPE) == BaremInterface::INSTALLMENTS_TYPE_RANGE) {
            $options = explode(",", $baremModel->getData(BaremInterface::INSTALLMENTS));
            foreach ($options as $option) {
                $this->options[] = [
                    'value' => $option,
                    'label' => $option
                ];
            }
        } else {
            $this->options[] = [
                'value' => $baremModel->getData(BaremInterface::INSTALLMENTS),
                'label' => $baremModel->getData(BaremInterface::INSTALLMENTS)
            ];
        }
    }

    /**
     * @return void
     */
    private function getDefaultOptions()
    {
        $this->options = [
            'value' => 1,
            'label' => 1
        ];
    }


}