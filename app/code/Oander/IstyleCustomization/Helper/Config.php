<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Oander\IstyleCustomization\Enum\AddressAttributeEnum;

/**
 * Class Config
 *
 * @package Oander\IstyleCustomization\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * Config constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param Context $context
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Context $context
    )
    {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
    }

    /**
     * @return bool
     */
    public function useTopmenuBlock()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_categories/topmenu/use_topmenu_block',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isSessionCheckerEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_session_checker/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getSessionCheckerEmailReceivers(): array
    {
        $value = (string)$this->scopeConfig->getValue(
            'oander_session_checker/general/email_receivers',
            ScopeInterface::SCOPE_STORE
        );
        $value = explode(';', $value);

        return (array)array_filter($value);
    }

    /**
     * @return bool
     */
    public function isUrlCheckerEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_session_checker/url/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getUrlCheckerEmailReceivers(): array
    {
        $value = (string)$this->scopeConfig->getValue(
            'oander_session_checker/url/email_receivers',
            ScopeInterface::SCOPE_STORE
        );
        $value = explode(';', $value);

        return (array)array_filter($value);
    }


    /**
     * @return bool
     */
    public function isBasicDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getBasicDescriptionRejectedTags()
    {
        $rejectedTags = [];
        $value = (string)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/rejected_tags',
            ScopeInterface::SCOPE_STORE
        );

        if (strpos($value, ';') !== false) {
            $rejectedTags = explode(';', $value);
        } else {
            $rejectedTags[] = $value;
        }

        return (array)$rejectedTags;
    }

    /**
     * @return int
     */
    public function getBasicDescriptionMaxChars()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/max_chars',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getBasicDescriptionPostfix()
    {
        return (string)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/postfix',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isRiverDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/river/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isWidgetDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/widget/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getWidgetDescriptionMaxBlocks()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/widget/max_widget',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getDobShow($orderItems)
    {
        $dobAttributeSets = $this->getDobAttributeSets();
        $hasAttributeSet = false;
        foreach ($orderItems as $orderItem) {
            if ($product = $orderItem->getProduct()) {
                if (in_array($product->getAttributeSetId(), $dobAttributeSets)) {
                    $hasAttributeSet = true;
                    break;
                }
            }
        }

        if ($hasAttributeSet) {
            return $this->scopeConfig->getValue(
                'customer/address/dob_show',
                ScopeInterface::SCOPE_STORE
            );
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getDobAttributeSets()
    {
        $value = (string)$this->scopeConfig->getValue(
            'customer/address/dob_attribute_sets',
            ScopeInterface::SCOPE_STORE
        );
        if (strpos($value, ',') !== false) {
            $value = explode(',', $value);
        }

        return (array)$value;
    }

    /**
     * @return int
     */
    public function isSearchBlockEnabled()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_search_block/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getSearchBlockId()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_search_block/general/search_block',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSearchBlock()
    {
        if ($this->isSearchBlockEnabled() && $this->getSearchBlockId()) {
            return $this->blockRepository->getById($this->getSearchBlockId())->getContent();
        }

        return '';
    }

    /**
     * @return array
     */
    public function getAddressAttributePosition($attribute = null)
    {
        $value = (string)$this->scopeConfig->getValue(
            'customer/address_attributes_order/address_attributes_positions',
            ScopeInterface::SCOPE_STORE
        );

        if($value != '') {
            try {
                $attributes = unserialize($value);
                $value = [];
                if (is_array($attributes)) {
                    $value = [];
                    foreach ($attributes as $attribute) {
                        $value[$attribute[AddressAttributeEnum::COLUMN_ATTRIBUTE]] = [
                            AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_INDIVIDUAL_POSITION],
                            AddressAttributeEnum::COLUMN_COMPANY_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_COMPANY_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_COMPANY_POSITION],
                            AddressAttributeEnum::COLUMN_DEFAULT_POSITION => empty($attribute[AddressAttributeEnum::COLUMN_DEFAULT_POSITION])? null: (int)$attribute[AddressAttributeEnum::COLUMN_DEFAULT_POSITION],
                        ];
                    }
                }

            } catch (\Exception $e){
                $value = [];
            }
        }

        return $value;
    }
}
