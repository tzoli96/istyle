<?php

namespace Oander\IstyleBase\Model\Service;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Oander\UnityPickup\Api\Data\QuoteInterface;
use Oander\UnityPickup\Api\Data\QuoteInterfaceFactory;
use Oander\UnityPickup\Api\QuoteRepositoryInterface;
use Oander\UnityPickup\Logger\Logger;
use Oander\UnityPickup\Model\ResourceModel\Quote\Collection;
use Oander\UnityPickup\Model\Quote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Oander\UnityPickup\Model\Storage\PickupSession;


class QuoteRepository extends \Oander\UnityPickup\Model\Service\QuoteRepository
{
    /**
     * @var QuoteInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var PickupSession
     */
    private $pickupSession;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $pickupData = [];

    public function __construct(
        QuoteInterfaceFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        PickupSession $pickupSession,
        Logger $logger
    ){
        parent::__construct($quoteFactory, $checkoutSession, $messageManager, $pickupSession, $logger);
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->pickupSession = $pickupSession;
        $this->logger = $logger;
    }

    /**
     * @param int|null $quoteId
     *
     * @return int
     * @throws \Zend_Db_Select_Exception
     */
    public function getPickupId(int $quoteId = null): int
    {
        $pickupId = 0;
        if ($quoteId === null) {
            $quoteId = (int)$this->checkoutSession->getQuoteId();
            if ($quoteId == 0) {
                $quoteId = (int)$this->checkoutSession->getLastRealOrder()->getQuoteId();
            }
        }

        try {
            $quote = $this->getByQuoteId($quoteId);
            $pickupId = (int)$quote->getPickupId();
        } catch (\Exception $exception) {
            $pickupId = $this->pickupSession->getPickupId();
            if ($pickupId < 1) {
                $this->messageManager->addErrorMessage('Please select a pickup point.');
                $this->logger->addError($exception->getMessage());
            }
        }

        return $pickupId;
    }

}