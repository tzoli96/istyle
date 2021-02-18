<?php
namespace Oander\HelloBankPayment\Controller\Adminhtml\MassAction;

use Oander\HelloBankPayment\Model\ResourceModel\Barems\CollectionFactory;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractMass extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var BaremRepositoryInterface
     */
    protected $baremRepository;

    /**
     * AbstractMass constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BaremRepositoryInterface $baremRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BaremRepositoryInterface $baremRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->baremRepository = $baremRepository;
        parent::__construct($context);
    }
}