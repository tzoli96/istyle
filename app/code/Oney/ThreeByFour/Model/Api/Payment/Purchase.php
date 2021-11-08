<?php

namespace Oney\ThreeByFour\Model\Api\Payment;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\Country;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Api\Payment\PurchaseInterface;
use Oney\ThreeByFour\Helper\Config as HelperConfig;
use Oney\ThreeByFour\Logger\Logger;
use Oney\ThreeByFour\Model\Api\ApiAbstract;

class Purchase extends ApiAbstract implements PurchaseInterface
{
    /**
     * @var Country
     */
    protected $_country;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Purchase constructor.
     *
     * @param Curl                        $client
     * @param HelperConfig                $config
     * @param Logger                      $logger
     * @param ManagerInterface            $messageManager
     * @param Country                     $country
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface                $urlBuilder
     */
    public function __construct(
        Curl $client,
        HelperConfig $config,
        Logger $logger,
        ManagerInterface $messageManager,
        Country $country,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlBuilder
    )
    {
        $this->_country = $country;
        $this->_urlBuilder = $urlBuilder;
        $this->_customerRepository = $customerRepository;
        parent::__construct($client, $config, $logger, $messageManager);
    }

    public function purchase(OrderInterface $order)
    {
        $this->setParams($this->buildParams($order));

        $this->addHeader('X-Oney-Authorization', $this->_helperConfig->getGeneralConfigValue('api_payment'))
            ->addHeader('X-Oney-Partner-Country-Code', 'SP')
            ->addHeader('X-Oney-Secret', 'None');

        return $this->call(
            "POST",
            $this->_helperConfig->getUrlForStep('purchase')
        );
    }

    protected function buildParams(OrderInterface $order)
    {
        $carrier_code = explode("_", $order->getShippingMethod())[0];
        $mode_shipping = $this->_helperConfig->getCarrierConfig($carrier_code, 'deliverymodecode');
        $shipping_address = $order->getShippingAddress();
        $billing_address = $order->getBillingAddress();

        $itemList = [];
        $maxPrice = 0;
        $mainItemKey = 0;
        $qtyOrdered = 1;

        foreach ($order->getItems() as $item){
            /** @var  \Magento\Sales\Model\Order\Item $item */
            if($maxPrice < $item->getPrice()){
                $maxPrice = $item->getPrice();
                $mainItemKey = count($itemList);
            }
            $itemList[] = [
                "is_main_item" => 0,
                "category_code" => 4,
                "label" => $item->getName(),
                "item_external_code" => $item->getSku(),
                "quantity" => (int)$item->getQtyOrdered(),
                "price" => $item->getPrice()
            ];
            $qtyOrdered += $item->getQtyOrdered();
        }

        $itemList[$mainItemKey]['is_main_item'] = 1;
        $itemList[] = [
            "is_main_item" => 0,
            "category_code" => 4,
            "label" => 'shipping_amount',
            "item_external_code" => 'shipping_amount',
            "quantity" => 1,
            "price" => $order->getShippingAmount()
        ];

        $delivery_address = [
            "postal_code" => $shipping_address->getPostCode(),
            "municipality" => $shipping_address->getCity(),
            "country_code" => $this->_country->loadByCode($shipping_address->getCountryId())->getData('iso3_code'),
            "country_label" => $this->_country->loadByCode($shipping_address->getCountryId())->getName()
        ];

        $this->streetToLine($delivery_address, $shipping_address->getStreet(), $mode_shipping);

        $delivery = [
            "delivery_date" => date('Y-m-d', strtotime('+7 days')),
            "delivery_mode_code" => $mode_shipping,
            "delivery_option" => $this->_helperConfig->getCarrierConfig($carrier_code, 'deliveryoption'),
            "address_type" => $this->_helperConfig->getCarrierConfig($carrier_code, 'addresstype'),
            "priority_delivery_code" => $this->_helperConfig->getCarrierConfig($carrier_code, 'prioritydeliverycode'),
            "delivery_address" => $delivery_address
        ];

        $purchase = [
            "external_reference_type" => "CMDE",
            "external_reference" => $order->getIncrementId(),
            "purchase_amount" => $order->getGrandTotal(),
            "currency_code" => $order->getOrderCurrencyCode(),
            "delivery" => $delivery,
            "number_of_items" => $qtyOrdered,
            "item_list" => $itemList
        ];

        $customer_address = [
            "postal_code" => $billing_address->getPostcode(),
            "municipality" => $billing_address->getCity(),
            "country_code" => $this->_country->loadByCode($billing_address->getCountryId())->getData('iso3_code'),
            "country_label" => $this->_country->loadByCode($billing_address->getCountryId())->getName()
        ];

        $this->streetToLine($customer_address,$billing_address->getStreet(),$mode_shipping);

        $customer = [
            "customer_external_code" => $order->getIncrementId(),
            "language_code" => $this->_helperConfig->getLanguageCode(),
            "identity" => array(
                "person_type" => 2,
                "honorific_code" => $this->getGender($order),
                "birth_name" => $order->getCustomerLastname(),
                "first_name" => $order->getCustomerFirstname()
            ),
            "contact_details" => array(
                "mobile_phone_number" => $billing_address->getTelephone(),
                "email_address" => $order->getCustomerEmail()
            ),
            "customer_address" => $customer_address
        ];

        $payment = [
            "payment_amount" => $order->getGrandTotal(),
            "currency_code" => $order->getOrderCurrencyCode(),
            "business_transaction" => [
                "code" => str_replace('facilypay_', '', $order->getPayment()->getMethod())
            ]
        ];

        $navigation = [
            "success_url" => $this->_urlBuilder->getUrl("checkout/onepage/success"),
            "fail_url" => $this->_urlBuilder->getUrl("checkout/onepage/success"),
            "server_response_url" => $this->_urlBuilder->getUrl("facilypay/payment/callback")
        ];

        return [
            "purchase" => $purchase,
            "psp_guid" => $this->_helperConfig->getGeneralConfigValue("psp_guid"),
            "merchant_guid" => $this->_helperConfig->getGeneralConfigValue("merchant_guid"),
            "customer" => $customer,
            "navigation" => $navigation,
            "payment" => $payment,
            "merchant_request_id" => $order->getIncrementId() . "_" . date('YmdHms')
        ];
    }

    protected function getGender(OrderInterface $order) {
        if($order->getCustomerGender() == 3){
            return 0;
        }
        return $order->getCustomerGender();
    }

    protected function verifyPhone() {
        return true;
    }

    protected function verifyPostalCode() {
        return true;
    }

    protected function streetToLine(&$delivery_address, $streets = [], $mode_shipping = 1) {
        $delivery_address_full = "";

        foreach ($streets as $key => $line) {
            $delivery_address_full .= $line;
        }

        $name = array();
        if ($mode_shipping == 1 && strpos($delivery_address_full, '/') !== false) {
            $name = explode('/', $delivery_address_full);
            $delivery_address_full = str_replace($name[0].'/', '', $delivery_address_full);
        }

        $delivery_address_split = str_split($delivery_address_full,38);

        if ($mode_shipping == 1 && count($name) > 1) {
            array_unshift($delivery_address_split, $name[0]);
        }

        foreach ($delivery_address_split as $key => $line) {
            $delivery_address['line'. ($key+1)] = $line;
        }
    }
}
