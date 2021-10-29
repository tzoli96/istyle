<?php

namespace Oney\ThreeByFour\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Payment extends Action
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * Payment constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $returned_url = $this->_checkoutSession->getData('oney_response', false);
        echo $returned_url;
    }
}
