<?php
namespace Oander\AdinaPlayer\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;

class Adinaplayer extends Template implements BlockInterface
{

    CONST UID_PARAMTER = "uid";

    /**
     * @var string
     */
    protected $_template = "widget/adinaplayer.phtml";

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return false|int
     */
    public function getUid()
    {
        return ($this->_request->getParam(self::UID_PARAMTER)) ? $this->_request->getParam(self::UID_PARAMTER)  : false;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getNoUidMessage()
    {
        return __("No uid in the parameter, it necessary for the widget render");
    }

    /**
     * @return string
     */
    public function customOgThumbnailImage()
    {
        return 'https://api.motionlab.io/images/'.$this->getData("campaign_key").'/'.$this->getUiId().'?type=thumbnail_1';
    }
}


	