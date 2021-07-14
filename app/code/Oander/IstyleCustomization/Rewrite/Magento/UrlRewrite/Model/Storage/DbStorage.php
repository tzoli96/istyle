<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\IstyleCustomization\Rewrite\Magento\UrlRewrite\Model\Storage;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{

    protected function doReplace($urls)
    {
        $this->connection->beginTransaction();

        try {
            $this->deleteOldUrls($urls);

            $data = [];
            foreach ($urls as $url) {
                $data[] = $url->toArray();
            }

            $this->insertMultiple($data);

            $this->connection->commit();
            // @codingStandardsIgnoreStart
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            // @codingStandardsIgnoreEnd
            $this->connection->rollBack();

            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewrite::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewrite::STORE_ID => $url->getStoreId(),
                    ]
                );
                if (isset($urlFound[UrlRewrite::URL_REWRITE_ID])) {
                    $urlConflicted[$urlFound[UrlRewrite::URL_REWRITE_ID]] = $url->toArray();
                }
            }
            if ($urlConflicted) {
                $conflictedUrls = [];
                foreach ($urlConflicted as $conflict)
                {
                    $conflictedUrls[] = $conflict["request_path"];
                }
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.' . ' (URLS:' . implode(",", $conflictedUrls) . ')'),
                    $e,
                    $e->getCode(),
                    $urlConflicted
                );
            } else {
                throw $e->getPrevious() ?: $e;
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            file_put_contents(BP . "/var/log/temp.log", date('Y-m-d H:i:s').' | \Magento\UrlRewrite\Model\Storage\DbStorage::doReplace Exception: '.var_export($e->getMessage(),true).PHP_EOL, FILE_APPEND | LOCK_EX);

            throw $e;
        }

        return $urls;
    }

    private function deleteOldUrls(array $urls)
    {
        $oldUrlsSelect = $this->connection->select();
        $oldUrlsSelect->from(
            $this->resource->getTableName(self::TABLE_NAME)
        );

        $uniqueEntities = $this->prepareUniqueEntities($urls);
        foreach ($uniqueEntities as $storeId => $entityTypes) {
            foreach ($entityTypes as $entityType => $entities) {
                $oldUrlsSelect->orWhere(
                    $this->connection->quoteIdentifier(
                        UrlRewrite::STORE_ID
                    ) . ' = ' . $this->connection->quote($storeId, 'INTEGER') .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_ID
                    ) . ' IN (' . $this->connection->quote($entities, 'INTEGER') . ')' .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_TYPE
                    ) . ' = ' . $this->connection->quote($entityType)
                );
            }
        }

        // prevent query locking in a case when nothing to delete
        $checkOldUrlsSelect = clone $oldUrlsSelect;
        $checkOldUrlsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $checkOldUrlsSelect->columns('count(*)');
        $hasOldUrls = (bool)$this->connection->fetchOne($checkOldUrlsSelect);

        if ($hasOldUrls) {
            $this->connection->query(
                $oldUrlsSelect->deleteFromSelect(
                    $this->resource->getTableName(self::TABLE_NAME)
                )
            );
        }
    }


    /**
     * Prepare array with unique entities
     *
     * @param  UrlRewrite[] $urls
     * @return array
     */
    private function prepareUniqueEntities(array $urls)
    {
        $uniqueEntities = [];
        /** @var UrlRewrite $url */
        foreach ($urls as $url) {
            $entityIds = (!empty($uniqueEntities[$url->getStoreId()][$url->getEntityType()])) ?
                $uniqueEntities[$url->getStoreId()][$url->getEntityType()] : [];

            if (!\in_array($url->getEntityId(), $entityIds)) {
                $entityIds[] = $url->getEntityId();
            }
            $uniqueEntities[$url->getStoreId()][$url->getEntityType()] = $entityIds;
        }

        return $uniqueEntities;
    }

    public function replace(array $urls)
    {
        if (!$urls) {
            return;
        }

        try {
            $this->doReplace($urls);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __('URL key for specified store already exists.' . $e->getMessage())
            );
        }
    }
}