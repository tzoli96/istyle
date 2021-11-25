<?php

namespace Oander\Cleaner\Model\Service;

use Exception;
use Magento\Framework\Exception\FileSystemException;
use Oander\Cleaner\Helper\Config;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Execute
{
    const API_GATEWAY_DB_NAME = "apigateway";
    const API_GATEWAY_HISTORY_TABLE_NAME = "oander_api_gateway_history";
    /**
     * @var Config
     */
    private $helperConfig;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $response = [];
    /**
     * @var File
     */
    private $driverFile;
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param Config $helperConfig
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param File $driverFile
     */
    public function __construct(
        Config             $helperConfig,
        ResourceConnection $resourceConnection,
        LoggerInterface    $logger,
        DirectoryList      $directoryList,
        File               $driverFile
    )
    {
        $this->helperConfig = $helperConfig;
        $this->resourceConnection = $resourceConnection;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
    }

    /**
     * @return array
     * @throws FileSystemException
     * @throws Exception
     */
    public function execute()
    {
        if ($this->helperConfig->isCleanerEnabled()) {
            if ($this->helperConfig->isDbEnabled()) {
                $this->dbCleaner();
            }
            if ($this->helperConfig->isFilesEnabled()) {
                $this->filesCleaner();
            }
        }
        return $this->response;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function dbCleaner()
    {
        if ($this->helperConfig->getOlderThan()) {
            $apiGateWayConnection = $this->resourceConnection->getConnectionByName(self::API_GATEWAY_DB_NAME);
            $table = $apiGateWayConnection->getTableName(self::API_GATEWAY_HISTORY_TABLE_NAME);
            $queryDelete = 'DELETE FROM ' . $table . ' WHERE created_at < NOW() -' . $this->helperConfig->getOlderThan();
            $querySelect = 'SELECT * FROM ' . $table . ' WHERE created_at < NOW() -' . $this->helperConfig->getOlderThan();

            try {
                $this->response['dbClean'] = $apiGateWayConnection->fetchAll($querySelect);
                $apiGateWayConnection->query($queryDelete);
                $this->logger->info("OANDER_CLEANER: Successfully DB Clean");
            } catch (\Zend_Db_Statement_Exception $e) {
                $this->logger->error("OANDER_CLEANER:" . $e->getMessage());
                throw new Exception($e->getMessage());
            }

        }

    }

    /**
     * @return void
     * @throws FileSystemException
     */
    private function filesCleaner()
    {
        $pathsDir = [
            '/export_bkp',
            '/orderexport_extend',
        ];

        foreach ($pathsDir as $dir) {
            try {
                $path = $this->directoryList->getPath('var') . $dir;
                $paths = $this->driverFile->readDirectoryRecursively($path);
                foreach ($paths as $file) {
                    $fileCreated = date('m/d/Y', filemtime($file));
                    $now = date("m/d/Y", strtotime("- " . $this->helperConfig->getOlderThan() . ' days'));
                    if ($fileCreated <= $now) {
                        $this->driverFile->deleteFile($file);
                        $this->response['delete'][] = $file;
                    }
                }
                $this->logger->info("OANDER_CLEANER: Successfully " . $dir . " Dir Clean");
            } catch (FileSystemException $e) {
                $this->logger->error("OANDER_CLEANER: Dir " . $e->getMessage());
                $this->logger->error($e->getMessage());
            }
        }
    }
}