<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleImportTemp\Model\Import;

use Magento\CustomerImportExport\Model\Import\Customer as MagentoCustomer;

/**
 * Class Customer
 *
 * @package Oander\IstyleImportTemp\Model\Import
 */
class Customer extends MagentoCustomer
{
    /**
     * Prepare customer data for update
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $entitiesToCreate = [];
        $entitiesToUpdate = [];
        $attributesToSave = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $emailInLowercase = strtolower($rowData[self::COLUMN_EMAIL]);
        $newCustomer = false;
        $entityId = $this->_getCustomerId($emailInLowercase, $rowData[self::COLUMN_WEBSITE]);
        if (!$entityId) {
            // create
            $newCustomer = true;
            $entityId = $this->_getNextEntityId();
            $this->_newCustomers[$emailInLowercase][$rowData[self::COLUMN_WEBSITE]] = $entityId;
        }

        $entityRow = [
            'group_id' => empty($rowData['group_id']) ? self::DEFAULT_GROUP_ID : $rowData['group_id'],
            'store_id' => empty($rowData[self::COLUMN_STORE]) ? 0 : $this->_storeCodeToId[$rowData[self::COLUMN_STORE]],
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'entity_id' => $entityId,
        ];

        // password change/set
        if (isset($rowData['password']) && strlen($rowData['password'])) {
            $rowData['password_hash'] = $this->_customerModel->hashPassword($rowData['password']);
        }

        // attribute values
        foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $value) {
            if ($newCustomer && !strlen($value)) {
                continue;
            }

            $attributeParameters = $this->_attributes[$attributeCode];
            if ('select' == $attributeParameters['type']) {
                $value = isset($attributeParameters['options'][strtolower($value)])
                    ? $attributeParameters['options'][strtolower($value)]
                    : 0;
            } elseif ('datetime' == $attributeParameters['type'] && !empty($value)) {
                $value = (new \DateTime())->setTimestamp(strtotime($value));
                $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }

            if (!$this->_attributes[$attributeCode]['is_static']) {
                /** @var $attribute \Magento\Customer\Model\Attribute */
                $attribute = $this->_customerModel->getAttribute($attributeCode);
                if (method_exists($attribute,'getBackendModel')) {
                    $backendModel = $attribute->getBackendModel();
                    if ($backendModel
                        && $attribute->getFrontendInput() != 'select'
                        && $attribute->getFrontendInput() != 'datetime') {
                        $attribute->getBackend()->beforeSave($this->_customerModel->setData($attributeCode, $value));
                        $value = $this->_customerModel->getData($attributeCode);
                    }
                    $attributesToSave[$attribute->getBackend()
                        ->getTable()][$entityId][$attributeParameters['id']] = $value;

                    // restore 'backend_model' to avoid default setting
                    $attribute->setBackendModel($backendModel);
                } else {
                    file_put_contents('missing_cust_attr.log', 'email: '.$emailInLowercase.' - attr_code: '. $attributeCode.PHP_EOL,FILE_APPEND);
                }
            } else {
                $entityRow[$attributeCode] = $value;
            }
        }

        if ($newCustomer) {
            // create
            $entityRow['website_id'] = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];
            $entityRow['email'] = $emailInLowercase;
            $entityRow['is_active'] = 1;
            $entitiesToCreate[] = $entityRow;
        } else {
            // edit
            $entitiesToUpdate[] = $entityRow;
        }

        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
            self::ATTRIBUTES_TO_SAVE_KEY => $attributesToSave
        ];
    }

}
