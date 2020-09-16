<?php
/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2018 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

namespace Oander\IstyleCustomization\Rewrite\Anowave\Ec\Preference;

use Magento\Framework\Url\EncoderInterface;

class Related extends \Anowave\Ec\Preference\Related
{

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * Related constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param EncoderInterface $urlEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        parent::__construct($context, $checkoutCart, $catalogProductVisibility, $checkoutSession, $moduleManager, $data);
        $this->urlEncoder = $urlEncoder;
    }

    /**
	 * Get loaded items
	 * 
	 * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
	 */
	public function getLoadedItems()
	{
		$this->_prepareData();
		
		return $this->getItems();
	}
	
	/**
	 * Prevent template issues by overriding _toHtml() method 
	 * 
	 * {@inheritDoc}
	 * @see \Magento\Framework\View\Element\Template::_toHtml()
	 */
	protected function _toHtml()
	{
		$this->setModuleName($this->extractModuleName('Magento\Catalog\Block\Product\ProductList\Related'));
		
		return parent::_toHtml();
	}


    /**
     * Encode url
     *
     * @param string $url
     * @return string
     */
    public function encodeUrl($url)
    {
        return $this->urlEncoder->encode($url);
    }
}