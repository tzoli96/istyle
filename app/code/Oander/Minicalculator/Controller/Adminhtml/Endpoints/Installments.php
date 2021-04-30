<?php

namespace Oander\Minicalculator\Controller\Adminhtml\Endpoints;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;
use Oander\HelloBankPayment\Model\BaremsFactory;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

class Installments extends Action
{
    /**
     * @var BaremsFactory
     */
    private $baremFactory;

    private $options = [];

    public function __construct(
        Context $context,
        BaremsFactory $baremFactory
    )
    {
        $this->baremFactory = $baremFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $type = $this->_request->getParam("type");
        $barem = $this->_request->getParam("barem");
        $this->handle($type, $barem);
        /* @var Json $result */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($this->options);
    }

    /**
     * @param $type
     * @return void
     */
    private function handle($type, $baremId)
    {
        switch ($type) {
            case "hellobank":
                $this->setOptions($this->baremFactory->create()->load($baremId));
                break;
        }
    }

    /**
     * @return void
     */
    private function setOptions($baremModel)
    {
        if ($baremModel->getData(BaremInterface::INSTALLMENTS_TYPE) == BaremInterface::INSTALLMENTS_TYPE_RANGE) {
            $options = explode(",", $baremModel->getData(BaremInterface::INSTALLMENTS));
            foreach ($options as $option) {
                $this->options[] = [
                    'value' => $option,
                    'label' => $option
                ];
            }
        } else {
            $this->options = [
                'value' => $baremModel->getData(BaremInterface::INSTALLMENTS),
                'label' => $baremModel->getData(BaremInterface::INSTALLMENTS)
            ];
        }
    }
}

