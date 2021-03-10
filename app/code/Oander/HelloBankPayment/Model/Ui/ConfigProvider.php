<?php
namespace Oander\HelloBankPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session;
use Oander\HelloBankPayment\Enum\Attribute;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;
use Oander\HelloBankPayment\Helper\BaremCheck;
use Oander\HelloBankPayment\Gateway\Config as ConfigHelper;


class ConfigProvider implements ConfigProviderInterface
{
    const CODE                = 'hellobank';

    /**
     * @var Collection
     */
    private $baremCollection;

    /**
     * @var ConfigValueHandler
     */
    private $helloBankPaymentConfig;

    /**
     * @var BaremRepositoryInterface
     */
    private $baremRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var BaremCheck
     */
    private $baremCheck;

    private $barems = [];

    /**
     * ConfigProvider constructor.
     *
     * @param BaremCheck $baremCheck
     * @param Collection $baremCollection
     * @param BaremRepositoryInterface $baremRepository
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     * @param Session $checkoutSession
     */
    public function __construct(
        BaremCheck $baremCheck,
        Collection $baremCollection,
        BaremRepositoryInterface $baremRepository,
        ConfigValueHandler $helloBankPaymentConfig,
        StoreInterface $store,
        Session $checkoutSession
    ) {
        $this->baremCheck = $baremCheck;
        $this->baremCollection = $baremCollection;
        $this->baremRepository = $baremRepository;
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
                    'isAcitve'  => $this->getAcitve(),
                    'logoSrc'   => $this->getLogoSrc(),
                    'barems'    => $this->getBarems(),
                    'response' => [
                        ConfigHelper::HELLOBANK_REPONSE_TYPE_OK => __('Application approved automatically (OK)'),
                        ConfigHelper::HELLOBANK_REPONSE_TYPE_KO => __('Application subject to further review (KO)')
                    ]

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

    /**
     * @return string
     */
    private function getAcitve(): string
    {
        return $this->helloBankPaymentConfig->getIsActive();
    }

    /**
     * @return array
     */
    private function getBarems()
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        $grandTotal=$quote->getGrandTotal();
        return $this->baremCheck->fillterTotal($items, $grandTotal);
    }



}
