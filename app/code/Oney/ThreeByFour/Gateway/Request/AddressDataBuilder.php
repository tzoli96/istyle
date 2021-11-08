<?php

namespace Oney\ThreeByFour\Gateway\Request;

use Magento\Checkout\Model\Session;
use Magento\Framework\Filter\FilterManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Directory\Model\Country;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;

class AddressDataBuilder implements BuilderInterface
{
    const MAX_LENGTH = 38;
    const PICKUP = 1;
    const RELAY_POINT = 2;

    /**
     * @var Country
     */
    protected $_country;
    /**
     * @var FilterManager
     */
    protected $_filterManager;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        Country $country,
        FilterManager $filterManager,
        Session $checkoutSession,
        Config $config,
        Logger $logger
    )
    {
        $this->_filterManager = $filterManager;
        $this->_country = $country;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $billing_address = $payment->getOrder()->getBillingAddress();
        $shipping_address = $payment->getOrder()->getShippingAddress();
        $carrier_code = $this->getCarrierCode();
        $shipping_mode = $this->config->getCarrierConfig($carrier_code, 'deliverymodecode');

        $customer_address = [
            "postal_code" => $billing_address->getPostcode(),
            "municipality" => $billing_address->getCity(),
            "country_code" => $this->_country->loadByCode($billing_address->getCountryId())->getData('iso3_code'),
            "country_label" => $this->_country->loadByCode($billing_address->getCountryId())->getName()
        ];
        $delivery_address = [
            "postal_code" => $shipping_address->getPostcode(),
            "municipality" => $shipping_address->getCity(),
            "country_code" => $this->_country->loadByCode($shipping_address->getCountryId())->getData('iso3_code'),
            "country_label" => $this->_country->loadByCode($shipping_address->getCountryId())->getName()
        ];
        $delivery_address_string = '';
        $storename = '';
        switch ($shipping_mode) {
            case self::PICKUP:
                $storename = $this->config->getConfigValue('general/store_information/name') ?? "Pickup Store";
                $storename .= ' -';
                $delivery_address_string .= $storename.$this->config->getConfigValue('general/store_information/street_line1');
                if ($this->config->getConfigValue('general/store_information/street_line2') !== null) {
                    $delivery_address_string .= ' -'.$this->config->getConfigValue('general/store_information/street_line2');
                }
                $delivery_address = [
                    "postal_code" => $this->config->getConfigValue('general/store_information/postcode'),
                    "municipality" => $this->config->getConfigValue('general/store_information/city'),
                    "country_code" => $this->_country->loadByCode(
                        $this->config->getConfigValue('general/store_information/country_id')
                    )->getData('iso3_code'),
                    "country_label" => $this->_country->loadByCode(
                        $this->config->getConfigValue('general/store_information/country_id')
                    )->getName()
                ];
                break;
            case self::RELAY_POINT:
                $storename = $shipping_address->getCompany() ?? "Relay Point";
                $storename .= ' -';
                break;
        }

            $delivery_address_string .= $storename.$this->config->getConfigValue('general/store_information/street_line1');
            if ($this->config->getConfigValue('general/store_information/street_line2') !== null) {
                $delivery_address_string .= ' -'.$this->config->getConfigValue('general/store_information/street_line2');
            }
        if ($shipping_mode != self::PICKUP) {

            $delivery_address_string .= $storename.$shipping_address->getStreetLine1();
            if ($shipping_address->getStreetLine2() !== null) {
                $delivery_address_string .= ' -'.$shipping_address->getStreetLine2();
            }
        }
        $delivery_address = array_merge($this->cutString($delivery_address_string, 38), $delivery_address);

        $customer_address_string = $billing_address->getStreetLine1();
        if ($billing_address->getStreetLine2() !== null) {
            $customer_address_string .= $billing_address->getStreetLine2();
        }

        $customer_address = array_merge($this->cutString($customer_address_string, 38), $customer_address);

        $this->logger->info("Address Builder :: ", [
            "customer" => [
                "customer_address" => $customer_address
            ],
            "purchase" => [
                "delivery" => [
                    "delivery_address" => $delivery_address
                ]
            ]]);

        return [
            "customer" => [
                "customer_address" => $customer_address
            ],
            "purchase" => [
                "delivery" => [
                    "delivery_address" => $delivery_address
                ]
            ]
        ];
    }

    protected function getCarrierCode()
    {
        $code = $this->checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();
        return substr($code, 0, (int)(strlen($code) / 2)); // Cut the code in half like : flatrate_flatrate => flatrate
    }

    protected function cutString($string, $length, $format = [])
    {
        if (strlen($string) > $length) {
            if (strlen($string) > $length && $string[$length] == ' ') {
                $pos = $length;
            } else {
                $pos = strrpos(substr($string, 0, $length), ' ');
            }
            $key = count($format)+1;
            $format['line'.$key] = trim(substr($string, 0, $pos));
            $string = substr($string, $pos+1);
            if (strlen($string) > 0) {
                $format = $this->cutString($string, $length, $format);
            }
        } else if (strlen($string) > 0) {
            $key = count($format)+1;
            $format['line'.$key] = trim(substr($string, 0));
        }

        return $format;
    }

}
