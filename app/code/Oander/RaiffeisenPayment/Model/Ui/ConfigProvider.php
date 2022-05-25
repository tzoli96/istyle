<?php
namespace Oander\RaiffeisenPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Checkout\Model\Session;
use Oander\RaiffeisenPayment\Helper\Config;
use Oander\RaiffeisenPayment\Gateway\Config\ConfigValueHandler;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE                = 'raiffeisen';
    /**
     * @var Config
     */
    private $configData;
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ConfigValueHandler
     */
    private $configValueHandler;

    /**
     * @param StoreInterface $store
     * @param Session $checkoutSession
     * @param Config $configData
     * @param ConfigValueHandler $configValueHandler
     */
    public function __construct(
        StoreInterface $store,
        Session $checkoutSession,
        Config $configData,
        ConfigValueHandler $configValueHandler
    ) {
        $this->configData = $configData;
        $this->store = $store;
        $this->checkoutSession = $checkoutSession;
        $this->configValueHandler = $configValueHandler;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'isAcitve'  => $this->getAcitve(),
                    'eligibilityquestions' => $this->configData->getEligibilityQuestions(),
                    'logoSrc'   => $this->configValueHandler->getLogoSrc(),
                ],
            ]
        ];
    }


    /**
     * @return string
     */
    private function getAcitve(): string
    {
        return $this->configData->getPaymentMethodIsActive();
    }

}
