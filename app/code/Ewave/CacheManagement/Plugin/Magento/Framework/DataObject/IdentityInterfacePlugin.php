<?php
namespace Ewave\CacheManagement\Plugin\Magento\Framework\DataObject;

use Magento\Framework\DataObject\IdentityInterface as Subject;
use Ewave\CacheManagement\Helper\Data as Helper;

class IdentityInterfacePlugin
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * IdentityInterfacePlugin constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(
        Subject $subject,
        $result
    ) {
        if ($storeTag = $this->helper->getStoreTag()) {
            $result[] = $storeTag;
        }
        return $result;
    }
}
