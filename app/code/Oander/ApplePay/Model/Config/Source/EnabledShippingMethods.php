<?php
namespace Oander\ApplePay\Model\Config\Source;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;

class EnabledShippingMethods implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     * @param StoreResolver         $storeResolver
     * @param Http                  $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        StoreManagerInterface $storeManager,
        StoreResolver $storeResolver,
        Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->storeResolver = $storeResolver;
        $this->request = $request;
    }

    private function getStoreId()
    {
        $storeId = null;
        $requestStoreId = $this->storeManager->getStore()->getId();

        if ($requestStoreId === null) {
            $requestWebsiteId = $this->request->getParam('website');
            if ($requestWebsiteId !== null) {
                $requestWebsiteId = (int)$requestWebsiteId;
                $defaultGroupId = $this->storeManager->getWebsite($requestWebsiteId)->getDefaultGroupId();
                $storeId = $this->storeManager->getGroup($defaultGroupId)->getDefaultStoreId();
            }
        } else {
            $storeId = (int)$requestStoreId;
        }

        return $storeId;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $storeId = $this->getStoreId();
        $activeCarriers = $this->config->getActiveCarriers($storeId);
        $methods = [];

        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $carrierTitle = '';
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                $carrierTitle = $this->scopeConfig->getValue(
                    'carriers/' . $carrierCode . '/title'
                );
            }

            $methods[] = [
                'value' => $carrierCode,
                'label' => $carrierTitle
            ];
        }
        return $methods;
    }
}
