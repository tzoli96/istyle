<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\App\Request\Http;

/**
 * Class Product
 * @package Oander\IstyleCustomization\Plugin\Catalog\Model
 */
class Product
{
    const OANDER_API_MODULE_NAME = 'oander_api';
    const OANDER_API_ENTITY_REQUEST_URI = '/oander_api/gateway/entity';

    /**
     * @var Http
     */
    protected $http;

    /**
     * Product constructor.
     *
     * @param Http $http
     */
    public function __construct(
        Http $http
    ) {
        $this->http = $http;
    }

    /**
     * @param MagentoProduct $subject
     * @param                $result
     *
     * @return mixed
     */
    public function afterGetIdentities(MagentoProduct $subject, $result)
    {
        if ($tagKey = array_search(MagentoProduct::CACHE_TAG, $result, true)) {
            if ($this->http->getServer('REQUEST_URI') === self::OANDER_API_ENTITY_REQUEST_URI
                || $this->http->getModuleName() === self::OANDER_API_MODULE_NAME)
            {
                unset($result[$tagKey]);
            }
        }

        return $result;
    }
}