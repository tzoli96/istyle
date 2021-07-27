<?php
namespace Oander\IstyleCheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteIdConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * WebsiteIdConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ){
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['istyle_checkout']['website_id'] = $this->storeManager->getStore()->getWebsiteId();

        return $config;
    }
} 
