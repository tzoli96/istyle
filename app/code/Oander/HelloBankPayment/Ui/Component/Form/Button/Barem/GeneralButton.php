<?php
namespace Oander\HelloBankPayment\Ui\Component\Form\Button\Barem;

use Magento\Backend\Block\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Api\Data\BaremInterface;

abstract class GeneralButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BaremRepositoryInterface
     */
    protected $baremRepository;

    /**
     * GeneralButton constructor.
     * @param Context $context
     * @param BaremRepositoryInterface $baremRepository
     */
    public function __construct(
        Context $context,
        BaremRepositoryInterface $baremRepository
    ) {
        $this->context = $context;
        $this->baremRepository = $baremRepository;
    }

    protected function getBaremId()
    {
        try {
            $baremId = $this->context->getRequest()->getParam(BaremInterface::ID, 0);
            $barem = $this->baremRepository->getById($baremId);

            return $barem->getId();
        } catch (NoSuchEntityException $exception) {
            return 0;
        }
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl(
            $route,
            $params
        );
    }

}