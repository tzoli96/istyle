<?php

declare(strict_types=1);

namespace Oander\ApplePay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\Quote\Address;
use Oander\ApplePay\Enum\ApplePayObjects\ApplePayPaymentContact;

/**
 * Class ApplePay
 * @package Oander\ApplePay\Helper
 */
class ApplePay extends AbstractHelper
{
    /**
     * @param $address Address
     * @param $applepaydata array
     */
    public function setAddressByApplePayData($address, $applepaydata)
    {
        $address->setCountryId($applepaydata[ApplePayPaymentContact::countryCode]);
        $address->setTelephone($applepaydata[ApplePayPaymentContact::phoneNumber]);
        $address->setStreet($applepaydata[ApplePayPaymentContact::addressLines]);
        $address->setEmail($applepaydata[ApplePayPaymentContact::emailAddress]);
        $address->setLastname($applepaydata[ApplePayPaymentContact::familyName]);
        $address->setFirstname($applepaydata[ApplePayPaymentContact::givenName]);
        $address->setPostcode($applepaydata[ApplePayPaymentContact::postalCode]);
        $address->setRegion($applepaydata[ApplePayPaymentContact::administrativeArea]);
        $address->setCity($applepaydata[ApplePayPaymentContact::locality]);
    }

    /**
     * @param $address Address
     * @return array
     */
    public function getAddressToApplePayData($address)
    {
        $result = [];
        $result[ApplePayPaymentContact::countryCode] = $address->getCountryId();
        $result[ApplePayPaymentContact::country] = $address->getCountry();
        $result[ApplePayPaymentContact::phoneNumber] = $address->getTelephone();
        $result[ApplePayPaymentContact::addressLines] = $address->getStreet();
        $result[ApplePayPaymentContact::emailAddress] = $address->getEmail();
        $result[ApplePayPaymentContact::familyName] = $address->getLastname();
        $result[ApplePayPaymentContact::givenName] = $address->getFirstname();
        $result[ApplePayPaymentContact::postalCode] = $address->getPostcode();
        $result[ApplePayPaymentContact::administrativeArea] = $address->getRegion();
        $result[ApplePayPaymentContact::locality] = $address->getCity();
        return $result;
    }
}