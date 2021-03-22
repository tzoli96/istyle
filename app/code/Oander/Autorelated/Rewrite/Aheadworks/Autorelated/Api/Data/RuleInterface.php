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

namespace Oander\Autorelated\Rewrite\Aheadworks\Autorelated\Api\Data;

interface RuleInterface extends \Aheadworks\Autorelated\Api\Data\RuleInterface
{
    /**
     * Get subtitle
     *
     * @return string|null
     */
    public function getSubtitle();

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @return \Aheadworks\Autorelated\Api\Data\RuleInterface
     */
    public function setSubtitle($subtitle);
}