<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Rewrite\Oander\ApiOrderAttachment\Model;

use Oander\ApiOrderAttachment\Enum\Config as ConfigEnum;

/**
 * Class AttachmentHandler
 *
 * @package Oander\IstyleCustomization\Rewrite\Oander\ApiOrderAttachment\Model
 */
class AttachmentHandler extends \Oander\ApiOrderAttachment\Model\AttachmentHandler
{

    /**
     * @param string $url
     *
     * @return string
     */
    public function getFileContents(string $url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $serverAuth = $this->getServerAuthData($url);
        if ($serverAuth) {
            curl_setopt($ch, CURLOPT_USERPWD, $serverAuth[ConfigEnum::USERNAME] . ':' . $serverAuth[ConfigEnum::PASSWORD]);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}