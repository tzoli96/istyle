<?php

namespace Oander\AddressFieldsProperties\Helper;

/**
 * Class Config
 */
class Config
{
    /**
     * @var array
     */
    private $properties = [];

    /**
     * Config constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Oander\AddressFieldsProperties\Helper\ConfigReader $configReader
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavCollection,
        \Oander\AddressFieldsProperties\Helper\ConfigReader $configReader
    )
    {
        $eavCollection->addFieldToSelect("attribute_id");
        $eavCollection->addFieldToSelect("attribute_code");
        $idToCode = $eavCollection->getConnection()->fetchPairs($eavCollection->getSelect());
        foreach ($configReader->readAll(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeManager->getStore()->getId()) as $attributeId => $properties)
        {
            if(isset($idToCode[$attributeId]))
            {
                $this->properties[$idToCode[$attributeId]] = $properties;
            }
        }
    }

    public function getAttributeProperties($attributeCode)
    {
        return $this->properties[$attributeCode]??[];
    }

    public function getPlaceholder($attributeCode)
    {
        $config = (new \Magento\Framework\DataObject())->setData($this->getAttributeProperties($attributeCode));
        if(!empty($config->getData(ConfigAbstract::CONFIG_PLACEHOLDER)))
        {
            return $config->getData(ConfigAbstract::CONFIG_PLACEHOLDER);
        }
        return false;
    }

    public function getErrorMessage($attributeCode)
    {
        $config = (new \Magento\Framework\DataObject())->setData($this->getAttributeProperties($attributeCode));
        if(!empty($config->getData(ConfigAbstract::CONFIG_ERROR_MESSAGE)))
        {
            return $config->getData(ConfigAbstract::CONFIG_ERROR_MESSAGE);
        }
        return false;
    }

    public function getValidations($attributeCode)
    {
        $config = (new \Magento\Framework\DataObject())->setData($this->getAttributeProperties($attributeCode));
        $result = [];

        //Need from jquery validator, Cleave.js do not have param like this. Used for validation and not for field format

        switch($config->getData(ConfigAbstract::CONFIG_VALIDATION_TYPE))
        {
            case \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_STRINGLENGTH:
            {
                if($config->getData(ConfigAbstract::CONFIG_STRING_LENGTH))
                {
                    $result["oandervalidate-length"] = [
                        $config->getData(ConfigAbstract::CONFIG_ERROR_MESSAGE),
                        $config->getData(ConfigAbstract::CONFIG_STRING_LENGTH)
                    ];
                }
                break;
            }
            case \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_FULLREGEX:
            {
                if(
                    $config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN) &&
                    is_array($config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN))
                )
                {
                    $result["oandervalidate-regex"] = [$config->getData(ConfigAbstract::CONFIG_ERROR_MESSAGE)];
                    foreach ($config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN) as $regex) {
                        if(isset($regex["value"]))
                            $result["oandervalidate-regex"][] = $regex["value"];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get prepared Config for Classes
     * @return array
     */
    public function getFormattingClasses($attributeCode, $returnValidations = false) : array
    {
        $config = (new \Magento\Framework\DataObject())->setData($this->getAttributeProperties($attributeCode));
        $result = [];
        //Need from Cleave.js
        if($config->getData(ConfigAbstract::CONFIG_ENABLE_FORMATTING)) {
            if (!empty($config->getData(ConfigAbstract::CONFIG_BLOCKS))) {
                $result[] = "cleave-pattern--blocks-{" . $config->getData(ConfigAbstract::CONFIG_BLOCKS) . "}";
            }
            if ($config->getData(ConfigAbstract::CONFIG_NUMBERS_ONLY)) {
                $result[] = "cleave-pattern--numericOnly-{1}";
                if (empty($config->getData(ConfigAbstract::CONFIG_BLOCKS))) {
                    $result[] = "cleave-pattern--numeral-{1}";
                    $result[] = "cleave-pattern--numeralDecimalScale-{0}";
                    $result[] = "cleave-pattern--numeralThousandsGroupStyle-{0}";
                }
            }
            if (!empty($config->getData(ConfigAbstract::CONFIG_PREFIX))) {
                $result[] = "cleave-pattern--prefix-{" . $config->getPrefix() . "}";
                $result[] = "cleave-pattern--noImmediatePrefix-{1}";
            }
            if(!empty($config->getData(ConfigAbstract::CONFIG_CASE))) {
                if ($config->getData(ConfigAbstract::CONFIG_CASE) == \Oander\AddressFieldsProperties\Enum\CaseEnum::CASE_UPPERCASE) {
                    if (empty($config->getBlocks())) {
                        $result[] = "cleave-pattern--blocks-{1024}";
                    }
                    $result[] = "cleave-pattern--uppercase-{1}";
                }
                if ($config->getData(ConfigAbstract::CONFIG_CASE) == \Oander\AddressFieldsProperties\Enum\CaseEnum::CASE_LOWERCASE) {
                    if (empty($config->getData(ConfigAbstract::CONFIG_BLOCKS))) {
                        $result[] = "cleave-pattern--blocks-{1024}";
                    }
                    $result[] = "cleave-pattern--lowercase-{1}";
                }
            }

            if (is_string($config->getData(ConfigAbstract::CONFIG_DELIMITERS))) {
                //Delimiters can be "'-','$'" as string, in this case the "'" char need to be removed only if it is not a delimiter
                $delimiters = preg_split("/(?:'[^']*'|)\K\s*(,\s*|$)/", $config->getData(ConfigAbstract::CONFIG_DELIMITERS));
                if (count($delimiters) > 2) {
                    $count = 1;
                    foreach ($delimiters as $delimiter) {
                        if ($delimiter) {
                            $result[] = "cleave-pattern--delimiters-{" . $delimiter . "}-" . $count;
                            $count++;
                        }
                    }
                } elseif (count($delimiters) == 2) {
                    $result[] = "cleave-pattern--delimiter-{" . substr(substr($delimiters[0], 1), 0, -1) . "}";
                } else {
                }
            }
            if (count($result)) {
                $result[] = "cleave-pattern";
            }
        }

        //Need from jquery validator, Cleave.js do not have param like this. Used for validation and not for field format
        if($returnValidations) {
            switch ($config->getData(ConfigAbstract::CONFIG_VALIDATION_TYPE)) {
                case \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_STRINGLENGTH:
                {
                    if ($config->getData(ConfigAbstract::CONFIG_STRING_LENGTH)) {
                        $result[] = "oandervalidate-length";
                        $result[] = "oandervalidate-length-" . $config->getData(ConfigAbstract::CONFIG_STRING_LENGTH);
                    }
                    break;
                }
                case \Oander\AddressFieldsProperties\Enum\ValidationType::VALIDATIONTYPE_FULLREGEX:
                {
                    if (
                        $config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN) &&
                        is_array($config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN))
                    ) {
                        $result[] = "oandervalidate-regex";
                        $i = 0;
                        foreach ($config->getData(ConfigAbstract::CONFIG_REGEX_PATTERN) as $regex) {
                            if(isset($regex["value"])) {
                                $result[] = "oandervalidate-regex-" . $i . "-" . str_replace(" ", "&nbsp;", htmlspecialchars($regex["value"]));
                                $i++;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
