<?php
namespace Aheadworks\Popup\Model\Rule\Condition;

/**
 * Class Combine
 * @package Aheadworks\Popup\Model\Rule\Condition
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{

    /**
     * Attribute factory
     * @var \Aheadworks\Popup\Model\Rule\Condition\Product\AttributesFactory
     */
    protected $_attributeFactory;

    /**
     * Constructor
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Aheadworks\Popup\Model\Rule\Condition\Product\AttributesFactory $attributesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Aheadworks\Popup\Model\Rule\Condition\Product\AttributesFactory $attributesFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_attributeFactory = $attributesFactory;
        $this->setType(\Aheadworks\Popup\Model\Rule\Condition\Combine::class);
    }

    /**
     * Prepare child rules option list
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [
            ['value' => $this->getType(), 'label' => __('Conditions Combination')],
            $this->_attributeFactory->create()->getNewChildSelectOptions(),
        ];

        $conditions = array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    /**
     * Get conditions
     *
     * @return array|mixed
     */
    public function getConditions()
    {
        if ($this->getData($this->getPrefix()) === null) {
            $this->setData($this->getPrefix(), []);
        }
        return $this->getData($this->getPrefix());
    }

    /**
     * Validate attributes
     *
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->setAggregator($condition->getAggregator());
            $condition->setTrue((bool)$condition->getValue());
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
