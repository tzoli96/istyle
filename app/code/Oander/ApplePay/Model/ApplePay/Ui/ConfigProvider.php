<?php
namespace Oander\ApplePay\Model\ApplePay\Ui;

use Oander\ApplePay\Model\ApplePay\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 * @package Oander\ApplePay\Model\ApplePay\Ui
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_applepay';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @param Repository $assetRepo
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter,
        Repository $assetRepo
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'braintree_applepay' => [
                    'clientToken' => $this->getClientToken(),
                    'merchantName' => $this->getMerchantName(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Generate a new client token if necessary
     * @return string
     */
    public function getClientToken()
    {
        if (empty($this->clientToken)) {
            $this->clientToken = $this->adapter->generate();
        }

        return $this->clientToken;
    }

    /**
     * Get merchant name
     * @return string
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * Get the url to the payment mark image
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/applepaymark.png');
    }
}
