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
 * ResourceModel for Koongo Connector taxonomy category mapping collection
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
 
namespace Nostress\Koongo\Model\ResourceModel\Taxonomy\Category\Mapping;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{		
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Nostress\Koongo\Model\Taxonomy\Category\Mapping', 'Nostress\Koongo\Model\ResourceModel\Taxonomy\Category\Mapping');
	}

 	public function addFieldsToFilter($fields)
    {
    	if(!is_array($fields))
    		return;
    	foreach ($fields as $field => $condition)
		{
			$this->addFieldToFilter($field,$condition);
		}
    }
    
	public function addFieldsToSelect($fields)
    {
    	if(!is_array($fields))
    		return;
    		
    	foreach ($fields as $alias => $field)
		{
			$this->addFieldToSelect($field,$alias);
		}
    }
}