<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleBase\Model\Storage;

use Magento\UrlRewrite\Model\Storage\DbStorage as MagentoDbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class DbStorage
 *
 * @package Oander\IstyleBase\Model\Storage
 */
class DbStorage extends MagentoDbStorage
{
    /**
     * @param UrlRewrite[] $urls
     *
     * @return int|void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function doReplace($urls)
    {
        foreach ($this->createFilterDataBasedOnUrls($urls) as $type => $urlData) {
            $urlData[UrlRewrite::ENTITY_TYPE] = $type;
            $this->deleteByData($urlData);
        }
        $data = [];
        $requestPaths = [];
        foreach ($urls as $url) {
            $storeId = $url->getStoreId();
            $requestPath = $url->getRequestPath();

            $urlExists = $this->connection->fetchOne(
                    $this->connection->select()
                        ->from('url_rewrite')
                        ->where('request_path = ?', $requestPath)
                        ->where('store_id = ?', $storeId)
                );

            if (!$urlExists) {
                $requestPaths[] = $storeId . '-' . $requestPath;
                $data[] = $url->toArray();
            }
        }

        $n = count($requestPaths);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if ($requestPaths[$i] == $requestPaths[$j]) {
                    unset($data[$j]);
                }
            }
        }
        $this->insertMultiple($data);
    }
}
