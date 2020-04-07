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
 * Koongo service connection adjustment page
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Service\Connection;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Step2 extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Nostress_Koongo::service_api_connection';        

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * Integration factory
     *
     * @var \Magento\Integration\Model\IntegrationFactory
     */
    protected $_integrationFactory;    

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Integration\Model\IntegrationFactory $integrationFactory
     * @param \Magento\Framework\Escaper $escaper     
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\IntegrationFactory $integrationFactory,
        \Magento\Framework\Escaper $escaper   
    ) {
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_integrationFactory = $integrationFactory;
        $this->escaper = $escaper;
        parent::__construct($context);
    }
    
    public function execute()
    {                 
        try {
            $this->_activateIntegration();            
            $this->messageManager->addSuccess(__('A system integration with name %1 was sucessfully activated.'
                , \Nostress\Koongo\Helper\Data::KOONGO_SYSTEM_INTEGRATION_NAME));
            
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (\Exception $e) {
            $message = __("Error during integration activation.");
            $this->messageManager->addError($message." ".$this->escaper->escapeHtml($e->getMessage()));            
        }        
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');        
    }

    /**
     * Activate integration
     *
     * @return void
     */
    protected function _activateIntegration()
    {
        $paramId = $this->getRequest()->getParam('integration_id');
        $integration = $this->_integrationFactory->create()->load($paramId);
        if(!$integration->getId())
            throw new \Exception(__("Integration #%1 does not exists.", $integration->getId()));
        
        if($integration->getName() != \Nostress\Koongo\Helper\Data::KOONGO_SYSTEM_INTEGRATION_NAME)
            throw new \Exception(__("Validation error. Integration #%1 does not have valid name.", $integration->getId(), \Nostress\Koongo\Helper\Data::KOONGO_SYSTEM_INTEGRATION_NAME));

        $integration->setStatus(1);
        $integration->save();
        return;        
    }
}
