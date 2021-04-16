<?php
namespace Oander\HelloBankPayment\Block\Barem;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Oander\HelloBankPayment\Enum\Attribute;

class Display extends Template
{
    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * @var CollectionFactory
     */
    private $baremCollection;

    /**
     * @var ConfigValueHandler
     */
    private $helloBankPaymentConfig;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepsitory;


    public function __construct(
        JsonHelper $jsonHelper,
        ProductRepositoryInterface $productRepsitory,
        CurrencyFactory $currencyFactory,
        Registry $registry,
        CollectionFactory $baremCollection,
        ConfigValueHandler $helloBankPaymentConfig,
        Template\Context $context,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->productRepsitory = $productRepsitory;
        $this->currencyCode = $currencyFactory->create();
        $this->baremCollection = $baremCollection;
        $this->helloBankPaymentConfig = $helloBankPaymentConfig;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    private function product()
    {
        return $this->registry->registry('product');
    }

    private function getDisallowedBarems()
    {
        $disAllowedBarems= $this->product()->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);
        return ($disAllowedBarems) ? $disAllowedBarems->getValue() : false;
    }

    /**
     * @return string
     */
    public function getBarems()
    {
       return $this->jsonHelper->jsonEncode($this->getBaremsData());
    }

    /**
     * @return array
     */
    public function getBaremsData()
    {

        if($this->product()->getTypeId() == "configurable")
        {
            return $this->getChildProductBarem($this->product()->getTypeInstance()->getUsedProductIds($this->product()));
        }

        $availableBarems = $this->getAvailableBarems($this->getDisallowedBarems());

        $barems = [];
        foreach ($availableBarems->getItems() as $availableBarem)
        {
            if(!in_array($availableBarem->getData(), $barems))
            {
                if($this->product()->getTypeId() === "simple")
                {
                    if($availableBarem->getData(BaremInterface::MINIMUM_PRICE) <= round($this->product()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()))
                    {
                        $barems[] = $availableBarem->getData();
                    }
                }else {
                    $barems[] = $availableBarem->getData();
                }

            }
        }
        return $barems;
    }

    /**
     * @param $productIds
     * @return array
     * @throws NoSuchEntityException
     */
    private function getChildProductBarem($productIds)
    {
        $response = [];
        foreach($productIds as $productId)
        {
            $product = $this->productRepsitory->getById($productId);
            $disAllowedBarems = ($this->productRepsitory->getById($productId)->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)) ? $product->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)->getValue() : false;

                $collection = $this->getAvailableBarems($disAllowedBarems);
                foreach($collection as $item){
                    $response[$productId][] = $item->getData();
                }
        }

        return $response;
    }

    /**
     * @param $params
     * @return Collection
     */
    private function getAvailableBarems($params)
    {
        return $this->baremCollection->create()->AddFillterAvailableBarems()
            ->addFieldToFilter(BaremInterface::ID, ['nin' => $params]);
    }

    /**
     * @return string
     */
    public function getSellerId()
    {
        return $this->helloBankPaymentConfig->getSellerId();
    }
    
    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->helloBankPaymentConfig->getIsActive();
    }

    /**
     * @return string
     */
    public function getProductPrice()
    {
        /** @var Product $product */
        $product = $this->registry->registry('current_product');

        return round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);

        return $currency->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function getProductType() {
        /** @var Product $product */
        $product = $this->registry->registry('current_product');

        return $product->getTypeId();
    }
}
