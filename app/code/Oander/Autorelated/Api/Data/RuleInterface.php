<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Oander\Autorelated\Api\Data;

/**
 * Autorelated rule interface
 *
 * @api
 */
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
     * @return \Oander\Autorelated\Api\Data\RuleInterface
     */
    public function setSubtitle($subtitle);
}
