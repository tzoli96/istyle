## Synopsis
Module for extending grand total rounding in cart side.
The module allows the use of new plugin and processors for rounding.
The processor isn't manipulates quote grand total instead of calculated grand total.## Technical feature
It's add a new quote totals which show different between grand total to  rounding grand total.## Technical feature

### Module configuration
1. Package details [composer.json](composer.json).
2. Module configuration details (sequence) in [module.xml](etc/module.xml).
3. Module configuration available through Stores->Configuration [system.xml](etc/adminhtml/system.xml)

## Installation
1. Copy files to the app/code/ folder

2. Enable the module
```bash
$ bin/magento module:enable Oander_ExternalRoundingUnit
$ bin/magento setup:upgrade
```

## Implementation

1. Added a new total
```xml
<group name="totals">
    <item name="external_rounding" instance="Oander\ExternalRoundingUnit\Model\Total\Quote\ExternalRounding" sort_order="110"/>
</group>
```
2. Defination the new total
```php
    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|ExternalRounding
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        return $this;
    }
```
3. Added a plugin grand total plugin
```php
/**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function aroundCollect(
        Subject                     $subject,
        \Closure                    $proceed,
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {

        $result = $proceed($quote, $shippingAssignment, $total);
        if ($this->helperConfig->IsEnabled()) {
            $grandTotal = array_sum($total->getAllTotalAmounts());
            if ($grandTotal) {
                $roundTotalAmmount = $this->helperConfig->getRounding($grandTotal);
                if ($grandTotal > $roundTotalAmmount) {
                    $externalRoundingAmmount = $grandTotal - $roundTotalAmmount;
                    $operator = "-";
                } else {
                    $externalRoundingAmmount = $roundTotalAmmount - $grandTotal;
                    $operator = "";
                }

                $total->setData(EnumConfig::SALES_CODE, $operator . $this->helperConfig->getFormatNumber($externalRoundingAmmount));
                $total->setGrandTotal($roundTotalAmmount);
                $total->setBaseGrandTotal($roundTotalAmmount);
                $quote->setData(Attribute::EXTERNAL_ROUNDING_UNITE_QUOTE_ATTRIBUTE, $operator . $externalRoundingAmmount);
            }

        }

        return $result;
    }
```


## Contributors
Oander Team 
Zoltan Turi (zoltan.turi@oander.hu)

## License
[Open Source License](LICENSE.txt)