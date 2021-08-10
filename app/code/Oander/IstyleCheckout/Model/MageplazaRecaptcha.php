<?php

namespace Oander\IstyleCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Mageplaza\GoogleRecaptcha\Helper\Data as MageplazaHelperData;
use Oander\IstyleCheckout\Plugin\Mageplaza\GoogleRecaptcha\Model\System\Config\Source\Frontend\Forms;

class MageplazaRecaptcha implements ConfigProviderInterface
{
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
        $enabled = false;
        if ($this->mageplazaHelperData->isCaptchaFrontend()
            && in_array(Forms::TYPE_OANDER_CHECKOUT_FORGOT, $this->mageplazaHelperData->getFormsFrontend())
        ) {
            $enabled = true;
        }

        $config['mpRecaptcha'] = [
            'forgotPasswordEnabled' => $enabled,
            'sitekey' => $this->mageplazaHelperData->getInvisibleKey(),
            'theme' => $this->mageplazaHelperData->getThemeFrontend(),
            'position' => $this->mageplazaHelperData->getPositionFrontend(),
            'language' => $this->mageplazaHelperData->getLanguageCode()
        ];

        return $config;
    }
}
