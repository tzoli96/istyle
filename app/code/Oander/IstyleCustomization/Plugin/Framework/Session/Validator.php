<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Framework\Session;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Oander\IstyleCustomization\Logger\Logger;

/**
 * Class Validator
 * @package Oander\IstyleCustomization\Plugin\Framework\Session
 */
class Validator
{
    const SESSION_REGENERATE_COUNT = 'session_regenerate_count';
    const MAX_SESSION_REGENERATE = 10;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Validator constructor.
     *
     * @param ResourceConnection $resource
     * @param Registry           $registry
     * @param Logger             $logger
     */
    public function __construct(
        ResourceConnection $resource,
        Registry $registry,
        Logger $logger
    ) {
        $this->resource = $resource;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Session\Validator $subject
     * @param SessionManagerInterface              $session
     */
    public function beforeValidate(\Magento\Framework\Session\Validator $subject, SessionManagerInterface $session)
    {
        $sessionId = $session->getSessionId();

        if ($this->isSessionExist($sessionId)) {
            $regenerateCount = $this->increaseRegenerateCounter();
            $this->logger->addWarning(
                __('Session with this session_id already exist - session_id: %1, regenerate count: %2 ', $session->getSessionId(), $regenerateCount),
                [
                    '$_SERVER'  => $_SERVER,
                    '$_SESSION' => $_SESSION,
                    '$_REQUEST' => $_REQUEST,
                    '$_COOKIE'  => $_COOKIE
                ]
            );

            if ($regenerateCount < self::MAX_SESSION_REGENERATE) {
                $this->logger->addInfo(
                    __('New session generated - old session_id: %1', $session->getSessionId()),
                    [
                        '$_SERVER'  => $_SERVER,
                        '$_SESSION' => $_SESSION,
                        '$_REQUEST' => $_REQUEST,
                        '$_COOKIE'  => $_COOKIE
                    ]
                );
                $session->destroy(['clear_storage' => false]);
                $session->start();

            } elseif ($regenerateCount == self::MAX_SESSION_REGENERATE) {
                $customSessionId = md5(rand().microtime());
                $this->logger->addError(
                    __('Session regeneration reached the maximum limit(%1), custom session_id has been set - session_id: %2 - custom session_id: %3',
                       self::MAX_SESSION_REGENERATE, $session->getSessionId(), $customSessionId
                    ),
                    [
                        '$_SERVER'  => $_SERVER,
                        '$_SESSION' => $_SESSION,
                        '$_REQUEST' => $_REQUEST,
                        '$_COOKIE'  => $_COOKIE
                    ]
                );
                $session->setSessionId($customSessionId);

            }
        }
    }

    /**
     * @param $session_id
     *
     * @return bool
     */
    private function isSessionExist($session_id)
    {
        $connection = $this->resource->getConnection();

        return (bool)$connection->fetchOne(
            $connection->select()
                       ->from($this->resource->getTableName('customer_visitor'))
                       ->where($connection->quoteIdentifier('session_id'). ' = ?', $session_id)
                       ->limit(1)
        );
    }

    /**
     * @return integer
     */
    private function getRegenerateCount()
    {
        if (!$this->registry->registry(self::SESSION_REGENERATE_COUNT)) {
            $this->registry->register(self::SESSION_REGENERATE_COUNT,0);
        }

        return $this->registry->registry(self::SESSION_REGENERATE_COUNT);
    }

    /**
     * @return integer
     */
    private function increaseRegenerateCounter()
    {
        $regenerateCount = $this->getRegenerateCount();
        $regenerateCount++;
        $this->registry->unregister(self::SESSION_REGENERATE_COUNT);
        $this->registry->register(self::SESSION_REGENERATE_COUNT, $regenerateCount);

        return $this->getRegenerateCount();
    }
}