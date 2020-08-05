<?php

/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Oander\FanCourierValidator\Helper\Data
     */
    protected $fanCourierHelper;
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Oander\FanCourierValidator\Helper\Data $fanCourierHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->fanCourierHelper = $fanCourierHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $currentState = $this->getRequest()->getParam('state');
        $citiesByState = $this->fanCourierHelper->getCitiesByState($currentState);

        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'cities' => $citiesByState
            ]);

        return $response;
    }
}
