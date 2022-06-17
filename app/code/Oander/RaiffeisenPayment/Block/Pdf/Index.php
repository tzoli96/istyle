<?php

namespace Oander\RaiffeisenPayment\Block\Pdf;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}