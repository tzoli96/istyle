<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Rewrite\Oander\OrderAttachment\Magento\Sales\Model\Order\Email\Sender;

use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Oander\ApiOrderAttachment\Model\AttachmentHandler;
use Oander\OrderAttachment\Api\OrderCommentAttachmentRepositoryInterface;
use Oander\OrderAttachment\Model\File;
use Psr\Log\LoggerInterface;

/**
 * Class OrderCommentSender
 *
 * @package Oander\OrderAttachment\Magento\Sales\Model\Order\Email\Sender
 */
class OrderCommentSender extends \Oander\OrderAttachment\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
{

    /**
     * @var AttachmentHandler
     */
    private $attachmentHandler;

    /**
     * OrderCommentSender constructor.
     *
     * @param Template $templateContainer
     * @param OrderCommentIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     * @param OrderCommentAttachmentRepositoryInterface $orderCommentAttachmentRepository
     * @param File $file
     * @param \Oander\IstyleCustomization\Rewrite\Oander\ApiOrderAttachment\Model\AttachmentHandler $attachmentHandler
     */
    public function __construct(
        Template $templateContainer,
        OrderCommentIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        ManagerInterface $eventManager,
        OrderCommentAttachmentRepositoryInterface $orderCommentAttachmentRepository,
        File $file,
        \Oander\IstyleCustomization\Rewrite\Oander\ApiOrderAttachment\Model\AttachmentHandler $attachmentHandler
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $eventManager, $orderCommentAttachmentRepository, $file);
        $this->attachmentHandler = $attachmentHandler;
    }


    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @param string $comment
     * @param null $dataItems
     * @return bool
     */
    public function sendDirect(Order $order, $notify = true, $comment = '', $dataItems = null)
    {
        $transport = [
            'order'                    => $order,
            'comment'                  => $comment,
            'billing'                  => $order->getBillingAddress(),
            'store'                    => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
        ];

        $this->eventManager->dispatch(
            'email_order_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        if ($dataItems !== null) {
            foreach ($dataItems as $dataItem) {
                $attachments = $dataItem['attachments'];
                foreach ($attachments as $attachmentItem) {
                    $content = $this->attachmentHandler->getFileContents($attachmentItem);
                    $this->getSender()->addAttachment($content, basename($attachmentItem));
                }
            }
        }

        $this->templateContainer->setTemplateVars($transport);

        return $this->checkAndSend($order, $notify);
    }

}