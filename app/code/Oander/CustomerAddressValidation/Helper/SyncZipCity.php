<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerAddressValidation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Archive\Zip;

class SyncZipCity extends AbstractHelper
{

    const TXTCOLUMNS = [
        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE => 0,
        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE => 1,
        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY => 2
    ];
    const DOWNLOADURL = "http://download.geonames.org/export/zip/";
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    private $storeRepository;
    /**
     * @var Zip
     */
    private $zip;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $dir;
    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Zip $zip
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\File\Csv $csv
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Archive\Zip $zip,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\File\Csv $csv,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->zip = $zip;
        $this->file = $file;
        $this->dir = $dir;
        $this->csv = $csv;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function syncAll()
    {
        foreach ($this->storeRepository->getList() as $store)
        {
            if($store["store_id"])
            {
                $this->syncStore($store["store_id"]);
            }
        }
    }

    /**
     * @param $storeId int
     * @return bool
     */
    public function syncStore($storeId)
    {
        try {
            //Base values
            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::TABLE);
            $countryCode = $this->scopeConfig->getValue(
                self::COUNTRY_CODE_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            //Download and get CSV
            $csv = $this->_getZipCityCSV($countryCode);

            if (is_array($csv)) {

                //Delete store specific
                $whereConditions = [
                    $connection->quoteInto(\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE . ' = ?', $countryCode),
                ];
                $connection->delete($tableName, $whereConditions);

                //Insert store specific
                $insertPreparedData = [];
                foreach ($csv as $row) {
                    if (
                        isset($row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE]]) &&
                        isset($row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE]]) &&
                        isset($row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY]])
                    ) {
                        $insertPreparedData[] =
                            [
                                $row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE]],
                                $row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE]],
                                $row[self::TXTCOLUMNS[\Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY]]
                            ];
                    }
                }
                $connection->insertArray($tableName,
                    [
                        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::COUNTRYCODE,
                        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::ZIPCODE,
                        \Oander\CustomerAddressValidation\Api\Data\CityzipInterface::CITY
                    ],
                    $insertPreparedData
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("General Error during update cityzip pairs"));
            }
        }
        catch (\Exception $e)
        {
            $this->logger->error(sprintf("Error during update cityzip pairs for storeid %s: %s", $storeId, $e->getMessage()));
            return false;
        }
        return true;
    }

    /**
     * @param $countryCode string
     * @return array|bool
     * @throws \Exception
     */
    private function _getZipCityCSV($countryCode)
    {
        $data = false;
        $filename = $this->_downloadFileAndGetName($countryCode);
        if($filename) {
            $this->csv->setDelimiter("\t");
            $data = $this->csv->getData($filename);
        }
        return $data;
    }

    /**
     * @param $countryCode string
     * @return string
     */
    private function _downloadFileAndGetName($countryCode)
    {
        $zipfileName = $countryCode . ".zip";
        $archiveFolder = $this->_getArchiveSaveFolder();
        $archiveDestination = $archiveFolder . $zipfileName;
        $unpackDestination = $this->_getUnPackSaveFolder($countryCode);
        $content = file_get_contents(self::DOWNLOADURL . $countryCode . ".zip");
        file_put_contents($archiveDestination, $content);
        $this->unpack($archiveDestination, $unpackDestination);
        return $unpackDestination . $countryCode . ".txt";
    }

    /**
     * @return string
     */
    private function _getArchiveSaveFolder()
    {
        $archive = $this->dir->getPath('var').'/zipcityarchive';
        if (!file_exists($archive)) {
            $this->file->mkdir($archive);
        }
        return $archive . "/";
    }

    /**
     * @param $countryCode string
     * @return string
     */
    private function _getUnPackSaveFolder($countryCode)
    {
        $archive = $this->_getArchiveSaveFolder() . $countryCode;
        if (!file_exists($archive)) {
            $this->file->mkdir($archive);
        }
        return $archive . "/";
    }

    /**
     * Unpack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $index = 0;
        while ($filename = $zip->getNameIndex($index))
        {
            $zip->extractTo($destination, $filename);
            $index++;
        }
        $zip->close();
        return $destination;
    }
}