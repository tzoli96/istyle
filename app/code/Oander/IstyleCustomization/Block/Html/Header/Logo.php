<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Block\Html\Header;

/**
 * Class Logo
 * @package Oander\IstyleCustomization\Block\Html\Header
 */
class Logo extends \Magento\Theme\Block\Html\Header\Logo
{
    /**
     * Retrieve logo url
     *
     * @return int
     */
    public function getLogoUrl()
    {
        if (empty($this->_data['logo_url'])) {
            $this->_data['logo_url'] = $this->_scopeConfig->getValue(
                'design/header/logo_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->getUrl((string)$this->_data['logo_url'] ? : "");
    }

}
