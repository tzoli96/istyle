<?php

namespace Oander\IstyleBase\Plugin\Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\Product\SaveHandler as OriginalSaveHandler;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Oander\IstyleBase\Plugin\Magento\Bundle\Model\Option\SaveAction;

class SaveHandler extends OriginalSaveHandler
{

    /**
     * @var SaveAction
     */
    private $optionSave;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        SaveAction $optionSave,
        OptionRepository $optionRepository,
        ProductLinkManagementInterface $productLinkManagement,
        MetadataPool $metadataPool
    )
    {
        $this->optionSave = $optionSave;
        $this->metadataPool = $metadataPool;
        parent::__construct($optionRepository, $productLinkManagement);
    }


    public function execute($entity, $arguments = [])
    {
        $bundleProductOptions = $entity->getExtensionAttributes()->getBundleProductOptions() ?: [];

        //Only processing bundle products.
        if ($entity->getTypeId() !== 'bundle' || empty($bundleProductOptions)) {
            return $entity;
        }
        $existingBundleProductOptions = $this->optionRepository->getList($entity->getSku());
        $existingOptionsIds = !empty($existingBundleProductOptions)
            ? $this->getOptionIds($existingBundleProductOptions)
            : [];
        $optionIds = !empty($bundleProductOptions)
            ? $this->getOptionIds($bundleProductOptions)
            : [];

        if (!$entity->getCopyFromView()) {
            $this->processRemovedOptions($entity, $existingOptionsIds, $optionIds);
            $newOptionsIds = array_diff($optionIds, $existingOptionsIds);
            $this->saveOptions($entity, $bundleProductOptions, $newOptionsIds);
        } else {
            //save only labels and not selections + product links
            $this->saveOptions($entity, $bundleProductOptions);
            $entity->setCopyFromView(false);
        }

        return $entity;
    }

    /**
     * Get options ids from array of the options entities.
     *
     * @param array $options
     * @return array
     */
    private function getOptionIds(array $options): array
    {
        $optionIds = [];

        if (!empty($options)) {
            /** @var OptionInterface $option */
            foreach ($options as $option) {
                if ($option->getOptionId()) {
                    $optionIds[] = $option->getOptionId();
                }
            }
        }

        return $optionIds;
    }

    /**
     * Perform save for all options entities.
     *
     * @param object $entity
     * @param array $options
     * @param array $newOptionsIds
     * @return void
     */
    private function saveOptions($entity, array $options, array $newOptionsIds = [])
    {
        foreach ($options as $option) {
            if (in_array($option->getOptionId(), $newOptionsIds, true)) {
                $option->setOptionId(null);
            }
            $this->optionSave->save($entity, $option);
        }
    }


    /**
     * @param string $entitySku
     * @param OptionInterface $option
     * @return void
     */
    protected function removeOptionLinks($entitySku, $option)
    {
        $links = $option->getProductLinks();
        if (!empty($links)) {
            foreach ($links as $link) {
                $this->productLinkManagement->removeChild($entitySku, $option->getId(), $link->getSku());
            }
        }
    }

    /**
     * Removes old options that no longer exists.
     *
     * @param ProductInterface $entity
     * @param array $existingOptionsIds
     * @param array $optionIds
     * @return void
     */
    private function processRemovedOptions(ProductInterface $entity, array $existingOptionsIds, array $optionIds)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $parentId = $entity->getData($metadata->getLinkField());
        foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
            $option = $this->optionRepository->get($entity->getSku(), $optionId);
            $option->setParentId($parentId);
            $this->removeOptionLinks($entity->getSku(), $option);
            $this->optionRepository->delete($option);
        }
    }

}