<?php
namespace Oander\HelloBankPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE                = 'hellobank';

    /**
     * @var ConfigValueHandler
     */
    private $helloBankPaymentConfig;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * ConfigProvider constructor.
     *
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     */
    public function __construct(
        ConfigValueHandler $helloBankPaymentConfig,
        StoreInterface $store
    ) {
        $this->helloBankPaymentConfig = $helloBankPaymentConfig;
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'logoSrc' => $this->getLogoSrc(),
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    private function getLogoSrc()
    {
        return 'test';
    }

}
