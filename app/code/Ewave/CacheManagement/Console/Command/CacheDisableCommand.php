<?php
namespace Ewave\CacheManagement\Console\Command;

class CacheDisableCommand extends AbstractCacheSetCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:store:disable');
        $this->setDescription('Disables cache type(s) for specified store(s)');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function isEnable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage(array $stores)
    {
        return 'Disabled cache types (' . implode(', ', $stores) . ')';
    }
}
