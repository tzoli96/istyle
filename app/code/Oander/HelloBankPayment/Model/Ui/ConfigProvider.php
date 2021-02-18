<?php
namespace Oander\HelloBankPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Magento\Checkout\Model\Session;
use Oander\HelloBankPayment\Enum\Attribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
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
     * @param BaremRepositoryInterface $baremRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigValueHandler $helloBankPaymentConfig
     * @param StoreInterface $store
     * @param Session $checkoutSession
     */
    public function __construct(
        BaremRepositoryInterface $baremRepository,
        ProductRepositoryInterface $productRepository,
        ConfigValueHandler $helloBankPaymentConfig,
        StoreInterface $store,
        Session $checkoutSession
    ) {
        $this->baremRepository = $baremRepository;
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
                    'logoSrc' => $this->getLogoSrc(),
                    'barems'  => $this->getBarems()
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
     * todo: repository helyett quoteból lekérdezni
     * @return array
     */
    private function getBarems()
    {
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        $barems = [];
        foreach($items as $item)
        {
            $baremAttribute = $this->productRepository
                ->get($item->getSku())
                ->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);

            if (null !== $baremAttribute && !in_array($baremAttribute->getValue(),$barems))
            {
                $baremIds=$baremAttribute->getValue();
                if (strpos($baremIds, ',') !== false)
                {
                    $baremIds = explode(',', $baremIds);
                    foreach ($baremIds as $baremId)
                    {
                        $barem=$this->baremRepository->getById($baremId);
                        $barems[] = [
                            "id"        => $barem->getId(),
                            "name"      => $barem->getName(),
                            "min_price" => $barem->getMinPrice(),
                            "max_price" => $barem->getMaxPrice(),
                            "priority"  => $barem->getPriority(),
                        ];
                    }
                } else {
                    $barem = $this->baremRepository->getById($baremIds);
                    $barems[] = [
                        "id"        => $barem->getId(),
                        "name"      => $barem->getName(),
                        "min_price" => $barem->getMinPrice(),
                        "max_price" => $barem->getMaxPrice(),
                        "priority"  => $barem->getPriority(),
                    ];
                }
            }

        }
        return $barems;
    }



}
