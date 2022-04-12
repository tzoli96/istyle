<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Controller\Customer;

use Magento\Customer\Model\Session\Proxy;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Oander\SalesforceLoyalty\Helper\Config;

class Account extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Proxy
     */
    private $customerSession;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Proxy $customerSession
     * @param Config $configHelper
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory,
        Proxy       $customerSession,
        Config      $configHelper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws NotFoundException
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            if (!$this->configHelper->getLoyaltyServiceEnabled()) {
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
            return $this->resultPageFactory->create();
        } else {
            $this->customerSession->setAfterAuthUrl($this->_url->getCurrentUrl());
            $this->customerSession->authenticate();
        }
    }
}
