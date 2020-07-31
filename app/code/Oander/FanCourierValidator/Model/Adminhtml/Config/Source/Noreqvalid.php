<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Model\Adminhtml\Config\Source;

/**
 * Class Noreqvalid
 * @package Oander\FanCourierValidator\Model\Adminhtml\Config\Source
 */
class Noreqvalid implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('No')],
            ['value' => 'req', 'label' => __('Required')],
            ['value' => 'valid', 'label' => __('Validated')],
        ];
    }
}
