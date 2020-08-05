<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Block\Address;

/**
 * Class Edit
 * @package Oander\FanCourierValidator\Block\Address
 */
class Edit extends \Magento\Customer\Block\Address\Edit
{
    /**
     * @var \Oander\FanCourierValidator\Helper\Data
     */
    protected $fanCourierHelper;

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
            $data
        );
        $this->fanCourierHelper = $fanCourierHelper;
    }

    public function getAjaxUrl(){
        return $this->getUrl("fan_courier_validator"); // Controller Url
    }

    /**
     * @return bool
     */
    public function isRequiredRegion()
    {
        if ($this->fanCourierHelper->getValidationLevel() === 'req') {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isValidateRegion()
    {
        if ($this->fanCourierHelper->getValidationLevel() === 'valid') {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getStates()
    {
        return $this->fanCourierHelper->getStates();
    }

    /**
     * @return array
     */
    public function getCities()
    {
        return $this->fanCourierHelper->getCities();
    }
}