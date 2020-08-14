<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Controller\Address;

/**
 * Class ValidateStateCity
 * @package Oander\FanCourierValidator\Controller\Address
 */
class ValidateStateCity extends \Magento\Framework\App\Action\Action
{
    const ROUTE = 'fan_courier_validator/Address/ValidateStateCity';

    /**
     * @var \Oander\FanCourierValidator\Helper\Data
     */
    protected $fanCourierHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * ValidateStateCity constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Oander\FanCourierValidator\Helper\Data $fanCourierHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Oander\FanCourierValidator\Helper\Data $fanCourierHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->fanCourierHelper = $fanCourierHelper;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $currentState = $this->getRequest()->getParam('state','');
        $currentCity = $this->getRequest()->getParam('city','');
        $isValid = $this->fanCourierHelper->isStateCityValid($currentState, $currentCity);

        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'isVaild' => $isValid
            ]);

        return $response;
    }
}
