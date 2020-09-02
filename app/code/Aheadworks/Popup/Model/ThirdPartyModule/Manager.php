<?php
namespace Aheadworks\Popup\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 * @package Aheadworks\Popup\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * Magento Page Builder module name
     */
    const MAGE_PB_MODULE_NAME = 'Magento_PageBuilder';

    /**
     * Customer Segmentation by Aheadworks module name
     */
    const CUSTOMER_SEGMENTATION_MODULE_NAME = 'Aheadworks_CustomerSegmentation';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if Magento Page Builder module enabled
     *
     * @return bool
     */
    public function isMagePageBuilderModuleEnabled()
    {
        return $this->moduleList->has(self::MAGE_PB_MODULE_NAME);
    }

    /**
     * Check if Customer Segmentation module enabled
     *
     * @return bool
     */
    public function isCustomerSegmentationModuleEnabled()
    {
        return $this->moduleList->has(self::CUSTOMER_SEGMENTATION_MODULE_NAME);
    }
}
