<?php

namespace Oney\ThreeByFour\Gateway\Response;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Oney\ThreeByFour\Logger\Logger;

class OrderHandler implements HandlerInterface
{
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var Logger
     */
    private $_logger;

    public function __construct(
        Session $checkoutSession,
        Logger $logger
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->_logger->info("Oney:: response : ", $response);
        $this->_checkoutSession->setOneyResponse($response);
    }
}
