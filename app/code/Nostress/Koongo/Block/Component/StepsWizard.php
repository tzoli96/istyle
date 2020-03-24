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

namespace Nostress\Koongo\Block\Component;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class StepsWizard extends \Magento\Ui\Block\Component\StepsWizard
{
    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'Nostress_Koongo::koongo/stepswizard.phtml';
    
    /**
     * \Nostress\Koongo\Helper\Data
     */
    protected $helper;
    
    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,\Nostress\Koongo\Helper\Data $helper , array $data = [])
    {
    	$this->helper = $helper;
    	parent::__construct($context, $data);    	
    }
    
    /**
	* Is magento version equal or greater than 2.1.0
     */
    public function isVersionEqualorGreater210()
    {
    	return $this->helper->isMagentoVersionEqualOrGreaterThan("2.1.0");
    }
}
