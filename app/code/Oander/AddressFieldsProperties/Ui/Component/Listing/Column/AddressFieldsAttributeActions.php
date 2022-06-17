<?php

namespace Oander\AddressFieldsProperties\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface as UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface as ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory as UiComponentFactory;

class AddressFieldsAttributeActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_DETAILS = 'oander_addressfieldsproperties/addressfieldsattribute/details';
    const URL_PATH_EDIT = 'oander_addressfieldsproperties/addressfieldsattribute/edit';
    const URL_PATH_DELETE = 'oander_addressfieldsproperties/addressfieldsattribute/delete';
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['attribute_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'attribute_id' => $item['attribute_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
}
