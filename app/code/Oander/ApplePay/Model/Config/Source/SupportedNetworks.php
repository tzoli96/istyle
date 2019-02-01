<?php
namespace Oander\ApplePay\Model\Config\Source;

use \Oander\ApplePay\Enum\SupportedNetworks as SupportedNetworksEnum;

class SupportedNetworks implements \Magento\Framework\Option\ArrayInterface
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
            $this->_options[] = ['value' => SupportedNetworksEnum::amex, 'label' => __(SupportedNetworksEnum::amex)];
            $this->_options[] = ['value' => SupportedNetworksEnum::cartesBancaires, 'label' => __(SupportedNetworksEnum::cartesBancaires)];
            $this->_options[] = ['value' => SupportedNetworksEnum::chinaUnionPay, 'label' => __(SupportedNetworksEnum::chinaUnionPay)];
            $this->_options[] = ['value' => SupportedNetworksEnum::discover, 'label' => __(SupportedNetworksEnum::discover)];
            $this->_options[] = ['value' => SupportedNetworksEnum::eftpos, 'label' => __(SupportedNetworksEnum::eftpos)];
            $this->_options[] = ['value' => SupportedNetworksEnum::electron, 'label' => __(SupportedNetworksEnum::electron)];
            $this->_options[] = ['value' => SupportedNetworksEnum::interac, 'label' => __(SupportedNetworksEnum::interac)];
            $this->_options[] = ['value' => SupportedNetworksEnum::jcb, 'label' => __(SupportedNetworksEnum::jcb)];
            $this->_options[] = ['value' => SupportedNetworksEnum::maestro, 'label' => __(SupportedNetworksEnum::maestro)];
            $this->_options[] = ['value' => SupportedNetworksEnum::masterCard, 'label' => __(SupportedNetworksEnum::masterCard)];
            $this->_options[] = ['value' => SupportedNetworksEnum::privateLabel, 'label' => __(SupportedNetworksEnum::privateLabel)];
            $this->_options[] = ['value' => SupportedNetworksEnum::visa, 'label' => __(SupportedNetworksEnum::visa)];
            $this->_options[] = ['value' => SupportedNetworksEnum::vPay, 'label' => __(SupportedNetworksEnum::vPay)];
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}
