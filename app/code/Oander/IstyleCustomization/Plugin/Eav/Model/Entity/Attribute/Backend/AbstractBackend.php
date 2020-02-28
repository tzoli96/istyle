<?php

namespace Oander\IstyleCustomization\Plugin\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class AbstractBackend
 * @package Oander\IstyleCustomization\Plugin\Eav\Model\Entity\Attribute\Backend
 */
class AbstractBackend
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Session
     */
    protected $session;

    /**
     * AbstractBackend constructor.
     * @param Session $session
     * @param Config $config
     */
    public function __construct(
        Session $session,
        Config $config
    ) {
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Validate object
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        $object
    ) {
        $result = false;

        try {
            $result = $proceed($object);
        } catch (LocalizedException $exception) {
            if (isset($exception->getParameters()[0]) &&
                $exception->getParameters()[0] == 'dob'
            ) {
                if ($quote = $this->session->getQuote()) {
                    $quoteItems = $this->session->getQuote()->getAllItems();
                    if ($this->config->getDobShow($quoteItems) !== 'req') {
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }
}