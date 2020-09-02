<?php
namespace Aheadworks\Popup\Block;

use Aheadworks\Popup\Model\ResourceModel\Popup\Collection;
use Aheadworks\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Aheadworks\Popup\Model\Source\Event;
use Aheadworks\Popup\Model\Source\PageType;
use Aheadworks\Popup\Model\ThirdPartyModule\Manager;
use Aheadworks\Popup\Model\PopupFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Popup
 * @package Aheadworks\Popup\Block
 */
class Popup extends Template
{
    /**
     * Path to template file in theme.
     * @var string
     */
    protected $_template = 'Aheadworks_Popup::popup.phtml';

    /**
     * Block type
     *
     * @var int|null
     */
    private $blockType = null;

    /**
     * Template filter provider
     *
     * @var FilterProvider
     */
    private $templateFilterProvider;

    /**
     * Formkey
     *
     * @var FormKey
     */
    private $formKey;

    /**
     * Popup collection factory
     *
     * @var CollectionFactory
     */
    private $popupCollectionFactory;

    /**
     * Popup model factory
     *
     * @var PopupFactory
     */
    private $popupFactory;

    /**
     * Customer session
     *
     * @var Session
     */
    private $customerSession;

    /**
     * Cookie Manager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param Context $context
     * @param FilterProvider $templateFilterProvider
     * @param FormKey $formKey
     * @param CollectionFactory $popupCollectionFactory
     * @param PopupFactory $popupFactory
     * @param Session $customerSession
     * @param CookieManagerInterface $cookieManager
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $templateFilterProvider,
        FormKey $formKey,
        CollectionFactory $popupCollectionFactory,
        PopupFactory $popupFactory,
        Session $customerSession,
        CookieManagerInterface $cookieManager,
        Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->templateFilterProvider = $templateFilterProvider;
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->popupFactory = $popupFactory;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->cookieManager = $cookieManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get popup type (private)
     *
     * @return int|null
     */
    private function getBlockType()
    {
        if ($this->blockType === null) {
            if (false !== strpos($this->getNameInLayout(), 'popup_product')) {
                $this->blockType = PageType::PRODUCT_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_category')) {
                $this->blockType = PageType::CATEGORY_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_cart')) {
                $this->blockType = PageType::SHOPPINGCART_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_home')) {
                $this->blockType = PageType::HOME_PAGE;
            }
            if (false !== strpos($this->getNameInLayout(), 'popup_checkout')) {
                $this->blockType = PageType::CHECKOUT_PAGE;
            }
        }
        return $this->blockType;
    }

    /**
     * If can show popup (private)
     *
     * @param mixed $popup
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canShow($popup)
    {
        $result = true;
        $popupPageTypeArray = explode(',', $popup->getPageType());
        if ($this->getBlockType() == PageType::PRODUCT_PAGE
            && in_array(PageType::PRODUCT_PAGE, $popupPageTypeArray)
        ) {
            $result = false;
            $currentProductId = $this->getProductId();
            if (null === $currentProductId) {
                return $result;
            }
            $popupModel = $this->popupFactory->create();
            $popupModel->load($popup->getId());
            $conditions = $popupModel->getRuleModel()->getConditions();
            if (isset($conditions)) {
                $match = $popupModel->getRuleModel()->getMatchingProductIds();
                if (in_array($currentProductId, $match)) {
                    $result = true;
                }
            }
        }

        if ($this->getBlockType() == PageType::CATEGORY_PAGE
            && in_array(PageType::CATEGORY_PAGE, $popupPageTypeArray)
        ) {
            $result = false;
            $currentCategoryId = $this->getCurrentCategoryId();
            if ((!$popup->getCategoryIds())
                || ($currentCategoryId && in_array($currentCategoryId, explode(',', $popup->getCategoryIds())))
            ) {
                $result = true;
            }
        }
        if (!empty($popup->getCustomerSegments())
            && $this->moduleManager->isCustomerSegmentationModuleEnabled()
            && !$this->customerSession->getCustomerId()
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * get popups for current page
     *
     * @return array
     */
    public function getPopupsArrayContentHtml()
    {
        $rFrom = [
            '"' . ActionInterface::PARAM_NAME_URL_ENCODED . '":',
            "checkout\/cart\/add\/" . ActionInterface::PARAM_NAME_URL_ENCODED . "\/"
        ];
        $rTo = [
            '"' . ActionInterface::PARAM_NAME_URL_ENCODED . 'disable":',
            "checkout\/cart\/add\/".ActionInterface::PARAM_NAME_URL_ENCODED . "disable\/"
        ];

        /** @var Collection $popupCollection */
        $popupCollection = $this->popupCollectionFactory->create();

        $templateFilter = $this->templateFilterProvider->getBlockFilter()
            ->setStoreId($this->_storeManager->getStore()->getId());

        /* prepare popup from backend for preview */
        if ($this->getRequest()->getParam('preview', 0)) {
            $popupsContentArr = [];
            // phpcs:disable Magento2.Functions
            $popupInfo = json_decode(base64_decode($this->getRequest()->getParam('popup_info', '')));
            $id = $popupInfo->popupId;
            $popupsContentArr[$id]['preview'] = true;
            $popupsContentArr[$id]['effect'] = $popupInfo->effect;
            $popupsContentArr[$id]['position'] = $popupInfo->position;
            $popupsContentArr[$id]['content'] =
                '<div class="popup-content mfp-with-anim">' .
                str_replace($rFrom, $rTo, $templateFilter->filter($popupInfo->content)) .
                '<style type="text/css">' . $popupInfo->customCss . '</style></div>';
            return $popupsContentArr;
        }

        $excludedIds = $this->__getExcludedPopupIds();
        $customerPageViewed = $this->__getCustomerPageViewedCount();

        $popupCollection
            ->addCustomerGroupFilter($this->customerSession->getCustomerGroupId())
            ->addPageTypeFilter($this->getBlockType())
            ->addPageViewedFilter($customerPageViewed)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addStatusEnabledFilter();
        if ($excludedIds) {
            $popupCollection->addExcludedIdsFilter($excludedIds);
        }
        if ($this->moduleManager->isCustomerSegmentationModuleEnabled() && $this->customerSession->getCustomerId()) {
            $popupCollection->addCustomerSegmentFilter(
                $this->customerSession->getCustomerId(),
                $this->_storeManager->getStore()->getId()
            );
        }

        $popupsContentArr = [];
        foreach ($popupCollection->getItems() as $popup) {
            if ($this->canShow($popup)) {
                $popupsContentArr[$popup->getId()]['effect'] = $popup->getEffect();
                $popupsContentArr[$popup->getId()]['event'] = $popup->getEvent();
                $popupsContentArr[$popup->getId()]['position'] = $popup->getPosition();
                $popupsContentArr[$popup->getId()]['event_value'] = $popup->getEventValue();
                $popupsContentArr[$popup->getId()]['content'] =
                    '<div class="popup-content mfp-with-anim">' .
                    str_replace($rFrom, $rTo, $templateFilter->filter($popup->getContent())) .
                    '<style type="text/css">' . $popup->getCustomCss() . '</style></div>'
                ;
                $popupsContentArr[$popup->getId()]['lifetime'] = $popup->getCookieLifetime() * 60;
            }
        }
        return $popupsContentArr;
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get product id (used for popups in product page)
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_request->getParam('id', null);
    }

    /**
     * Get category id (used for popups in category page. Private)
     *
     * @return mixed
     */
    private function getCurrentCategoryId()
    {
        return $this->_request->getParam('id', null);
    }

    /**
     * Get showed popups (private)
     *
     * @return array
     */
    private function __getExcludedPopupIds()
    {
        // phpcs:disable Magento2.Security.Superglobal
        $keys = array_keys($_COOKIE);
        $pattern = '/' . Event::VIEWED_POPUP_COUNT_COOKIE_NAME . '*/';
        $popupKeys = preg_grep($pattern, $keys);
        $result = [];
        foreach ($popupKeys as $key) {
            $popupId = $this->cookieManager->getCookie($key);
            if (null !== $popupId) {
                $result[] = $popupId;
            }
        }
        return $result;
    }

    /**
     * Get different viewed pages count (Private)
     *
     * @return int
     */
    private function __getCustomerPageViewedCount()
    {
        $pageViewedJson = $this->cookieManager->getCookie(Event::VIEWED_PAGE_COUNT_COOKIE_NAME);
        $result = 0;
        if (null !== $pageViewedJson) {
            $pageViewedArray = json_decode($pageViewedJson);
            $result = count($pageViewedArray);
        }
        return $result;
    }

    /**
     * Check if use https
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->_storeManager->getStore()->isCurrentlySecure();
    }
}
