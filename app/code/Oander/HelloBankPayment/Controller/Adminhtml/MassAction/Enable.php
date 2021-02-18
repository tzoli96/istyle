<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\MassAction;

use Magento\Framework\Controller\ResultFactory;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Enum\Request;

class Enable extends AbstractMass
{
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $item) {
            $this->baremRepository->updateStatus(
                $item->getId(),
                BaremInterface::STATUS_ENABLED
            );
        }

        $this->messageManager->addSuccessMessage(__('%1 Barem(s) has enabled', $collection->getSize()));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(Request::ACTION_BAREM_GRID_INDEX);
    }
}