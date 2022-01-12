<?php
namespace Ewave\CacheManagement\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCacheSetCommand extends AbstractCacheCommand
{
    /**
     * Is enable cache or not
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * {@inheritdoc}
     */
    protected function performAction(array $stores, array $cacheTypes, InputInterface $input, OutputInterface $output)
    {
        $isEnable = $this->isEnable();
        foreach ($stores as $store) {
            if ($storeCode = $this->helper->getStoreCode($store)) {
                $output->writeln(sprintf('Changed cache status (%s):', $storeCode));
                $changedTypes = [];
                foreach ($cacheTypes as $type) {
                    $this->cacheState->setEnabled($type, $isEnable, $storeCode);
                    $changedTypes[] = $type;
                    $output->writeln(sprintf('%30s: %d -> %d', $type, !$isEnable, $isEnable));
                }

                if (!empty($changedTypes)) {
                    $this->cacheState->persist(true);
                    foreach ($changedTypes as $changedType) {
                        if ($isEnable) {
                            $this->cacheTypeList->cleanType($changedType, $storeCode);
                        }
                    }

                    if ($isEnable) {
                        $output->writeln(sprintf('Cleaned cache types:'));
                        $output->writeln(join(PHP_EOL, $changedTypes));
                    }
                }
            }
        }
    }
}
