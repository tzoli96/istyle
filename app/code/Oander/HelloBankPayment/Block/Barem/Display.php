<?php
namespace Oander\HelloBankPayment\Block\Barem;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Oander\HelloBankPayment\Enum\Attribute;

class Display extends Template
{
    /**
     * @var Collection
     */
    private $baremCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    private $product;


    public function __construct(
        Registry $registry,
        Collection $baremCollection,
        Template\Context $context,
        array $data = []
    ) {
        $this->baremCollection = $baremCollection;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    private function getDisallowedBarems()
    {
        $disAllowedBarems= $this->registry->registry('product')->getCustomAttribute(Attribute::PRODUCT_BAREM_CODE);
        return ($disAllowedBarems) ? $disAllowedBarems->getValue() : false;
    }

    /**
     * @return array
     */
    public function getBarems()
    {
        $avaliabelBarems = $this->baremCollection->getAvailableBarems()
        ->getDissAllowed($this->getDisallowedBarems());

        $barems = [];
        foreach ($avaliabelBarems as $avaliabelBarem)
        {
            if(!in_array($avaliabelBarem->getData(), $barems))
            {
                $barems[] = $avaliabelBarem->getData();
            }
        }
        return $barems;
    }

}