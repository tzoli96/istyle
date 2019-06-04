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

        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                 ['payment']['children']['payments-list']['children'] as $key => $payment) {

            if (isset($payment['children']['form-fields']['children']['firstname'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['firstname']['sortOrder'] = 1;
            }

            if (isset($payment['children']['form-fields']['children']['lastname'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['lastname']['sortOrder'] = 2;
            }

            if (isset($payment['children']['form-fields']['children']['company'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['company']['sortOrder'] = 10;
            }

            if (isset($payment['children']['form-fields']['children']['vat_id'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['vat_id']['sortOrder'] = 11;
            }

            if (isset($payment['children']['form-fields']['children']['pfpj_reg_no'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['pfpj_reg_no']['sortOrder'] = 12;
            }

            if (isset($payment['children']['form-fields']['children']['postcode'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['postcode']['sortOrder'] = 13;
            }

            if (isset($payment['children']['form-fields']['children']['city'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city']['sortOrder'] = 14;
            }

            if (isset($payment['children']['form-fields']['children']['street'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['street']['sortOrder'] = 15;
            }

            if (isset($payment['children']['form-fields']['children']['country_id'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['country_id']['visible'] = false;
            }

            if (isset($payment['children']['form-fields']['children']['telephone'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['telephone']['sortOrder'] = 20;
            }

            if (isset($payment['children']['form-fields']['children']['dob'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['dob']['sortOrder'] = 21;
            }
        }

        return $jsLayout;
    }
}
