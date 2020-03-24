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
* Config source for dropdown menu "Include pub folder"
* 
* @category Nostress 
* @package Nostress_Koongo
* 
*/
namespace Nostress\Koongo\Model\Config\Source;

class Includepub  extends \Nostress\Koongo\Model\Config\Source
{
	const ACCORDING_TO_SYSTEM = "according_to_system";
	const YES = "yes";
	const NO = "no";
	
    public function toOptionArray()
    {
        return array(
            array('value'=> self::ACCORDING_TO_SYSTEM, 'label'=> __('According to system settings (Recommended)')),
            array('value'=> self::YES, 'label'=> __('Yes - Use PUB in media links.')),
            array('value'=> self::NO, 'label'=> __('No - Never use PUB in media links.'))
        );
    }
}
?>
