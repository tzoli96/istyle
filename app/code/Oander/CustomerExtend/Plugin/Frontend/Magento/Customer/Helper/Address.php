<?php

namespace Oander\CustomerExtend\Plugin\Frontend\Magento\Customer\Helper;

/**
 * Class Address. Plugin to add classes for address fields in profile address edit.
 * @package Oander\SimpleVATNumberValidation\Plugin\Frontend\Magento\Customer\Helper
 */
class Address
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Customer\Helper\Address $subject
     * @param \Closure $proceed
     * @param string $attributeCode
     * @return mixed|string
     */
    public function aroundGetAttributeValidationClass(
        \Magento\Customer\Helper\Address $subject,
        \Closure $proceed,
        $attributeCode
    ) {
        $result = $proceed($attributeCode);
        if($attributeCode==='company') {
            $this->addClass($result, "required-entry");
        }
        if($attributeCode==='vat_id') {
            if($this->scopeConfig->isSetFlag('customer/address/taxvat_profile_checkout_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->addClass($result, "required-entry");
            }
        }
        if($attributeCode==='pfpj_reg_no') {
            if ($this->scopeConfig->getValue('customer/address/show_pfpj_reg_no', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'req') {
                $this->addClass($result, "required-entry");
            }
        }
        return $result;
    }

    private function addClass(&$classes, $newClass) {
        if ($classes == "")
            $classes = $newClass;
        else
            $classes .= " " . $newClass;
    }
}