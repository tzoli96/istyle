<?php

namespace Oander\Minicalculator\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Oander\HelloBankPayment\Helper\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;

class CalculatorType extends AbstractSource
{
    /**
     * @var State
     */
    protected $state;
    /**
     * @var Config
     */
    protected $hellobankConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * CalculatorType constructor.
     * @param State $state
     * @param Config $hellobankConfig
     * @param StoreManagerInterface $storeConfig
     */
    public function __construct(
        RequestInterface $request,
        State $state,
        Config $hellobankConfig,
        StoreManagerInterface $storeConfig
    )
    {
        $this->request = $request;
        $this->state = $state;
        $this->storeConfig = $storeConfig;
        $this->hellobankConfig = $hellobankConfig;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentId()
    {
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $storeId = (int)$this->request->getParam('store', 0);
        } else {
            $storeId = true;
        }

        return $this->storeConfig->getStore($storeId)->getId();
    }

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $response = [];
        if ($this->hellobankConfig->getPaymnetMethodIsActive($this->getCurrentId())) {
            $response[] = [
                'value' => 'hellobank',
                'label' => __('HelloBank')
            ];
        }

        $response[] = [
            'value' => 'lorem',
            'label' => __('Lorem ipsum')
        ];

        return $response;

    }
}