<?php

namespace Oander\Cleaner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const SETTINGS_PATH_CLEANER         = 'cleaner/general';
    const SETTINGS_CLEANER_ENABLED      = 'enabled';
    const SETTINGS_CLEANER_DB           = 'files';
    const SETTINGS_CLEANER_FILES        = 'db';
    const SETTINGS_CLEANER_EXECUTE_TIME = 'execute_time';
    const SETTINGS_CLEANER_OLDER_THAN   = 'older_than';

    /**
     * @var array
     */
    private $cleaner = [];

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->cleaner = (array)$this->scopeConfig->getValue(
            self::SETTINGS_PATH_CLEANER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCleanerEnabled(): bool
    {
        return (bool)$value = $this->cleaner[self::SETTINGS_CLEANER_ENABLED] ?? false;
    }

    /**
     * @return bool
     */
    public function isDbEnabled(): bool
    {
        return (bool)$value = $this->cleaner[self::SETTINGS_CLEANER_DB] ?? false;
    }

    /**
     * @return bool
     */
    public function isFilesEnabled(): bool
    {
        return (bool)$value = $this->cleaner[self::SETTINGS_CLEANER_FILES] ?? false;
    }

    /**
     * @return string
     */
    public function getExecuteTime(): string
    {
        return (string)$value = $this->cleaner[self::SETTINGS_CLEANER_EXECUTE_TIME] ?? false;
    }

    /**
     * @return int
     */
    public function getOlderThan(): int
    {
        return (int)$value = $this->cleaner[self::SETTINGS_CLEANER_OLDER_THAN] ?? 0;
    }
}