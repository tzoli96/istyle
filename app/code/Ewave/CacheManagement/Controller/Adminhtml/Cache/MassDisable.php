<?php
namespace Ewave\CacheManagement\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;

class MassDisable extends \Magento\Backend\Controller\Adminhtml\Cache\MassDisable
{
    /**
     * @var State
     */
    private $state;

    /**
     * Mass action for cache disabling
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($store = $this->getRequest()->getParam('store')) {
            if ($this->getState()->getMode() === State::MODE_PRODUCTION) {
                $this->messageManager->addErrorMessage(__(
                    'You can\'t change status of cache type(s) in production mode'
                ));
            } else {
                $this->disableCache($store);
            }
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('adminhtml/*', ['store' => $store]);
        }
        return parent::execute();
    }

    /**
     * Disable cache
     *
     * @param string $store
     * @return void
     */
    private function disableCache($store)
    {
        try {
            $types = $this->getRequest()->getParam('types');
            $updatedTypes = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validateTypes($types);
            foreach ($types as $code) {
                $this->_cacheTypeList->cleanType($code, $store);
                if ($this->_cacheState->isEnabled($code, $store)) {
                    $this->_cacheState->setEnabled($code, false, $store);
                    $updatedTypes++;
                }
            }
            if ($updatedTypes > 0) {
                $this->_cacheState->persist(true);
                $this->messageManager->addSuccessMessage(__("%1 cache type(s) disabled.", $updatedTypes));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred while disabling cache.'));
        }
    }

    /**
     * Get State Instance
     *
     * @return State
     */
    private function getState()
    {
        if ($this->state === null) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }
        return $this->state;
    }
}
