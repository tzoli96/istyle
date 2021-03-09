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
                    'barems'    => $this->getBarems()
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

            $availableBarems = $this->baremCollection->getAvailableBarems()
                    ->addFieldToFilter(BaremInterface::ID, ['nin' => $disAllowedBarems])
                    ->getItems();

            $availableBarems=$this->baremCheck->fillterTotal($availableBarems,$grandTotal);

            foreach ($availableBarems as $availableBarem)
            {
                if(!in_array($availableBarem->getData(), $barems))
                {
                    $barems[] = $availableBarem->getData();
                }
            }

        }

        return $barems;
    }



}
