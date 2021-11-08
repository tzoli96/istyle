<?php


namespace Oney\ThreeByFour\Gateway\Request;


use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Country;
use Magento\Framework\Filter\FilterManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;

class PurchaseDataBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    protected $_helperConfig;
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var Country
     */
    protected $_country;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        Session $checkoutSession,
        Config $config,
        Logger $logger,
        Country $country,
        $orderRepository
    )
    {
        $this->_helperConfig = $config;
        $this->_country = $country;
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $this->_logger->info('Oney :: building Purchase :', [$buildSubject]);
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $shipping_address = $order->getShippingAddress();
        $this->_country->loadByCode($shipping_address->getCountryId());
        $carrier_code = $this->getCarrierCode();
        $mode_shipping = $this->_helperConfig->getCarrierConfig($carrier_code, 'deliverymodecode');
        $orderFullPrice = 0;

        $itemList = [];
        $maxPrice = 0;
        $mainItemKey = 0;
        $qtyOrdered = 1;

        foreach ($order->getItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            /** @var  \Magento\Sales\Model\Order\Item $item */
            if ($maxPrice < $item->getPriceInclTax()) {
                $maxPrice = $item->getPriceInclTax();
                $mainItemKey = count($itemList);
            }
            $orderFullPrice += $item->getPriceInclTax() * (int)$item->getQtyOrdered();
            $itemList[] = [
                "is_main_item" => 0,
                "category_code" => $this->_helperConfig->getGeneralConfigValue('category'),
                "label" => $item->getName(),
                "item_external_code" => $item->getSku(),
                "quantity" => (int)$item->getQtyOrdered(),
                "price" => $item->getPriceInclTax()
            ];
            $qtyOrdered += $item->getQtyOrdered();
        }

        $delivery_address = [
            "postal_code" => $shipping_address->getPostCode(),
            "municipality" => $shipping_address->getCity(),
            "country_code" => $this->_country->getData('iso3_code'),
            "country_label" => $this->_country->getName()
        ];
        if ($this->_helperConfig->getCarrierConfig($carrier_code, 'deliverymodecode') == "1") {
            $delivery_address = [
                "postal_code" => $this->_helperConfig->getConfigValue('general/store_information/postcode'),
                "municipality" => $this->_helperConfig->getConfigValue('general/store_information/city'),
                "country_code" => $this->_country->loadByCode(
                    $this->_helperConfig->getConfigValue('general/store_information/country_id')
                )->getData('iso3_code'),
                "country_label" => $this->_country->loadByCode(
                    $this->_helperConfig->getConfigValue('general/store_information/country_id')
                )->getName()
            ];
        }

        $address_type = $this->_helperConfig->getCarrierConfig($carrier_code, 'addresstype');
        $delivery = [
            "delivery_date" => date('Y-m-d', strtotime('+7 days')),
            "delivery_mode_code" => $mode_shipping,
            "delivery_option" => $this->_helperConfig->getCarrierConfig($carrier_code, 'deliveryoption'),
            "address_type" => $address_type,
            "priority_delivery_code" => $this->_helperConfig->getCarrierConfig($carrier_code, 'prioritydeliverycode'),
            "delivery_address" => $delivery_address
        ];

        if ($address_type == "5") {
            $delivery['recipient'] = [
                "surname" => $shipping_address->getLastname(),
                "first_name" => $shipping_address->getFirstname(),
                "phone_number" => $this->formatPhone($shipping_address->getTelephone(), $this->_country->getId())
            ];
        }
        $orderFullPrice += $this->_checkoutSession->getQuote()->getShippingAddress()->getTotals()['shipping']->getValue();
        $quote = $this->_checkoutSession->getQuote();

        $itemList[$mainItemKey]['is_main_item'] = 1;
        $itemList[] = [
            "is_main_item" => 0,
            "category_code" => $this->_helperConfig->getGeneralConfigValue('category'),
            "label" => 'shipping_amount',
            "item_external_code" => 'shipping_amount',
            "quantity" => 1,
            "price" => $quote->getShippingAddress()->getTotals()['shipping']->getValue()
        ];
        $response = ["purchase" => [
            "external_reference_type" => "CMDE",
            "external_reference" => $order->getOrderIncrementId(),
            "purchase_amount" => $orderFullPrice,
            "currency_code" => $order->getCurrencyCode(),
            "number_of_items" => $qtyOrdered,
            "item_list" => $itemList,
            "delivery" => $delivery
        ],
            "merchant_request_id" => $order->getOrderIncrementId() . "_" . date('YmdHms')
        ];
        $this->_logger->info('Oney :: Purchase :', $response);
        return $response;
    }

    protected function getCarrierCode()
    {
        $code = $this->_checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
        return substr($code, 0, (int)(strlen($code) / 2)); // Cut the code in half like : flatrate_flatrate => flatrate
    }

    /**
     * @param string  $phone
     * @param integer $country
     *
     * @return int|null
     */
    protected function formatPhone($phone, $country)
    {

        $countryCode = $this->_helperConfig->getPhonePrefixByCountry($country);

        if (preg_match("/^\\+" . $countryCode . "/", $phone)) { //If international Format (+33XXXXXXXXXXX)
            return "00" . ltrim($phone, "+");
        }

        if (preg_match("/^00" . $countryCode . "/", $phone)) { //If international Format with 00 (0033XXXXXXXXXXX)
            return $phone;
        }

        return '00' . $countryCode . ltrim($phone, "0"); // If national format (0XXXXXXXXXX)
    }
}
