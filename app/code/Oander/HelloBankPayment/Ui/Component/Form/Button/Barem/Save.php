<?php
namespace Oander\HelloBankPayment\Ui\Component\Form\Button\Barem;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Oander\HelloBankPayment\Enum\Request;

class Save extends GeneralButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save Barem'),
            'class'          => 'save primary',
            'on_click'       => sprintf("location.href = '%s';", $this->getUrl(Request::ACTION_BAREM_FORM_SAVE)),
            'data_attribute' => [
                'mage-init'  => ['button' => ['event' => 'save']],
                'form-role'  => 'save'
            ],
            'sort_order'     => 90
        ];
    }
}