<?php

namespace Oander\CofidisPayment\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Oander\CofidisPayment\Helper\Config;
use Oander\CofidisPayment\Helper\Data;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'cofidis';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Config $config,
        Data $helper,
        CheckoutSession $checkoutSession
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $postdata = array(
            'termsUrl'  => $this->config->getTermsUrl(),
            'shopId'    => $this->config->getShopId(),
            'amount'    => $this->checkoutSession->getQuote()->getGrandTotal(),
            'downpmnt'  => '0',
            'pre_evaluation' => '0',
            'size'      => 'small',
        );

        $barems = array();
        foreach ($this->config->getOwnshares() as $id => $ownshare)
        {
            if($this->helper->isAllowedByMinimumTotalAmount($this->checkoutSession->getQuote()->getGrandTotal(), $ownshare))
                array_push($barems, $ownshare);
        }

        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isEnabled(),
                    'redirectUrl' => $this->helper->getRedirectUrl($this->config->getRedirectPath()),
                    'instructions' => $this->config->getInstructions(),
                    "params" => http_build_query($postdata),
                    "data" => $postdata,
                    "barems" => $barems
                ],
            ],
        ];
    }
}
