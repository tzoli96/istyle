<?php

namespace Oander\IstyleCheckout\Controller\Adminhtml\Agreement;

use Magento\CheckoutAgreements\Controller\Adminhtml\Agreement\Edit as extendedClass;

class Edit extends extendedClass
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->_objectManager->create('Magento\CheckoutAgreements\Model\Agreement');

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getAgreementData(true);
        if (!empty($data)) {
            $agreementModel->setData($data);
        }

        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Condition') : __('New Condition'),
            $id ? __('Edit Condition') : __('New Condition')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                'Oander\IstyleCheckout\Block\Adminhtml\Agreement\Edit'
            )->setData(
                'action',
                $this->getUrl('checkout/*/save')
            )
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Terms and Conditions'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $agreementModel->getId() ? $agreementModel->getName() : __('New Condition')
        );
        $this->_view->renderLayout();
    }

}