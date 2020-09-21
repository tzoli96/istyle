<?php
namespace Oander\ConfigurationProductsShow\Block\Adminhtml\Product\Edit\Tab;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AssociatedConfigProduct extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $configureProduct;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/associated_config_product.phtml';

    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Configurable $configureProduct,
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->productRepository = $productRepository;
        $this->configureProduct = $configureProduct;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param $id
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById($id){
        return $this->productRepository->getById($id);
    }


    public function getParentProduct($id){
        $product=$this->configureProduct->getParentIdsByChild($id);
        if(isset($product[0])){
            return $product;
        }else{
            return false;
        }
    }

    /**
     * @param $number
     * @return Phrase
     */
    public function getProductStatus($number){
        if($number == 2){
            return __("Disabled");
        }else{
            return __("Enabled");
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function getAdminUrlProduct($id){
        return '/admin/catalog/product/edit/id/'.$id.'/store/'.$this->_storeManager->getStore()->getId();
    }
}