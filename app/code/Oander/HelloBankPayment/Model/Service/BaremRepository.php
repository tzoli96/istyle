<?php
namespace Oander\HelloBankPayment\Model\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Model\Barems;
use Oander\HelloBankPayment\Model\BaremsFactory;

class BaremRepository implements BaremRepositoryInterface
{
    /**
     * @var Barems
     */
    private $baremsFactory;

    public function __construct(
        BaremsFactory $baremsFactory
    ) {
        $this->baremsFactory = $baremsFactory;
    }

    public function getById(int $baremId)
    {
        /** @var Barems $barem */
        $barem = $this->baremsFactory->create();

        $barem->getResource()->load(
            $barem,
            $baremId,
            BaremInterface::ID
        );

        if ($barem->getId() <= 0) {
            new NoSuchEntityException();
        }

        return $barem;
    }

    /**
     * @param int $baremId
     *
     * @return BaremInterface
     * @throws NoSuchEntityException
     */
    public function get(int $baremId): BaremInterface
    {
        /** @var Barems $barem */
        $barem = $this->baremsFactory->create();

        $barem->getResource()->load(
            $barem,
            $baremId,
            BaremInterface::ID
        );

        if ($barem->get() <= 0) {
            throw new NoSuchEntityException(
                __('Barem with %1 ID does not exist', $baremId)
            );
        }

        return $barem;
    }

    public function delete(int $baremId)
    {
        /** @var Barems $barem */
        $barem = $this->getById($baremId);
        $barem->getResource()->delete($barem);
    }

    public function save(BaremInterface $barem): BaremInterface
    {
        $barem->getResource()->save($barem);
        return $barem;
    }

    public function updateStatus(int $baremId, int $status)
    {
        /** @var Barems $barem */
        $barem = $this->get($baremId);
        $barem->setStatus($status);

        return $barem->getResource()->save($barem);
    }

    public function create(): BaremInterface
    {
       return $this->baremsFactory->create();
    }
}