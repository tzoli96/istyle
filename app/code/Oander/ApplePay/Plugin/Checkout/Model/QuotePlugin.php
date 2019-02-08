<?php

declare(strict_types=1);

namespace Oander\ApplePay\Plugin\Checkout\Model;

use Magento\Quote\Model\Quote;


/**
 * Class QuotePlugin
 * @package Oander\ApplePay\Plugin\Checkout\Model
 */
class QuotePlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Oander\ApplePay\Helper\PaymentConfig
     */
    private $config;

    /**
     * QuotePlugin constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Oander\ApplePay\Helper\PaymentConfig $config
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Oander\ApplePay\Helper\PaymentConfig $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param Quote $subject
     * @param $result
     * @return bool|mixed
     *
     */
    public function afterGetIsActive(Quote $subject, $result)
    {
        if($this->request->getParam('isApplePay',false) && $this->config->isActive()){
            $result = 1;
        }

        return $result;
    }

}