<?php

namespace Oander\IstyleCheckout\Plugin\Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend;

class Forms
{
    const TYPE_OANDER_CHECKOUT_FORGOT = 'oander-checkout-forgot';
    const TYPE_OANDER_SUCCESS_CREATE_USER = 'oander-success-create-user';

    /**
     * @param \Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend\Forms $subject
     * @param $result
     * @return mixed
     */
    public function afterGetOptionHash(
        \Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend\Forms $subject,
        $result
    ) {
        $result[self::TYPE_OANDER_CHECKOUT_FORGOT] = __('Checkout Forgot Password');
        $result[self::TYPE_OANDER_SUCCESS_CREATE_USER] = __('Success Page Create User');

        return $result;
    }

    /**
     * @param \Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend\Forms $subject
     * @param $result
     */
    public function afterDefaultForms(
        \Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend\Forms $subject,
        $result
    ) {
        $result[self::TYPE_OANDER_CHECKOUT_FORGOT] = 'rest/hu_hu/V1/new_checkout/forgetpassword';
        $result[self::TYPE_OANDER_SUCCESS_CREATE_USER] = 'istylecheckout/account/create';

        return $result;
    }
}
