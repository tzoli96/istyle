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
use Oander\ImportM2\Model\Import\Product as ProductImport;

/**
 * Class ImportAttribute
 *
 * @package Oander\ImportM2\Model
 */
class ImportAttribute extends ImportBase
{
    /**
     * @var ProductImport
     */
    private $productImport;

    /**
     * Import constructor.
     *
     * @param Logger        $logger
     * @param Config        $config
     * @param ProductImport $productImport
     */
    public function __construct(
        Logger $logger,
        Config $config,
        ProductImport $productImport
    ) {
        parent::__construct($logger,$config);
        $this->productImport = $productImport;
    }

    public function execute()
    {
        $this->logger->info('OANDER MAGNETO 2 IMPORT HAS STARTED NOW');

        $this->productImport->executeAttribute();

        $this->logger->info('OANDER MAGNETO 2 IMPORT HAS JUST FINISHED');
    }
}
