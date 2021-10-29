<?php

namespace Oney\ThreeByFour\Model\Api\Marketing;

use Oney\ThreeByFour\Api\Marketing\TermofUseInterface;
use Oney\ThreeByFour\Model\Api\ApiAbstract;

class TermofUse extends ApiAbstract implements TermofUseInterface
{

    /**
     * @var string
     */
    protected $_language;

    /**
     * @var string
     */
    protected $_type;

    public function getTermofUse()
    {
        $this->setHeaders([
            'X-Oney-Authorization' => $this->_helperConfig->getGeneralConfigValue('api_marketing'),
            'X-Oney-Partner-Country-Code' => $this->_helperConfig->getGeneralConfigValue('country')
        ]);
        $this->setParams([
            'legal_notice_type' => $this->_type,
            'language_code' => $this->_language,
            'merchant_guid' => $this->_helperConfig->getGeneralConfigValue('merchant_guid'),
            'psp_guid' => $this->_helperConfig->getGeneralConfigValue('psp_guid')
        ]);
        return json_decode($this->call('GET', $this->_helperConfig->getUrlForStep('legal_notice')), true);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->_language = $language;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
}
