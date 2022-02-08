<?php

namespace Oander\AdinaPlayer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const ADINA_PLAYER_MATCH = "Oander\AdinaPlayer\Block\Widget\Adinaplayer";

    protected $pageContent = null;

    protected $exam = false;

    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @param $pageContent
     * @return bool
     */
    public function examContent($pageContent)
    {
        $this->pageContent = $pageContent;
        return $this->exam = (strpos($this->pageContent, self::ADINA_PLAYER_MATCH) !== false) ? true : false;
    }

    /**
     * @return bool|string
     */
    public function getUid()
    {
        return $this->_request->getParam('uid');
    }

    /**
     * @return false|string
     */
    public function getCampaignKey()
    {
        if ($this->exam) {
            preg_match('/campaign_key="(.*?)"/', $this->pageContent, $match);
            return $match[1];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getImageTag()
    {
        if ($this->exam) {
            preg_match('/og_image_tag="(.*?)"/', $this->pageContent, $match);
            return $match[1];
        }
        return false;
    }

    public function prepareResult($pageContent, $result)
    {
        if ($this->examContent($pageContent)) {
            $image = $this->getImageTag();
            if ($image) {
                $campageKey = $this->getCampaignKey();
                $uId = $this->getUid();
                if ($uId && $campageKey) {
                    $customThumbnailImage = 'https://api.motionlab.io/images/' . $campageKey . '/' . $uId . '?type=thumbnail_1';
                    $result->getLayout()->getBlock('opengraph.general')->setData('og_thumbnail', $customThumbnailImage);
                }
            }
        }
        return $result;
    }

}