<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oander\SalesforceLoyalty\Controller\Checkout;

use Oander\SalesforceLoyalty\Helper\Data;

class LoyaltyPost extends \Magento\Checkout\Controller\Cart
{
    const ROUTE = "salesforceloyalty/checkout/loyaltyPost";
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var Data
     */
    private $loyaltyHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Data $loyaltyHelper
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteRepository = $quoteRepository;
        $this->salesforceHelper = $salesforceHelper;
        $this->loyaltyHelper = $loyaltyHelper;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('amount'));

        $cartQuote = $this->cart->getQuote();
        //$oldCouponCode = $cartQuote->getCouponCode();

        try {
            $redeemablePoints = (int)trim($this->getRequest()->getParam('amount'));
            $maxRedeemablePoints = (int)$this->loyaltyHelper->getMaxRedeemablePoints();
            $availablePoints = (int)$this->salesforceHelper->getCustomerAffiliatePoints($this->cart->getCustomerSession()->getCustomer());
            if ($redeemablePoints > $maxRedeemablePoints) {
                $this->messageManager->addSuccess(
                    __(
                        'You can only spend a maximum of %s points for this purchase.',
                        $maxRedeemablePoints
                    )
                );
            }
            else if($redeemablePoints > $availablePoints) {
                $this->messageManager->addSuccess(
                    __(
                        'You only have %s points available to spend.',
                        $availablePoints
                    )
                );
            }
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->setLoyaltyDiscount($redeemablePoints)->collectTotals();
            $this->quoteRepository->save($cartQuote);
            $this->cart->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the loyalty points.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
}
