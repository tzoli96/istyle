<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Frontend\Magento\Customer\Block\Address;

use Oander\CustomerExtend\Enum\Config as ConfigEnum;
use Oander\CustomerExtend\Helper\Config;

class Edit
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_url;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Oander\AddressListAPI\Api\GetCityInterface
     */
    private $getCity;

    private $regions = null;
    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param Config $config
     * @param \Oander\AddressListAPI\Api\GetCityInterface $getCity
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url,
        \Oander\CustomerExtend\Helper\Config $config,
        \Oander\AddressListAPI\Api\GetCityInterface $getCity
    )
    {
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->getCity = $getCity;
        $this->config = $config;
    }

    public function around__call(
        \Magento\Customer\Block\Address\Edit $subject,
        \Closure $proceed,
        $method,
        $args
    ) {
        if($method=="getCitiesAjaxUrl")
        {
            return $this->_url->getUrl("/rest/V1/oander/addresslist/getCityByRegion/");
        }
        if($method=="getReplacePostcodeRegion")
        {
            return $this->_scopeConfig->isSetFlag(ConfigEnum::PATH_CUSTOMER_REPLACE_POSTCODE_REGION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        if($method=="getRegionCollection")
        {
            if( $this->regions === null )
                $this->regions = $this->getCity->getAllRegion();
            return $this->regions;
        }
        $result = $proceed($method, $args);
        return $result;
    }



    public function aroundGetCitiesAjaxUrl(
        \Magento\Customer\Block\Address\Edit $subject,
        \Closure $proceed
    ) {
        return $this->_url->getUrl("/rest/V1/oander/addresslist/getCityByRegion/");
    }

    public function aroundGetReplacePostcodeRegion(
        \Magento\Customer\Block\Address\Edit $subject,
        \Closure $proceed
    ) {
        return $this->_scopeConfig->isSetFlag(ConfigEnum::PATH_CUSTOMER_REPLACE_POSTCODE_REGION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function aroundGetRegionCollection(
        \Magento\Customer\Block\Address\Edit $subject,
        \Closure $proceed
    ) {
        if( $this->regions === null )
            $this->regions = $this->getCity->getAllRegion();
        return $this->regions;
    }

    public function beforeToHtml(
        \Magento\Customer\Block\Address\Edit $subject
    ) {
        $subject->setData('address_attributes_positions', $this->config->getAddressAttributePosition());
        return [];
    }
}