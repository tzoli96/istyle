<?php

namespace Oander\IstyleCustomization\Rewrite\Magento\Sales\Block\Order\Info;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Context as ContextModel;
use Magento\Sales\Model\Order;
use Oander\OrderAttachment\Api\Data\OrderCommentAttachmentInterface;
use Oander\OrderAttachment\Enum\Attachment;
use Oander\OrderAttachment\Model\ResourceModel\OrderCommentAttachment\Collection;
use Oander\OrderAttachment\Model\ResourceModel\OrderCommentAttachment\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Buttons extends Template
{
    /**
     * @var Json
     */
    protected $jsonHelper;
    /**
     * @var CollectionFactory
     */
    protected $orderAttachmentCollection;
    /**
     * @var string
     */
    protected $_template = 'Magento_Sales::order/info/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        Context                             $context,
        \Magento\Framework\Registry         $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        CollectionFactory                   $orderAttachmentCollection,
        Json                                $jsonHelper,
        array                               $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->jsonHelper = $jsonHelper;
        $this->orderAttachmentCollection = $orderAttachmentCollection;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Get url for printing order
     *
     * @param Order $order
     * @return string
     */
    public function getPrintUrl($order)
    {
        if (!$this->httpContext->getValue(ContextModel::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/print', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/print', ['order_id' => $order->getId()]);
    }

    /**
     * Get url for reorder action
     *
     * @param Order $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        if (!$this->httpContext->getValue(ContextModel::CONTEXT_AUTH)) {
            return $this->getUrl('sales/guest/reorder', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return Collection
     */
    private function getAttachments()
    {
        $collection = $this->orderAttachmentCollection->create();
        $collection->getSelect()->join(
            ['status' => $collection->getTable('sales_order_status_history')],
            "main_table.history_id = status.entity_id",
            ['order_id' => 'status.parent_id']
        )->where('status.parent_id = ' . $this->getOrder()->getEntityId());

        return $collection;
    }

    /**
     * @param array $attachment
     * @return string
     */
    private function getDownloadUrl(array $attachment): string
    {
        return $this->getUrl(
            Attachment::FRONTEND_DOWNLOAD_PATH,
            [
                Attachment::PARAM_ATTACHMENT_ID => $attachment[OrderCommentAttachmentInterface::ATTACHMENT_ID]
            ]
        );
    }

    /**
     * @return bool|string
     */
    public function getFilesJsonFormat()
    {
        $collection = $this->getAttachments();
        $response = [];
        foreach ($collection as $attachment) {
            $response[] = $this->getDownloadUrl($attachment->getData());
        }
        return $this->jsonHelper->serialize($response);
    }
}