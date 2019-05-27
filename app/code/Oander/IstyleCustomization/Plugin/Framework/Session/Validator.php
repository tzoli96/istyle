<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Framework\Session;

use Magento\Framework\DB\Select;
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

    const CUSTOMER_VISITOR_TABLE = 'customer_visitor';
    const VISITOR_ID = 'visitor_id';
    const SESSION_ID = 'session_id';

    const VISITOR_DATA = 'visitor_data';
    const VISITOR_DATA_STRUCTURE = [
        self::VISITOR_ID => null,
        self::SESSION_ID => null
    ];

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

        if ($this->isSessionIdUsedByAnotherVisitor($sessionId)) {
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
                $session->destroy(['clear_storage' => true]);
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
     * @param $sessionId
     *
     * @return bool
     */
    private function isSessionIdUsedByAnotherVisitor($sessionId)
    {
        $visitorDatabase = $this->getVisitorDatabaseData($sessionId);
        if ($visitorDatabase[self::VISITOR_ID] === null || $visitorDatabase[self::SESSION_ID] === null) {
            return false;
        }

        $visitorSession = $this->getVisitorSessionData();
        if ($sessionId == $visitorDatabase[self::SESSION_ID] && $sessionId == $visitorSession[self::SESSION_ID]
            && $visitorDatabase[self::VISITOR_ID] == $visitorSession[self::VISITOR_ID]
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getVisitorSessionData()
    {
        $visitorData = self::VISITOR_DATA_STRUCTURE;

        if (isset($_SESSION['default'],
            $_SESSION['default'][self::VISITOR_DATA],
            $_SESSION['default'][self::VISITOR_DATA][self::SESSION_ID],
            $_SESSION['default'][self::VISITOR_DATA][self::VISITOR_ID]
        )) {
            $visitorData[self::SESSION_ID] = $_SESSION['default'][self::VISITOR_DATA][self::SESSION_ID];
            $visitorData[self::VISITOR_ID] = $_SESSION['default'][self::VISITOR_DATA][self::VISITOR_ID];
        }

        return $visitorData;
    }

    /**
     * @param $sessionId
     *
     * @return array
     */
    private function getVisitorDatabaseData($sessionId)
    {
        $visitorData = self::VISITOR_DATA_STRUCTURE;
        $connection = $this->resource->getConnection();
        $databaseVisitorData = $connection->fetchRow(
            $connection->select()
               ->from($this->resource->getTableName(self::CUSTOMER_VISITOR_TABLE))
               ->where($connection->quoteIdentifier(self::SESSION_ID). ' = ?', $sessionId)
               ->order(self::VISITOR_ID, Select::SQL_ASC)
               ->limit(1)
        );

        if (isset($databaseVisitorData[self::SESSION_ID], $databaseVisitorData[self::VISITOR_ID])) {
            $visitorData[self::SESSION_ID] = $databaseVisitorData[self::SESSION_ID];
            $visitorData[self::VISITOR_ID] = $databaseVisitorData[self::VISITOR_ID];
        }

        return $visitorData;
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