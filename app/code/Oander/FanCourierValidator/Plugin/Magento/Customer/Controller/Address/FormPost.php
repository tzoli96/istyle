<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Plugin\Magento\Customer\Controller\Address;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Oander\FanCourierValidator\Helper\Data;

/**
 * Class FormPost
 * @package Oander\FanCourierValidator\Plugin\Magento\Customer\Controller\Address
 */
class FormPost
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * FromPost constructor.
     * @param Data $data
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        Data $data,
        RequestInterface $request,
        ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->data = $data;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->redirect = $redirect;
        $this->url = $url;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\AddressRepository $subject
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Address\FormPost $subject,
        \Closure $proceed
    ) {
        if ($this->data->getValidationLevel() != '') {
            $region = $this->request->getParam('region', false);
            if (empty($region)) {
                $this->messageManager->addError(__('%fieldName is a required field.', ['fieldName' => 'region']));
                $this->session->setAddressFormData($this->request->getPostValue());
                $url = $this->url->getUrl('*/*/edit', ['id' => $this->request->getParam('id')]);

                return $this->redirectFactory->create()->setUrl($this->redirect->error($url));
            }
        }

        return $proceed();
    }

}