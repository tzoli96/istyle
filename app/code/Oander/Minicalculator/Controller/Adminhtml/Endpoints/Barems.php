<?php

namespace Oander\Minicalculator\Controller\Adminhtml\Endpoints;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;

class Barems extends Action
{
    /**
     * @var Collection
     */
    private $baremCollection;

    private $options = [];

    public function __construct(
        Context $context,
        Collection $baremCollection
    ){
        $this->baremCollection = $baremCollection;
        parent::__construct($context);
    }


    public function execute() {
        $type = $this->_request->getParam("type");
        $this->handle($type);
        /* @var Json $result */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($this->options);
    }

    /**
     * @param $type
     * @return void
     */
    private function handle($type)
    {
        switch ($type) {
            case "hellobank":
                $this->setOptions($this->baremCollection->AddFillterAvailableBarems());
                break;
        }
    }

    /**
     * @return void
     */
    private function setOptions($collection)
    {
        try {
            foreach ($collection as $barem) {
                $this->options[] = [
                    'value' => $barem->getId(),
                    'label' => $barem->getName()
                ];
            }
        } catch (Exception $e) {
            $this->options = [];
        }
    }

}