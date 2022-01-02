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

class ConfigWriter extends ConfigAbstract {
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;
    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $appConfig;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    private $configCollectionFactory;

    /**
     * ConfigWriter constructor.
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig
    )
    {
        $this->configWriter = $configWriter;
        $this->appConfig = $appConfig;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    public function writeByAttribute($attributeId, $values, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addScopeFilter($scope, $scopeId, self::CONFIG_BASE_PATH . "/" . $attributeId);
        foreach ($configCollection as $oldConfig)
        {
            $field = str_replace(self::CONFIG_BASE_PATH . "/" . $attributeId . "/", "", $oldConfig->getPath());
            if(!isset($values[$field]))
                $this->configWriter->delete($oldConfig->getPath(), $scope, $scopeId);
        }
        $baseConfig = array_keys($this->getBaseConfig());
        if(isset($values[self::CONFIG_REGEX_PATTERN]) && is_array($values[self::CONFIG_REGEX_PATTERN]))
        {
            $regexes = [];
            foreach ($values[self::CONFIG_REGEX_PATTERN] as $regex)
            {
                if(!isset($regex["delete"]) && !empty($regex["value"]))
                    $regexes[] = ["value" => $regex["value"]];
            }
            $values[self::CONFIG_REGEX_PATTERN] = serialize($regexes);
        }
        foreach ($values as $path => $value)
        {
            if($path !== "use_default")
            {
                if(isset($values["use_default"][$path])) {
                    if ($values["use_default"][$path]==="true") {
                        $this->configWriter->delete(self::CONFIG_BASE_PATH . "/" . $attributeId . "/" . $path, $scope, $scopeId);
                        continue;
                    }
                }
                if(in_array($path, $baseConfig)) {
                    $this->configWriter->save(self::CONFIG_BASE_PATH . "/" . $attributeId . "/" . $path, $value, $scope, $scopeId);
                }
            }
        }
        $this->appConfig->reinit();
    }
}