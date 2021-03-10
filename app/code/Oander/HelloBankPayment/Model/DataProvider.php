<?php
namespace Oander\HelloBankPayment\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Oander\HelloBankPayment\Api\Data\BaremRepositoryInterface;
use Oander\HelloBankPayment\Model\Barems;
use Oander\HelloBankPayment\Model\BaremsFactory;

class DataProvider extends  AbstractDataProvider
{
    protected $loadedData;

    /**
     * @var BaremRepositoryInterface
     */
    protected $baremRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        BaremsFactory $baremsFactory,
        BaremRepositoryInterface $baremRepository
    ) {
        $this->baremRepository = $baremRepository;
        $this->collection = $baremsFactory->create()->getCollection();

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->meta = $this->prepareMeta($this->meta);
    }

    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $barems = $this->collection->getItems();

        /** @var Barems $barems */
        foreach ($barems as $barem) {

            $baremData = $this->baremRepository
                ->getById($barem->getId())
                ->getData();

            $this->loadedData[$barem->getId()] = $baremData;
        }
        return $this->loadedData;
    }

}