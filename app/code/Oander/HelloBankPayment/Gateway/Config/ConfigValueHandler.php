<?php
namespace Oander\HelloBankPayment\Gateway\Config;

use Oander\HelloBankPayment\Model\Ui\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Gateway\Config\Config as DefaultConfig;

class ConfigValueHandler extends DefaultConfig
{
    const KEY_ACTIVE                  = 'active';
    const KEY_TITLE                   = 'title';
    const KEY_INSTRUCTIONS            = 'instructions';
    const KEY_SELLER_ID               = 'seller_id';
    const KEY_APPROVAL_MESSAGE        = 'approval_message';
    const KEY_FURTHER_REVIEW_MESSAGE  = 'further_review_message';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * Config constructor.
     *
     * @param Json                 $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface    $timezone
     * @param AssetRepository      $assetRepository
     * @param null                 $methodCode
     * @param string               $pathPattern
     */
    public function __construct(
        Json $serializer,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        AssetRepository $assetRepository,
        $methodCode = null,
        $pathPattern = DefaultConfig::DEFAULT_PATH_PATTERN
    ) {
        DefaultConfig::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );

        $this->setMethodCode(ConfigProvider::CODE);

        $this->serializer = $serializer;
        $this->timezone = $timezone;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getIsActive($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return (string)$this->getValue(self::KEY_TITLE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getInstructions($storeId = null)
    {
        return (string)$this->getValue(self::KEY_INSTRUCTIONS, $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getSellerId($storeId = null)
    {
        return (int)$this->getValue(self::KEY_SELLER_ID, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getApprovalMessage($storeId = null)
    {
        return (string)$this->getValue(self::KEY_APPROVAL_MESSAGE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getFutherReviewMessage($storeId = null)
    {
        return (string)$this->getValue(self::KEY_FURTHER_REVIEW_MESSAGE, $storeId);
    }

}