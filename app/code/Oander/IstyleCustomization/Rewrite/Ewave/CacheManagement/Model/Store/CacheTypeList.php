<?php

namespace Oander\IstyleCustomization\Rewrite\Ewave\CacheManagement\Model\Store;

class CacheTypeList
{
    /**
     * {@inheritdoc}
     */
    public function aftergetTypes(\Ewave\CacheManagement\Model\Store\CacheTypeList $subject, $result)
    {
        unset($result['full_page']);
        return $result;
    }
}