<?php
namespace Aheadworks\Popup\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Aheadworks\Popup\Block\Popup;
use Aheadworks\Popup\Model\Config;

/**
 * Class PrepareContent
 * @package Aheadworks\Popup\Controller\Ajax
 */
class PrepareContent extends \Aheadworks\Popup\Controller\Ajax
{
    /**
     * Config model
     *
     * @var Config
     */
    private $config;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Aheadworks\Popup\Model\Config $config
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context,
        Config $config
    ) {
        parent::__construct(
            $customerSession,
            $formKeyValidator,
            $context
        );
        $this->config = $config;
    }

    /**
     * Prepare popups for current page
     *
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $nameInLayout = $this->getRequest()->getParam('name_in_layout', '');

        $blockInstance = null;

        /** @var \Aheadworks\Popup\Block\Popup $blockInstance */
        $blockInstance = $resultPage->getLayout()->createBlock(Popup::class, $nameInLayout);
        $cookies = $this->cookiesToArray($this->getRequest()->getParam('cookies',''));

        if ($blockInstance) {
            $productId = $this->getRequest()->getParam('product_id', null);
            $isPreview = $this->getRequest()->getParam('preview', false);
            $popupInfo = $this->getRequest()->getParam('popup_info', '');
            $blockInstance->getRequest()->setParams(
                ['id' => $productId, 'preview' => $isPreview, 'popup_info' => $popupInfo]
            );

            $result['popups'] = json_encode($this->getPopupsContentHtml($blockInstance, $cookies));
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }

        return $resultJson->setData($result);
    }

    /**
     * @param $cookies
     * @return array
     */
    private function cookiesToArray($cookies)
    {
        $result = [];
        $tmpCookies = [];
        if(strpos($cookies,';') !== false){
            $tmpCookies = explode(';', $cookies);
        }
        foreach ($tmpCookies as $cookie) {
            if(strpos($cookie,'=' ) !== false){
                $tmpCookie = explode('=', $cookie);
                $result[trim($tmpCookie[0])] = $tmpCookie[1];
            }
        }

        return $result;
    }

    /**
     * Retrieve Popups content
     *
     * @param \Aheadworks\Popup\Block\Popup $block
     * @return array
     */
    private function getPopupsContentHtml($block, $cookies = [])
    {
        if (!$this->config->getHidePopupForMobileDevices() && !$this->config->getHidePopupForSearchEngines()) {
            return $block->getPopupsArrayContentHtml($cookies);
        }

        $userAgent = $this->getRequest()->getHeader('User-Agent');
        $server = $this->getRequest()->getServer();
        $isMobile = \Zend_Http_UserAgent_Mobile::match($userAgent, $server);
        $isBot = \Zend_Http_UserAgent_Bot::match($userAgent, $server);
        $result = [];

        if (($this->config->getHidePopupForMobileDevices() && $isMobile)
            || ($this->config->getHidePopupForSearchEngines() && $isBot)) {
            return $result;
        } else {
            return $block->getPopupsArrayContentHtml($cookies);
        }
    }
}
