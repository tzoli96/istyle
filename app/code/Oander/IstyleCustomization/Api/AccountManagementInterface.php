<?php
namespace Oander\IstyleCustomization\Api;

/**
 * Interface for managing customers accounts.
 * @api
 */
interface AccountManagementInterface
{
    /**
     * Check if given email is associated with a customer account in given website.
     *
     * @param string $customerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEmailAvailable($customerEmail, $websiteId = null);
}