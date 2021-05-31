<?php
/**
 * Oander_FreeShippingDisplay
 *
 * @author  David Belicza <david.belicza@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCheckout\Block\FreeShippingDisplay;

use Magento\Framework\View\Element\Template;
use Oander\FreeShippingDisplay\Model\Calculator;
use Oander\IstyleCheckout\Block\Cart\Totals;

/**
 * Class FreeShipping
 *
 * @package Oander\FreeShippingDisplay\Block
 */
class FreeShipping extends Template
{
    /**
     * @var Calculator
     */
    private $calculator;

    private $totals;

    /**
     * FreeShipping constructor.
     *
     * @param Template\Context $context
     * @param Calculator       $calculator
     * @param Totals           $totals
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        Calculator $calculator,
        Totals $totals,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->calculator = $calculator;
        $this->totals = $totals;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->calculator->isEnabled();
    }

    /**
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->calculator->isFree();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->calculator->getTextCart();
    }

    /**
     * @return bool
     */
    public function hasCartItems(): bool
    {
        return $this->totals->hasCartItems();
    }
}