<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CmsPageEvent implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();
        if ($eventName === 'controller_front_send_response_before') {
            /**
             * @var \Magento\Framework\App\Request\Http $request
             */
            $request = $observer->getEvent()->getRequest();
            if ($request->getModuleName() === 'cms' && $request->getControllerName() === 'page') {
                /**
                 * @var \Magento\Framework\App\Response\Http\Interceptor $response
                 */
                $response = $observer->getEvent()->getResponse();

                $content = $response->getContent();

                if (preg_match('~<body[^>]*>(.*?)</body>~si', $content, $body)) {
                    if (empty($body[1])) {
                        $response->setHeader('Pragma', 'no-cache');
                        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
                        $response->setHeader('Expires', date('D, d M Y H:i:s e'));
                    }
                }
            }
        }
    }
}