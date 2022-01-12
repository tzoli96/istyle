<?php
namespace Ewave\CacheManagement\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class MassRefresh extends \Magento\Backend\Controller\Adminhtml\Cache\MassRefresh
{
    /**
     * Mass action for cache refresh
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($store = $this->getRequest()->getParam('store')) {
            try {
                $types = $this->getRequest()->getParam('types');
                $updatedTypes = 0;
                if (!is_array($types)) {
                    $types = [];
                }
                $this->_validateTypes($types);
                foreach ($types as $type) {
                    $this->_cacheTypeList->cleanType($type, $store);
                    $updatedTypes++;
                }
                if ($updatedTypes > 0) {
                    $this->messageManager->addSuccessMessage(__("%1 cache type(s) refreshed.", $updatedTypes));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('An error occurred while refreshing cache.'));
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('adminhtml/*', ['store' => $store]);
        }
        return parent::execute();
    }
}
