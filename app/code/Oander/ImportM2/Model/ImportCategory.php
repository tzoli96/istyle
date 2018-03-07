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
use Oander\ImportM2\Model\Import\Category as CategoryImport;

/**
 * Class ImportCategory
 *
 * @package Oander\ImportM2\Model
 */
class ImportCategory extends ImportBase
{
    /**
     * @var CategoryImport
     */
    private $categoryImport;

    /**
     * Import constructor.
     *
     * @param Logger         $logger
     * @param Config         $config
     * @param CategoryImport $categoryImport
     */
    public function __construct(
        Logger $logger,
        Config $config,
        CategoryImport $categoryImport
    ) {
        parent::__construct($logger,$config);
        $this->categoryImport = $categoryImport;
    }

    public function execute()
    {
        $this->logger->info('OANDER MAGNETO 2 IMPORT HAS STARTED NOW');

        $this->categoryImport->execute();

        $this->logger->info('OANDER MAGNETO 2 IMPORT HAS JUST FINISHED');
    }
}
