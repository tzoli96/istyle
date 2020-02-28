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

use \Magento\Checkout\Model\Session as CheckoutSession;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class LayoutProcessor
 * @package Oander\IstyleCustomization\Plugin\Checkout\Model\Checkout+
 */
class LayoutProcessor
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        Config $configHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {

        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
        $dobShow = $this->configHelper->getDobShow($quoteItems);

        $showPfpjRegNo = $this->scopeConfig->getValue('customer/address/show_pfpj_reg_no', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($showPfpjRegNo == 'req') {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['pfpj_reg_no'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => 'shippingAddress.custom_attributes.pfpj_reg_no',
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
        } elseif ($showPfpjRegNo == 'opt') {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['pfpj_reg_no'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'dataScope' => 'shippingAddress.custom_attributes.pfpj_reg_no',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
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
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['firstname']['placeholder'] = __('firstname_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['lastname'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['lastname']['sortOrder'] = 2;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['lastname']['placeholder'] = __('lastname_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['company'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['company']['sortOrder'] = 10;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['company']['placeholder'] = __('company_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['vat_id'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['vat_id']['sortOrder'] = 11;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['vat_id']['placeholder'] = __('vat_id_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['pfpj_reg_no'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['pfpj_reg_no']['sortOrder'] = 12;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['pfpj_reg_no']['placeholder'] = __('pfpj_reg_no_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['postcode'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['postcode']['sortOrder'] = 13;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['postcode']['placeholder'] = __('postcode_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['city'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city']['sortOrder'] = 14;
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city']['placeholder'] = __('city_placeholder');
            }

            if (isset($payment['children']['form-fields']['children']['street'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['street']['sortOrder'] = 15;

                //START STREETS
                foreach($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                        ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                        ['street']["children"] as $i => &$streetComponent) {
                    $streetComponent['placeholder'] = __('street_' . $i . '_placeholder');
                }
                //END STREETS
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
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['telephone']['placeholder'] = __('telephone_placeholder');
            }


            if (isset($payment['children']['form-fields']['children']['dob'])) {
                $dobField = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['dob'];

                $dobField['sortOrder'] = 21;
                $dobField['placeholder'] = __('dob_placeholder');
                $dobField['customScope'] = 'shippingAddress.custom_attributes';
                $dobField['dataScope'] = 'shippingAddress.custom_attributes.dob';
                $dobField['visible'] = true;
                $dobField['options']['changeMonth'] = true;
                $dobField['options']['changeYear'] = true;
                $dobField['options']['maxDate'] = '-1d';
                $dobField['options']['yearRange'] = '-100y:c+nn';
                $dobField['validation']['required-entry'] = false;

                if ($dobShow === null) {
                    $dobField['visible'] = false;
                } elseif ($dobShow === 'req') {
                    $dobField['validation']['required-entry'] = true;
                }

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['dob'] = $dobField;
            }
        }

        //Change Shipping Address
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['firstname']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['firstname']['sortOrder'] = 1;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['firstname']['placeholder'] = __('firstname_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['lastname']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['lastname']['sortOrder'] = 2;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['lastname']['placeholder'] = __('lastname_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['company']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['company']['sortOrder'] = 10;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['company']['placeholder'] = __('company_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['vat_id']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['vat_id']['sortOrder'] = 11;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['vat_id']['placeholder'] = __('vat_id_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['pfpj_reg_no']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['pfpj_reg_no']['sortOrder'] = 12;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['pfpj_reg_no']['placeholder'] = __('pfpj_reg_no_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['postcode']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['postcode']['sortOrder'] = 13;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['postcode']['placeholder'] = __('postcode_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['sortOrder'] = 14;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['placeholder'] = __('city_placeholder');
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['street']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['street']['sortOrder'] = 15;

            //START STREETS
            foreach($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
                    ['street']["children"] as $i => &$streetComponent) {
                $streetComponent['placeholder'] = __('street_' . $i . '_placeholder');
            }
            //END STREETS
        }

        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['country_id']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['country_id']['visible'] = false;
        }
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['telephone']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['telephone']['sortOrder'] = 20;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['telephone']['placeholder'] = __('telephone_placeholder');
        }


        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['dob']))
        {
            $dobField = $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]
            ["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['dob'];

            $dobField['sortOrder'] = 21;
            $dobField['placeholder'] = __('dob_placeholder');
            $dobField['customScope'] = 'shippingAddress.custom_attributes';
            $dobField['dataScope'] = 'shippingAddress.custom_attributes.dob';
            $dobField['visible'] = true;
            $dobField['options']['changeMonth'] = true;
            $dobField['options']['changeYear'] = true;
            $dobField['options']['maxDate'] = '-1d';
            $dobField['options']['yearRange'] = '-100y:c+nn';
            $dobField['validation']['required-entry'] = false;

            if ($dobShow === null) {
                $dobField['visible'] = false;
            } elseif ($dobShow === 'req') {
                $dobField['validation']['required-entry'] = true;
            }

            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]
            ["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['dob'] = $dobField;
        }


        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region_id']))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region_id']['visible'] = false;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region_id']['config']['componentDisabled'] = true;
        }

        //START COMMENT
        if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ["oander-order-comment-form-container"]["children"]["oander-order-comment-form-fieldset"]["children"]["comment"]))
        {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ["oander-order-comment-form-container"]["children"]["oander-order-comment-form-fieldset"]["children"]["comment"]["placeholder"] = __('comment_placeholder');
        }
        //END COMMENT


        return $jsLayout;
    }
}
