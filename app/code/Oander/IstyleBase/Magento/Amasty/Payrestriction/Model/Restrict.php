<?php
namespace Oander\IstyleBase\Magento\Amasty\Payrestriction\Model;

use Amasty\CommonRules\Model\Validator\SalesRule;
use Amasty\Payrestriction\Model\ResourceModel\Rule\Collection;
use Amasty\Payrestriction\Model\Restrict as OriginalClass;
use Amasty\Payrestriction\Model\Rule;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;

class Restrict extends OriginalClass
{

    const HOT_FIX_SHIPPINGMETHODE_CODE = "oander_unity_pickup_base";
    const HOT_FIX_PAYMENTMEHODE_CODE = "cashonpickup";
    const HOT_FIX_STORE_ID = 2;

    /**
     * @var SalesRule
     */
    private $salesRuleValidator;

    public function __construct(
        Collection $ruleCollection,
        State $appState,
        SalesRule $salesRuleValidator,
        ProductMetadataInterface $productMetaData
    ) {
        parent::__construct($ruleCollection, $appState, $salesRuleValidator, $productMetaData);
        $this->salesRuleValidator = $salesRuleValidator;
    }

    /**
     * @param AbstractMethod[] $paymentMethods
     * @param Address $address
     * @param Item[] $items
     *
     * @return AbstractMethod[]
     * @throws LocalizedException
     */
    private function validateMethods($paymentMethods, $address, $items)
    {
        foreach ($paymentMethods as $key => $method) {
            /** @var Rule $rule */
            foreach ($this->getRules($address) as $rule) {
                if ($rule->restrict($method)
                    && $this->salesRuleValidator->validate($rule, $items)
                    && $rule->validate($address, $items)
                ) {
                    unset($paymentMethods[$key]);
                }
                //HOT FIX
                if($address->getQuote()->getStoreId() == self::HOT_FIX_STORE_ID
                    && $method->getCode() === self::HOT_FIX_PAYMENTMEHODE_CODE
                    && $address->getShippingMethod() === self::HOT_FIX_SHIPPINGMETHODE_CODE
                ) {
                    unset($paymentMethods[$key]);
                }
            }

        }

        return $paymentMethods;
    }


    /**
     * @param Address $address
     *
     * @return Collection|null
     * @throws LocalizedException
     */
    protected function getRules($address)
    {
        if (is_null($this->allRules)) {
            $this->allRules = $this->ruleCollection->addAddressFilter($address);

            if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
                $this->allRules->addFieldToFilter('for_admin', 1);
            }

            $this->allRules->load();

            /** @var Rule $rule */
            foreach ($this->allRules as $rule
            ){
                $rule->afterLoad();
            }
        }

        return $this->allRules;
    }
}