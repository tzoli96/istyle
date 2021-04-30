<?php

namespace Oander\Minicalculator\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\Minicalculator\Api\Data\CalculatorInterface;

class HandleSaveProduct implements ObserverInterface
{
    protected $request;

    /**
     * HandleSaveProduct constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ){
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {

        $params                 = $this->request->getParams();
        $product                = $observer->getEvent()->getProduct();
        if(isset($params['product'])){
            $calculatorType         = (isset($params['product'][CalculatorInterface::CALCULATOR_TYPE])) ? $params['product'][CalculatorInterface::CALCULATOR_TYPE] : '';
            $calculatorBarem        = (isset($params['product'][CalculatorInterface::CALCULATOR_BAREM])) ? $params['product'][CalculatorInterface::CALCULATOR_BAREM] : '';
            $calculatorInstallment  = (isset($params['product'][CalculatorInterface::CALCULATOR_INSTALLMENT])) ? $params['product'][CalculatorInterface::CALCULATOR_INSTALLMENT] : '';

            $product->setCustomAttribute(CalculatorInterface::CALCULATOR_TYPE, $calculatorType);
            $product->setCustomAttribute(CalculatorInterface::CALCULATOR_BAREM, $calculatorBarem);
            $product->setCustomAttribute(CalculatorInterface::CALCULATOR_INSTALLMENT, $calculatorInstallment);
        }
    }
}