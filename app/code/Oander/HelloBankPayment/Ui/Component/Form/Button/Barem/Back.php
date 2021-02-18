<?php
namespace Oander\HelloBankPayment\Ui\Component\Form\Button\Barem;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Back extends GeneralButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'       => __('Back'),
            'on_click'    => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'class'       => 'back',
            'sort_order'  => 10
        ];
    }
}