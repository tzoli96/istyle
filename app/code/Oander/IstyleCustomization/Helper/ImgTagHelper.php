<?php

namespace Oander\IstyleCustomization\Helper;

use \Magento\Framework\View\Asset\Repository;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\View\Element\Template;
use \Magento\Theme\Model\Theme;

class ImgTagHelper
{
    const LAZY_LOAD_CLASS = 'b-lazy';
    const LAZY_LOAD_PLACEHOLDER_THEME = 'Oander/istyle';
    const LAZY_LOAD_PLACEHOLDER_IMAGE = 'images/iSTYLE-logo-1200.png';

    private $assetRepo;
    private $placeholderPath;
    private $storeManager;
    /**
     * @var Template
     */
    private $template;
    /**
     * @var Theme
     */
    private $theme;

    /**
     * ImgTagHelper constructor.
     * @param Repository $assetRepo
     * @param StoreManagerInterface $storeManager
     * @param Template $template
     */
    public function __construct(
        Repository $assetRepo,
        StoreManagerInterface $storeManager)
        //Template $template)
    {
        $this->assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
        //$this->template = $template;
    }

    /**
     *
     */
    private function setPlaceholderPath()
    {
        $theme = self::LAZY_LOAD_PLACEHOLDER_THEME;
        if ($this->theme) {
            $theme = $this->theme->getThemePath();
        }

        $this->placeholderPath = "{{view url='Oander/istyle::images/iSTYLE-logo-1200.png'}}";



        /*$this->template->getViewFileUrl(
            self::LAZY_LOAD_PLACEHOLDER_IMAGE,
            ['theme' => $theme]);*/
    }

    /**
     * @param Theme $theme
     */
    public function setTheme(Theme $theme)
    {
        if (!$this->theme) {
            $this->theme = $theme;
            $this->setPlaceholderPath();
        }
    }

    /**
     * @param string $html
     * @return string
     */
    public function processImgTags(string $html): string
    {
        $origImgTags = $this->getImgTagsFromHtml($html);
        if (!empty($origImgTags)) {
            foreach ($origImgTags as $origImgTag) {
                if (!$this->imgTagIsProcessed($origImgTag)) {
                    $newImgTag = $this->processImgTagClassAttribute($origImgTag);
                    $newImgTag = $this->changeImgTagSrcAttributeToDataSrc($newImgTag);
                    $newImgTag = $this->addNewSrcAttribute($newImgTag);

                    $html = str_replace($origImgTag, $newImgTag, $html);
                }
            }
        }

        return $html;
    }

    /**
     * @param string $imgTag
     * @return bool
     */
    public function imgTagIsProcessed(string $imgTag): bool
    {
        return (bool)preg_match('/'.self::LAZY_LOAD_CLASS.'/', $imgTag);
    }
    /**
     * @param string $imgTag
     * @return string
     */
    public function addNewSrcAttribute(string $imgTag): string
    {
        return str_replace('data-src', 'src="'.$this->placeholderPath.'" data-src', $imgTag);
    }

    /**
     * @param string $imgTag
     * @return string
     */
    public function changeImgTagSrcAttributeToDataSrc(string $imgTag): string
    {
        return str_replace('src=', 'data-src=', $imgTag);
    }

    /**
     * @param string $imgTag
     * @return string
     */
    public function processImgTagClassAttribute(string $imgTag): string
    {
        $class = [];
        preg_match('/class=\"[\w\-\s\_]+\"/', $imgTag, $class);
        $class = reset($class);

        if (empty($class)) {
            $newClass = 'class="'.self::LAZY_LOAD_CLASS.'"';
            $imgTag = preg_replace('/<img/', '<img '.$newClass, $imgTag);
        } else {
            $newClass = preg_replace('/\"$/', ' '.self::LAZY_LOAD_CLASS.'"', $class);
            $imgTag = str_replace($class, $newClass, $imgTag);
        }

        return $imgTag;
    }

    /**
     * @param string $html
     * @return array
     */
    public function getImgTagsFromHtml(string $html): array
    {
        $imgTags = [];
        preg_match_all('/<img.*[\/|"|]>/', $html, $imgTags);
        $imgTags = call_user_func('array_merge', $imgTags[0]);

        return $imgTags;
    }
}