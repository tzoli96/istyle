<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\IstyleCheckout\Plugin\Frontend\Magento\Customer\Controller\Address;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;

class FormPost
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $_formKeyValidator;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlBuilder;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $_redirect;

    /**
     * FormPost constructor.
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(
        \Magento\Customer\Controller\Address\FormPost $subject,
        \Closure $proceed
    ) {
        $redirectUrl = null;
        if ($this->_formKeyValidator->validate($subject->getRequest())) {
            if ($subject->getRequest()->isPost()) {
                if ($this->_scopeConfig->isSetFlag("customer/create_account/vat_frontend_visibility", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->_scopeConfig->isSetFlag("customer/address/taxvat_profile_checkout_required", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $error = false;
                    if (\Zend_Validate::is($subject->getRequest()->getParam("company"), 'NotEmpty') xor \Zend_Validate::is($subject->getRequest()->getParam("vat_id"), 'NotEmpty')) {
                        if (!\Zend_Validate::is($subject->getRequest()->getParam("company"), 'NotEmpty')) {
                            $this->messageManager->addError(__('%fieldName is a required field.', ['fieldName' => __('Company')]));
                            $error = true;
                        }
                        if (!\Zend_Validate::is($subject->getRequest()->getParam("vat_id"), 'NotEmpty')) {
                            $this->messageManager->addError(__('%fieldName is a required field.', ['fieldName' => __('VAT Number')]));
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->_customerSession->setAddressFormData($subject->getRequest()->getPostValue());
                        $url = $this->_urlBuilder->getUrl('*/*/edit', ['id' => $subject->getRequest()->getParam('id')]);
                        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
                    }
                }
            }
        }
        $result = $proceed();
        return $result;
    }
}
