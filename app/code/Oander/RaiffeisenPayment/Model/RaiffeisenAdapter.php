<?php

namespace Oander\RaiffeisenPayment\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Oander\RaiffeisenPayment\Helper\Config;

class RaiffeisenAdapter extends Adapter
{
    /**
     * @var Config
     */
    protected $raiffeisenConfigHelper;

    /**
     * @param Config $raiffeisenConfigHelper
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param $code
     * @param $formBlockType
     * @param $infoBlockType
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     */
    public function __construct(
        Config                    $raiffeisenConfigHelper,
        ManagerInterface          $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory  $paymentDataObjectFactory,
                                  $code,
                                  $formBlockType,
                                  $infoBlockType,
        CommandPoolInterface      $commandPool = null,
        ValidatorPoolInterface    $validatorPool = null,
        CommandManagerInterface   $commandExecutor = null
    )
    {
        $this->raiffeisenConfigHelper = $raiffeisenConfigHelper;
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $grandTotalWithoutShippingAmmount =$quote->getGrandTotal()-$quote->getTotals()["shipping"]->getData("value");
        return ($grandTotalWithoutShippingAmmount >= $this->raiffeisenConfigHelper->getMinAmount());
    }
}