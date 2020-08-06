<?php
/**
 * Oander_FanCourierValidator
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\FanCourierValidator\Plugin\Magento\Checkout\Model;

use Magento\Framework\Exception\InputException;
use Oander\FanCourierValidator\Helper\Data;

/**
 * Class ShippingInformationManagement
 * @package Oander\FanCourierValidator\Plugin\Magento\Checkout\Model
 */
class ShippingInformationManagement
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws InputException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($this->data->getValidationLevel() != '') {
            $address = $addressInformation->getShippingAddress();
            $region = $address->getRegion();
            if (empty($region)) {
                $exception = new InputException();
                $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'region']));
                throw $exception;
            }
        }
    }

}