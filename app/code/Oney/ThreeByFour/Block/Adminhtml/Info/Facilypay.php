<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Oney\ThreeByFour\Block\Adminhtml\Info;

use Magento\Backend\Block\Template;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;

class Facilypay extends \Magento\Config\Block\System\Config\Form\Field
{
    const ERROR_MESSAGE = "oney_error_message_admin";
    const WARNING_MESSAGE = "Unable to establish connection with Oney API. Please verify your login details. If the problem persists, please contact your administrator.";
    const SUCCESS_MESSAGE = "Connection to the Oney API has been completed successfully. You can configure the different payment methods in the 'Oney Payments' section.";
    const ERROR_CLASS = "oney error";
    const WARNING_CLASS = "oney warning";
    const SUCCESS_CLASS = "oney success";

    /**
     * @var string
     */
    protected $error = "";
    /**
     * @var BusinessTransactionsInterface
     */
    protected $businessTransactions;

    public function __construct(
        Template\Context $context,
        BusinessTransactionsInterface $businessTransactions,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->businessTransactions = $businessTransactions;
    }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_layout
            ->createBlock(self::class)
            ->setTemplate('Oney_ThreeByFour::system/config/admin_info.phtml')
            ->setCacheable(false)
            ->toHtml();

        return $html;
    }

    /**
     * Get Message class by Error
     */
    public function getMessageClass()
    {
        $error = $this->businessTransactions->getResponse();

        if ($error === 200) {
            return self::SUCCESS_CLASS;
        }
        if(in_array($error, [400, 401], false)) {
            return self::WARNING_CLASS;
        }
        return self::ERROR_CLASS;
    }

    /**
     * Get Message message by Error
     */
    public function getMessageContent()
    {
        $error = $this->businessTransactions->getResponse();

        if($error === 200) {
            return self::SUCCESS_MESSAGE;
        }
        if(in_array($error, [400, 401], false)) {
            return self::WARNING_MESSAGE;
        }
        return self::ERROR_MESSAGE;
    }
}
