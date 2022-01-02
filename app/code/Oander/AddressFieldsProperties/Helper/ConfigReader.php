<?php
/**
 * Address Fields Properties
 * Copyright (C) 2019
 *
 * This file is part of Oander/AddressFieldsProperties.
 *
 * Oander/AddressFieldsProperties is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\AddressFieldsProperties\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigReader extends ConfigAbstract {

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $configCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigReader constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory
    )
    {
        $this->configCollectionFactory = $configCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $scope
     * @param null|int $scopeCode
     * @return array
     */
    public function readAll($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if($scopeCode)
            $scopeCode = (int)$scopeCode;

        $configArray = $this->scopeConfig->getValue(self::CONFIG_BASE_PATH, $scope, $scopeCode);
        foreach ($configArray as $attributeId => &$item)
        {
            if(isset($item[self::CONFIG_REGEX_PATTERN]))
            {
                $item[self::CONFIG_REGEX_PATTERN] = unserialize($item[self::CONFIG_REGEX_PATTERN]);
            }
            $item = array_replace_recursive(self::getBaseConfigWithDefault(), $item);
        }
        return $configArray;
    }

    /**
     * @param int $attributeId
     * @param string $scope
     * @param int|null $scopeCode
     * @return array
     */
    public function readByAttribute($attributeId, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        if($scopeCode)
            $scopeCode = (int)$scopeCode;

        $config = $this->scopeConfig->getValue(self::CONFIG_BASE_PATH . "/" . $attributeId, $scope, $scopeCode);

        if(isset($config[self::CONFIG_REGEX_PATTERN]))
        {
            $config[self::CONFIG_REGEX_PATTERN] = unserialize($config[self::CONFIG_REGEX_PATTERN]);
        }
        if(is_array($config))
            $config = array_replace_recursive(self::getBaseConfigWithDefault(), $config);
        else
            $config = self::getBaseConfigWithDefault();
        $config["attribute_id"] = $attributeId;
        return $config;
    }

    /**
     * @param int $attributeId
     * @param bool $getIsUseDefaultFlag
     * @param string $scope
     * @param int|null $scopeCode
     * @return array
     */
    public function getNotDefaultConfigsByAttribute($attributeId, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $result = [];
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addScopeFilter($scope, $scopeId, self::CONFIG_BASE_PATH . "/" . $attributeId);
        foreach ($configCollection as $scopeConfig)
        {
            $result[] = str_replace(self::CONFIG_BASE_PATH . "/" . $attributeId . "/", "", $scopeConfig->getPath());
        }
        return $result;
    }
}