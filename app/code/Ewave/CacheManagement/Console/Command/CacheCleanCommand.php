<?php
namespace Ewave\CacheManagement\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCleanCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:store:clean');
        $this->setDescription('Cleans cache type(s) for specified store(s)');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function performAction(array $stores, array $cacheTypes, InputInterface $input, OutputInterface $output)
    {
        foreach ($stores as $store) {
            if ($storeCode = $this->helper->getStoreCode($store)) {
                $output->writeln(sprintf('Changed cache status (%s):', $storeCode));
                foreach ($cacheTypes as $cacheType) {
                    $this->cacheTypeList->cleanType($cacheType, $storeCode);
                }
                $this->eventManager->dispatch('adminhtml_cache_flush_system_store', ['store' => $storeCode]);
                $output->writeln(join(PHP_EOL, $cacheTypes));
            }
        }
    }
}
