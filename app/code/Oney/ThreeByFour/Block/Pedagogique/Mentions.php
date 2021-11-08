<?php


namespace Oney\ThreeByFour\Block\Pedagogique;


use Magento\Directory\Model\Currency;
use Magento\Framework\View\Element\Template;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Block\AbstractOney;
use Oney\ThreeByFour\Helper\Config;

class Mentions extends AbstractOney
{
    /**
     * @var BusinessTransactionsInterface
     */
    protected $businessTransactions;
    /**
     * @var Currency
     */
    protected $currency;

    public function __construct(
        Template\Context $context,
        Config $helperConfig,
        Currency $currency,
        BusinessTransactionsInterface $businessTransactions,
        array $data = []
    )
    {
        parent::__construct($context, $helperConfig, $data);
        $this->checkAndSetTemplate('pedagogique/mentions.phtml');
        $this->businessTransactions = $businessTransactions;
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function getBusinessTransactions()
    {
        $array = [];
        foreach ($this->businessTransactions->getActiveBusinessTransactions() as $transaction) {
            $key = $transaction['number_of_instalments'];
            if (!isset($array[$key])) {
                $array[$key] = [
                    "min_amount" => $transaction['min_order_total'],
                    "max_amount" =>$transaction['max_order_total']
                ];
            }
            else {
                if($array[$key]['min_amount'] > $transaction['min_order_total']){
                    $array[$key]['min_amount'] = $transaction['min_order_total'];
                }
                if($array[$key]['max_amount'] < $transaction['max_order_total']){
                    $array[$key]['max_amount'] = $transaction['max_order_total'];
                }
            }
        }
        return $array;
    }

    /**
     *
     */
    public function currency($amount)
    {
        return $this->currency->format($amount);
    }

    /**
     *
     */
    public function isFree() {
        $isFree = true;
        foreach ($this->businessTransactions->getActiveBusinessTransactions() as $transaction) {
            $isFree = $isFree && $transaction['without_fee'];
        }
        return $isFree;
    }

    /**
     *
     */
}
