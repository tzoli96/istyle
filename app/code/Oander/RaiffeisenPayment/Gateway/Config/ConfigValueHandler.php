<?php

namespace Oander\RaiffeisenPayment\Gateway\Config;

use Oander\RaiffeisenPayment\Model\Ui\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Gateway\Config\Config as DefaultConfig;

class ConfigValueHandler extends DefaultConfig
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_INSTRUCTIONS = 'instructions';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var TimezoneInterface
     */
    private $timezone;


    /**
     * Config constructor.
     *
     * @param Json $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        Json                 $serializer,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface    $timezone,
                             $methodCode = null,
                             $pathPattern = DefaultConfig::DEFAULT_PATH_PATTERN
    )
    {
        DefaultConfig::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );

        $this->setMethodCode(ConfigProvider::CODE);

        $this->serializer = $serializer;
        $this->timezone = $timezone;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getIsActive($storeId = null): bool
    {
        return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return (string)$this->getValue(self::KEY_TITLE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstructions($storeId = null)
    {
        return (string)$this->getValue(self::KEY_INSTRUCTIONS, $storeId);
    }

}