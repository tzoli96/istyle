<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Model\Import;

use Oander\ImportM2\Helper\Config;
use Oander\ImportM2\Logger\Logger;
use Oander\ImportM2\Model\ImportBase;
use Oander\ImportM2\Model\Resource\Donor\CategoryDonor;

/**
 * Class Product
 *
 * @package Oander\ImportM2\Model\Import
 */
class Category extends ImportBase
{
    /**
     * @var CategoryDonor
     */
    private $categoryDonor;

    /**
     * Category constructor.
     *
     * @param Logger        $logger
     * @param Config        $config
     * @param CategoryDonor $categoryDonor
     */
    public function __construct(
        Logger $logger,
        Config $config,
        CategoryDonor $categoryDonor
    ) {
        parent::__construct($logger, $config);
        $this->categoryDonor = $categoryDonor;
    }

    public function execute()
    {
        $donorCategories = $this->categoryDonor->getCategoryVarcharAttributes($this->donorStoreIds);
    }
}
