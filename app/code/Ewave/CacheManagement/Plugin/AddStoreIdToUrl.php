<?php
namespace Ewave\CacheManagement\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\AbstractBlock as Subject;

class AddStoreIdToUrl
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $fullActionNames = [];

    /**
     * AddStoreIdToUrl constructor.
     * @param RequestInterface $request
     * @param array $fullActionNames
     */
    public function __construct(
        RequestInterface $request,
        array $fullActionNames = []
    ) {
        $this->request = $request;
        $this->fullActionNames = $fullActionNames;
    }

    /**
     * @param Subject $block
     * @param string $route
     * @param array $params
     * @return array
     */
    public function beforeGetUrl(
        Subject $block,
        $route = '',
        $params = []
    ) {
        if (in_array($this->request->getFullActionName(), $this->fullActionNames)) {
            $params['_current'] = true;
        }
        return [$route, $params];
    }
}
