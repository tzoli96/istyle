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
            'barem'     => $this->config->getConstructionGroup(),
            'amount'    => $this->checkoutSession->getQuote()->getGrandTotal(),
            'downpmnt'  => '0',
            'pre_evaluation' => '0',
            'size'      => 'small',
        );

        $barems = array(
            131 => array(
                10 => array(
                    'intervalMin' => 40000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
                12 => array(
                    'intervalMin' => 40000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
                15 => array(
                    'intervalMin' => 50000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
                20 => array(
                    'intervalMin' => 60000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
                24 => array(
                    'intervalMin' => 80000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                )
            ),
            12 => array(
                10 => array(
                    'intervalMin' => 40000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
            ),
            51 => array(
                12 => array(
                    'intervalMin' => 40000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
            ),
            93 => array(
                15 => array(
                    'intervalMin' => 50000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
            ),
            48 => array(
                20 => array(
                    'intervalMin' => 60000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
            ),
            49 => array(
                24 => array(
                    'intervalMin' => 80000,
                    'intervalMax' => 1500000,
                    'intervalThm' => 0,
                    'intervalRate' => 0,
                ),
            ),
        );

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
