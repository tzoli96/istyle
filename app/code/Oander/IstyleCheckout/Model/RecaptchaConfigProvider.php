<?php

namespace Oander\IstyleCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Mageplaza\GoogleRecaptcha\Helper\Data as MageplazaHelperData;

class RecaptchaConfigProvider implements ConfigProviderInterface {
    /**
     * @var MageplazaHelperData
     */
    protected $mageplazaHelperData;

    /**
     * @param MageplazaHelperData $mageplazaHelperData
     */
    public function __construct(
        MageplazaHelperData $mageplazaHelperData
    ) {
        $this->mageplazaHelperData = $mageplazaHelperData;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['istyle_checkout']['get_invisible_key'] = $this->mageplazaHelperData->getInvisibleKey();
        $config['istyle_checkout']['get_position_frontend'] = $this->mageplazaHelperData->getPositionFrontend();
        $config['istyle_checkout']['is_captcha_frontend'] = $this->mageplazaHelperData->isCaptchaFrontend();
        $config['istyle_checkout']['get_language_code'] = $this->mageplazaHelperData->getLanguageCode();
        $config['istyle_checkout']['get_theme_frontend'] =  $this->mageplazaHelperData->getThemeFrontend();

        return $config;
    }
}
