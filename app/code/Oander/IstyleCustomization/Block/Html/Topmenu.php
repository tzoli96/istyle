<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Block\Html;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\View\Element\Template;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class Topmenu
 * @package Oander\IstyleCustomization\Block\Html
 */
class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{

    /**
     * @var Config
     */
    protected $customizationConfig;

    /**
     * Topmenu constructor.
     *
     * @param Template\Context            $context
     * @param NodeFactory                 $nodeFactory
     * @param TreeFactory                 $treeFactory
     * @param Config                      $customizationConfig
     * @param array                       $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Config $customizationConfig,
        array $data = []
    ) {
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->customizationConfig = $customizationConfig;
    }

    /**
     * @return Config
     */
    public function getCustomizationConfig()
    {
        return $this->customizationConfig;
    }
}
