<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Controller\Address;

/**
 * Class GetCities
 * @package Oander\FanCourierValidator\Controller\Address
 */
class GetCities extends \Magento\Framework\App\Action\Action
{
    const ROUTE = 'fan_courier_validator/Address/GetCities';

    /**
     * @var \Oander\FanCourierValidator\Helper\Data
     */
    protected $fanCourierHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * GetCities constructor.
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
        $citiesByState = $this->fanCourierHelper->getCitiesByState($currentState);

        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'cities' => $citiesByState
            ]);

        return $response;
    }
}
