<?php
namespace Ewave\CacheManagement\Console\Command;

use Ewave\CacheManagement\Model\Store\CacheTypeList;
use Ewave\CacheManagement\Model\Store\CacheState;
use Ewave\CacheManagement\Helper\Data as Helper;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCacheCommand extends Command
{
    /**
     * Input argument stores
     */
    const INPUT_KEY_STORES = 'stores';
    const INPUT_KEY_TYPES = 'types';

    /**
     * @var CacheTypeList
     */
    protected $cacheTypeList;

    /**
     * @var CacheState
     */
    protected $cacheState;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * CacheCleanCommand constructor.
     * @param CacheTypeList $cacheTypeList
     * @param CacheState $cacheState
     * @param Helper $helper
     * @param EventManager $eventManager
     */
    public function __construct(
        CacheTypeList $cacheTypeList,
        CacheState $cacheState,
        Helper $helper,
        EventManager $eventManager
    ) {
        parent::__construct();
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheState = $cacheState;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_STORES,
            InputArgument::OPTIONAL,
            'Space-separated list of store codes to cache clean.'
        );

        $this->addArgument(
            self::INPUT_KEY_TYPES,
            InputArgument::OPTIONAL,
            'Space-separated list of cache types or omit to apply to all cache types.'
        );

        parent::configure();
    }

    /**
     * Cleans cache types
     *
     * @param array $stores
     * @param array $cacheTypes
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    abstract protected function performAction(
        array $stores,
        array $cacheTypes,
        InputInterface $input,
        OutputInterface $output
    );

    /**
     * @param array $stores
     * @return string
     */
    protected function getDisplayMessage(array $stores)
    {
        return '';
    }

    /**
     * Perform cache management action
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stores = $this->getRequestedStores($input);
        $types = $this->getRequestedTypes($input);
        $this->performAction($stores, $types, $input, $output);
        $output->writeln($this->getDisplayMessage($stores));
    }

    /**
     * Get requested cache types
     *
     * @param InputInterface $input
     * @return array
     */
    protected function getRequestedStores(InputInterface $input)
    {
        return $this->getInputOptions(
            $input,
            self::INPUT_KEY_STORES,
            $this->helper->getStoresCodes()
        );
    }

    /**
     * Get requested cache types
     *
     * @param InputInterface $input
     * @return array
     */
    protected function getRequestedTypes(InputInterface $input)
    {
        return $this->getInputOptions(
            $input,
            self::INPUT_KEY_TYPES,
            array_keys($this->cacheTypeList->getTypes())
        );
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @param array $availableOptions
     * @return array
     */
    protected function getInputOptions(InputInterface $input, $argument, $availableOptions)
    {
        $requestedOptions = [];
        if ($input->getArgument($argument)) {
            $requestedOptions = explode(',', $input->getArgument($argument));
            $requestedOptions = array_filter(array_map('trim', $requestedOptions), 'strlen');
        }

        if (empty($requestedOptions)) {
            return $availableOptions;
        } else {
            $unsupportedOptions = array_diff($requestedOptions, $availableOptions);
            if ($unsupportedOptions) {
                throw new \InvalidArgumentException(
                    "The following requested " . $argument . " are not supported: '"
                    . join("', '", $unsupportedOptions)
                    . "'." . PHP_EOL . 'Supported ' . $argument . ': '
                    . join(", ", $availableOptions)
                );
            }
            return array_values(array_intersect($availableOptions, $requestedOptions));
        }
    }
}
