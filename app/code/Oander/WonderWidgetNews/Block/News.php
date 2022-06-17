<?php
/**
 * Oander_WonderWidgetNews
 *
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\WonderWidgetNews\Block;

use Oander\News\Model\Service\ArticleRepository;


/**
 * Class News
 *
 * @package Oander\WonderWidgetNews\Block
 */
class News extends Basic
{

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var $latestArticle \Oander\News\Model\Article
     */
    private $latestArticle;

    /**
     * @var $articleRepository
     */
    private $articleRepository;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ArticleRepository $articleRepository,
        array $data = [])
    {
        $this->_storeManager = $context->getStoreManager();
        $this->articleRepository = $articleRepository;
        $this->latestArticle = $this->getLatestArticle();
        parent::__construct($context, $data);
    }


    /**
     * @return mixed
     */
    public function getLatestArticle() {
        /*
         * this returns articles ordered by position
        $articleCollection = $this->articleRepository->getArticlesByStoreId(
            $this->_storeManager->getStore()->getId(), 1, 1
        );
        */

        //returns collection ordered by id desc
        $articleCollection = $this->articleRepository->getLatestArticles(
            $this->_storeManager->getStore()->getId(), 0
        );

        return $articleCollection->getFirstItem();
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->latestArticle->getShortDescription();
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return (string)$this->latestArticle->getUrl();
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->latestArticle->getTitle();
    }

    /**
     * @return string
     */
    public function getImage()
    {
        $image = (string)$this->latestArticle->getImageUrl();

        if ($image) {
            if (substr($image, 0,4) != 'http') {
                return $this->getBaseUrl() . self::PATH_WYSIWYG . $image;
            } else {
                return $image;
            }
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getImageAlt()
    {
        return (string)$this->latestArticle->getImageAlt();
    }
}
