<?php
/**
 * Oander_ImportM2
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\ImportM2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\ImportM2\Enum\Config as ConfigEnum;
use Oander\ImportM2\Helper\Config\General;

/**
 * Class Config
 *
 * @package Oander\ImportM2\Helper
 */
class Config extends AbstractHelper
{
    use General;

    /**
     * @var array
     */
    protected $general;

    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->general = (array)$this->scopeConfig->getValue(
            ConfigEnum::GENERAL_SETTINGS_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
