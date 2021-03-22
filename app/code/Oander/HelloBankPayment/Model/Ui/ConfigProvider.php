<?php
namespace Oander\HelloBankPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session;
use Oander\HelloBankPayment\Enum\Attribute;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;
use Oander\HelloBankPayment\Helper\BaremCheck;
use Oander\HelloBankPayment\Gateway\Config as ConfigHelper;


class ConfigProvider implements ConfigProviderInterface
{
    const CODE                = 'hellobank';

    /**
     * @var CollectionFactory
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
     * @param CollectionFactory $baremCollection
     * @param BaremRepositoryInterface $baremRepository
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     * @param Session $checkoutSession
     */
    public function __construct(
        BaremCheck $baremCheck,
        CollectionFactory $baremCollection,
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
                    'sellerId'  => $this->getSellerId(),
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
        $result = [];
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        foreach($items as $item)
        {
            $currentDisAllowedProductAttribute = $item->getProduct()->getData(Attribute::PRODUCT_BAREM_CODE);
            $availableBarems = $this->baremCollection->create()->AddFillterAvailableBarems()
                ->addFieldToFilter(BaremInterface::ID, ['nin' => $currentDisAllowedProductAttribute]);

            foreach($availableBarems as $barems)
            {
                if(!in_array($barems->getData() ,$result))
                {
                    $result[] = $barems->getData();
                }
            }
        }

        return $result;

    }

    /**
     * @return string
     */
    private function getSellerId()
    {
        return $this->helloBankPaymentConfig->getSellerId();
    }
}
