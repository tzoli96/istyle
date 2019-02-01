<?php
namespace Oander\ApplePay\Model\Config\Source;

use \Oander\ApplePay\Enum\MerchantCapabilities as MerchantCapabilitiesEnum;

class MerchantCapabilities implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options[] = ['value' => MerchantCapabilitiesEnum::supports3DS, 'label' => __(MerchantCapabilitiesEnum::supports3DS)];
            $this->_options[] = ['value' => MerchantCapabilitiesEnum::supportsCredit, 'label' => __(MerchantCapabilitiesEnum::supportsCredit)];
            $this->_options[] = ['value' => MerchantCapabilitiesEnum::supportsDebit, 'label' => __(MerchantCapabilitiesEnum::supportsDebit)];
            $this->_options[] = ['value' => MerchantCapabilitiesEnum::supportsEMV, 'label' => __(MerchantCapabilitiesEnum::supportsEMV)];
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}
