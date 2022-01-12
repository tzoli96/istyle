<?php

namespace Ewave\CacheManagement\Model\Config\Source;

use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Magento\Framework\Data\OptionSourceInterface;

class CacheType implements OptionSourceInterface
{
    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * CacheType constructor.
     * @param CacheTypeList $cacheTypeList
     */
    public function __construct(CacheTypeList $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($isMultiselect = false)
    {
        $options = [];

        if (!$isMultiselect) {
            $options[] = [
                'label' => (string)__('-- Please, select --'),
                'value' => '',
            ];
        }

        foreach ($this->cacheTypeList->getTypeLabels() as $type => $label) {
            $options[] = [
                'label' => (string)$label,
                'value' => $type,
            ];
        }

        return $options;
    }
}
