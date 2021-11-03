<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Oander\IstyleCustomization\Helper\Config;
use Oander\NameSwitcher\Helper\Switching;

/**
 * Class AddressAttributesOrder
 * @package Oander\IstyleCustomizatioon\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /** @var Config  */
    protected $config;

    /** @var Switching  */
    protected $switching;

    /**
     * @param Config $config
     * @param Switching $switching
     */
    public function __construct(
        Config $config,
        Switching $switching
    ) {
        $this->config = $config;
        $this->switching = $switching;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $addressAttributesPositions = $this->config->getAddressAttributePosition();
        if ($this->switching->isInverted()) {
            $this->replaceKeys(
                $addressAttributesPositions,
                AddressInterface::FIRSTNAME,
                AddressInterface::LASTNAME
            );
        }

        return [
            'addressAttributesPositions' => $addressAttributesPositions
        ];
    }

    /**
     * @param $array
     * @param $aKey
     * @param $bKey
     */
    protected function replaceKeys(&$array, $aKey, $bKey)
    {
        if(!array_key_exists($aKey, $array) || !array_key_exists($bKey, $array)) {
            return $array;
        }

        $array[$bKey.'_temp'] = $array[$aKey];
        $array[$aKey] = $array[$bKey];
        $array[$bKey] = $array[$bKey.'_temp'];
        unset($array[$bKey.'_temp']);
    }
}