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
use Magento\Quote\Model\Quote;
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

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;


    /**
     * @var Collection
     */
    protected $ruleCollection;

    /**
     * @var State
     */
    protected $appState;


    public function __construct(
        Collection $ruleCollection,
        State $appState,
        SalesRule $salesRuleValidator,
        ProductMetadataInterface $productMetaData
    ) {
        parent::__construct($ruleCollection, $appState, $salesRuleValidator, $productMetaData);
        $this->salesRuleValidator = $salesRuleValidator;
        $this->productMetaData = $productMetaData;
        $this->appState = $appState;
        $this->ruleCollection = $ruleCollection;
    }

    /**
     * @param AbstractMethod[] $paymentMethods
     * @param Quote|null $quote
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function restrictMethods($paymentMethods, $quote = null)
    {
        if (!$quote) {
            return $paymentMethods;
        }

        if ($this->productMetaData->getVersion() <= '2.2.1') {
            $quote->collectTotals();
        }

        $address = $quote->getShippingAddress();
        $items = $quote->getAllItems();
        $address->setItemsToValidateRestrictions($items);
        $hasBackOrders = false;
        $hasNoBackOrders = false;

        /** @var Item $item */
        foreach ($items as $item){
            if ($item->getBackorders() > 0 )
            {
                $hasBackOrders = true;
            } else {
                $hasNoBackOrders = true;
            }

            if ($hasBackOrders && $hasNoBackOrders) {
                break;
            }
        }
        $paymentMethods = $this->validateMethodsHotFix($paymentMethods, $address, $items);

        return $paymentMethods;
    }

    /**
     * @param AbstractMethod[] $paymentMethods
     * @param Address $address
     * @param Item[] $items
     *
     * @return AbstractMethod[]
     * @throws LocalizedException
     */
    private function validateMethodsHotFix($paymentMethods, $address, $items)
    {
        foreach ($paymentMethods as $key => $method) {
            /** @var Rule $rule */
            $rules=$this->getRules($address);
            foreach ($rules as $rule) {
                //HOT FIX
                if($address->getQuote()->getStoreId() == self::HOT_FIX_STORE_ID
                    && $method->getCode() === self::HOT_FIX_PAYMENTMEHODE_CODE
                    && $address->getShippingMethod() === self::HOT_FIX_SHIPPINGMETHODE_CODE
                ) {
                    unset($paymentMethods[$key]);
                }

                $rule->getId();
                if ($rule->restrict($method)
                    && $this->salesRuleValidator->validate($rule, $items)
                    && $rule->validate($address, $items)
                ) {
                    $rule->getId();
                    unset($paymentMethods[$key]);
                }
            }

        }

        $test=$paymentMethods;
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