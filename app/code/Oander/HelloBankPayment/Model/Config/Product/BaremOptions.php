<?php
namespace Oander\HelloBankPayment\Model\Config\Product;

use Exception;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Oander\HelloBankPayment\Model\ResourceModel\Barems\Collection;

class BaremOptions extends AbstractSource
{
    /**
     * @var Collection
     */
    private $baremCollection;

    /**
     * BaremOptions constructor.
     * @param Collection $baremCollection
     */
    public function __construct(
        Collection $baremCollection
    ) {
        $this->baremCollection = $baremCollection;
    }

    /**
     * @var array|null
     */
    private $options;

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        if ($this->options === null) {
            try {
                $baremCollection = $this->baremCollection->AddFillterAvailableBarems();

                foreach ($baremCollection as $barem) {
                    $this->options[] = [
                        'value' => $barem->getId(),
                        'label' => $barem->getName()
                    ];
                }
            } catch (Exception $e) {
                $this->options = [];
            }
        }
        return $this->options;
    }
}