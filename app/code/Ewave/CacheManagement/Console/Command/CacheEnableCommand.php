<?php
namespace Ewave\CacheManagement\Console\Command;

class CacheEnableCommand extends AbstractCacheSetCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:store:enable');
        $this->setDescription('Enables cache type(s) for specified store(s)');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function isEnable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayMessage(array $stores)
    {
        return 'Enabled cache types (' . implode(', ', $stores) . ')';
    }
}
