<?php

namespace Oander\IstyleCustomization\Plugin\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractBackend
 * @package Oander\IstyleCustomization\Plugin\Eav\Model\Entity\Attribute\Backend
 */
class AbstractBackend
{
    /**
     * Validate object
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        $object
    ) {
        try {
            $result = $proceed($object);
        } catch (LocalizedException $exception) {
            if (isset($exception->getParameters()[0]) &&
                ($exception->getParameters()[0] == 'show_address_dob'
                    || $exception->getParameters()[0] == 'show_pfpj_reg_no'
                    || $exception->getParameters()[0] == 'address_dob')
            ) {
                $result = true;
            }
        }

        return $result;
    }
}