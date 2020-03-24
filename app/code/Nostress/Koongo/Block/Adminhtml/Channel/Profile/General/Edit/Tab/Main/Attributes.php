<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce). 
 * With the purchase, download of the software or the installation of the software 
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * BLock for attribute settings form options tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab\Main;

use Nostress\Koongo\Model\Channel\Profile;

class Attributes extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     *  @var $model \Nostress\Koongo\Model\Channel\Profile
     **/
    protected $profile;
    
    /**
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/channel/profile/general/main/attributes.phtml';

    protected $_tooltip = 'attributes_mapping_attributes_mapping';
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Attributes
    */
    protected $attributeSource;
    
    /**
     * @var \Magento\Framework\Validator\UniversalFactory $universalFactory
     */
    protected $_universalFactory;

    /**
     * Attriutbes array
     * @var unknown_type
     */
    protected $_attributes;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Nostress\Koongo\Model\Config\Source\Attributes $attributeSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
    	\Nostress\Koongo\Model\Config\Source\Attributes $attributeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_universalFactory = $universalFactory;
        $this->profile = $this->_registry->registry('koongo_channel_profile');
        $this->attributeSource = $attributeSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
    	if (null === $this->getData('component_name')) {
    		$this->setData('component_name', $this->getNameInLayout());
    	}
    	return $this->getData('component_name');
    }
    
    public function getChannelLabel()
    {
    	return $this->profile->getFeed()->getChannel()->getLabel();
    }
    
    public function getStandardAttributesItems()
    {
    	if(!isset($this->_attributes))
    	{
    		$collection = $this->getChildBlock('attributes_table_grid')->getCollection();
	    	$attributes = [];
	    	foreach($collection as $item)
		    {
		    	$attributes[] = $item->getData();
		    }
	    	$this->_attributes = $attributes;
    	}
    	return $this->_attributes;
    }
    
    public function getStandardAttributesJson()
    {
    	$attributes = $this->getStandardAttributesItems();
    	return json_encode($attributes);
    }

    public function getTooltip() {
        
        return $this->profile->helper->renderTooltip( $this->_tooltip);
    }
    
    public function getHelp( $key) {
    
        return $this->profile->helper->getHelp( $key);
    }
}
