<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Ui\Component\MassAction;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;

/**
 * Class Filter
 * @package Oander\IstyleCustomization\Plugin\Magento\Ui\Component\MassAction
 */
class Filter
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Filter constructor.
     *
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        RequestInterface $request,
        FilterBuilder $filterBuilder
    ) {
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param \Magento\Ui\Component\MassAction\Filter $subject
     * @param \Closure $proceed
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function aroundGetCollection(
        \Magento\Ui\Component\MassAction\Filter $subject,
        \Closure $proceed,
        AbstractDb $collection
    ) {
        $idsArray = $this->getFilterIds($subject);
        if (!empty($idsArray)) {
            $collection->addFieldToFilter(
                $collection->getIdFieldName(),
                ['in' => $idsArray]
            );
        }

        return $collection;
    }

    /**
     * Apply selection by Excluded Included to Search Result
     *
     * @throws LocalizedException
     * @return void
     */
    public function applySelectionOnTargetProvider(\Magento\Ui\Component\MassAction\Filter $subject)
    {
        $selected = $this->request->getParam($subject::SELECTED_PARAM);
        $excluded = $this->request->getParam($subject::EXCLUDED_PARAM);
        if ('false' === $excluded) {
            return;
        }
        $dataProvider = $this->getDataProvider($subject);
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $this->filterBuilder->setConditionType('nin')
                    ->setField($dataProvider->getPrimaryFieldName())
                    ->setValue($excluded);
                $dataProvider->addFilter($this->filterBuilder->create());
            } elseif (is_array($selected) && !empty($selected)) {
                $this->filterBuilder->setConditionType('in')
                    ->setField($dataProvider->getPrimaryFieldName())
                    ->setValue($selected);
                $dataProvider->addFilter($this->filterBuilder->create());
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get data provider
     *
     * @param \Magento\Ui\Component\MassAction\Filter $subject
     * @return DataProviderInterface
     * @throws LocalizedException
     */
    private function getDataProvider(\Magento\Ui\Component\MassAction\Filter $subject)
    {
        if (!$this->dataProvider) {
            $component = $subject->getComponent();
            $subject->prepareComponent($component);
            $this->dataProvider = $component->getContext()->getDataProvider();
        }
        return $this->dataProvider;
    }

    /**
     * Get filter ids as array
     *
     * @param \Magento\Ui\Component\MassAction\Filter $subject
     * @return array
     * @throws LocalizedException
     */
    private function getFilterIds(\Magento\Ui\Component\MassAction\Filter $subject)
    {
        $this->applySelectionOnTargetProvider($subject);
        if ($this->getDataProvider($subject)->getSearchResult()) {
            return $this->getDataProvider($subject)->getSearchResult()->getAllIds();
        }

        return [];
    }
}