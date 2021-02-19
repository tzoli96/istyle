<?php
namespace Oander\HelloBankPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session;
use Oander\HelloBankPayment\Enum\Attribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE                = 'hellobank';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigValueHandler
     */
    private $helloBankPaymentConfig;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * ConfigProvider constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     * @param Session $checkoutSession
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigValueHandler $helloBankPaymentConfig,
        StoreInterface $store,
        Session $checkoutSession
    ) {
        $this->productRepository = $productRepository;
        $this->helloBankPaymentConfig = $helloBankPaymentConfig;
        $this->store = $store;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'logoSrc' => $this->getLogoSrc()
                ],
            ]
        ];
    }

    /**
     * @return string
     */
    private function getLogoSrc(): string
    {
        return $this->helloBankPaymentConfig->getLogoSrc();
    }



}
