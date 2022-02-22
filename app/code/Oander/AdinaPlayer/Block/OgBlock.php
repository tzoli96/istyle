<?php

namespace Oander\AdinaPlayer\Block;

use Magento\Framework\View\Element\Template;

class OgBlock extends Template
{
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}