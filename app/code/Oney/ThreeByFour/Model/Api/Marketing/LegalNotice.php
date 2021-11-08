<?php


namespace Oney\ThreeByFour\Model\Api\Marketing;


use Oney\ThreeByFour\Api\Marketing\LegalNoticeInterface;
use Oney\ThreeByFour\Model\Api\ApiAbstract;

class LegalNotice extends ApiAbstract implements LegalNoticeInterface
{

    public function getLegalNotice($type = '')
    {
        $this->setHeaders([
            'X-Oney-Authorization' => $this->_helperConfig->getGeneralConfigValue('api_marketing'),
            'X-Oney-Partner-Country-Code' => $this->_helperConfig->getGeneralConfigValue('country')
        ]);
        $this->setParams([
            'legal_notice_type' => $type,
            'merchant_guid' => $this->_helperConfig->getGeneralConfigValue('merchant_guid')
        ]);
        return json_decode($this->call('GET', $this->_helperConfig->getUrlForStep('legal_notice')), true);
    }
}
