<?php


namespace Oney\ThreeByFour\Controller\Payment;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Pricing\Helper\Data;
use Oney\ThreeByFour\Api\Marketing\SimulationInterface;
use Oney\ThreeByFour\Api\Marketing\BusinessTransactionsInterface;
use Oney\ThreeByFour\Helper\Config;

class Simulation extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SimulationInterface
     */
    protected $_simulationService;
    /**
     * @var Config
     */
    protected $helperConfig;

    protected $pricingHelper;

    /**
     * Simulation constructor.
     *
     * @param Context             $context
     * @param SimulationInterface $simulation
     * @param Config              $helperConfig
     */
    public function __construct(
        Context $context,
        SimulationInterface $simulation,
        Data $pricingHelper,
        Config $helperConfig
    )
    {
        $this->_simulationService = $simulation;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context);
        $this->helperConfig = $helperConfig;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $amount = $this->currency((float)$this->getRequest()->getParam('amount'));
        if (($code = $this->getRequest()->getParam('code')) &&
            $this->helperConfig->isPaymentActiveForCode($this->getRequest()->getParam('code'))) {
            echo json_encode($this->_simulationService->build($amount)->getSimulation([
                "payment_amount" => (float)round($amount,2),
                "business_transaction_code" => str_replace('facilypay_','', $code)
            ]));
            exit;
        }
        echo json_encode($this->_simulationService->build($amount)->getSimulations());
        exit;
    }

    /**
     * @param float $value
     *
     * @return mixed
     */
    public function currency($value) {
        return $this->pricingHelper->currency($value, false, false);
    }
}
