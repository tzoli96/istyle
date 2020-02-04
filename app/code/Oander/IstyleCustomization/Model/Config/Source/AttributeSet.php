<?php

namespace Oander\IstyleCustomization\Model\Config\Source;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class AttributeSet implements OptionSourceInterface
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getAttributeSets() as $set) {
            $result[] = [
                'label' => $set->getAttributeSetName(),
                'value' => $set->getAttributeSetId(),
            ];
        }

        return $result;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeSetInterface[]
     */
    public function getAttributeSets()
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_type_code')
                    ->setValue(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                    ->setConditionType('eq')
                    ->create()
            ]
        );
        $searchResults = $this->attributeSetRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $attributeSets = $searchResults->getItems();

        return $attributeSets;
    }
}
