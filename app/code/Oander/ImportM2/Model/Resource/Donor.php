<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Resource;

use Magento\Framework\App\ResourceConnection;
use Oander\ImportM2\Enum\Resource;

/**
 * Class Donor
 *
 * @package Oander\ImportM2\Model\Resource
 */
class Donor
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $donorConnection;

    /**
     * Donor constructor.
     *
     * @param ResourceConnection $resourceConnection
     *
     * @throws \DomainException
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->donorConnection = $resourceConnection->getConnection(Resource::CONNECTION_NAME);
    }
}
