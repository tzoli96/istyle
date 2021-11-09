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
use Oander\FanCourierValidator\Helper\Data;
use Oander\IstyleCustomization\Enum\AddressAttributeEnum;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class LayoutProcessor
 * @package Oander\IstyleCustomization\Plugin\Checkout\Model\Checkout+
 */
class LayoutProcessor
{
    const BILLING_ONLY_FIELDS = [
        \Magento\Customer\Api\Data\AddressInterface::COMPANY,
        \Magento\Customer\Api\Data\AddressInterface::VAT_ID
    ];

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
     * @var Data
     */
    private $fanCourierHelper;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        Data $fanCourierHelper,
        Config $configHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
        $this->fanCourierHelper = $fanCourierHelper;
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


        foreach (self::BILLING_ONLY_FIELDS as $field) {
            if(isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"][$field])){
                $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"][$field]['config']['componentDisabled'] = true;
            }
        }


        if ($this->fanCourierHelper->getValidationLevel() == 'req' || $this->fanCourierHelper->getValidationLevel() == 'valid') {
            $label = (isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['region_id']['label']))
                ? $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]['region_id']['label']
                : __('Region');
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'customScope' => 'shippingAddress',
                ],
                'dataScope' => 'shippingAddress.region',
                'label' => $label,
                'provider' => 'checkoutProvider',
                'sortOrder' => 11,
                'placeholder' => __('region_placeholder'),
                'validation' => [
                    'required-entry' => true
                ],
                'visible' => true
            ];

            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['validation']['required-entry'] = true;
        }

        if ($this->fanCourierHelper->getValidationLevel() == 'valid') {
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region']['config']['elementTmpl'] = 'ui/form/element/select';
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region']['filterBy'] = null;
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region']['component'] = 'Magento_Ui/js/form/element/select';
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region']['options'] = $this->fanCourierHelper->getStates();

            /*$jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['config'] = $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['region']['config'];
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['filterBy'] = [
                'target' => '${ $.provider }:${ $.parentScope }.region',
                'field' => 'state'
            ];
            $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['component'] = 'Magento_Ui/js/form/element/select';
            /*$jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
            ['city']['options'] = $this->fanCourierHelper->getCities();*/
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

            if ($this->fanCourierHelper->getValidationLevel() == 'req' || $this->fanCourierHelper->getValidationLevel() == 'valid') {
                $dataScopePrefix = 'billingAddress';
                if (isset($payment['dataScopePrefix'])) {
                    $dataScopePrefix = $payment['dataScopePrefix'];
                } elseif (isset($payment['dataScope'])) {
                    $dataScopePrefix = $payment['dataScope'];
                }

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['region'] = $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
                ['region'];
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['region']['dataScope'] = $dataScopePrefix.'.'.'region';
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['region']['config']['customScope'] = $dataScopePrefix;

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city'] = $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
                ['city'];
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city']['dataScope'] = $dataScopePrefix.'.'.'city';
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['city']['config']['customScope'] = $dataScopePrefix;
            }

            $showPfpjRegNo = $this->scopeConfig->getValue('customer/address/show_pfpj_reg_no', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($showPfpjRegNo) {

                $showPfpjRegValidation = ($showPfpjRegNo == 'req') ? ['required-entry' => true] : [];

                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']
                ['pfpj_reg_no'] = [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddressshared',
                        'customEntry' => null,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                    ],
                    'dataScope' => 'billingAddressshared.custom_attributes.pfpj_reg_no',
                    'label' => __('Registration Number'),
                    'provider' => 'checkoutProvider',
                    'visible' => true,
                    'validation' => $showPfpjRegValidation,
                    'sortOrder' => 66,
                    'id' => 'pfpj_reg_no',
                    'options' => [],
                    'filterBy' => null,
                    'customEntry' => null
                ];
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

        $this->setAddressAttributesShortOrder($jsLayout);

        return $jsLayout;
    }


    /**
     * @param $jsLayout
     */
    protected function setAddressAttributesShortOrder(&$jsLayout)
    {
        $addressAttributesPositions = $this->configHelper->getAddressAttributePosition();

        if (isset($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]))
        {
            foreach ($jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"] as $shippingAddressAttributeCode => $shippingAddressAttributeValue)
            {
                if (isset($addressAttributesPositions[$shippingAddressAttributeCode][AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION]))
                {
                    $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
                    [$shippingAddressAttributeCode]['sortOrder'] = $addressAttributesPositions[$shippingAddressAttributeCode][AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION];
                }

                if (isset($addressAttributesPositions[$shippingAddressAttributeCode][AddressAttributeEnum::COLUMN_WIDTH]))
                {
                    if ($addressAttributesPositions[$shippingAddressAttributeCode][AddressAttributeEnum::COLUMN_WIDTH] == 100)
                    {
                        $additionalClasses = $shippingAddressAttributeValue['additionalClasses'] ?? '';
                        $additionalClasses .= "w-100";

                        $jsLayout["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]
                        [$shippingAddressAttributeCode]['additionalClasses'] = $additionalClasses;
                    }
                }
            }
        }


        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']))
        {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $paymentCode => $payment)
            {
                if (isset($payment['children']['form-fields']['children']))
                {
                    foreach ($payment['children']['form-fields']['children'] as $billingAttributeCode => $billingAttributeValue)
                    {
                        if (isset($addressAttributesPositions[$billingAttributeCode][AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION]))
                        {
                            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                            ['payment']['children']['payments-list']['children'][$paymentCode]['children']['form-fields']['children']
                            [$billingAttributeCode]['sortOrder'] = $addressAttributesPositions[$billingAttributeCode][AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION];
                        }
                    }
                }
            }
        }
    }
}
