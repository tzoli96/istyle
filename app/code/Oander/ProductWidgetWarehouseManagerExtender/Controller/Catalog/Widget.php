<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Controller\Catalog;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Oander\ProductWidgetWarehouseManagerExtender\Block\Product\ProductsList;
use Oander\WarehouseManager\Helper\Attribute as AttributeHelper;

/**
 * Class Widget
 * @package Oander\ProductWidgetWarehouseManagerExtender\Controller\Catalog
 */
class Widget extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AttributeHelper
     */
    protected $attributeHelper;

    /**
     * Attribute constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AttributeHelper $attributeHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AttributeHelper $attributeHelper
    ) {
        parent::__construct($context);
        $this->attributeHelper = $attributeHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $template = $this->getRequest()->getParam('template');
        $widgetBlock = $this->getRequest()->getParam('widget_block');
        $data = $this->getRequest()->getParams();
        if (isset($data['is_ajax'])) {
            unset($data['is_ajax']);
        }

        $block = $this->_view->getLayout()->createBlock($widgetBlock);
        $block->setTemplate($template);
        $block->setData($data);
        $result->setData($block->toHtml());

        return $result;
    }
}