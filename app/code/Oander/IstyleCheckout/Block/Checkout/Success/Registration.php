<?php

namespace Oander\IstyleCheckout\Block\Checkout\Success;

use Magento\Customer\Model\AccountManagement;
use Oander\IstyleCheckout\Controller\Account\Create;
use Mageplaza\GoogleRecaptcha\Helper\Data as MageplazaHelperData;
use Magento\Framework\View\Element\Template;


/**
 * Class Registration
 * @package Oander\IstyleCheckout\Block\OnePage\Success
 */
class Registration extends \Magento\Checkout\Block\Registration
{
    /**
     * @var MageplazaHelperData
     */
    protected $mageplazaHelperData;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Address\Validator $addressValidator
     * @param MageplazaHelperData $mageplazaHelperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Address\Validator $addressValidator,
        MageplazaHelperData $mageplazaHelperData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $customerSession,
            $registration,
            $accountManagement,
            $orderRepository,
            $addressValidator,
            $data
        );

        $this->mageplazaHelperData = $mageplazaHelperData;
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl(Create::ROUTE);
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    /**
     * @return mixed
     */
    public function getInvisibleKey()
    {
        return $this->mageplazaHelperData->getInvisibleKey();
    }

    /**
     * @return array|mixed
     */
    public function getPositionFrontend()
    {
        return $this->mageplazaHelperData->getPositionFrontend();
    }

    /**
     * @return array|mixed
     */
    public function isCaptchaFrontend()
    {
        return $this->mageplazaHelperData->isCaptchaFrontend();
    }

    /**
     * @return mixed
     */
    public function getLanguageCode()
    {
        return $this->mageplazaHelperData->getLanguageCode();
    }

    /**
     * @return array|mixed
     */
    public function getThemeFrontend()
    {
        return $this->mageplazaHelperData->getThemeFrontend();
    }


    protected function _prepareLayout()
    {
        return $this;
    }
}