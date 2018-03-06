<?php
/**
 * Oander_IstyleImportTemp
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleImportTemp\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product\Type\Simple as MagentoSimple;

/**
 * Class Simple
 *
 * @package Oander\IstyleImportTemp\Model\Import\Product\Type
 */
class Simple extends MagentoSimple
{
    /**
     * Prepare attributes values for save: exclude non-existent, static or with empty values attributes;
     * set default values if needed
     *
     * @param array $rowData
     * @param bool  $withDefaultValue
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = [];

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if ($attrParams['is_static']) {
                continue;
            }
            if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                if (in_array($attrParams['type'], ['select', 'boolean'])) {
                    if ($rowData[$attrCode][0] === '"' && substr($rowData[$attrCode], -1) === '"') {
                        $rowData[$attrCode] = str_split($rowData[$attrCode]);
                        unset($rowData[$attrCode][count($rowData[$attrCode]) - 1], $rowData[$attrCode][0]);
                        $rowData[$attrCode] = implode('', $rowData[$attrCode]);

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '""') !== false) {
                            $rowData[$attrCode] = str_replace('""', '"', $rowData[$attrCode]);
                        }
                    } elseif (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && in_array($rowData[$attrCode], $attrParams['options'])) {
                        $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                    } elseif ($attrCode === 'msrp_display_actual_price_type') {
                        $rowData[$attrCode] = (int)$rowData[$attrCode] - 1;
                        $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && strpos($rowData[$attrCode], '"') !== false) {
                        $rowData[$attrCode] = str_replace('"', '&quot;', $rowData[$attrCode]);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && strpos($rowData[$attrCode], '&quot;') !== false) {
                        $rowData[$attrCode] = str_replace('&quot;', '', $rowData[$attrCode]);
                    }

                    if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                        && $attrCode === 'config_size' && $rowData[$attrCode] === '0-5 m') {
                        $rowData[$attrCode] = '0,5 m';
                    }

                    if (isset($attrParams['options'][strtolower($rowData[$attrCode])])) {
                        $resultAttrs[$attrCode] = $attrParams['options'][strtolower($rowData[$attrCode])];
                    }

                } elseif ('multiselect' == $attrParams['type']) {
                    $resultAttrs[$attrCode] = [];
                    foreach ($this->_entityModel->parseMultiselectValues($rowData[$attrCode]) as $value) {

                        if ($value[0] === '"' && substr($value, -1) === '"') {
                            $value = str_split($value);
                            unset($value[count($value) - 1], $value[0]);
                            $value = implode('', $value);

                            if (!isset($attrParams['options'][strtolower($value)])
                                && strpos($value, '""') !== false) {
                                $value = str_replace('""', '"', $value);
                            }
                        } elseif (!isset($attrParams['options'][strtolower($value)])
                            && in_array($value, $attrParams['options'])) {
                            $value = array_search($value, $attrParams['options']);
                        } elseif ($attrCode === 'msrp_display_actual_price_type') {
                            $rowData[$attrCode] = (int)$rowData[$attrCode] - 1;
                            $rowData[$attrCode] = array_search($rowData[$attrCode], $attrParams['options']);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '"') !== false) {
                            $rowData[$attrCode] = str_replace('"', '&quot;', $rowData[$attrCode]);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && strpos($rowData[$attrCode], '&quot;') !== false) {
                            $rowData[$attrCode] = str_replace('&quot;', '', $rowData[$attrCode]);
                        }

                        if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])
                            && $attrCode === 'config_size' && $rowData[$attrCode] === '0-5 m') {
                            $rowData[$attrCode] = '0,5 m';
                        }

                        if (isset($attrParams['options'][strtolower($rowData[$attrCode])])) {
                            $resultAttrs[$attrCode][] = $attrParams['options'][strtolower($value)];
                        }

                    }
                    $resultAttrs[$attrCode] = implode(',', $resultAttrs[$attrCode]);
                } else {
                    $resultAttrs[$attrCode] = $rowData[$attrCode];
                }
            } elseif (array_key_exists($attrCode, $rowData)) {
                $resultAttrs[$attrCode] = $rowData[$attrCode];
            } elseif ($withDefaultValue && null !== $attrParams['default_value']) {
                $resultAttrs[$attrCode] = $attrParams['default_value'];
            }
        }

        return $resultAttrs;
    }

}
