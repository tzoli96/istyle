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
     * ConfigProvider constructor.
     *
     * @param Collection $baremCollection
     * @param BaremRepositoryInterface $baremRepository
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     * @param Session $checkoutSession
     */
    public function __construct(
        Collection $baremCollection,
        BaremRepositoryInterface $baremRepository,
        ConfigValueHandler $helloBankPaymentConfig,
        StoreInterface $store,
        Session $checkoutSession
    ) {
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
     * todo: repository helyett quoteból lekérdezni
     * @return array
     */
    private function getBarems()
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getAllVisibleItems();
        $grandTotal=$quote->getGrandTotal();
        $barems = [];
        foreach($items as $item)
        {
            $disAllowedBaremsFromQuote = $item->getProduct()->getData(Attribute::PRODUCT_BAREM_CODE);

            $disAllowedBarems = ($disAllowedBaremsFromQuote) ? $disAllowedBaremsFromQuote : false;
            $avaliabelBarems = $this->baremCollection->getAvailableBarems()
                ->getDissAllowed($disAllowedBarems, $grandTotal);

            foreach ($avaliabelBarems as $avaliabelBarem)
            {
                if(!in_array($avaliabelBarem->getData(), $barems))
                {
                    $barems[] = $avaliabelBarem->getData();
                }
            }

        }

        return $barems;
    }



}
