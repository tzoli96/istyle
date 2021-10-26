<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class AddressAttributesOrder
 * @package Oander\IstyleCustomizatioon\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /** @var Config  */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'addressAttributesPositions' => $this->config->getAddressAttributePosition()
        ];
    }
}