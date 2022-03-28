<?php

namespace Oander\AdinaPlayer\Plugin;

use Magento\Cms\Helper\Page;
use Oander\AdinaPlayer\Helper\Data;

class PageHelper
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;
    /**
     * @var Data
     */
    protected $adinaHelper;

    /**
     * @param \Magento\Cms\Model\Page $page
     * @param Data $adinaHelper
     */
    public function __construct(
        \Magento\Cms\Model\Page $page,
        Data                    $adinaHelper
    ){
        $this->page = $page;
        $this->adinaHelper = $adinaHelper;
    }

    /**
     * @param Page $subject
     * @param $result
     * @return mixed
     */
    public function afterprepareResultPage(Page $subject, $result)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/adina.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($result->getLayout());
        if(!$result->getLayout()){
            return $result;
        }
        $pageContent =  $result->getLayout()->getBlock('cms_page')->getPage()->getContent();
        return $this->adinaHelper->prepareResult($pageContent, $result);

    }

}