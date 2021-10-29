<?php

namespace Oney\ThreeByFour\Gateway\Request;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\Country;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oney\ThreeByFour\Helper\Config;
use Oney\ThreeByFour\Logger\Logger;

class CustomerDataBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    protected $_helperConfig;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var Country
     */
    protected $country;
    /**
     * CustomerDataBuilder constructor.
     *
     * @param Config                      $helperConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param Logger                      $logger
     * @param Country                     $country
     */
    public function __construct(
        Config $helperConfig,
        CustomerRepositoryInterface $customerRepository,
        Logger $logger,
        Country $country
    )
    {
        $this->_customerRepository = $customerRepository;
        $this->_logger = $logger;
        $this->country = $country;
        $this->_helperConfig = $helperConfig;
    }

    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];

        $order = $payment->getOrder();
        $billing_address = $order->getBillingAddress();

        $response = ['customer' => [
            "customer_external_code" => $order->getOrderIncrementId(),
            "language_code" => $this->_helperConfig->getLanguageCode(),
            "identity" => array(
                "person_type" => 2,
                "honorific_code" => $this->getGender($order),
                "birth_name" => $billing_address->getLastname(),
                "first_name" => $billing_address->getFirstname()
            ),
            "contact_details" => array(
                "mobile_phone_number" => $this->formatPhone($billing_address->getTelephone(), $billing_address->getCountryId()),
                "email_address" => $billing_address->getEmail()
            )
        ]
        ];
        $this->_logger->info('Oney :: Customer builder :', $response);
        return $response;
    }

    /**
     * @param OrderAdapterInterface $orderAdapter
     *
     * @return int|null
     */
    protected function getGender(OrderAdapterInterface $orderAdapter)
    {
        try{
            $gender = $this->_customerRepository->getById($orderAdapter->getCustomerId())
                ->getGender();
        }catch (\Exception $e){
            $gender = null;
        }

        return ($gender === 3 || $gender === null) ? 0 : $gender; //3 & null correspond to "Not specified"
    }

    /**
     * @param string  $phone
     * @param integer $country
     *
     * @return int|null
     */
    protected function formatPhone($phone, $country)
    {

        $countryCode = $this->_helperConfig->getPhonePrefixByCountry($country);

        if (preg_match("/^\\+" . $countryCode . "/", $phone)) { //If international Format (+33XXXXXXXXXXX)
            return $phone;
        }

        if (preg_match("/^00" . $countryCode . "/", $phone)) { //If international Format with 00 (0033XXXXXXXXXXX)
            return '+' . ltrim($phone, "0");
        }

        return '+' . $countryCode . ltrim($phone, "0"); // If national format (0XXXXXXXXXX)
    }
}
