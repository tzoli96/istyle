<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Quote\Model\ResourceModel;

use Oander\IstyleCustomization\Plugin\Magento\Quote\Model\QuoteRepository;

/**
 * Class Quote
 * @package Oander\IstyleCustomization\Plugin\Magento\Quote\Model\ResourceModel
 */
class Quote
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Quote constructor.
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function beforeSave(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ($object->getId()) {
            $this->registry->unregister(QuoteRepository::QUOTE_REGISTRY . $object->getId());
            $this->registry->register(QuoteRepository::QUOTE_REGISTRY . $object->getId(), $object);
        }
    }

}