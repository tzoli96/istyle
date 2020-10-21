<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_IstyleBase
 *
 * TransportBuilderByStore
 *
 * @author  Róbert Betlen <robert.betlen@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */
namespace Oander\IstyleBase\Magento\Framework\Mail\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param $from
     * @param $store
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    public function setFromByScope($from, $store)
    {
        $result = $this->_senderResolver->resolve($from, $store);
        $this->message->setFrom($result['email'], $result['name']);
        return $this;
    }

    /**
     * @param $content
     * @param $name
     * @return $this
     */
    public function addAttachment($content, $name)
    {
        $this->message->createAttachment(
            $content,
            \Zend_Mime::TYPE_OCTETSTREAM,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $name
        );

        return $this;
    }
}