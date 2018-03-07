<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model;

use Oander\ImportM2\Helper\Config;
use Oander\ImportM2\Logger\Logger;

/**
 * Class ImportBase
 *
 * @package Oander\ImportM2\Model
 */
abstract class ImportBase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $donorStoreIds = [];

    /**
     * ImportBase constructor.
     *
     * @param Logger        $logger
     * @param Config        $config
     */
    public function __construct(
        Logger $logger,
        Config $config
    ) {
        $this->logger = $logger;
        $this->config = $config;

        $this->donorStoreIds = $this->config->getDonorStoreIds();
    }
}
