<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * @license Oander Media Kft. (http://www.oander.hu)
 */
declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\IstyleCustomization\Helper\ImgTagHelper;

class EventManagerObserver implements ObserverInterface
{
    private $imgTagHelper;

    public function __construct(ImgTagHelper $imgTagHelper)
    {
        $this->imgTagHelper = $imgTagHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        if ($eventName === 'model_load_after') {
            $object = $observer->getEvent()->getData('object');
            $content = null;

            if ($object instanceof \Magento\Theme\Model\Theme) {
                $this->imgTagHelper->setTheme($object);
            } elseif ($object instanceof \Magento\Cms\Model\Block) {
                $content = $this->processCmsBlock($object);
            } elseif ($object instanceof \Magento\Cms\Model\Page) {
                $content = $this->processCmsPage($object);
            }

            if (!empty($content)) {
                $observer->getEvent()->getData('object')->setData('content', $content);
            }
        }
    }

    /**
     * @param \Magento\Cms\Model\Block $page
     * @return mixed|string
     */
    private function processCmsBlock(\Magento\Cms\Model\Block $page)
    {
        $content = $page->getData('content');
        if (!empty($content)) {
            $content = $this->imgTagHelper->processImgTags($content);
        }
        return $content;
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @return string
     */
    private function processCmsPage(\Magento\Cms\Model\Page $page)
    {
        $content = $page->getData('content');
        if (!empty($content)) {
            $content = $this->imgTagHelper->processImgTags($content);
        }
        return $content;
    }


}