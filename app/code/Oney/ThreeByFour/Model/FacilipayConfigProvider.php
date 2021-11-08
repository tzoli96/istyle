<?php

namespace Oney\ThreeByFour\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Helper\Config;

class FacilipayConfigProvider implements ConfigProviderInterface
{
    const REDIRECT_URL = "facilypay/place/payment";
    /**
     * @var BusinessTransactionsInterface
     */
    protected $_businessTransactions;
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var Config
     */
    protected $_helperConfig;

    public function __construct(
        BusinessTransactionsInterface $businessTransactions,
        UrlInterface $_urlBuilder,
        Config $helperConfig
    )
    {
        $this->_helperConfig = $helperConfig;
        $this->_businessTransactions = $businessTransactions;
        $this->_urlBuilder = $_urlBuilder;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $businessTransactions = $this->_businessTransactions->getActiveBusinessTransactions();
        $facilypay_methods = [];
        $config = [];
        $country_code = $this->_helperConfig->addCountryCodeTranslation();

        foreach ($businessTransactions as $key => $businessTransaction) {
            $facilypay_methods[] = [
                'type' => $key,
                'component' => 'Oney_ThreeByFour/js/view/payment/payment-method'
            ];
            $config['payment']['oney_facilypay'][$key] = [
                'title' => strtoupper($businessTransaction['title']),
                'number' => $businessTransaction['number_of_instalments']
            ];
        }
        $config['payment']['oney_facilypay']['redirect_url'] = $this->_urlBuilder->getUrl(self::REDIRECT_URL);
        $config['payment']['oney_facilypay']['facilypay_methods'] = $facilypay_methods;
        $config['payment']['oney_facilypay']['phone'] = $this->_helperConfig->getCountrySpecificationsConfigValue('phone');
        $config['payment']['oney_facilypay']['postal'] = $this->_helperConfig->getCountrySpecificationsConfigValue('postal');
        $config['payment']['oney_facilypay']['country'] = $this->_helperConfig->getConfigValue("general/country/default");
        $config['payment']['oney_facilypay']['use_tin'] = $this->_helperConfig->getCountrySpecificationsConfigValue('use_tin');
        $config['payment']['oney_facilypay']['error'] = [
            "postal" => __("Wrong postal for Oney".$country_code),
            "phone" => __("Wrong phone for Oney".$country_code),
            "country" => __("Wrong country for Oney".$country_code)
        ];
        $config['payment']['oney_facilypay']['translate'] = [
            "Payment %1 :" => __('Payment %1 :'.$country_code),
            "Simulation Text" => __('oney_simulation_text'.$country_code),
            "Legal Text" => __('legal_text_payment'.$country_code),
            "And a commission of" => __('And a commission of'.$country_code),
        ];

        if ($this->_helperConfig->isLegalEnabled() == true && $this->_helperConfig->isCreditIntermediary() === false) {
            $config['payment']['oney_facilypay']['translate']['Legal Text'] = __('not_exclusive_text_payment'.$country_code);
            $config['payment']['oney_facilypay']['translate']['Simulation Text'] = '';
        }

        return $config;
    }
}
