<?php
/**
 * Oander_News
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types = 1);

namespace Oander\Queue\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Oander\News\Helper
 */
class Config extends AbstractHelper
{
    const SETTINGS_PATH             = 'oander_base/queue';
    const SETTINGS_ENABLED          = 'enabled';
    const SETTINGS_METHOD           = 'run_method';

    /**
     * @var array
     */
    private $queue;

    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->queue = (array)$this->scopeConfig->getValue(
            self::SETTINGS_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$value = $this->spending[self::SETTINGS_ENABLED] ?? false;
    }

    /**
     * @return int
     */
    public function getMethod(): int
    {
        return (int)$value = $this->spending[self::SETTINGS_METHOD] ?? 0;
    }
}
