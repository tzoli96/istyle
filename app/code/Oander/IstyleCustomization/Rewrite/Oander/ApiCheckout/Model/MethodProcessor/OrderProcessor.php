<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Rewrite\Oander\ApiCheckout\Model\MethodProcessor;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Oander\ApiBase\Api\Data\MessageTransporterInterface;
use Oander\ApiCheckout\Enum\EntityLoggerEnum;
use Oander\ApiGateway\Helper\EntityLogger;

/**
 * Class OrderProcessor
 *
 * @package Oander\ApiCheckout\Model\MethodProcessor
 */
class OrderProcessor extends \Oander\ApiCheckout\Model\MethodProcessor\OrderProcessor
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var OrderCommentSender
     */
    private $orderCommentSender;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $dataItems;
    /**
     * OrderProcessor constructor.
     *
     * @param OrderRepositoryInterface    $orderRepository
     * @param OrderFactory                $orderFactory
     * @param MessageTransporterInterface $messageTransporter
     * @param HistoryFactory              $historyFactory
     * @param OrderCommentSender          $orderCommentSender
     * @param Registry                    $registry
     * @param ManagerInterface            $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory,
        MessageTransporterInterface $messageTransporter,
        HistoryFactory $historyFactory,
        OrderCommentSender $orderCommentSender,
        Registry $registry,
        ManagerInterface $eventManager,
        EntityLogger $entityLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->historyFactory = $historyFactory;
        $this->orderCommentSender = $orderCommentSender;
        $this->eventManager = $eventManager;
        parent::__construct($orderRepository, $orderFactory, $messageTransporter, $historyFactory, $orderCommentSender, $registry, $eventManager, $entityLogger);
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function execute(array &$data)
    {
        $this->setEntityType(EntityLoggerEnum::ORDER);
        $this->dataItems = $data['items'];
        foreach ($data['items'] as $item) {
            $method = function (array &$params) {
                $isCustomerNotified = (bool)$params[0]['is_customer_notified'];
                $comment = $params[0]['comment'] ?? '';
                $order = $this->orderFactory->create();

                /** @var Order $order */
                $order = $order->getCollection()
                    ->addFieldToFilter(
                        OrderInterface::INCREMENT_ID,
                        (string)$params[0]['order_increment_id']
                    )->getFirstItem();

                if (!$order->getId()) {
                    throw new NoSuchEntityException();
                }
                /** @var  $history */
                $history = $this->historyFactory->create();
                $history
                    ->setStatus($params[0]['status'])
                    ->setComment($comment)
                    ->setIsVisibleOnFront((bool)$params[0]['is_visible_on_front']);
                $history->setEntityName('order');
                $history->setIsCustomerNotified($isCustomerNotified);
                $order->addStatusHistory($history);
                $order->setState($params[0]['state']);

                $this->logEventBefore($order->getId(), EntityLoggerEnum::ORDER, $order->getStoreId());

                $this->orderRepository->save($order);

                $this->logEventAfter($order->getId(), EntityLoggerEnum::ORDER, $order->getStoreId());

                $this->eventManager->dispatch(
                    'oander_api_gateway_checkout_add_history',
                    [
                        'history' => $history,
                        'order' => $order,
                        'item_data' => $this->dataItems
                    ]
                );

                $this->logEventBefore($order->getId(), EntityLoggerEnum::ORDER_COMMENT, $order->getStoreId());

                $this->orderCommentSender->sendDirect($order, $isCustomerNotified, $comment, $this->dataItems);

                $this->logEventAfter($order->getId(), EntityLoggerEnum::ORDER_COMMENT, $order->getStoreId());

                $this->messageTransporter->getResponseContainer()->appendDataAutoIncrement(
                    'order',
                    (int)$order->getIncrementId(),
                    (array)$order->getStatusHistoryById($history->getEntityId())->getData()
                );
            };

            $this->call(
                $method,
                $data['type'],
                $item['order_increment_id'],
                'checkout',
                $this->messageTransporter,
                $data['continue_on_error'] ?? false,
                $item
            );
        }
    }
}
