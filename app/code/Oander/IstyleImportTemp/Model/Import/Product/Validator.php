<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\IstyleImportTemp\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator as MagentoValidator;

class Validator extends MagentoValidator
{
    /**
     * @param string $attrCode
     * @param array  $attrParams
     * @param array  $rowData
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundIsAttributeValid(MagentoValidator $subject, callable $proceed, $attrCode, array $attrParams, array $rowData)
    {
        $subject->_rowData = $rowData;
        if (isset($rowData['product_type']) && !empty($attrParams['apply_to'])
            && !in_array($rowData['product_type'], $attrParams['apply_to'])
        ) {
            return true;
        }

        if (!$subject->isRequiredAttributeValid($attrCode, $attrParams, $rowData)) {
            $valid = false;
            $subject->_addMessages(
                [
                    sprintf(
                        $subject->context->retrieveMessageTemplate(
                            RowValidatorInterface::ERROR_VALUE_IS_REQUIRED
                        ),
                        $attrCode
                    )
                ]
            );
            return $valid;
        }

        if (!strlen(trim($rowData[$attrCode]))) {
            return true;
        }
        switch ($attrParams['type']) {
            case 'varchar':
            case 'text':
                $valid = $subject->textValidation($attrCode, $attrParams['type']);
                break;
            case 'decimal':
            case 'int':
                $valid = $subject->numericValidation($attrCode, $attrParams['type']);
                break;
            case 'select':
            case 'boolean':
                $valid = $this->validateOption($attrCode, $attrParams['options'], $rowData[$attrCode]);
                break;
            case 'multiselect':
                $values = $subject->context->parseMultiselectValues($rowData[$attrCode]);
                foreach ($values as $value) {
                    $valid = $this->validateOption($attrCode, $attrParams['options'], $value);
                    if (!$valid) {
                        break;
                    }
                }
                break;
            case 'datetime':
                $val = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false;
                if (!$valid) {
                    $subject->_addMessages([RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
                }
                break;
            default:
                $valid = true;
                break;
        }

        if ($valid && !empty($attrParams['is_unique'])) {
            if (isset($subject->_uniqueAttributes[$attrCode][$rowData[$attrCode]])
                && ($subject->_uniqueAttributes[$attrCode][$rowData[$attrCode]] != $rowData[Product::COL_SKU])) {
                $subject->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE]);
                return false;
            }
            $subject->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = $rowData[Product::COL_SKU];
        }

        if (!$valid) {
            $subject->setInvalidAttribute($attrCode);
        }

        return (bool)$valid;

    }

    /**
     * Check if value is valid attribute option
     *
     * @param string $attrCode
     * @param array $possibleOptions
     * @param string $value
     * @return bool
     */
    private function validateOption($attrCode, $possibleOptions, $value)
    {
        if ($value[0] === '"' && substr($value, -1) === '"') {
            $value = str_split($value);
            unset($value[count($value) - 1], $value[0]);
            $value = implode('', $value);

            if (!isset($possibleOptions[strtolower($value)])
                && strpos($value, '""') !== false) {
                $value = str_replace('""', '"', $value);
            }
        } elseif (!isset($possibleOptions[strtolower($value)])
            && in_array($value, $possibleOptions)) {
            $value = array_search($value, $possibleOptions);
        }

        if (!isset($possibleOptions[strtolower($value)])) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(
                            RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_OPTION
                        ),
                        $attrCode
                    )
                ]
            );
            return false;
        }
        return true;
    }
}
