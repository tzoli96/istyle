<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerAddressValidation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class QueryZipCity extends AbstractHelper
{


    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
    }

    public function getCityByZip($countryCode, $zipCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::TABLE);

        $select = $connection->select();
        $select->from($tableName, [\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY])
            ->where($connection->quoteInto(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE . ' = ?', $countryCode))
            ->where($connection->quoteInto(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE . ' = ?', $zipCode));
        return $connection->fetchOne($select);
    }
}