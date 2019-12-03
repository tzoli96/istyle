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

use Magento\Framework\Mail\MessageInterface;
use \Magento\Framework\Mail\Template\SenderResolverInterface;

class TransportBuilderByStore
{

    /**
     * Message.
     *
     * @var \Magento\Framework\Mail\Message
     */
    protected $message;

    /**
     * Sender resolver.
     *
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        MessageInterface $message,
        SenderResolverInterface $senderResolver
    ) {
        $this->message = $message;
        $this->senderResolver = $senderResolver;
    }

    /**
     * Set mail from address by store.
     *
     * @param string|array $from
     * @param string|int $store
     *
     * @return $this
     */
    public function setFromByStore($from, $store)
    {
        $result = $this->senderResolver->resolve($from, $store);
        $this->message->setFrom($result['email'], $result['name']);
        return $this;
    }
}