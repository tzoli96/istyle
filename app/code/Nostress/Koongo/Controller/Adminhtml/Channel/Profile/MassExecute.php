<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Export profiles grid mass execute controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Nostress\Koongo\Model\ResourceModel\Channel\Profile\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Nostress\Koongo\Model\Channel\Profile\Manager;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlInterface;

/**
 * Class MassExecute
 */
class MassExecute extends SaveAbstract
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $resultPageFactory, $helper, $manager, $profileFactory, $translation);
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $this->manager->runProfiles($collection, false);
        
        $counter = 0;
        
        foreach ($collection as $item) {
            $status = $item->getStatus();
            if ($status == \Nostress\Koongo\Model\Channel\Profile::STATUS_ERROR) {
                $this->messageManager->addError($this->getErrorRunMessage($item->getId()).$this->translation->replaceActionLinks($item->getMessage()));
            } elseif ($status == \Nostress\Koongo\Model\Channel\Profile::STATUS_DISABLED) {
                $this->messageManager->addSuccess($this->getDisabledProfileMessage($item->getId()));
            } else {
                $counter++;
                $this->messageManager->addSuccess($this->getSuccessRunMessage($item->getId()));
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 profile(s) have been executed sucessfully.', $counter));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function getErrorRunMessage($id)
    {
        return __("Profile #%1 finished with error:", $id)." ";
    }
    
    protected function getSuccessRunMessage($id)
    {
        return __("Profile #%1 has been successfully executed.", $id)." ";
    }
    
    protected function getDisabledProfileMessage($id)
    {
        return __("Profile #%1 is disabled.", $id)." ";
    }
}