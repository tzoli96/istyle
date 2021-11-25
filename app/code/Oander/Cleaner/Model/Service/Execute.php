<?php

namespace Oander\Cleaner\Model\Service;

use Oander\Cleaner\Helper\Config;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Execute
{
    CONST API_GATEWAY_DB_NAME               = "apigateway";
    CONST API_GATEWAY_HISTORY_TABLE_NAME    = "oander_api_gateway_history";
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


    public function __construct(
        Config $helperConfig,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ){
        $this->helperConfig = $helperConfig;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function execute()
    {
        if($this->helperConfig->isCleanerEnabled())
        {
            if($this->helperConfig->isDbEnabled()){
                $this->dbCleaner();
            }
            if($this->helperConfig->isFilesEnabled()){
                $this->filesCleaner();
            }
        }
    }

    private function dbCleaner()
    {
        if($this->helperConfig->getOlderThan())
        {
            $apiGateWayConnection = $this->resourceConnection->getConnectionByName(self::API_GATEWAY_DB_NAME);
            $table = $apiGateWayConnection->getTableName(self::API_GATEWAY_HISTORY_TABLE_NAME);
            $query = 'DELETE FROM '.$table.' WHERE created_at < NOW() -'.$this->helperConfig->getOlderThan();

            try {
                $apiGateWayConnection->query($query);
                $this->logger->info("OANDER_CLEANER: Successfully DB Clean");
            }catch (\Zend_Db_Statement_Exception $e){
                $this->logger->error("OANDER_CLEANER:".$e->getMessage());
            }

        }

    }

    private function filesCleaner()
    {

    }
}