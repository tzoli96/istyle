<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\CustomerExtend\Block\Address;

use Oander\IstyleCustomization\Enum\AddressAttributeEnum;

/**
 * Class Edit
 * @package Oander\IstyleCustomization\Block\Address
 */
class Edit extends \Oander\FanCourierValidator\Block\Address\Edit
{
    /**
     * @var \Oander\IstyleCustomization\Helper\Config
     */
    protected $config;

    /**
     * Edit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Oander\FanCourierValidator\Helper\Data $fanCourierHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Oander\FanCourierValidator\Helper\Data $fanCourierHelper,
        \Oander\IstyleCustomization\Helper\Config $config,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $fanCourierHelper,
            $data
        );
        $this->config = $config;
    }

    /**
     * @return array|string
     */
   public function getAddressAttributesDefaultOrder()
   {
       $attributes = $this->config->getAddressAttributePosition();
       foreach ($attributes as $attributeCode => $attribute) {
           $attributes[$attributeCode][AddressAttributeEnum::COLUMN_ATTRIBUTE] = $attributeCode;
           if (is_null($attribute[AddressAttributeEnum::COLUMN_DEFAULT_POSITION])) {
               unset($attributes[$attributeCode]);
           }
       }

       usort($attributes, function($a, $b) {
           return $a[AddressAttributeEnum::COLUMN_DEFAULT_POSITION] <=> $b[AddressAttributeEnum::COLUMN_DEFAULT_POSITION];
       });

       return $attributes;
   }
}