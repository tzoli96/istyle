<?php

namespace Pgc\Pgc\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Pgc\Pgc\Helper\Data;

final class ConfigProvider implements ConfigProviderInterface
{
    const CREDITCARD_CODE = 'pgc_creditcard';

    /**
     * @var Data
     */
    private $pgcHelper;

    public function __construct
    (
        Data $pgcHelper
    )
    {
        $this->pgcHelper = $pgcHelper;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                static::CREDITCARD_CODE => [
                    'title' => $this->pgcHelper->getPaymentConfigData('title', 'pgc_creditcard'),
                    'instructions' => $this->pgcHelper->getPaymentConfigData('instructions', 'pgc_creditcard'),
                    'seamless' => $this->pgcHelper->getPaymentConfigDataFlag('seamless', static::CREDITCARD_CODE),
                    'integration_key' => $this->pgcHelper->getPaymentConfigDataFlag('integration_key', static::CREDITCARD_CODE)
                ]
            ],
        ];
    }
}
