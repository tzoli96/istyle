<?php
namespace Oander\HelloBankPayment\Model;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Oander\HelloBankPayment\Helper\BaremCheck;

class HelloBankAdapter extends Adapter
{
    /**
     * @var BaremCheck
     */
    private $bramChecker;

    public function __construct(
        BaremCheck $baremChecker,
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        $this->bramChecker = $baremChecker;
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
     * @return array|bool|mixed|null
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return ($this->bramChecker->checkItHasBarem($quote)) ? parent::isAvailable($quote) : false;
    }
}