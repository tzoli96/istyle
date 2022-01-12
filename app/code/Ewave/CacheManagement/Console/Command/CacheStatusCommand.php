<?php
namespace Ewave\CacheManagement\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking cache status
 *
 * @api
 * @since 100.0.2
 */
class CacheStatusCommand extends AbstractCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:store:status');
        $this->setDescription('Checks cache status by stores');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function performAction(array $stores, array $cacheTypes, InputInterface $input, OutputInterface $output)
    {
        foreach ($stores as $store) {
            if ($storeCode = $this->helper->getStoreCode($store)) {
                $output->writeln(sprintf('Current status (%s):', $storeCode));
                foreach ($cacheTypes as $type) {
                    $output->writeln(sprintf('%30s: %d', $type, $this->cacheState->isEnabled($type, $storeCode)));
                }
            }
        }
    }
}
