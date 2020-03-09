<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Popup\Model\Event\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\Element\UiComponent\Context\Interceptor as Interceptor;

/**
 * Class ContextPlugin
 * @package Aheadworks\Popup\Model\Event\Plugin
 */
class ContextPlugin
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @param HttpRequest $request
     */
    public function __construct(
        HttpRequest $request
    ) {
        $this->request = $request;
    }

    /**
     * Set render type for correct work of Recently Viewed, Recently Compared Widgets
     *
     * @param Interceptor $interceptor
     * @param string $originalType
     * @return string
     */
    public function afterGetAcceptType(
        Interceptor $interceptor,
        $originalType
    ) {
        if ($this->request->getParam('aw_popup')) {
            return 'html';
        }

        return $originalType;
    }
}
