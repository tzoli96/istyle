<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Oander\IstyleCustomization\Helper\Config;
use Magento\Framework\App\CacheInterface;

/**
 * Class UrlChecker
 * @package Oander\IstyleCustomization\Cron
 */
class UrlChecker
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $connection;

    private $selectSql;

    private $cache;

    /**
     * SessionChecker constructor.
     *
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder      $transportBuilder
     * @param ResourceConnection    $resourceConnection
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ResourceConnection $resourceConnection,
        CacheInterface $cache
    ) {
        $this->config             = $config;
        $this->transportBuilder   = $transportBuilder;
        $this->storeManager       = $storeManager;
        $this->cache              = $cache;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isUrlCheckerEnabled()) {
            return;
        }

        $errorTable = '';
        $stores = $this->storeManager->getStores(false);
        foreach ($stores as $store) {
            if (!$store->isActive()) {
                continue;
            }

            $result = $this->check($store);
            if (!empty($result)) {
                foreach ($result as $error) {
                    $errorTable .= '<tr>';
                    foreach ($error as $key => $value) {
                        if ($key == $store->getId()) {
                            $value = $store->getName();
                        }
                        if ($key == 'cp.entity_id') {
                            $this->cache->clean('catalog_product_'.$value);
                        }

                        $errorTable .= '<td>' . (string)$value . '</td>';
                    }
                    $errorTable .= '</tr>';
                }

                $errorTable .= $this->insert();
            }
        }

        if (!empty($errorTable)) {
            $this->send($errorTable);
        }

    }

    /**
     * @return array
     */
    protected function check($store)
    {
        $this->selectSql = sprintf("SELECT 'product', cp.entity_id, CONCAT(IFNULL(cpvvs.value, cpvvd.value), '.html'), CONCAT('catalog/product/view/id/', cp.entity_id), 0, %s, 'sql script insert',1  FROM `catalog_product_entity` AS `cp`
INNER JOIN `catalog_product_website` AS `cpw` ON cpw.product_id = cp.entity_id
INNER JOIN `catalog_product_entity_int` AS `cpsd` ON cpsd.entity_id = cp.entity_id AND cpsd.store_id = 0 AND cpsd.attribute_id = 97
LEFT JOIN `catalog_product_entity_int` AS `cpss` ON cpss.entity_id = cp.entity_id AND cpss.attribute_id = cpsd.attribute_id AND cpss.store_id = %s
INNER JOIN `catalog_product_entity_int` AS `cpvd` ON cpvd.entity_id = cp.entity_id AND cpvd.store_id = 0 AND cpvd.attribute_id = 99
LEFT JOIN `catalog_product_entity_int` AS `cpvs` ON cpvs.entity_id = cp.entity_id AND cpvs.attribute_id = cpvd.attribute_id  AND cpvs.store_id = %s 
INNER JOIN `catalog_product_entity_varchar` AS `cpvvd` ON cpvvd.entity_id = cp.entity_id AND cpvvd.store_id = 0 AND cpvvd.attribute_id = 126
LEFT JOIN `catalog_product_entity_varchar` AS `cpvvs` ON cpvvs.entity_id = cp.entity_id AND cpvvs.attribute_id = cpvvd.attribute_id  AND cpvvs.store_id = %s
LEFT JOIN `url_rewrite` AS `ur` ON ur.entity_id = cp.entity_id AND ur.entity_type = 'product' AND ur.store_id = %s
WHERE (cpw.website_id = %s) AND (IFNULL(cpss.value, cpsd.value) = 1) AND (IFNULL(cpvs.value, cpvd.value) IN (2, 3, 4)) 
AND (IFNULL(cpvvs.value, cpvvd.value) IS NOT NULL)
AND CONCAT(IFNULL(cpvvs.value, cpvvd.value), '.html') NOT IN (SELECT request_path FROM `url_rewrite` WHERE store_id = %s )
GROUP BY `cp`.`entity_id`",
            $store->getId(),
            $store->getId(),
            $store->getId(),
            $store->getId(),
            $store->getId(),
            $store->getWebsiteId(),
            $store->getId()
        );

        return $this->connection->fetchAll($this->selectSql);
    }

    /**
     * @return string
     */
    protected function insert()
    {
        $insertSql = "INSERT IGNORE INTO url_rewrite(entity_type, entity_id, request_path, target_path, redirect_type, store_id, description, is_autogenerated)";
        try {
            $this->connection->query($insertSql . ' ' . $this->selectSql);
        } catch (\Exception $exception) {
            $insertResult = $exception->getMessage();
            return '<tr><td colspan="7">' . (string)$insertResult . '</td></tr>';
        }

        return '';
    }

    /**
     * @param string $errorTable
     *
     * @throws MailException
     */
    protected function send(string $errorTable)
    {
        $emailAddresses = $this->config->getUrlCheckerEmailReceivers();
        if (!empty($emailAddresses)) {
            $mainReceiver = $emailAddresses[0];
            unset($emailAddresses[0]);
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'oander_url_checker_email_template'
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_ADMINHTML,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                [
                    'errorTable' => $errorTable
                ]
            )->addTo(
                $mainReceiver
            )->addCc(
                $emailAddresses
            )->getTransport();
            $transport->sendMessage();
        }
    }
}