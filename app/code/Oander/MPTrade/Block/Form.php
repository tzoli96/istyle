<?php
namespace Oander\MPTrade\Block;

use Magento\Framework\View\Element\Template;
use Oander\MPTrade\Helper\Data as Helper;

class Form extends Template
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


    /**
     * @return Helper
     */
    public function getHelper(): Helper
    {
        return $this->helper;
    }
}