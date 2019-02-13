<?php


namespace Oander\ApplePay\Controller\Ajax;

use Magento\Framework\Exception\NoSuchEntityException;
use Oander\ApplePay\Enum\GenerateQuote\Type as GenerateQuoteTypeEnum;
use Magento\Quote\Model\Quote\Address;

class PaymentData extends \Magento\Framework\App\Action\Action
{

    const ROUTE = 'applepay/ajax/paymentData';

    const PARAM_TYPE = 'type';
    const PARAM_PRODUCTID = 'product';
    const PARAM_QUOTEID = 'quote';

    protected $resultPageFactory;
    protected $jsonHelper;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $store;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    private $quoteAddressFactory;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    private $converter;
    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;
    /**
     * @var \Oander\ApplePay\Helper\PaymentConfig
     */
    private $paymentConfig;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Oander\ApplePay\Helper\PaymentConfig $paymentConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Oander\ApplePay\Helper\PaymentConfig $paymentConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->quoteFactory = $quoteFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->store = $this->storeManager->getStore();
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->converter = $converter;
        $this->taxHelper = $taxHelper;
        $this->cart = $cart;
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = ['success' => true, 'message' => '', 'id' => null];
        try {
            $requestData = $this->getRequest()->getParams();
            $quote = false;
            switch($this->getRequest()->getParam(self::PARAM_TYPE))
            {
                case GenerateQuoteTypeEnum::fromProduct:
                {
                    $quote = $this->validateAndCreateQuote($requestData);
                    break;
                }
                default:
                {
                    $quote = $this->getQuote($requestData);
                    break;
                }
            }
            if($quote->getId())
            {
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $maskid = $quoteIdMask->load($quote->getId(), 'quote_id')->getMaskedId();
                if($maskid === null)
                {
                    $quoteIdMask->clearInstance();
                    $quoteIdMask->setQuoteId($quote->getId())->save();
                    $maskid = $quoteIdMask->getMaskedId();
                }
                $data['id'] = $maskid;
                $data['isLoggedIn'] = $this->customerSession->isLoggedIn();
                $data['total'] = $quote->getGrandTotal();
                if($this->customerSession->isLoggedIn()) {
                    $customer = $this->customerSession->getCustomer();
                    if($customer->getDefaultShippingAddress())
                    {
                        $data['shipping_address'] = $customer->getDefaultShippingAddress()->getData();
                        $data['shipping_address']['email'] = $customer->getEmail();
                        $data['shipping_address']['street'] = $customer->getDefaultShippingAddress()->getStreet();
                    }
                    if($customer->getDefaultBillingAddress())
                    {
                        $data['billing_address'] = $customer->getDefaultBillingAddress()->getData();
                        $data['billing_address']['email'] = $customer->getEmail();
                        $data['billing_address']['street'] = $customer->getDefaultBillingAddress()->getStreet();
                    }
                }
                else
                {
                    $data['shipping_address'] = null;
                    $data['billing_address'] = null;
                }
            }
            else
            {
                throw new \Exception('No quote created');
            }
            return $this->jsonResponse($data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }


    /**
     * @param $data
     * @return \Magento\Quote\Model\Quote
     * @throws \Exception
     */
    protected function validateAndCreateQuote($data)
    {
        if(count($data))
        {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteFactory->create();
            $quote->setIsSuperMode(true);
            $quote->setIsActive(false);
            $quote->setStoreId($this->store->getId());
            if($this->customerSession->isLoggedIn())
            {
                $defaultShippingAddress = $this->customerSession->getCustomer()->getDefaultShippingAddress();
                $quoteShippingAddress = $this->quoteAddressFactory->create();
                if ($defaultShippingAddress) {
                    if($defaultShippingAddress->getCountryId() == $this->paymentConfig->getDefaultCountryId())
                        $quoteShippingAddress->importCustomerAddressData($this->addressRepository->getById($defaultShippingAddress->getId()));
                    else
                        $quoteShippingAddress->setCountryId($this->paymentConfig->getDefaultCountryId());
                } else {
                    $quoteShippingAddress->setCountryId($this->paymentConfig->getDefaultCountryId());
                }

                $quoteShippingAddress->setCustomerAddressId(null);
                $quote->setShippingAddress($quoteShippingAddress);

                $defaultBillingAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
                $quoteBillingAddress = $this->quoteAddressFactory->create();
                if ($defaultBillingAddress) {
                    if($defaultBillingAddress->getCountryId() == $this->paymentConfig->getDefaultCountryId())
                        $quoteBillingAddress->importCustomerAddressData($this->addressRepository->getById($defaultBillingAddress->getId()));
                    else
                        $quoteBillingAddress->setCountryId($this->paymentConfig->getDefaultCountryId());
                } else {
                    $quoteBillingAddress->setCountryId($this->paymentConfig->getDefaultCountryId());
                }

                $quoteBillingAddress->setCustomerAddressId(null);
                $quote->setBillingAddress($quoteBillingAddress);

                $quote->setCustomer($this->customerSession->getCustomerData());
                $quote->setCustomerIsGuest(0);
            }
            else {
                $quote->setBillingAddress($this->quoteAddressFactory->create()->setCountryId($this->paymentConfig->getDefaultCountryId()));
                $quote->setShippingAddress($this->quoteAddressFactory->create()->setCountryId($this->paymentConfig->getDefaultCountryId()));
                $quote->setCustomerIsGuest(1);
            }
            $product = $this->_initProduct($data);
            $quote->addProduct($product);
            $this->setCountryId($quote->getShippingAddress());
            $this->setCountryId($quote->getBillingAddress());
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            return $quote;
        }
        else
        {
            throw new \Exception('No quote data');
        }
    }

    public function getQuote($data)
    {
        $quote = null;
        if(isset($data[self::PARAM_QUOTEID]))
        {
            $quote = $this->quoteRepository->get($data[self::PARAM_QUOTEID]);
        }
        if(!$quote)
        {
            $quote = $this->cart->getQuote();
        }

        if(!$quote)
        {
            throw new \Exception('No active Quote');
        }
        return $quote;

    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($data)
    {
        $productId = (int)$data[self::PARAM_PRODUCTID];
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Get Shipping Price including or excluding tax
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param bool $flag
     * @return float
     */
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }

    /**
     * @param $address Address
     */
    private function setCountryId($address)
    {
        if($address->getCountryId()==null) {
            $countryid = $this->scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $address->setCountryId($countryid);
        }
    }
}
