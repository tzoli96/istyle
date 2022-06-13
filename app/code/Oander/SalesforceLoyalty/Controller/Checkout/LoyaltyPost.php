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
        $cartQuote = $this->cart->getQuote();
        $redeemablePoints = (int)trim($this->getRequest()->getParam('amount'));

        try {
            $hasError = false;
            $maxRedeemablePoints = (int)$this->loyaltyHelper->getMaxRedeemablePoints();
            $availablePoints = $this->salesforceHelper->getCustomerAffiliatePoints($this->cart->getCustomerSession()->getCustomer());
            $availablePointsCash = $this->salesforceHelper->getCustomerAffiliatePointsCashConverted($this->cart->getCustomerSession()->getCustomer());
            if ($availablePointsCash > $maxRedeemablePoints)
            {
                if ($redeemablePoints > $maxRedeemablePoints) {
                    $this->messageManager->addError(
                        __(
                            'You can only spend a maximum of %1 points for this purchase.',
                            $maxRedeemablePoints
                        )
                    );
                    $hasError = true;
                }
            }
            elseif($redeemablePoints > $availablePoints)
            {
                $this->messageManager->addError(
                    __(
                        'You only have %1 points available to spend.',
                        $availablePoints
                    )
                );
                $hasError = true;
            }
            if(!$hasError) {
                $itemsCount = $cartQuote->getItemsCount();
                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->setLoyaltyPoint($redeemablePoints)->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
                $this->_checkoutSession->getQuote()->setLoyaltyPoint($redeemablePoints)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the loyalty points.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
}
