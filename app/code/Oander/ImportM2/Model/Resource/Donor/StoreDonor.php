<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Resource\Donor;

use Oander\ImportM2\Model\Resource\Donor;

/**
 * Class StoreDonor
 *
 * @package Oander\ImportM2\Model\Resource\Donor
 */
class StoreDonor extends Donor
{
    /**
     * @return array
     */
    public function getStores()
    {
        $sql = $this->donorConnection->select()
            ->from('store')
            ->where('code <> ?','admin');

        return $this->donorConnection->fetchAll($sql);
    }
}
