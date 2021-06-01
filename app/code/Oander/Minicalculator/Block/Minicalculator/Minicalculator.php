<?php
namespace Oander\Minicalculator\Block\Minicalculator;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\Minicalculator\Helper\Config;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Oander\Minicalculator\Api\Data\CalculatorInterface;
use Oander\HelloBankPayment\Gateway\Config\ConfigValueHandler;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;
use Oander\HelloBankPayment\Enum\Attribute;

class Minicalculator extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ConfigValueHandler
     */
    private $helloBankPaymentConfig;

    /**
     * @var CollectionFactory
     */
    private $baremCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Config $config,
        Registry $registry,
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $baremCollection,
        ConfigValueHandler $helloBankPaymentConfig,
        array $data = []
    ){
        $this->config = $config;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->baremCollection = $baremCollection;
        $this->helloBankPaymentConfig = $helloBankPaymentConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get product
     * @return Product
     */
    private function getProduct()
    {
        if(is_null($this->product))
        {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId())
            {
                return false;
            }
        }

        return $this->product;
    }

    /**
     * Get store identifier
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get module is active
     * @return bool
     */
    public function getModuleIsActive()
    {
        return $this->config->getModuleIsActive($this->getStoreId());
    }

    /**
     * Get calculator data
     * @return Array
     */
    public function getCalculatorData()
    {
        $data = [];

        if($this->config->getModuleIsActive($this->getStoreId()))
        {
            $data = [
                'type' => $this->getProduct()->getData(CalculatorInterface::CALCULATOR_TYPE),
                'barem' => $this->getProduct()->getData(CalculatorInterface::CALCULATOR_BAREM),
                'installment' => $this->getProduct()->getData(CalculatorInterface::CALCULATOR_INSTALLMENT)
            ];
        }

        return $data;
    }

    /**
     * Get Hello Bank data
     */
    public function getHelloBankData() {
        return [
            'isActive' => $this->helloBankPaymentConfig->getIsActive(),
            'sellerId' => $this->helloBankPaymentConfig->getSellerId()
        ];
    }

    /**
     * Get simple product price
     * @return string
     */
    public function getProductPrice()
    {
        return round($this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue());
    }

    /**
     * Get product type
     * @return string
     */
    public function getProductType() {
        return $this->getProduct()->getTypeId();
    }

    /**
     * Get disallowed barems
     * @return Array
     */
    private function getDisallowedBarems()
    {
        $disAllowedBarems= $this->getProduct()->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);
        return ($disAllowedBarems) ? $disAllowedBarems->getValue() : false;
    }

    /**
     * Get available barems
     * @param $params
     * @return Collection
     */
    private function getAvailableBarems($params)
    {
        return $this->baremCollection->create()->AddFillterAvailableBarems()
            ->addFieldToFilter('id', ['nin' => $params]);
    }

    /**
     * Get barems
     * @return array
     */
    public function getBarems()
    {
        $availableBarems = $this->getAvailableBarems($this->getDisallowedBarems());

        $barems = [];
        foreach ($availableBarems->getItems() as $availableBarem)
        {
            if(!in_array($availableBarem->getData(), $barems))
            {
                $barems[] = $availableBarem->getData();
            }
        }

        return $barems;
    }

    /**
     * Get configurable product ids
     * @return Array
     */
    private function getConfigurableProductIds()
    {
        if($this->getProduct()->getTypeId() == "configurable")
        {
            return $this->getProduct()->getTypeInstance()->getUsedProductIds($this->getProduct());
        }
    }

    /**
     * Get configurable calculator data
     * @return Array
     */
    public function getConfigurableCalculatorData()
    {
        $data = [];

        if ($this->getConfigurableProductIds())
        {
            foreach ($this->getConfigurableProductIds() as $simpleProduct)
            {
                $product = $this->productRepository->getById($simpleProduct);

                $data[$simpleProduct] = [
                    'type' => $product->getData(CalculatorInterface::CALCULATOR_TYPE),
                    'barem' => $product->getData(CalculatorInterface::CALCULATOR_BAREM),
                    'installment' => $product->getData(CalculatorInterface::CALCULATOR_INSTALLMENT)
                ];
            }
        }

        return $data;
    }

    /**
     * Get configurable barems data
     * @return Array
     */
    public function getConfigurableBaremsData()
    {
        $data = [];

        if ($this->getConfigurableProductIds())
        {
            foreach($this->getConfigurableProductIds() as $productId)
            {
                $product = $this->productRepository->getById($productId);
                $disAllowedBarems = ($this->productRepository->getById($productId)->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)) ? $product->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE)->getValue() : false;
                $collection = $this->getAvailableBarems($disAllowedBarems);

                foreach($collection as $item)
                {
                    $data[$productId][] = $item->getData();
                }
            }
        }

        return $data;
    }
}
