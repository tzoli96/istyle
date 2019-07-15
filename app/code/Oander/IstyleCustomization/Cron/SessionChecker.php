<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Cron;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Oander\IstyleCustomization\Helper\Config;

/**
 * Class SessionChecker
 * @package Oander\IstyleCustomization\Cron
 */
class SessionChecker
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * SessionChecker constructor.
     *
     * @param Config                $config
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder      $transportBuilder
     * @param ResourceConnection    $resourceConnection
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->config             = $config;
        $this->transportBuilder   = $transportBuilder;
        $this->storeManager       = $storeManager;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isSessionCheckerEnabled()) {
            return;
        }

        $customerVisitorTable = $this->resourceConnection->getTableName('customer_visitor');
        $customerEntityTable  = $this->resourceConnection->getTableName('customer_entity');
        $connection           = $this->resourceConnection->getConnection();

        $sql = sprintf(
            'SELECT %s.*, %s, %s FROM %s LEFT JOIN %s ON %s.%s = %s.%s WHERE %s IN (SELECT %s FROM %s WHERE %s IS NOT null GROUP BY %s HAVING count(*) > 1) ORDER BY %s.%s ASC',
            $connection->quoteIdentifier($customerVisitorTable),
            $connection->quoteIdentifier('email'),
            $connection->quoteIdentifier('created_in'),
            $connection->quoteIdentifier($customerVisitorTable),
            $connection->quoteIdentifier($customerEntityTable),
            $connection->quoteIdentifier($customerEntityTable),
            $connection->quoteIdentifier('entity_id'),
            $connection->quoteIdentifier($customerVisitorTable),
            $connection->quoteIdentifier('customer_id'),
            $connection->quoteIdentifier('session_id'),
            $connection->quoteIdentifier('session_id'),
            $connection->quoteIdentifier($customerVisitorTable),
            $connection->quoteIdentifier('customer_id'),
            $connection->quoteIdentifier('session_id'),
            $connection->quoteIdentifier($customerVisitorTable),
            $connection->quoteIdentifier('session_id')
        );

        $result = $connection->fetchAll($sql);

        if (!empty($result)) {
            $errorMsg = '';
            foreach ($result as $error) {
                foreach ($error as $key => $value) {
                    $errorMsg .= $key . ':' . $value . ';';
                }
                $errorMsg .= PHP_EOL;
            }

            if ($errorMsg !== '') {
                $this->send($errorMsg);
            }
        }
    }

    /**
     * @param string $error
     *
     * @throws MailException
     */
    public function send(string $error)
    {
        $emailAddresses = $this->config->getSessionCheckerEmailReceivers();
        if (!empty($emailAddresses)) {
            $mainReceiver = $emailAddresses[0];
            unset($emailAddresses[0]);
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'oander_session_checker_email_template'
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getDefaultStoreView()->getCode(),
                ]
            )->setTemplateVars(
                [
                    'errorMsg' => $error
                ]
            )->addTo(
                $mainReceiver
            )->addCc(
                $emailAddresses
            )->getTransport();
            $transport->sendMessage();
        }
    }
}