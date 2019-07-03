<?php

/**
 * Product:       Xtento_OrderExport (2.3.9)
 * ID:            /weozjdbjPRa2i5d7NoLDIk5JMs58DgGlLfhYgTQlcs=
 * Packaged:      2017-10-27T08:27:16+00:00
 * Last Modified: 2017-08-10T11:13:28+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Data/Shared/Items.php
 * Copyright:     Copyright (c) 2017 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Oander\IstyleCustomization\Model\Export\Data\Shared;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\ValueFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Tax\Model\Sales\Order\TaxFactory;
use Oander\BundlePriceSwitcher\Enum\Option;
use Oander\BundlePriceSwitcher\Helper\Selection as SelectionHelper;
use Oander\SalesforceReservation\Api\Data\OrderItemInterface;
use Xtento\OrderExport\Model\Export;
use Xtento\XtCore\Helper\Date;
use Xtento\XtCore\Helper\Utils;

/**
 * Class Items
 * @package Oander\IstyleCustomization\Model\Export\Data\Shared
 */
class Items extends \Xtento\OrderExport\Model\Export\Data\Shared\Items
{
    /**
     * @var SelectionHelper
     */
    protected $selectionHelper;

    /**
     * Items constructor.
     *
     * @param Context                     $context
     * @param Registry                    $registry
     * @param Date                        $dateHelper
     * @param Utils                       $utilsHelper
     * @param ItemFactory                 $orderItemFactory
     * @param StockRegistryInterface      $stockRegistry
     * @param MessageFactory              $giftMessageFactory
     * @param TaxFactory                  $taxFactory
     * @param CollectionFactory           $optionValueCollectionFactory
     * @param ValueFactory                $optionValueFactory
     * @param ProductRepositoryInterface  $productRepository
     * @param AttributeSetRepository      $attributeSetRepository
     * @param Config                      $mediaConfig
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SelectionHelper             $selectionHelper
     * @param AbstractResource|null       $resource
     * @param AbstractDb|null             $resourceCollection
     * @param array                       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Date $dateHelper,
        Utils $utilsHelper,
        ItemFactory $orderItemFactory,
        StockRegistryInterface $stockRegistry,
        MessageFactory $giftMessageFactory,
        TaxFactory $taxFactory,
        CollectionFactory $optionValueCollectionFactory,
        ValueFactory $optionValueFactory,
        ProductRepositoryInterface $productRepository,
        AttributeSetRepository $attributeSetRepository,
        Config $mediaConfig,
        CategoryRepositoryInterface $categoryRepository,
        SelectionHelper $selectionHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $orderItemFactory, $stockRegistry, $giftMessageFactory, $taxFactory, $optionValueCollectionFactory, $optionValueFactory, $productRepository, $attributeSetRepository, $mediaConfig, $categoryRepository, $resource, $resourceCollection, $data);
        $this->selectionHelper = $selectionHelper;
    }

    /**
     * @param $entityType
     * @param $collectionItem
     *
     * @return array
     */
    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray      = [];
        $this->writeArray = & $returnArray['items'];
        // Fetch fields to export
        $object = $collectionItem->getObject();
        #$order = $collectionItem->getOrder();
        $items = $object->getAllItems();
        if (empty($items) || (!$this->fieldLoadingRequired('items') && !$this->fieldLoadingRequired('tax_rates') && !$this->fieldLoadingRequired('packages/') && !$this->fieldLoadingRequired('_total_cost'))) {
            return $returnArray;
        }

        // Export item information
        $taxRates        = [];
        $taxBaseAmounts  = [];
        $itemCount       = 0;
        $totalQty        = 0;
        $this->totalCost = 0;
        foreach ($items as $item) {
            $orderItem = false;
            // Check if this product type should be exported
            if ($this->getProfile() && $item->getProductType() && in_array($item->getProductType(), explode(",", $this->getProfile()->getExportFilterProductType()))) {
                continue; // Product type should be not exported
            }
            if ($this->getProfile() && !$item->getProductType() && $this->getProfile()->getExportFilterProductType() !== '' && $entityType !== Export::ENTITY_ORDER && $entityType !== Export::ENTITY_QUOTE && $entityType !== Export::ENTITY_AWRMA && $entityType !== Export::ENTITY_BOOSTRMA) {
                // We are not exporting orders, but need to check the product type - thus, need to load the order item.
                $orderItem = $this->orderItemFactory->create()->load($item->getOrderItemId());
                if ($orderItem->getProductType() && in_array($orderItem->getProductType(), explode(",", $this->getProfile()->getExportFilterProductType()))) {
                    continue; // Product type should be not exported
                }
            }
            // Get information from parent item if item price is 0
            /*if ($item->getPrice() == 0 && $item->getParentItem()) {
              $item = $item->getParentItem();
            }*/
            // Export general item information
            $this->writeArray     = &$returnArray['items'][];
            $this->origWriteArray = & $this->writeArray;
            $itemCount++;
            if ($entityType == Export::ENTITY_ORDER || $entityType == Export::ENTITY_AWRMA || $entityType == Export::ENTITY_BOOSTRMA) {
                $itemQty = $item->getQtyOrdered();
            } else {
                $itemQty = $item->getQty();
            }
            $totalQty += $itemQty;
            $this->writeValue('qty_ordered', $itemQty); // Legacy
            $this->writeValue('qty', $itemQty);

            $this->writeValue('item_number', $itemCount);
            $this->writeValue('order_product_number', $itemCount); // Legacy
            foreach ($item->getData() as $key => $value) {
                if ($key == 'qty_ordered' || $key == 'qty') continue;
                $this->writeValue($key, $value);
            }

            // Stock level
            if ($this->fieldLoadingRequired('qty_in_stock')) {
                $stockLevel = 0;
                $stockItem  = $this->stockRegistry->getStockItem($item->getProductId());
                if ($stockItem->getId()) {
                    $stockLevel = $stockItem->getQty();
                }
                $this->writeValue('qty_in_stock', $stockLevel);
            }

            // Magestore_Giftvoucher, export giftcards purchased
            /*
            $giftVouchers = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()->addItemFilter($item->getQuoteItemId());
            $codesPurchased = "";
            if ($giftVouchers->getSize()) {
                $giftVouchersCode = array();
                foreach ($giftVouchers as $giftVoucher) {
                    $giftVouchersCode[] = $giftVoucher->getGiftCode() . ' | ';
                }
                $codesPurchased = trim(trim(implode("", $giftVouchersCode)), '|');
                $this->writeValue('giftcards_purchased', $codesPurchased);
            }
            // Get gift cert redeemed (Put this in Shared/General.php)
            $giftVouchers = \Magento\Framework\App\ObjectManager::getInstance()->create('Magestore\Giftvoucher\Model\History')
                ->getCollection()
                ->joinGiftVoucher()
                ->addFieldToFilter('main_table.order_increment_id', $object->getIncrementId());
            $codesArray = array();
            $amountArray = array();
            foreach ($giftVouchers as $giftVoucher) {
                $codesArray[] = $giftVoucher->getGiftCode();
                // get original amount
                $origGiftVoucher = \Magento\Framework\App\ObjectManager::getInstance()->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()->addFieldToFilter('gift_code', $giftVoucher->getGiftCode())->joinHistory()->getFirstItem();
                $amountArray[] = $origGiftVoucher->getData('history_amount') . " | " . $giftVoucher->getAmount();
            }
            $codesRedeemed = trim(trim(implode("", $codesArray)), '|');
            $gcAmounts = trim(trim(implode("", $amountArray)), '|');
            $this->writeValue('giftcards_redeemed', $codesRedeemed);
            $this->writeValue('giftcards_amounts', $gcAmounts);
            */

            // (M1) Enterprise Gift Wrapping information
            /*if ($this->fieldLoadingRequired('enterprise_giftwrapping') && $this->utilsHelper->isMagentoEnterprise()) {
                if ($item->getGwId()) {
                    $this->writeArray['enterprise_giftwrapping'] = [];
                    $this->writeArray =& $this->writeArray['enterprise_giftwrapping'];
                    $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping')->load($item->getGwId());
                    if ($wrapping->getId()) {
                        foreach ($wrapping->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                        $this->writeValue('image_url', $wrapping->getImageUrl());
                    }
                }
            }*/

            // Repeat SKU by qty ordered, i.e. if "test" is ordered twice output test,test
            if ($this->fieldLoadingRequired('sku_repeated_by_qty')) {
                $this->writeValue('sku_repeated_by_qty', implode(",", array_fill(0, $itemQty, $item->getSku())));
            }

            if ($this->fieldLoadingRequired(OrderItemInterface::ATTRIBUTE_PRIMARY)) {
                $reservationPrimary = $item->getData(OrderItemInterface::ATTRIBUTE_PRIMARY) ?? 'null';
                $this->writeValue(OrderItemInterface::ATTRIBUTE_PRIMARY, $reservationPrimary);
            }

            // Add fields of order item for invoice exports
            $taxItem = false;
            if ($entityType !== Export::ENTITY_ORDER && $entityType !== Export::ENTITY_QUOTE && $entityType !== Export::ENTITY_AWRMA && $entityType !== Export::ENTITY_BOOSTRMA && ($this->fieldLoadingRequired('order_item') || $this->fieldLoadingRequired('tax_rates') || $this->fieldLoadingRequired('custom_options'))) {
                $this->writeArray['order_item'] = [];
                $this->writeArray               =& $this->writeArray['order_item'];
                if ($item->getOrderItemId()) {
                    if (!$orderItem) {
                        $orderItem = $this->orderItemFactory->create()->load($item->getOrderItemId());
                    }
                    if ($orderItem->getId()) {
                        $taxItem = $orderItem;
                        foreach ($orderItem->getData() as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                }
                $this->writeArray = & $this->origWriteArray;
                $tempOrigArray    = &$this->writeArray;
                if ($this->fieldLoadingRequired('custom_options') && $options = $orderItem->getProductOptions()) {
                    // Export custom options
                    $this->writeCustomOptions($options, $this->origWriteArray, $object, $orderItem->getProductId());
                }
                $this->writeArray =& $tempOrigArray;
            } else {
                $taxItem = $item;
            }

            // Gift message
            if ($this->fieldLoadingRequired('gift_message')) {
                $giftMessageId = $item->getGiftMessageId();
                if (!$giftMessageId && $orderItem) {
                    $giftMessageId = $orderItem->getGiftMessageId();
                }
                $giftMessageModel = $this->giftMessageFactory->create()->load($giftMessageId);
                if ($giftMessageModel->getId()) {
                    $this->writeValue('gift_message_sender', $giftMessageModel->getSender());
                    $this->writeValue('gift_message_recipient', $giftMessageModel->getRecipient());
                    $this->writeValue('gift_message', $giftMessageModel->getMessage());
                } else {
                    $this->writeValue('gift_message_sender', '');
                    $this->writeValue('gift_message_recipient', '');
                    $this->writeValue('gift_message', '');
                }
            }

            // Get parent item
            $parentItem = $item->getParentItem();
            if (!$parentItem && $orderItem) {
                $parentItemId = $orderItem->getParentItemId();
                if ($parentItemId) {
                    $parentItem = $this->orderItemFactory->create()->load($parentItemId);
                }
            }
            // Note: Parent item may be wrong for non-order exports (such as credit memos) as there is no parent_item_id field and thus getParentItem() fails. Theoretically an approach like this could be used, but has never been tested:
            // (M1 code): $parentItem = Mage::getModel('sales/order_creditmemo_item')->load($parentItem->getOrderItemId(), 'order_item_id’);

            // Get bundle price
            $productOptions = $item->getProductOptions();
            if ($parentItem && $parentItem->getProductType() == Type::TYPE_BUNDLE) {
                if (!isset($productOptions['bundle_selection_attributes']) && $parentItem) {
                    $productOptions = $parentItem->getProductOptions();
                }
                if (isset($productOptions['bundle_selection_attributes'])) {
                    if (version_compare($this->utilsHelper->getMagentoVersion(), '2.2', '>=')) {
                        $bundleOptions = @json_decode($productOptions['bundle_selection_attributes']);
                    } else {
                        $bundleOptions = @unserialize($productOptions['bundle_selection_attributes']);
                    }
                    if (isset($bundleOptions['price'])) {
                        $this->writeValue('is_bundle', true);
                        $this->writeValue('bundle_price', $bundleOptions['price']);
                    }
                }
            }

            if ($this->fieldLoadingRequired('product_options_data') && $productOptions && is_array($productOptions)) {
                $this->writeArray['product_options_data'] = [];
                $this->writeArray                         = &$this->origWriteArray['product_options_data'];
                foreach ($productOptions as $productOptionKey => $productOptionValue) {
                    if (($productOptionKey == 'giftcard_created_codes' || $productOptionKey == 'giftcard_sent_codes') && is_array($productOptionValue)) {
                        $productOptionValue = implode(",", $productOptionValue);
                    }
                    if (!is_array($productOptionKey) && !is_object($productOptionKey) && !is_object($productOptionValue)) {
                        $this->writeValue($productOptionKey, $productOptionValue);
                    }
                }
                $this->writeArray = & $this->origWriteArray;
            }

            /*if ($this->fieldLoadingRequired('info_buyrequest') && $productOptions && isset($productOptions['info_buyRequest']) && is_array($productOptions['info_buyRequest'])) {
                $this->writeArray['info_buyrequest'] = [];
                $this->writeArray = & $this->origWriteArray['info_buyrequest'];
                foreach ($productOptions['info_buyRequest'] as $productOptionKey => $productOptionValue) {
                    if (!is_array($productOptionKey) && !is_object($productOptionKey) && !is_array($productOptionValue) && !is_object($productOptionValue)) {
                        $this->writeValue($productOptionKey, $productOptionValue);
                    }
                }
                $this->writeArray = & $this->origWriteArray;
            }*/
            if ($this->fieldLoadingRequired('additional_options') && $productOptions && isset($productOptions['additional_options']) && is_array($productOptions['additional_options'])) {
                $this->writeArray['additional_options'] = [];
                foreach ($productOptions['additional_options'] as $additionalOption) {
                    $this->writeArray = & $this->origWriteArray['additional_options'][];
                    foreach ($additionalOption as $productOptionKey => $productOptionValue) {
                        if (!is_array($productOptionKey) && !is_object($productOptionKey) && !is_array($productOptionValue) && !is_object($productOptionValue)) {
                            $this->writeValue($productOptionKey, $productOptionValue);
                        }
                    }
                }
                $this->writeArray = & $this->origWriteArray;
            }
            /*
            if ($this->fieldLoadingRequired('swatch_data')) {
                // "Swatch Data" export
                if (isset($productOptions['info_buyRequest']['swatchData']) && is_array($productOptions['info_buyRequest']['swatchData'])) {
                    $this->writeArray['swatch_data'] = [];
                    foreach ($productOptions['info_buyRequest']['swatchData'] as $swatchId => $swatchData) {
                        $this->writeArray = & $this->origWriteArray['swatch_data'][];
                        foreach ($swatchData as $key => $value) {
                            $this->writeValue($key, $value);
                        }
                    }
                    $this->writeArray = & $this->origWriteArray;
                }
                // End "Swatch Data"
            }*/

            /*if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE && $this->fieldLoadingRequired('downloadable_links')) {
                $productOptions = $item->getProductOptions();
                if ($productOptions) {
                    if (isset($productOptions['links']) && is_array($productOptions['links'])) {
                        $this->writeArray['downloadable_links'] = [];
                        $downloadableLinksCollection = Mage::getModel('downloadable/link')->getCollection()
                            ->addTitleToResult()
                            ->addFieldToFilter('`main_table`.link_id', ['in' => $productOptions['links']]);
                        foreach ($downloadableLinksCollection as $downloadableLink) {
                            $this->writeArray = & $this->origWriteArray['downloadable_links'][];
                            foreach ($downloadableLink->getData() as $downloadableKey => $downloadableValue) {
                                $this->writeValue($downloadableKey, $downloadableValue);
                            }
                        }
                        $this->writeArray = & $this->origWriteArray;
                    }
                }
            }*/

            // Save tax information for order
            if ($taxItem && $item->getBaseTaxAmount() > 0 && $taxItem->getTaxPercent() > 0) {
                $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxItem->getTaxPercent()));
                if (!isset($taxRates[$taxPercent])) {
                    $taxRates[$taxPercent]       = $item->getBaseTaxAmount();
                    $taxBaseAmounts[$taxPercent] = $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();
                } else {
                    $taxRates[$taxPercent]       += $item->getBaseTaxAmount();
                    $taxBaseAmounts[$taxPercent] += $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();
                }
            }

            if ($this->fieldLoadingRequired('_total_cost')) {
                $this->totalCost += ($item->getBaseCost() * $item->getQtyOrdered());
                $this->writeValue('product_total_cost', ($item->getBaseCost() * $item->getQtyOrdered()));
            }

            // Add fields of parent item
            if ($this->fieldLoadingRequired('parent_item') && $parentItem) {
                $this->writeArray['parent_item'] = [];
                $this->writeArray                =& $this->writeArray['parent_item'];
                $tempOrigArray                   = &$this->writeArray;
                foreach ($parentItem->getData() as $key => $value) {
                    $this->writeValue($key, $value);
                }
                // Parent Item Gift Message
                if ($this->fieldLoadingRequired('gift_message')) {
                    $giftMessageId    = $parentItem->getGiftMessageId();
                    $giftMessageModel = $this->giftMessageFactory->create()->load($giftMessageId);
                    if ($giftMessageModel->getId()) {
                        $this->writeValue('gift_message_sender', $giftMessageModel->getSender());
                        $this->writeValue('gift_message_recipient', $giftMessageModel->getRecipient());
                        $this->writeValue('gift_message', $giftMessageModel->getMessage());
                    } else {
                        $this->writeValue('gift_message_sender', '');
                        $this->writeValue('gift_message_recipient', '');
                        $this->writeValue('gift_message', '');
                    }
                }
                // Export parent product options
                if ($this->fieldLoadingRequired('custom_options') && $options = $parentItem->getProductOptions()) {
                    $this->writeCustomOptions($options, $this->writeArray, $object, $parentItem->getProductId());
                }
                $this->writeArray =& $tempOrigArray;
                if ($this->fieldLoadingRequired('product_attributes')) {
                    $this->writeProductAttributes($object, $parentItem, true);
                }
                $this->writeArray =& $tempOrigArray;
            }
            $this->writeArray = & $this->origWriteArray;
            // Export product attributes
            if ($this->fieldLoadingRequired('product_attributes')) {
                $this->writeProductAttributes($object, $item, false);
            }

            $this->writeArray = & $this->origWriteArray;
            // Export product options
            if ($this->fieldLoadingRequired('custom_options') && $options = $item->getProductOptions()) {
                // Export custom options
                $this->writeCustomOptions($options, $this->origWriteArray, $object, $item->getProductId());

                if (isset($options['bundle_selection_attributes']) && is_string($options['bundle_selection_attributes']) && $parentItem) {
                    $bundleSelectionsAttributes = unserialize($options['bundle_selection_attributes']);
                    if (isset($bundleSelectionsAttributes['option_id'])) {
                        $bOptions = $this->selectionHelper->getProductOptions((int) $parentItem->getProductId());
                        if (isset($bOptions[$bundleSelectionsAttributes['option_id']])) {
                            $bOption = $bOptions[$bundleSelectionsAttributes['option_id']];
                            $this->writeValue(Option::BASE_OPTION, (bool) $bOption->getData(Option::BASE_OPTION));
                        }
                    }
                }

                // Export $options["attributes_info"].. maybe?
            }

            // Sample code to get ugiftcert gift certificate information:
            /*
             $giftCerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
             if (count($giftCerts)) {
                foreach ($giftCerts as $giftCert) {
                    if (isset($giftCert['cert_number'])) {
                        ...
                    }
                }
             }
             */
        }

        // Sample code to add specific things/amounts as line items:
        /*if ($object->getGiftMessageId() > 0) {
            $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($object->getGiftMessageId());
            $returnArray['items'][] = array(
                'sku' => 'MESSAGE',
                'qty_ordered' => 1,
                'qty' => 1,
                'price' => 0,
                'discount_percent' => '0',
                'custom_options' => array('custom_option' => array('value' => $giftMessage->getMessage()))
            );
        }*/

        $this->writeArray = & $returnArray;
        $this->writeValue('export_total_qty_ordered', $totalQty);
        $this->writeValue('products_total_cost', $this->totalCost);

        // Add tax amounts of other fees to $taxRates
        // Shipping
        $shippingAmount    = 0;
        $shippingTaxAmount = 0;
        if ($entityType == Export::ENTITY_ORDER) {
            $shippingAmount    = $object->getData('base_shipping_amount');
            $shippingTaxAmount = $object->getData('base_shipping_tax_amount');
        }
        if ($entityType == Export::ENTITY_INVOICE) {
            $shippingAmount    = $object->getData('base_shipping_amount');
            $shippingTaxAmount = $object->getData('base_shipping_tax_amount');
        }
        if ($entityType == Export::ENTITY_CREDITMEMO) {
            $shippingAmount    = $object->getData('base_shipping_amount');
            $shippingTaxAmount = $object->getData('base_shipping_tax_amount');
        }
        if ($shippingAmount > 0 && $shippingTaxAmount > 0) {
            $taxPercent = round($shippingTaxAmount / $shippingAmount * 100);
            $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxPercent));
            if (!isset($taxRates[$taxPercent])) {
                $taxRates[$taxPercent]       = $shippingTaxAmount;
                $taxBaseAmounts[$taxPercent] = $shippingAmount + $shippingTaxAmount;
            } else {
                $taxRates[$taxPercent]       += $shippingTaxAmount;
                $taxBaseAmounts[$taxPercent] += $shippingAmount + $shippingTaxAmount;
            }
        }
        // Cash on Delivery
        $codFee    = 0;
        $codFeeTax = 0;
        if ($entityType == Export::ENTITY_ORDER) {
            $codFee    = $object->getBaseCodFee();
            $codFeeTax = $object->getBaseCodTaxAmount();
        }
        if ($entityType == Export::ENTITY_INVOICE) {
            $codFee    = $object->getOrder()->getData('base_cod_fee_invoiced');
            $codFeeTax = $object->getOrder()->getData('base_cod_tax_amount_invoiced');
        }
        if ($entityType == Export::ENTITY_CREDITMEMO) {
            $codFee    = $object->getOrder()->getData('base_cod_fee_refunded');
            $codFeeTax = $object->getOrder()->getData('base_cod_tax_amount_refunded');
        }
        if ($codFee > 0 && $codFeeTax > 0) {
            $taxPercent = round($codFeeTax / $codFee * 100);
            $taxPercent = str_replace('.', '_', sprintf('%.4f', $taxPercent));
            if (!isset($taxRates[$taxPercent])) {
                $taxRates[$taxPercent]       = $codFeeTax;
                $taxBaseAmounts[$taxPercent] = $codFee + $codFeeTax;
            } else {
                $taxRates[$taxPercent]       += $codFeeTax;
                $taxBaseAmounts[$taxPercent] += $codFee + $codFeeTax;
            }
        }

        // At least provide a 0% tax rate if no tax was found, as no tax was charged then
        if (empty($taxRates)) {
            $taxRates = ['0_0000' => ''];
        }

        // Export tax information
        $this->writeArray['tax_rates'] = [];
        if ($this->fieldLoadingRequired('tax_rates')) {
            $grandTotalInclTax = $object->getGrandTotal();
            foreach ($taxRates as $taxRate => $taxAmount) {
                if ($taxRate == '0_0000') continue;
                $taxBaseAmount    = $taxBaseAmounts[$taxRate];
                $taxRate          = str_replace('_', '.', $taxRate);
                $this->writeArray = & $returnArray['tax_rates'][];
                $this->writeValue('rate', $taxRate);
                $this->writeValue('amount', $taxAmount);
                $this->writeValue('base', $taxBaseAmount);
                $grandTotalInclTax -= $taxBaseAmount;
            }
            if (isset($taxRates['0_0000'])) {
                $this->writeArray = & $returnArray['tax_rates'][];
                $this->writeValue('rate', '0.0000');
                $this->writeValue('amount', '0.0000');
                $this->writeValue('base', $grandTotalInclTax);
            }
        }
        $this->writeArray                    = &$returnArray;
        $this->writeArray['order_tax_rates'] = [];
        if ($this->fieldLoadingRequired('order_tax_rates')) {
            $taxRateCollection = $this->taxFactory->create()->getCollection()->loadByOrder($collectionItem->getOrder());
            if ($taxRateCollection->getSize()) {
                foreach ($taxRateCollection as $taxRate) {
                    $this->writeArray = & $returnArray['order_tax_rates'][];
                    foreach ($taxRate->getData() as $key => $value) {
                        if ($key == 'percent') $key = 'rate';
                        $this->writeValue($key, $value);
                    }
                    // Write "base_tax_base" - the base the tax_amount was calculated on
                    $this->writeValue('base_tax_base', ($taxRate->getBaseAmount() / ($taxRate->getPercent() / 100)) + $taxRate->getBaseAmount());
                }
            }
        }

        /*
        $this->writeArray = & $returnArray;
        $packageCollection = Mage::getModel('shipusa/packages')->getCollection()->addQuoteFilter($object->getQuoteId());
        $packageCount = 0;
        $this->writeArray['packages'] = [];
        foreach ($packageCollection as $package) {
            $packageCount++;
            $this->writeArray = & $returnArray['packages'][];
            $this->writeValue('weight', $package->getWeight());
            $this->writeValue('counter', $packageCount);
        }
        */

        // Done
        return $returnArray;
    }
}