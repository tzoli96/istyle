<?php
/**
 * Apesyto Sales
 * Copyright (C) 2019
 *
 * This file included in Oander/Autorelated is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\Autorelated\Rewrite\Aheadworks\Autorelated\Model\Data;

use Aheadworks\Autorelated\Api\Data\RuleInterface;

class Rule extends \Aheadworks\Autorelated\Model\Data\Rule implements \Oander\Autorelated\Api\Data\RuleInterface
{
    /**
     *  {@inheritDoc}
     */
    public function getSubtitle()
    {
        return $this->_get("subtitle");
    }

    /**
     *  {@inheritDoc}
     */
    public function setSubtitle($subtitle)
    {
        return $this->setData("subtitle", $subtitle);
    }
}