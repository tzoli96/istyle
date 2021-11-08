<?php

namespace Oney\ThreeByFour\Plugin\Config\Model\Config\Structure;

use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Structure\Data;
use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Module\Manager;
use Magento\Framework\TranslateInterface;
use Magento\Paypal\Model\Config\StructurePlugin;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;
use Magento\Shipping\Model\Config as CarrierConfig;
use Oney\ThreeByFour\Model\Config\Source;

class DataPlugin
{
    const PATH = "payment/oney_section/oney_payments";

    const DELIVERY_MODE_CODE = "deliverymodecode";
    const ADDRESS_TYPE = "addresstype";
    const DELIVERY_OPTION = "deliveryoption";
    const PRIORITY_DELIVERY_CODE = "prioritydeliverycode";

    const CARRIER_CONFIGS = [
        self::DELIVERY_MODE_CODE,
        self::DELIVERY_OPTION,
        self::PRIORITY_DELIVERY_CODE,
        self::ADDRESS_TYPE
    ];
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var BusinessTransactionsInterface
     */
    protected $_businessTransactions;
    /**
     * @var Config
     */
    protected $_config;
    /**
     * @var CarrierConfig
     */
    protected $_carrierConfig;
    /**
     * @var Manager
     */
    protected $moduleManager;
    /**
     * @var Resolver
     */
    protected $resolver;
    /**
     * @var Dictionary
     */
    protected $dictionary;
    /**
     * @var TranslateInterface
     */
    protected $translate;
    private $facilypayMethodStructure = [];

    /**
     * StructurePlugin constructor.
     *
     * @param Logger                        $logger
     * @param BusinessTransactionsInterface $businessTransactions
     * @param Config                        $config
     * @param CarrierConfig                 $carrierConfig
     * @param Manager                       $moduleManager
     */
    public function __construct(
        Logger $logger,
        BusinessTransactionsInterface $businessTransactions,
        Config $config,
        CarrierConfig $carrierConfig,
        Manager $moduleManager,
        Resolver $resolver,
        Dictionary $dictionary,
        TranslateInterface $translate
    )
    {
        $this->_config = $config;
        $this->_logger = $logger;
        $this->_carrierConfig = $carrierConfig;
        $this->_businessTransactions = $businessTransactions;
        $this->moduleManager = $moduleManager;
        $this->resolver = $resolver;
        $this->dictionary = $dictionary;
        $this->translate = $translate->loadData();
    }

    /**
     * @param Data $subject
     * @param      $return
     *
     * @return mixed
     */
    public function afterGet(Data $subject, $return)
    {
        if (isset($return['sections'])) {
            $paymentSection = [];
            if ($this->moduleManager->isEnabled("Magento_Paypal")) {
                foreach (StructurePlugin::getPaypalConfigCountries() as $country) {
                    if(!$this->_config->isLegalEnabled()) {
                        unset($return['sections'][$country]['children']['oney_section']['children']['oney_legal']);
                    }
                    $paymentSection[$country] = [
                        'children' => [
                            "oney_section" => [
                                "children" => [
                                    "oney_payments" => [
                                        "children" => $this->buildFacilypayMethodStructure()
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
                $paymentSection["payment_other"] = [
                    'children' => [
                        "oney_section" => [
                            "children" => [
                                "oney_payments" => [
                                    "children" => $this->buildFacilypayMethodStructure()
                                ]
                            ]
                        ]
                    ]
                ];
            }
            $paymentSection["payment"] = [
                'children' => [
                    "oney_section" => [
                        "children" => [
                            "oney_payments" => [
                                "children" => $this->buildFacilypayMethodStructure()
                            ]
                        ]
                    ]
                ]
            ];

            $sections['sections'] = array_merge($paymentSection, [
                'carriers' => [
                    'children' => $this->buildCarrierStructure($return['sections']['carriers']['children'])
                ]
            ]);
            $return = array_merge_recursive($return, $sections);
        }
        return $return;
    }

    /**
     * @return array
     */
    protected function buildFacilypayMethodStructure()
    {
        if (empty($this->facilypayMethodStructure)) {
            $comment_without_fee = "";
            foreach ($this->_businessTransactions->getBusinessTransactions() as $businessTransaction) {
                if($businessTransaction['without_fee']) {
                    $comment_without_fee = " - " . $this->translate('Without fees');
                }
                else {
                    $comment_without_fee = " - " . $this->translate('With fees');
                }
                $this->facilypayMethodStructure[$businessTransaction['code']] = [
                    "id" => $businessTransaction['code'],
                    "translate" => "label comment",
                    "sortOrder" => "0",
                    "type" => "select",
                    "showInDefault" => "1",
                    "showInWebsite" => "1",
                    "showInStore" => "1",
                    "comment" => $this->translate("Customer's cart eligible amounts from %1 to %2",
                        [$businessTransaction['min_order_total'], $businessTransaction['max_order_total']]) . $comment_without_fee,
                    "label" => $businessTransaction['title'],
                    "config_path" => "payment/oney_section/" . $businessTransaction['code'],
                    "source_model" => Enabledisable::class,
                    "path" => self::PATH,
                    "_elementType" => "field"
                ];
            }
        }
        return $this->facilypayMethodStructure;
    }

    /**
     * @return array
     */
    protected function buildCarrierStructure(array $existingCarriers)
    {
        $response = [];
        foreach ($this->_carrierConfig->getAllCarriers() as $carrier) {
            $code = $carrier->getCarrierCode();
            if (isset($existingCarriers[$code])) {
                foreach (self::CARRIER_CONFIGS as $configCode) {
                    $response[$code]['children'][$configCode] = $this->generateFieldCarrier($code, $configCode);
                }
            }
        }
        return $response;
    }

    /**
     * @return array
     */
    protected function generateFieldCarrier($carrierCode, $carrierConfigCode)
    {
        $field = [
            "id" => $carrierConfigCode,
            "translate" => "label",
            "type" => "select",
            "showInDefault" => "1",
            "showInWebsite" => "1",
            "showInStore" => "1",
            "sortOrder" => "100",
            "_elementType" => "field",
            "path" => "carriers/" . $carrierCode

        ];
        switch ($carrierConfigCode) {
            case self::DELIVERY_MODE_CODE:
                $field["source_model"] = Source\DeliveryModeCode::class;
                $field["label"] = Source\DeliveryModeCode::LABEL;
                break;
            case self::ADDRESS_TYPE:
                $field["source_model"] = Source\AddressType::class;
                $field["label"] = Source\AddressType::LABEL;
                break;
            case self::PRIORITY_DELIVERY_CODE:
                $field["source_model"] = Source\PriorityDeliveryCode::class;
                $field["label"] = Source\PriorityDeliveryCode::LABEL;
                break;
            case self::DELIVERY_OPTION:
                $field["source_model"] = Source\DeliveryOption::class;
                $field["label"] = Source\DeliveryOption::LABEL;
                break;
        }
        return $field;
    }

    /**
     * @param       $text
     * @param array $arguments
     */
    private function translate($text , $arguments = []) {
        try{
            $translate = $this->translate->getData()[$text];
        }catch (\Exception $e) {
            $translate = $text;
        }
        $placeHolder = [];
        foreach ($arguments as $key => $argument) {
            $placeHolder['%' . (is_int($key) ? (string)($key + 1) : $key)] = $argument;
        }
        return strtr($translate, $placeHolder);
    }
}
