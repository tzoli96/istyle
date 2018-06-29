<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_IstyleCustomization
 *
 * @author  Janos Pinczes <janos.pinczes.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Checkout\Model\Checkout;


class LayoutProcessor
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
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
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    )
    {
        $showPfpjRegNo = $this->scopeConfig->getValue('customer/address/show_pfpj_reg_no', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($showPfpjRegNo == 'req')
        {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['pfpj_reg_no'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => 'shippingAddress.pfpj_reg_no',
                'label' => __('Registration Number'),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => ['required-entry' => true],
                'sortOrder' => 66,
                'id' => 'pfpj_reg_no',
                'options' => [],
                'filterBy' => null,
                'customEntry' => null
            ];
        }
        elseif ($showPfpjRegNo == 'opt')
        {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['pfpj_reg_no'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => 'shippingAddress.pfpj_reg_no',
                'label' => __('Registration Number'),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 66,
                'id' => 'pfpj_reg_no',
                'options' => [],
                'filterBy' => null,
                'customEntry' => null
            ];
        }

        return $jsLayout;
    }
}