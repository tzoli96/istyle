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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Proxy $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Proxy $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if($this->customerSession->isLoggedIn())
        {
            return $this->resultPageFactory->create();
        }else{
            throw new NotFoundException(__('Parameter is incorrect.'));
        }
    }
}
